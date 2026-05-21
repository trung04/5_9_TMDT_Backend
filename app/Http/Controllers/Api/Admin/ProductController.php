<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\EnsuresAdminAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Requests\UpdateProductStatusRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    use EnsuresAdminAccess;

    public function index(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.products.view')) {
            return $response;
        }

        $query = Product::query()->with(['category', 'supplier']);

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->input('category_id'));
        }

        if ($request->filled('is_active')) {
            $isActive = filter_var(
                $request->input('is_active'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );

            if ($isActive !== null) {
                $query->where('is_active', $isActive);
            }
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->input('keyword'));

            $query->where(function ($q) use ($keyword): void {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('sku', 'like', "%{$keyword}%");
            });
        }

        $products = $query->orderByDesc('id')->get();

        return response()->json([
            'message' => 'Products retrieved successfully.',
            'data' => $products,
        ], 200);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.products.view')) {
            return $response;
        }

        $product = Product::query()
            ->with(['category', 'supplier'])
            ->find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Product retrieved successfully.',
            'data' => $product,
        ], 200);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.products.create')) {
            return $response;
        }

        $data = $request->validated();
        $data['is_active'] = $data['is_active'] ?? true;

        $product = Product::query()->create($data);

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => $product->load(['category', 'supplier']),
        ], 201);
    }

    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.products.update')) {
            return $response;
        }

        $product = Product::query()->find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        $product->update($request->validated());

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => $product->load(['category', 'supplier']),
        ], 200);
    }

    public function updateStatus(UpdateProductStatusRequest $request, int $id): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.products.update')) {
            return $response;
        }

        $product = Product::query()->find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        $product->update([
            'is_active' => $request->boolean('is_active'),
        ]);

        return response()->json([
            'message' => 'Product status updated successfully.',
            'data' => $product->load(['category', 'supplier']),
        ], 200);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.products.delete')) {
            return $response;
        }

        $product = Product::query()->find($id);

        if (! $product) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        $hasRelatedData =
            DB::table('cart_items')->where('product_id', $id)->exists() ||
            DB::table('order_items')->where('product_id', $id)->exists() ||
            DB::table('reviews')->where('product_id', $id)->exists() ||
            DB::table('complaints')->where('product_id', $id)->exists() ||
            DB::table('inventory_items')->where('product_id', $id)->exists() ||
            DB::table('supply_order_items')->where('product_id', $id)->exists() ||
            DB::table('delivery_requests')->where('product_id', $id)->exists() ||
            DB::table('prices')->where('product_id', $id)->exists();

        if ($hasRelatedData) {
            return response()->json([
                'message' => 'Product has related data and cannot be deleted. Deactivate it instead.',
            ], 422);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully.',
        ], 200);
    }
}
