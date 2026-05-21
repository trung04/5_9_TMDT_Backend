<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CategoryService
{
    /**
     * Get all active categories with optional pagination.
     */
    public function getAllCategories(int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Category::where('is_active', true);

        if ($perPage) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    /**
     * Get category by ID with product count.
     */
    public function getCategoryById(int $id): ?Category
    {
        return Category::where('is_active', true)
            ->withCount('products')
            ->find($id);
    }

    /**
     * Create a new category.
     */
    public function createCategory(array $data): Category
    {
        return Category::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update a category.
     */
    public function updateCategory(Category $category, array $data): Category
    {
        $category->update([
            'name' => $data['name'] ?? $category->name,
            'description' => $data['description'] ?? $category->description,
            'is_active' => $data['is_active'] ?? $category->is_active,
        ]);

        return $category;
    }

    /**
     * Delete a category (soft delete by setting is_active to false).
     */
    public function deleteCategory(Category $category): bool
    {
        return $category->update(['is_active' => false]);
    }

    /**
     * Check if category name already exists (for validation).
     */
    public function categoryNameExists(string $name, ?int $excludeId = null): bool
    {
        $query = Category::where('name', $name);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get products by category ID with optional filtering.
     */
    public function getProductsByCategory(
        int $categoryId,
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = Category::findOrFail($categoryId)
            ->products()
            ->where('is_active', true);

        // Filter by price range if provided
        if (isset($filters['min_price'])) {
            $query->where('sale_price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price'])) {
            $query->where('sale_price', '<=', $filters['max_price']);
        }

        // Filter by supplier if provided
        if (isset($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        // Search by name or description
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }
}
