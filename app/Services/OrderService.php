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

            $order = Order::query()->create([
                'user_id' => $user->id,
                'order_no' => null,
                'recipient_name' => $attributes['recipient_name'],
                'recipient_phone' => $attributes['recipient_phone'],
                'shipping_address' => $attributes['shipping_address'],
                'payment_method' => Order::PAYMENT_METHOD_COD,
                'status' => Order::STATUS_PENDING,
                'subtotal' => $this->decimal($subtotal),
                'shipping_fee' => $this->decimal($shippingFee),
                'discount_amount' => $this->decimal($discountAmount),
                'total_amount' => $this->decimal($totalAmount),
                'note' => $attributes['note'] ?: null,
            ]);

            $order->update([
                'order_no' => sprintf('ORD-%s%04d', now()->format('Y'), $order->id),
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

            Payment::query()->create([
                'order_id' => $order->id,
                'transaction_code' => null,
                'payment_method' => Order::PAYMENT_METHOD_COD,
                'payment_status' => Payment::STATUS_PENDING,
                'amount' => $this->decimal($totalAmount),
                'gateway_name' => null,
                'gateway_reference' => null,
                'paid_at' => null,
                'raw_payload' => null,
            ]);

            OrderStatusHistory::query()->create([
                'order_id' => $order->id,
                'changed_by_user_id' => $user->id,
                'from_status' => null,
                'to_status' => Order::STATUS_PENDING,
                'note' => 'Order created by customer checkout.',
                'changed_at' => now(),
            ]);

            $cart->update([
                'status' => Cart::STATUS_CHECKED_OUT,
            ]);

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
                'items' => fn ($query) => $query->orderBy('id'),
                'payment',
                'statusHistory' => fn ($query) => $query->orderBy('changed_at'),
            ])
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function orderSummaryPayload(Order $order): array
    {
        $order->loadMissing(['items', 'payment']);

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
