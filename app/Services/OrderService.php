<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
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
                    'cart' => ['The cart is empty.'],
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
                    'cart' => ['The cart is empty.'],
                ]);
            }

            $products = Product::query()
                ->whereIn('id', $cartItems->pluck('product_id')->all())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0.0;

            foreach ($cartItems as $cartItem) {
                $product = $products->get($cartItem->product_id);

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => ['One or more cart items are unavailable.'],
                    ]);
                }

                if ($product->stock_quantity < $cartItem->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => ["Insufficient stock for product {$product->name}."],
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

            $shippingFee = 0.0;
            $discountAmount = 0.0;
            $totalAmount = $subtotal + $shippingFee - $discountAmount;
            $paymentMethod = $attributes['payment_method'] ?? Order::PAYMENT_METHOD_COD;
            $paymentGateway = !empty($attributes['payment_gateway'])
                ? trim((string) $attributes['payment_gateway'])
                : null;

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
                'note' => !empty($attributes['note']) ? $attributes['note'] : null,
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

                $product->decrement('stock_quantity', $cartItem->quantity);
            }

            [$gatewayName, $gatewayReference, $paymentStatus, $rawPayload] = $this->paymentMetadata(
                $paymentMethod,
                $paymentGateway,
                $transactionCode,
                $totalAmount
            );

            Payment::query()->create([
                'order_id' => $order->id,
                'transaction_code' => $transactionCode,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'amount' => $this->decimal($totalAmount),
                'gateway_name' => $gatewayName,
                'gateway_reference' => $gatewayReference,
                'paid_at' => $paymentStatus === Payment::STATUS_SUCCESS ? now() : null,
                'raw_payload' => $rawPayload,
            ]);

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'changed_by_user_id' => $user->id,
                'from_status' => null,
                'to_status' => Order::STATUS_PENDING,
                'note' => sprintf('Order created by customer checkout with payment method %s.', $paymentMethod),
                'changed_at' => now(),
            ]);

            CartItem::query()
                ->where('cart_id', $cart->id)
                ->delete();

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
            ->with([
                'user',
                'items' => fn ($query) => $query->orderBy('id'),
                'payment',
                'statusHistory' => fn ($query) => $query->orderBy('changed_at'),
            ])
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
            ->with([
                'user',
                'items' => fn ($query) => $query->orderBy('id'),
                'payment',
                'statusHistory' => fn ($query) => $query->orderBy('changed_at'),
            ])
            ->first();
    }

    public function updateOrderStatus(Order $order, string $nextStatus, User $actor, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $nextStatus, $actor, $note): Order {
            $lockedOrder = Order::query()
                ->where('id', $order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $currentStatus = $lockedOrder->status;

            if ($currentStatus !== $nextStatus) {
                $lockedOrder->update([
                    'status' => $nextStatus,
                ]);

                OrderStatusHistory::query()->create([
                    'order_id' => $lockedOrder->id,
                    'changed_by_user_id' => $actor->id,
                    'from_status' => $currentStatus,
                    'to_status' => $nextStatus,
                    'note' => $note,
                    'changed_at' => now(),
                ]);
            }

            return $this->findAdminOrder($lockedOrder->id) ?? $lockedOrder;
        });
    }

    public function updatePaymentStatus(
        Order $order,
        string $nextStatus,
        User $actor,
        ?string $note = null
    ): Order {
        return DB::transaction(function () use ($order, $nextStatus, $actor, $note): Order {
            $payment = Payment::query()
                ->where('order_id', $order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->payment_status !== $nextStatus) {
                $payment->update([
                    'payment_status' => $nextStatus,
                    'paid_at' => $nextStatus === Payment::STATUS_SUCCESS ? now() : $payment->paid_at,
                ]);

                OrderStatusHistory::query()->create([
                    'order_id' => $order->id,
                    'changed_by_user_id' => $actor->id,
                    'from_status' => $order->status,
                    'to_status' => $order->status,
                    'note' => $note ?: sprintf('Payment status updated to %s.', $nextStatus),
                    'changed_at' => now(),
                ]);
            }

            return $this->findAdminOrder($order->id) ?? $order;
        });
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
        $order->loadMissing([
            'user',
            'items' => fn ($query) => $query->orderBy('id'),
            'payment',
            'statusHistory' => fn ($query) => $query->orderBy('changed_at'),
        ]);

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
            'note' => $order->note,
            'customer' => $order->user ? $this->customerPayload($order->user) : null,
            'items' => $order->items->map(fn (OrderItem $item): array => $this->orderItemPayload($item))->values()->all(),
            'payment' => $order->payment ? $this->paymentPayload($order->payment) : null,
            'status_history' => $order->statusHistory->map(
                fn (OrderStatusHistory $history): array => $this->statusHistoryPayload($history)
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
            'status' => $user->status,
            'is_active' => $user->is_active,
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
            'created_at' => $payment->created_at,
            'updated_at' => $payment->updated_at,
        ];
    }

    /**
     * @return array{0:string|null,1:string|null,2:string,3:array<string,mixed>|null}
     */
    private function paymentMetadata(
        string $paymentMethod,
        ?string $paymentGateway,
        string $transactionCode,
        float $totalAmount
    ): array {
        if ($paymentMethod === Order::PAYMENT_METHOD_BANK_TRANSFER) {
            $gatewayName = $paymentGateway ?: 'Vietcombank';

            return [
                $gatewayName,
                'BANK-' . $transactionCode,
                Payment::STATUS_PENDING,
                [
                    'instructions' => 'Transfer to the displayed bank account and wait for admin confirmation.',
                    'virtual_account_name' => 'HERITAGE HARVEST',
                    'virtual_account_no' => '0123456789',
                    'amount' => $this->decimal($totalAmount),
                ],
            ];
        }

        if ($paymentMethod === Order::PAYMENT_METHOD_E_WALLET) {
            $gatewayName = $paymentGateway ?: 'MoMo';

            return [
                $gatewayName,
                'EWALLET-' . $transactionCode,
                Payment::STATUS_PENDING,
                [
                    'instructions' => 'Complete the wallet payment and wait for gateway confirmation.',
                    'wallet' => $gatewayName,
                    'checkout_code' => 'QR-' . substr($transactionCode, -8),
                    'amount' => $this->decimal($totalAmount),
                ],
            ];
        }

        return [
            null,
            null,
            Payment::STATUS_PENDING,
            [
                'instructions' => 'Pay cash when the order is delivered.',
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
     * @param Collection<int, CartItem> $items
     */
    private function sumLineTotals(Collection $items): float
    {
        return (float) $items->reduce(
            fn (float $carry, CartItem $item): float => $carry + (float) $item->line_total,
            0.0
        );
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
