<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function getOrCreateActiveCart(User $user): Cart
    {
        $cart = Cart::query()
            ->where('user_id', $user->id)
            ->where('status', Cart::STATUS_ACTIVE)
            ->first();

        if (! $cart) {
            $cart = Cart::query()->create([
                'user_id' => $user->id,
                'status' => Cart::STATUS_ACTIVE,
            ]);
        }

        return $this->loadCart($cart);
    }

    /**
     * @return array<string, mixed>
     */
    public function cartPayload(Cart $cart): array
    {
        $cart = $this->loadCart($cart);
        $itemCount = $cart->items->count();
        $totalQuantity = (int) $cart->items->sum('quantity');
        $subtotal = $this->sumLineTotals($cart->items);

        return [
            'id' => $cart->id,
            'status' => $cart->status,
            'item_count' => $itemCount,
            'total_quantity' => $totalQuantity,
            'subtotal' => $this->decimal($subtotal),
            'items' => $cart->items->map(fn (CartItem $item): array => $this->cartItemPayload($item))->values()->all(),
            'created_at' => $cart->created_at,
            'updated_at' => $cart->updated_at,
        ];
    }

    public function addItem(User $user, int $productId, int $quantity): Cart
    {
        return DB::transaction(function () use ($user, $productId, $quantity): Cart {
            $cart = $this->getActiveCartForUpdate($user);

            if (! $cart) {
                $cart = Cart::query()->create([
                    'user_id' => $user->id,
                    'status' => Cart::STATUS_ACTIVE,
                ]);
            }

            $product = Product::query()->lockForUpdate()->find($productId);

            if (! $product || ! $product->is_active) {
                throw ValidationException::withMessages([
                    'product_id' => ['The selected product is unavailable.'],
                ]);
            }

            $existingItem = CartItem::query()
                ->where('cart_id', $cart->id)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            $targetQuantity = $quantity + ($existingItem?->quantity ?? 0);

            $this->ensureQuantityIsAvailable($product, $targetQuantity);

            $unitPrice = (float) $product->sale_price;
            $lineTotal = $this->lineTotal($targetQuantity, $unitPrice);

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $targetQuantity,
                    'unit_price' => $this->decimal($unitPrice),
                    'line_total' => $this->decimal($lineTotal),
                ]);
            } else {
                CartItem::query()->create([
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                    'quantity' => $targetQuantity,
                    'unit_price' => $this->decimal($unitPrice),
                    'line_total' => $this->decimal($lineTotal),
                ]);
            }

            return $this->loadCart($cart->fresh());
        });
    }

    public function findUserActiveCartItem(User $user, int $cartItemId): ?CartItem
    {
        return CartItem::query()
            ->where('id', $cartItemId)
            ->whereHas('cart', function ($query) use ($user): void {
                $query->where('user_id', $user->id)
                    ->where('status', Cart::STATUS_ACTIVE);
            })
            ->first();
    }

    public function updateItem(CartItem $cartItem, int $quantity): Cart
    {
        return DB::transaction(function () use ($cartItem, $quantity): Cart {
            $cartItem = CartItem::query()->lockForUpdate()->findOrFail($cartItem->id);
            $product = Product::query()->lockForUpdate()->find($cartItem->product_id);

            if (! $product || ! $product->is_active) {
                throw ValidationException::withMessages([
                    'product_id' => ['The selected product is unavailable.'],
                ]);
            }

            $this->ensureQuantityIsAvailable($product, $quantity);

            $unitPrice = (float) $product->sale_price;
            $lineTotal = $this->lineTotal($quantity, $unitPrice);

            $cartItem->update([
                'quantity' => $quantity,
                'unit_price' => $this->decimal($unitPrice),
                'line_total' => $this->decimal($lineTotal),
            ]);

            return $this->loadCart($cartItem->cart()->firstOrFail());
        });
    }

    public function removeItem(CartItem $cartItem): Cart
    {
        return DB::transaction(function () use ($cartItem): Cart {
            $cartItem = CartItem::query()->lockForUpdate()->findOrFail($cartItem->id);
            $cart = $cartItem->cart()->firstOrFail();
            $cartItem->delete();

            return $this->loadCart($cart->fresh());
        });
    }

    private function getActiveCartForUpdate(User $user): ?Cart
    {
        return Cart::query()
            ->where('user_id', $user->id)
            ->where('status', Cart::STATUS_ACTIVE)
            ->lockForUpdate()
            ->first();
    }

    private function loadCart(Cart $cart): Cart
    {
        return $cart->load([
            'items' => fn ($query) => $query->orderBy('id'),
            'items.product.category',
            'items.product.supplier',
        ]);
    }

    private function ensureQuantityIsAvailable(Product $product, int $quantity): void
    {
        if (! $product->is_active) {
            throw ValidationException::withMessages([
                'product_id' => ['The selected product is unavailable.'],
            ]);
        }

        if ($product->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => ['The requested quantity exceeds available stock.'],
            ]);
        }
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

    /**
     * @return array<string, mixed>
     */
    private function cartItemPayload(CartItem $item): array
    {
        $product = $item->product;

        return [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'unit_price' => $item->unit_price,
            'line_total' => $item->line_total,
            'product' => [
                'id' => $product->id,
                'category_id' => $product->category_id,
                'supplier_id' => $product->supplier_id,
                'sku' => $product->sku,
                'name' => $product->name,
                'description' => $product->description,
                'sale_price' => $product->sale_price,
                'stock_quantity' => $product->stock_quantity,
                'is_active' => $product->is_active,
                'category' => $product->category,
                'supplier' => $product->supplier,
            ],
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
        ];
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
