<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class GuestCartService
{
    public const SESSION_KEY = 'user_web.guest_cart';

    /**
     * @return array<int, int>
     */
    public function raw(): array
    {
        $items = Session::get(self::SESSION_KEY, []);

        if (! is_array($items)) {
            return [];
        }

        $normalized = [];

        foreach ($items as $productId => $quantity) {
            $productId = (int) $productId;
            $quantity = (int) $quantity;

            if ($productId > 0 && $quantity > 0) {
                $normalized[$productId] = $quantity;
            }
        }

        return $normalized;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function items(): Collection
    {
        $raw = $this->raw();

        if ($raw === []) {
            return collect();
        }

        $products = Product::query()
            ->available()
            ->with(['category', 'supplier', 'region'])
            ->whereIn('id', array_keys($raw))
            ->get()
            ->keyBy('id');

        $items = collect($raw)
            ->map(function (int $quantity, int $productId) use ($products): ?array {
                $product = $products->get($productId);

                if (! $product) {
                    return null;
                }

                $safeQuantity = max(1, min($quantity, (int) $product->stock_quantity));
                $unitPrice = (float) $product->sale_price;

                return [
                    'id' => $product->id,
                    'cart_item_id' => null,
                    'product_id' => $product->id,
                    'product' => $product,
                    'quantity' => $safeQuantity,
                    'unit_price' => $unitPrice,
                    'line_total' => round($unitPrice * $safeQuantity, 2),
                    'is_guest' => true,
                ];
            })
            ->filter()
            ->values();

        $this->store($items->mapWithKeys(
            fn (array $item): array => [(int) $item['product_id'] => (int) $item['quantity']]
        )->all());

        return $items;
    }

    public function addItem(int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => ['Số lượng phải lớn hơn 0.'],
            ]);
        }

        $product = Product::query()->available()->find($productId);

        if (! $product) {
            throw ValidationException::withMessages([
                'product_id' => ['Sản phẩm hiện không còn khả dụng.'],
            ]);
        }

        $items = $this->raw();
        $targetQuantity = ($items[$productId] ?? 0) + $quantity;

        $this->ensureAvailableQuantity($product, $targetQuantity);

        $items[$productId] = $targetQuantity;
        $this->store($items);
    }

    public function updateItem(int $productId, int $quantity): void
    {
        $items = $this->raw();

        if ($quantity <= 0) {
            unset($items[$productId]);
            $this->store($items);

            return;
        }

        $product = Product::query()->available()->find($productId);

        if (! $product) {
            throw ValidationException::withMessages([
                'product_id' => ['Sản phẩm hiện không còn khả dụng.'],
            ]);
        }

        $this->ensureAvailableQuantity($product, $quantity);

        $items[$productId] = $quantity;
        $this->store($items);
    }

    public function removeItem(int $productId): void
    {
        $items = $this->raw();
        unset($items[$productId]);
        $this->store($items);
    }

    public function totalQuantity(): int
    {
        return array_sum($this->raw());
    }

    public function subtotal(): float
    {
        return (float) $this->items()->sum('line_total');
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function mergeIntoCustomer(User $user, CartService $cartService): void
    {
        foreach ($this->raw() as $productId => $quantity) {
            try {
                $cartService->addItem($user, (int) $productId, (int) $quantity);
            } catch (ValidationException) {
                continue;
            }
        }

        $this->clear();
    }

    /**
     * @param array<int, int> $items
     */
    private function store(array $items): void
    {
        $normalized = [];

        foreach ($items as $productId => $quantity) {
            $productId = (int) $productId;
            $quantity = (int) $quantity;

            if ($productId > 0 && $quantity > 0) {
                $normalized[$productId] = $quantity;
            }
        }

        Session::put(self::SESSION_KEY, $normalized);
    }

    private function ensureAvailableQuantity(Product $product, int $quantity): void
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => ['Số lượng phải lớn hơn 0.'],
            ]);
        }

        if ((int) $product->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => ['Số lượng vượt quá tồn kho hiện có.'],
            ]);
        }
    }
}
