<?php

namespace App\Models;

use App\Models\Concerns\HasActiveState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasActiveState;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'supplier_id',
        'region_id',
        'sku',
        'slug',
        'name',
        'description',
        'image_url',
        'sale_price',
        'stock_quantity',
        'is_active',
        'is_deleted',
        'gallery',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sale_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'is_active' => 'boolean',
            'is_deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the category that this product belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the supplier that this product belongs to.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the region that this product belongs to.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the cart items that reference this product.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the order items that reference this product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return list<string>
     */
    public function galleryImageUrls(): array
    {
        return collect(explode('|', (string) $this->gallery))
            ->map(fn (string $path): string => trim($path))
            ->filter()
            ->map(fn (string $path): string => $this->displayImageUrl($path))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function displayImageUrl(?string $path = null): ?string
    {
        $path = trim((string) ($path ?? $this->image_url));

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        return asset('storage/'.$path);
    }
}
