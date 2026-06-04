<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\EnsuresAdminAccess;
use App\Http\Controllers\Api\Concerns\PaginatesApiResults;
use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use EnsuresAdminAccess;
    use PaginatesApiResults;

    private CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Get list of all active categories (public endpoint).
     *
     * @queryParam per_page int Number of items per page. Default: 15. Example: 15
     * @queryParam page int Page number. Default: 1. Example: 1
     */
    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryService->getAllCategories($this->perPage($request));

        return response()->json($categories);
    }

    /**
     * Get list of all categories for admin management, including inactive ones.
     */
    public function adminIndex(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdminWithAnyPermission($request, [
            'admin.categories.create',
            'admin.categories.update',
            'admin.categories.delete',
        ])) {
            return $response;
        }

        $categories = Category::query()
            ->orderByDesc('id')
            ->paginate($this->perPage($request));

        return response()->json($categories);
    }

    /**
     * Get category details with product count (public endpoint).
     *
     * @urlParam id int The category ID. Example: 1
     */
    public function show(Category $category): JsonResponse
    {
        if (! $category->is_active || $category->is_deleted) {
            return response()->json([
                'message' => 'Category not found.',
            ], 404);
        }

        $categoryWithCount = $this->categoryService->getCategoryById($category->id);

        return response()->json([
            'message' => 'Category retrieved successfully.',
            'data' => $categoryWithCount,
        ]);
    }

    /**
     * Create a new category (admin only).
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.categories.create')) {
            return $response;
        }

        $category = $this->categoryService->createCategory($request->validated());

        return response()->json([
            'message' => 'Category created successfully.',
            'data' => $category,
        ], 201);
    }

    /**
     * Update a category (admin only).
     *
     * @urlParam id int The category ID. Example: 1
     */
    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.categories.update')) {
            return $response;
        }

        $updatedCategory = $this->categoryService->updateCategory(
            $category,
            $request->validated()
        );

        return response()->json([
            'message' => 'Category updated successfully.',
            'data' => $updatedCategory,
        ]);
    }

    /**
     * Delete a category (admin only - soft delete).
     *
     * @urlParam id int The category ID. Example: 1
     */
    public function destroy(Request $request, Category $category): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.categories.delete')) {
            return $response;
        }

        $this->categoryService->deleteCategory($category);

        return response()->json([
            'message' => 'Category deleted successfully.',
            'data' => $category->refresh(),
        ]);
    }

    /**
     * Get products in a category with filtering support (public endpoint).
     *
     * @urlParam id int The category ID. Example: 1
     * @queryParam per_page int Number of items per page. Default: 15. Example: 15
     * @queryParam page int Page number. Default: 1. Example: 1
     * @queryParam min_price float Minimum price filter. Example: 100000
     * @queryParam max_price float Maximum price filter. Example: 5000000
     * @queryParam supplier_id int Filter by supplier ID. Example: 1
     * @queryParam search string Search by product name or description. Example: "iphone"
     */
    public function getProducts(Request $request, Category $category): JsonResponse
    {
        if (! $category->is_active || $category->is_deleted) {
            return response()->json([
                'message' => 'Category not found.',
            ], 404);
        }

        $filters = [
            'min_price' => $request->query('min_price'),
            'max_price' => $request->query('max_price'),
            'supplier_id' => $request->query('supplier_id'),
            'search' => $request->query('search'),
        ];

        $filters = array_filter($filters, fn ($value) => $value !== null);

        $products = $this->categoryService->getProductsByCategory(
            $category->id,
            $filters,
            $this->perPage($request)
        );

        return response()->json($products);
    }

    /**
     * @param array<int, string> $permissionKeys
     */
    private function ensureAdminWithAnyPermission(Request $request, array $permissionKeys): ?JsonResponse
    {
        return $this->ensureAdmin($request);
    }
}
