<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentStatusHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * @var array<string, list<string>>
     */
    private const ORDER_TRANSITIONS = [
        Order::STATUS_PENDING => [Order::STATUS_CONFIRMED, Order::STATUS_CANCELLED],
        Order::STATUS_CONFIRMED => [Order::STATUS_PACKED, Order::STATUS_CANCELLED],
        Order::STATUS_PACKED => [Order::STATUS_SHIPPED, Order::STATUS_CANCELLED],
        Order::STATUS_SHIPPED => [Order::STATUS_DELIVERED, Order::STATUS_DELIVERY_FAILED],
        Order::STATUS_DELIVERY_FAILED => [Order::STATUS_CANCELLED, Order::STATUS_SHIPPED],
        Order::STATUS_DELIVERED => [],
        Order::STATUS_CANCELLED => [],
    ];

    /**
     * @var array<string, list<string>>
     */
    private const PAYMENT_TRANSITIONS = [
        Payment::STATUS_PENDING => [Payment::STATUS_SUCCESS, Payment::STATUS_FAILED],
        Payment::STATUS_SUCCESS => [Payment::STATUS_REFUNDED],
        Payment::STATUS_FAILED => [],
        Payment::STATUS_REFUNDED => [],
    ];

    /**
     * @var array<string, array{status:string, success_message:string}>
     */
    private const BULK_ACTIONS = [
        'CONFIRM' => [
            'status' => Order::STATUS_CONFIRMED,
            'success_message' => 'Đã xác nhận đơn và trừ kho.',
        ],
        'PACK' => [
            'status' => Order::STATUS_PACKED,
            'success_message' => 'Đã chuyển đơn sang trạng thái đã đóng gói.',
        ],
        'SHIP' => [
            'status' => Order::STATUS_SHIPPED,
            'success_message' => 'Đã bàn giao đơn cho vận chuyển.',
        ],
        'DELIVER' => [
            'status' => Order::STATUS_DELIVERED,
            'success_message' => 'Đã đánh dấu giao hàng thành công.',
        ],
        'MARK_DELIVERY_FAILED' => [
            'status' => Order::STATUS_DELIVERY_FAILED,
            'success_message' => 'Đã đánh dấu giao hàng thất bại.',
        ],
        'CANCEL' => [
            'status' => Order::STATUS_CANCELLED,
            'success_message' => 'Đã hủy đơn hàng.',
        ],
        'RESHIP' => [
            'status' => Order::STATUS_SHIPPED,
            'success_message' => 'Đã chuyển đơn sang trạng thái giao lại.',
        ],
    ];

    public function checkout(User $user, array $attributes): Order
    {
        return DB::transaction(function () use ($user, $attributes): Order {
            $cart = Cart::query()
                ->where('user_id', $user->id)
                ->where('status', Cart::STATUS_ACTIVE)
                ->lockForUpdate()
                ->first();

            if (! $cart) {
                throw ValidationException::withMessages([
                    'cart' => ['Gio hang dang trong.'],
                ]);
            }

            $cartItems = CartItem::query()
                ->where('cart_id', $cart->id)
                ->with('product')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => ['Gio hang dang trong.'],
                ]);
            }

            $products = Product::query()
                ->whereIn('id', $cartItems->pluck('product_id')->all())
                ->available()
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0.0;

            foreach ($cartItems as $cartItem) {
                $product = $products->get($cartItem->product_id);

                if (! $product || ! $product->is_active || $product->is_deleted) {
                    throw ValidationException::withMessages([
                        'cart' => ['Một hoặc nhiều sản phẩm trong giỏ hiện không còn khả dụng.'],
                    ]);
                }

                if ($product->stock_quantity < $cartItem->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => [
                            "Sản phẩm {$product->name} chỉ còn {$product->stock_quantity}, không đủ cho số lượng {$cartItem->quantity}.",
                        ],
                    ]);
                }

                $unitPrice = (float) $product->sale_price;
                $lineTotal = $this->lineTotal($cartItem->quantity, $unitPrice);

                $cartItem->update([
                    'unit_price' => $this->decimal($unitPrice),
                    'line_total' => $this->decimal($lineTotal),
                ]);

                $subtotal += $lineTotal;
            }

            $shippingFee = $this->calculateShippingFee($subtotal, (string) $attributes['shipping_address']);
            $discountAmount = 0.0;
            $totalAmount = max(0, $subtotal + $shippingFee - $discountAmount);
            $paymentMethod = $attributes['payment_method'] ?? Order::PAYMENT_METHOD_COD;
            $orderNo = 'ORD-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);
            $transactionCode = 'PAY-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);

            $order = Order::query()->create([
                'user_id' => $user->id,
                'order_no' => $orderNo,
                'recipient_name' => $attributes['recipient_name'],
                'recipient_phone' => $attributes['recipient_phone'],
                'shipping_address' => $attributes['shipping_address'],
                'payment_method' => $paymentMethod,
                'status' => Order::STATUS_PENDING,
                'subtotal' => $this->decimal($subtotal),
                'shipping_fee' => $this->decimal($shippingFee),
                'discount_amount' => $this->decimal($discountAmount),
                'total_amount' => $this->decimal($totalAmount),
                'stock_deducted' => false,
                'note' => ! empty($attributes['note']) ? $attributes['note'] : null,
            ]);

            foreach ($cartItems as $cartItem) {
                $product = $products->get($cartItem->product_id);
                $unitPrice = (float) $product->sale_price;
                $lineTotal = $this->lineTotal($cartItem->quantity, $unitPrice);

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name_snapshot' => $product->name,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $this->decimal($unitPrice),
                    'line_total' => $this->decimal($lineTotal),
                ]);
            }

            [$gatewayName, $gatewayReference, $paymentStatus, $rawPayload] = $this->paymentMetadata(
                $paymentMethod,
                $transactionCode,
                $orderNo,
                $totalAmount,
            );

            $payment = Payment::query()->create([
                'order_id' => $order->id,
                'transaction_code' => $transactionCode,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'amount' => $this->decimal($totalAmount),
                'gateway_name' => $gatewayName,
                'gateway_reference' => $gatewayReference,
                'paid_at' => null,
                'raw_payload' => $rawPayload,
            ]);

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'changed_by_user_id' => $user->id,
                'from_status' => null,
                'to_status' => Order::STATUS_PENDING,
                'note' => sprintf('Don hang duoc tao voi phuong thuc thanh toan %s.', $paymentMethod),
                'changed_at' => now(),
            ]);

            PaymentStatusHistory::query()->create([
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'changed_by_user_id' => $user->id,
                'from_status' => null,
                'to_status' => Payment::STATUS_PENDING,
                'note' => 'Khoi tao trang thai thanh toan khi khach dat hang.',
                'changed_at' => now(),
            ]);

            CartItem::query()->where('cart_id', $cart->id)->delete();
            $cart->update(['status' => Cart::STATUS_CHECKED_OUT]);

            return $this->findUserOrder($user, $order->id) ?? $order;
        });
    }

    public function listUserOrders(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Order::query()
            ->where('user_id', $user->id)
            ->with(['items', 'payment'])
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findUserOrder(User $user, int $orderId): ?Order
    {
        return Order::query()
            ->where('user_id', $user->id)
            ->where('id', $orderId)
            ->with($this->orderRelations())
            ->first();
    }

    public function listAdminOrders(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::query()
            ->with(['user', 'items', 'payment'])
            ->orderByDesc('id');

        if (! empty($filters['status'])) {
            $query->where('status', (string) $filters['status']);
        }

        if (! empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);

            $query->where(function ($builder) use ($keyword): void {
                $builder->where('order_no', 'like', "%{$keyword}%")
                    ->orWhere('recipient_name', 'like', "%{$keyword}%")
                    ->orWhere('recipient_phone', 'like', "%{$keyword}%")
                    ->orWhere('shipping_address', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($userQuery) use ($keyword): void {
                        $userQuery->where('full_name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    });
            });
        }

        return $query->paginate($perPage);
    }

    public function findAdminOrder(int $orderId): ?Order
    {
        return Order::query()
            ->where('id', $orderId)
            ->with($this->orderRelations())
            ->first();
    }

    public function confirmBankTransferSubmitted(Order $order, User $actor, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $actor, $note): Order {
            $payment = Payment::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->payment_method !== Order::PAYMENT_METHOD_BANK_TRANSFER) {
                throw ValidationException::withMessages([
                    'payment_method' => ['Chi don chuyen khoan moi co the xac nhan da chuyen tien.'],
                ]);
            }

            if ($payment->payment_status !== Payment::STATUS_PENDING) {
                throw ValidationException::withMessages([
                    'payment_status' => ['Don chuyen khoan nay khong con o trang thai cho xac nhan.'],
                ]);
            }

            $rawPayload = is_array($payment->raw_payload) ? $payment->raw_payload : [];
            $rawPayload['customer_transfer_submitted'] = true;
            $rawPayload['customer_transfer_submitted_at'] = now()->toISOString();
            $rawPayload['customer_transfer_note'] = $note;

            $payment->update([
                'raw_payload' => $rawPayload,
            ]);

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'changed_by_user_id' => $actor->id,
                'from_status' => $order->status,
                'to_status' => $order->status,
                'note' => $note
                    ? "Khach da bao da chuyen khoan. Ghi chu: {$note}"
                    : 'Khach da bao da chuyen khoan va dang cho admin xac nhan.',
                'changed_at' => now(),
            ]);

            return $this->findUserOrder($actor, $order->id) ?? $order->fresh($this->orderRelations());
        });
    }

    public function confirmDeliveredByCustomer(Order $order, User $actor): Order
    {
        if ($order->status !== Order::STATUS_SHIPPED) {
            throw ValidationException::withMessages([
                'status' => ['Chi don dang giao moi co the xac nhan da nhan hang.'],
            ]);
        }

        return $this->updateOrderStatus(
            $order,
            Order::STATUS_DELIVERED,
            $actor,
            'Khach hang da xac nhan nhan duoc hang.',
        );
    }

    public function cancelOrderByCustomer(Order $order, User $actor, string $reason, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $actor, $reason, $note): Order {
            $lockedOrder = Order::query()
                ->where('id', $order->id)
                ->with(['items', 'payment'])
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($lockedOrder->status, [Order::STATUS_PENDING, Order::STATUS_CONFIRMED], true)) {
                throw ValidationException::withMessages([
                    'status' => [$this->customerCancellationMessage($lockedOrder)],
                ]);
            }

            $this->applyCancellation(
                $lockedOrder,
                $actor,
                $this->buildCustomerCancellationNote($reason, $note),
                $lockedOrder->stock_deducted,
                true,
            );

            return $this->findUserOrder($actor, $lockedOrder->id) ?? $lockedOrder->fresh($this->orderRelations());
        });
    }

    public function updateOrderStatus(
        Order $order,
        string $nextStatus,
        User $actor,
        ?string $note = null,
        array $options = []
    ): Order
    {
        return DB::transaction(function () use ($order, $nextStatus, $actor, $note, $options): Order {
            $lockedOrder = Order::query()
                ->where('id', $order->id)
                ->with(['items', 'payment'])
                ->lockForUpdate()
                ->firstOrFail();

            $currentStatus = $lockedOrder->status;

            if ($currentStatus === $nextStatus) {
                return $this->findOrderForActor($lockedOrder, $actor) ?? $lockedOrder;
            }

            $this->ensureOrderTransitionIsAllowed($lockedOrder, $nextStatus);

            if ($nextStatus === Order::STATUS_CONFIRMED) {
                $this->ensureOrderCanBeConfirmed($lockedOrder);
                $this->deductStockIfNeeded($lockedOrder);
            }

            if ($nextStatus === Order::STATUS_DELIVERED) {
                $this->ensureOrderCanBeDelivered($lockedOrder);
            }

            if ($nextStatus === Order::STATUS_CANCELLED) {
                $this->cancelOrderByAdmin($lockedOrder, $actor, $currentStatus, $note, $options);

                return $this->findOrderForActor($lockedOrder, $actor) ?? $lockedOrder->fresh($this->orderRelations());
            }

            $updatePayload = ['status' => $nextStatus];

            if ($nextStatus === Order::STATUS_SHIPPED) {
                $updatePayload['shipping_code'] = $lockedOrder->shipping_code ?: $this->generateShippingCode();
                $updatePayload['shipping_carrier'] = $lockedOrder->shipping_carrier ?: 'Giao hang mo phong';
                $updatePayload['shipped_at'] = now();
            }

            if ($nextStatus === Order::STATUS_DELIVERED) {
                $updatePayload['delivered_at'] = now();
            }

            if ($nextStatus === Order::STATUS_CANCELLED) {
                $updatePayload['cancelled_at'] = now();
            }

            $lockedOrder->update($updatePayload);

            OrderStatusHistory::query()->create([
                'order_id' => $lockedOrder->id,
                'changed_by_user_id' => $actor->id,
                'from_status' => $currentStatus,
                'to_status' => $nextStatus,
                'note' => $note,
                'changed_at' => now(),
            ]);

            if ($nextStatus === Order::STATUS_DELIVERED && $lockedOrder->payment_method === Order::PAYMENT_METHOD_COD) {
                $this->markPaymentSuccessForCod($lockedOrder, $actor);
            }

            $this->notifyCustomerAboutOrderStatus($lockedOrder, $currentStatus, $nextStatus);

            return $this->findOrderForActor($lockedOrder, $actor) ?? $lockedOrder->fresh($this->orderRelations());
        });
    }

    public function updatePaymentStatus(
        Order $order,
        string $nextStatus,
        User $actor,
        ?string $note = null
    ): Order {
        return DB::transaction(function () use ($order, $nextStatus, $actor, $note): Order {
            $lockedOrder = Order::query()
                ->where('id', $order->id)
                ->with('payment')
                ->lockForUpdate()
                ->firstOrFail();

            $payment = Payment::query()
                ->where('order_id', $lockedOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            $currentStatus = $payment->payment_status;

            if ($currentStatus === $nextStatus) {
                return $this->findAdminOrder($lockedOrder->id) ?? $lockedOrder;
            }

            $this->ensurePaymentTransitionIsAllowed($payment, $lockedOrder, $nextStatus);

            $payment->update([
                'payment_status' => $nextStatus,
                'paid_at' => $nextStatus === Payment::STATUS_SUCCESS ? now() : $payment->paid_at,
            ]);

            PaymentStatusHistory::query()->create([
                'payment_id' => $payment->id,
                'order_id' => $lockedOrder->id,
                'changed_by_user_id' => $actor->id,
                'from_status' => $currentStatus,
                'to_status' => $nextStatus,
                'note' => $note ?: sprintf('Cap nhat trang thai thanh toan sang %s.', $nextStatus),
                'changed_at' => now(),
            ]);

            $this->notifyCustomerAboutPaymentStatus($lockedOrder, $currentStatus, $nextStatus);

            return $this->findAdminOrder($lockedOrder->id) ?? $lockedOrder->fresh($this->orderRelations());
        });
    }

    /**
     * @param list<int> $orderIds
     * @return array<string, mixed>
     */
    public function bulkUpdateStatuses(User $actor, array $orderIds, string $action, ?string $note = null): array
    {
        $actionConfig = self::BULK_ACTIONS[$action] ?? null;

        if (! $actionConfig) {
            throw ValidationException::withMessages([
                'action' => ['Thao tác hàng loạt không hợp lệ.'],
            ]);
        }

        $results = [];
        $successCount = 0;
        $failedCount = 0;

        foreach ($orderIds as $orderId) {
            $order = $this->findAdminOrder($orderId);

            if (! $order) {
                $results[] = [
                    'orderId' => $orderId,
                    'orderNo' => null,
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng.',
                ];
                $failedCount++;
                continue;
            }

            try {
                $options = [];
                $actionNote = $note;

                if ($action === 'CANCEL' && $order->status === Order::STATUS_DELIVERY_FAILED) {
                    $options['restock_inventory'] = false;
                    $actionNote = $note ?: 'Giao thất bại, hàng không đủ điều kiện nhập lại kho.';
                }

                $this->updateOrderStatus(
                    $order,
                    $actionConfig['status'],
                    $actor,
                    $actionNote,
                    $options,
                );

                $results[] = [
                    'orderId' => $order->id,
                    'orderNo' => $order->order_no,
                    'success' => true,
                    'message' => $actionConfig['success_message'],
                ];
                $successCount++;
            } catch (ValidationException $exception) {
                $results[] = [
                    'orderId' => $order->id,
                    'orderNo' => $order->order_no,
                    'success' => false,
                    'message' => $this->validationMessage($exception),
                ];
                $failedCount++;
            } catch (\Throwable $exception) {
                $results[] = [
                    'orderId' => $order->id,
                    'orderNo' => $order->order_no,
                    'success' => false,
                    'message' => 'Không thể xử lý đơn hàng này lúc này.',
                ];
                $failedCount++;
            }
        }

        return [
            'total' => count($orderIds),
            'success' => $successCount,
            'failed' => $failedCount,
            'results' => $results,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function orderSummaryPayload(Order $order): array
    {
        $order->loadMissing(['user', 'items', 'payment']);

        return [
            'id' => $order->id,
            'order_no' => $order->order_no,
            'payment_method' => $order->payment_method,
            'status' => $order->status,
            'subtotal' => $order->subtotal,
            'shipping_fee' => $order->shipping_fee,
            'discount_amount' => $order->discount_amount,
            'total_amount' => $order->total_amount,
            'stock_deducted' => (bool) $order->stock_deducted,
            'stock_deducted_at' => $order->stock_deducted_at,
            'shipping_carrier' => $order->shipping_carrier,
            'shipping_code' => $order->shipping_code,
            'shipped_at' => $order->shipped_at,
            'delivered_at' => $order->delivered_at,
            'cancelled_at' => $order->cancelled_at,
            'item_count' => $order->items->count(),
            'customer' => $order->user ? $this->customerPayload($order->user) : null,
            'payment' => $order->payment ? $this->paymentPayload($order->payment) : null,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function orderDetailPayload(Order $order): array
    {
        $order->loadMissing($this->orderRelations());

        return [
            'id' => $order->id,
            'order_no' => $order->order_no,
            'recipient_name' => $order->recipient_name,
            'recipient_phone' => $order->recipient_phone,
            'shipping_address' => $order->shipping_address,
            'payment_method' => $order->payment_method,
            'status' => $order->status,
            'subtotal' => $order->subtotal,
            'shipping_fee' => $order->shipping_fee,
            'discount_amount' => $order->discount_amount,
            'total_amount' => $order->total_amount,
            'stock_deducted' => (bool) $order->stock_deducted,
            'stock_deducted_at' => $order->stock_deducted_at,
            'shipping_carrier' => $order->shipping_carrier,
            'shipping_code' => $order->shipping_code,
            'shipped_at' => $order->shipped_at,
            'delivered_at' => $order->delivered_at,
            'cancelled_at' => $order->cancelled_at,
            'note' => $order->note,
            'item_count' => $order->items->count(),
            'allowed_next_statuses' => $this->allowedNextStatuses($order),
            'allowed_payment_statuses' => $order->payment
                ? $this->allowedNextPaymentStatuses($order->payment, $order)
                : [],
            'customer' => $order->user ? $this->customerPayload($order->user) : null,
            'items' => $order->items->map(fn (OrderItem $item): array => $this->orderItemPayload($item))->values()->all(),
            'payment' => $order->payment ? $this->paymentPayload($order->payment) : null,
            'status_history' => $order->statusHistory->map(
                fn (OrderStatusHistory $history): array => $this->statusHistoryPayload($history)
            )->values()->all(),
            'payment_status_history' => $order->paymentStatusHistory->map(
                fn (PaymentStatusHistory $history): array => $this->paymentStatusHistoryPayload($history)
            )->values()->all(),
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function customerPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'is_active' => (bool) $user->is_active,
            'is_deleted' => (bool) $user->is_deleted,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function orderItemPayload(OrderItem $item): array
    {
        return [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'product_name_snapshot' => $item->product_name_snapshot,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'line_total' => $item->line_total,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function paymentPayload(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'transaction_code' => $payment->transaction_code,
            'payment_method' => $payment->payment_method,
            'payment_status' => $payment->payment_status,
            'amount' => $payment->amount,
            'gateway_name' => $payment->gateway_name,
            'gateway_reference' => $payment->gateway_reference,
            'paid_at' => $payment->paid_at,
            'raw_payload' => $payment->raw_payload,
            'created_at' => $payment->created_at,
            'updated_at' => $payment->updated_at,
        ];
    }

    /**
     * @return array{0:string|null,1:string|null,2:string,3:array<string,mixed>|null}
     */
    private function paymentMetadata(
        string $paymentMethod,
        string $transactionCode,
        string $orderNo,
        float $totalAmount
    ): array {
        if ($paymentMethod === Order::PAYMENT_METHOD_BANK_TRANSFER) {
            return [
                'Manual bank transfer',
                'BANK-' . $transactionCode,
                Payment::STATUS_PENDING,
                [
                    'instructions' => 'Chuyen khoan dung so tien don hang va cho admin xac nhan.',
                    'bank_name' => 'MB Bank',
                    'account_name' => 'HERITAGE HARVEST',
                    'account_number' => '0123456789',
                    'amount' => $this->decimal($totalAmount),
                    'transfer_content' => $orderNo,
                    'customer_transfer_submitted' => false,
                    'customer_transfer_submitted_at' => null,
                    'customer_transfer_note' => null,
                ],
            ];
        }

        return [
            null,
            null,
            Payment::STATUS_PENDING,
            [
                'instructions' => 'Thanh toan tien mat khi don hang duoc giao thanh cong.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function statusHistoryPayload(OrderStatusHistory $history): array
    {
        return [
            'id' => $history->id,
            'changed_by_user_id' => $history->changed_by_user_id,
            'from_status' => $history->from_status,
            'to_status' => $history->to_status,
            'note' => $history->note,
            'changed_at' => $history->changed_at,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function paymentStatusHistoryPayload(PaymentStatusHistory $history): array
    {
        return [
            'id' => $history->id,
            'payment_id' => $history->payment_id,
            'order_id' => $history->order_id,
            'changed_by_user_id' => $history->changed_by_user_id,
            'from_status' => $history->from_status,
            'to_status' => $history->to_status,
            'note' => $history->note,
            'changed_at' => $history->changed_at,
        ];
    }

    /**
     * @return list<string>
     */
    public function allowedNextStatuses(Order $order): array
    {
        return self::ORDER_TRANSITIONS[$order->status] ?? [];
    }

    /**
     * @return list<string>
     */
    public function allowedNextPaymentStatuses(Payment $payment, Order $order): array
    {
        $allowed = self::PAYMENT_TRANSITIONS[$payment->payment_status] ?? [];

        if (
            $payment->payment_status === Payment::STATUS_SUCCESS
            && ! in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_DELIVERY_FAILED], true)
        ) {
            return [];
        }

        return $allowed;
    }

    private function ensureOrderTransitionIsAllowed(Order $order, string $nextStatus): void
    {
        if (! in_array($nextStatus, $this->allowedNextStatuses($order), true)) {
            throw ValidationException::withMessages([
                'status' => ['Khong the chuyen don hang sang trang thai da chon.'],
            ]);
        }
    }

    private function ensurePaymentTransitionIsAllowed(Payment $payment, Order $order, string $nextStatus): void
    {
        if (! in_array($nextStatus, $this->allowedNextPaymentStatuses($payment, $order), true)) {
            throw ValidationException::withMessages([
                'payment_status' => ['Khong the chuyen trang thai thanh toan sang gia tri da chon.'],
            ]);
        }

        if (
            $nextStatus === Payment::STATUS_REFUNDED
            && ! in_array($order->status, [Order::STATUS_CANCELLED, Order::STATUS_DELIVERY_FAILED], true)
        ) {
            throw ValidationException::withMessages([
                'payment_status' => ['Chi duoc refund khi don da bi huy hoac giao hang that bai.'],
            ]);
        }
    }

    private function ensureOrderCanBeConfirmed(Order $order): void
    {
        $payment = $order->payment;

        if (! $payment) {
            throw ValidationException::withMessages([
                'payment' => ['Don hang chua co ban ghi thanh toan.'],
            ]);
        }

        if ($payment->payment_status === Payment::STATUS_FAILED) {
            throw ValidationException::withMessages([
                'payment_status' => ['Khong the xac nhan don vi thanh toan da that bai.'],
            ]);
        }

        if (
            $order->payment_method === Order::PAYMENT_METHOD_BANK_TRANSFER
            && $payment->payment_status !== Payment::STATUS_SUCCESS
        ) {
            throw ValidationException::withMessages([
                'payment_status' => ['Don chuyen khoan can xac nhan thanh toan truoc khi xac nhan don.'],
            ]);
        }
    }

    private function ensureOrderCanBeDelivered(Order $order): void
    {
        $payment = $order->payment;

        if (! $payment) {
            throw ValidationException::withMessages([
                'payment' => ['Đơn hàng chưa có bản ghi thanh toán.'],
            ]);
        }

        if (
            $order->payment_method === Order::PAYMENT_METHOD_BANK_TRANSFER
            && $payment->payment_status !== Payment::STATUS_SUCCESS
        ) {
            throw ValidationException::withMessages([
                'payment_status' => ['Đơn chuyển khoản chỉ được đánh dấu giao thành công khi đã xác nhận thanh toán.'],
            ]);
        }
    }

    private function deductStockIfNeeded(Order $order): void
    {
        if ($order->stock_deducted) {
            return;
        }

        $products = Product::query()
            ->whereIn('id', $order->items->pluck('product_id')->all())
            ->available()
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($order->items as $item) {
            $product = $products->get($item->product_id);

            if (! $product || ! $product->is_active || $product->is_deleted || $product->stock_quantity < $item->quantity) {
                throw ValidationException::withMessages([
                    'stock' => [
                        "Không đủ tồn kho để xác nhận đơn hàng. Sản phẩm {$item->product_name_snapshot} chỉ còn "
                        . ($product?->stock_quantity ?? 0)
                        . ", đơn cần {$item->quantity}.",
                    ],
                ]);
            }
        }

        foreach ($order->items as $item) {
            $products->get($item->product_id)?->decrement('stock_quantity', $item->quantity);
        }

        $order->update([
            'stock_deducted' => true,
            'stock_deducted_at' => now(),
        ]);
    }

    private function restoreStock(Order $order): void
    {
        if (! $order->stock_deducted) {
            return;
        }

        $products = Product::query()
            ->whereIn('id', $order->items->pluck('product_id')->all())
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($order->items as $item) {
            $products->get($item->product_id)?->increment('stock_quantity', $item->quantity);
        }

        $order->update([
            'stock_deducted' => false,
            'stock_deducted_at' => null,
        ]);
    }

    private function cancelOrderByAdmin(
        Order $order,
        User $actor,
        string $currentStatus,
        ?string $note,
        array $options
    ): void {
        if ($currentStatus === Order::STATUS_DELIVERED) {
            throw ValidationException::withMessages([
                'status' => ['Không thể hủy đơn đã giao thành công.'],
            ]);
        }

        if ($currentStatus === Order::STATUS_DELIVERY_FAILED) {
            $restockInventory = $options['restock_inventory'] ?? null;

            if (! is_bool($restockInventory)) {
                throw ValidationException::withMessages([
                    'restock_inventory' => ['Đơn giao thất bại cần chọn nhập lại kho hoặc không nhập lại kho trước khi hủy.'],
                ]);
            }

            $this->applyCancellation(
                $order,
                $actor,
                $note ?: (
                    $restockInventory
                        ? 'Giao thất bại, hàng hoàn về còn bán được, đã nhập lại kho.'
                        : 'Giao thất bại, hàng không đủ điều kiện nhập lại kho.'
                ),
                $restockInventory,
                false,
            );

            return;
        }

        $shouldRestock = $order->stock_deducted
            && in_array($currentStatus, [Order::STATUS_CONFIRMED, Order::STATUS_PACKED], true);

        $this->applyCancellation($order, $actor, $note, $shouldRestock, false);
    }

    private function applyCancellation(
        Order $order,
        User $actor,
        ?string $note,
        bool $shouldRestock,
        bool $autoRefundBankTransfer
    ): void {
        $currentStatus = $order->status;

        if ($shouldRestock) {
            $this->restoreStock($order);
        }

        $order->update([
            'status' => Order::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'changed_by_user_id' => $actor->id,
            'from_status' => $currentStatus,
            'to_status' => Order::STATUS_CANCELLED,
            'note' => $note,
            'changed_at' => now(),
        ]);

        if ($autoRefundBankTransfer) {
            $this->markPaymentRefundedIfEligible($order, $actor);
        }
    }

    private function markPaymentRefundedIfEligible(Order $order, User $actor): void
    {
        if ($order->payment_method !== Order::PAYMENT_METHOD_BANK_TRANSFER) {
            return;
        }

        $payment = Payment::query()
            ->where('order_id', $order->id)
            ->lockForUpdate()
            ->first();

        if (! $payment || $payment->payment_status !== Payment::STATUS_SUCCESS) {
            return;
        }

        $payment->update([
            'payment_status' => Payment::STATUS_REFUNDED,
        ]);

        PaymentStatusHistory::query()->create([
            'payment_id' => $payment->id,
            'order_id' => $order->id,
            'changed_by_user_id' => $actor->id,
            'from_status' => Payment::STATUS_SUCCESS,
            'to_status' => Payment::STATUS_REFUNDED,
            'note' => 'Khách hủy đơn trước khi giao, admin xử lý hoàn tiền thủ công.',
            'changed_at' => now(),
        ]);
    }

    private function customerCancellationMessage(Order $order): string
    {
        return match ($order->status) {
            Order::STATUS_PACKED, Order::STATUS_SHIPPED =>
                'Đơn hàng đã được đóng gói hoặc đang giao, bạn không thể tự hủy. Vui lòng liên hệ hỗ trợ nếu cần xử lý.',
            Order::STATUS_DELIVERED => 'Không thể hủy đơn đã giao thành công.',
            Order::STATUS_DELIVERY_FAILED => 'Đơn giao thất bại đang chờ admin xử lý, bạn không thể tự hủy.',
            Order::STATUS_CANCELLED => 'Đơn hàng này đã được hủy trước đó.',
            default => 'Đơn hàng hiện không thể tự hủy.',
        };
    }

    private function buildCustomerCancellationNote(string $reason, ?string $note): string
    {
        $baseNote = 'Khách hàng hủy đơn. Lý do: ' . $reason . '.';

        if (! $note) {
            return $baseNote;
        }

        return $baseNote . ' Ghi chú: ' . $note;
    }

    private function validationMessage(ValidationException $exception): string
    {
        $errors = $exception->errors();
        $firstErrorGroup = reset($errors);

        if (is_array($firstErrorGroup) && isset($firstErrorGroup[0]) && is_string($firstErrorGroup[0])) {
            return $firstErrorGroup[0];
        }

        return 'Dữ liệu cập nhật không hợp lệ.';
    }

    private function notifyCustomerAboutOrderStatus(Order $order, string $fromStatus, string $toStatus): void
    {
        if (! $order->user_id) {
            return;
        }

        Notification::query()->create([
            'user_id' => $order->user_id,
            'title' => 'Cập nhật đơn hàng ' . $order->order_no,
            'message' => sprintf(
                'Đơn hàng %s đã chuyển từ %s sang %s.',
                $order->order_no,
                $this->statusLabel($fromStatus),
                $this->statusLabel($toStatus),
            ),
            'channel' => Notification::CHANNEL_SYSTEM,
            'status' => Notification::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    private function notifyCustomerAboutPaymentStatus(Order $order, string $fromStatus, string $toStatus): void
    {
        if (! $order->user_id) {
            return;
        }

        Notification::query()->create([
            'user_id' => $order->user_id,
            'title' => 'Cập nhật thanh toán ' . $order->order_no,
            'message' => sprintf(
                'Thanh toán của đơn %s đã chuyển từ %s sang %s.',
                $order->order_no,
                $this->paymentStatusLabel($fromStatus),
                $this->paymentStatusLabel($toStatus),
            ),
            'channel' => Notification::CHANNEL_SYSTEM,
            'status' => Notification::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            Order::STATUS_PENDING => 'Chờ xác nhận',
            Order::STATUS_CONFIRMED => 'Đã xác nhận',
            Order::STATUS_PACKED => 'Đã đóng gói',
            Order::STATUS_SHIPPED => 'Đang giao',
            Order::STATUS_DELIVERED => 'Đã giao',
            Order::STATUS_DELIVERY_FAILED => 'Giao thất bại',
            Order::STATUS_CANCELLED => 'Đã hủy',
            default => $status,
        };
    }

    private function paymentStatusLabel(string $status): string
    {
        return match ($status) {
            Payment::STATUS_PENDING => 'Chờ thanh toán',
            Payment::STATUS_SUCCESS => 'Thanh toán thành công',
            Payment::STATUS_FAILED => 'Thanh toán thất bại',
            Payment::STATUS_REFUNDED => 'Đã hoàn tiền',
            default => $status,
        };
    }

    private function markPaymentSuccessForCod(Order $order, User $actor): void
    {
        $payment = Payment::query()
            ->where('order_id', $order->id)
            ->lockForUpdate()
            ->first();

        if (! $payment || $payment->payment_status === Payment::STATUS_SUCCESS) {
            return;
        }

        $fromStatus = $payment->payment_status;

        $payment->update([
            'payment_status' => Payment::STATUS_SUCCESS,
            'paid_at' => now(),
        ]);

        PaymentStatusHistory::query()->create([
            'payment_id' => $payment->id,
            'order_id' => $order->id,
            'changed_by_user_id' => $actor->id,
            'from_status' => $fromStatus,
            'to_status' => Payment::STATUS_SUCCESS,
            'note' => 'Tu dong xac nhan thanh toan COD khi don da giao thanh cong.',
            'changed_at' => now(),
        ]);
    }

    private function calculateShippingFee(float $subtotal, string $shippingAddress): float
    {
        if ($subtotal >= 500000) {
            return 0.0;
        }

        $normalizedAddress = $this->normalizeShippingAddress($shippingAddress);

        if (str_contains($normalizedAddress, 'ha noi')) {
            return 20000.0;
        }

        $northernKeywords = [
            'ha giang', 'cao bang', 'bac kan', 'tuyen quang', 'lao cai', 'yen bai',
            'thai nguyen', 'lang son', 'quang ninh', 'bac giang', 'phu tho', 'vinh phuc',
            'bac ninh', 'hai duong', 'hai phong', 'hung yen', 'thai binh', 'ha nam',
            'nam dinh', 'ninh binh', 'hoa binh', 'son la', 'dien bien', 'lai chau',
        ];

        foreach ($northernKeywords as $keyword) {
            if (str_contains($normalizedAddress, $keyword)) {
                return 30000.0;
            }
        }

        return 45000.0;
    }

    private function normalizeText(string $value): string
    {
        $normalized = mb_strtolower(trim($value), 'UTF-8');
        $normalized = str_replace(
            ['à', 'á', 'ạ', 'ả', 'ã', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ',
                'è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ',
                'ì', 'í', 'ị', 'ỉ', 'ĩ',
                'ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ',
                'ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ',
                'ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ',
                'đ'],
            ['a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
                'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e',
                'i', 'i', 'i', 'i', 'i',
                'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
                'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
                'y', 'y', 'y', 'y', 'y',
                'd'],
            $normalized
        );

        return preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
    }

    private function normalizeShippingAddress(string $value): string
    {
        $normalized = Str::of(trim($value))
            ->lower()
            ->ascii()
            ->replaceMatches('/\s+/', ' ')
            ->value();

        return trim($normalized);
    }

    private function generateShippingCode(): string
    {
        return 'SHIP-' . now()->format('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }

    /**
     * @return array<int, mixed>
     */
    private function orderRelations(): array
    {
        return [
            'user',
            'items' => fn ($query) => $query->orderBy('id'),
            'payment',
            'statusHistory' => fn ($query) => $query->orderBy('changed_at'),
            'paymentStatusHistory' => fn ($query) => $query->orderBy('changed_at'),
        ];
    }

    private function findOrderForActor(Order $order, User $actor): ?Order
    {
        if ($actor->role === User::ROLE_ADMIN) {
            return $this->findAdminOrder($order->id);
        }

        return $this->findUserOrder($actor, $order->id);
    }

    private function lineTotal(int $quantity, float $unitPrice): float
    {
        return round($quantity * $unitPrice, 2);
    }

    private function decimal(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}
