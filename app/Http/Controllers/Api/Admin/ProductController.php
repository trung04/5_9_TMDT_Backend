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

class ProductController extends Controller
{
    use EnsuresAdminAccess;

    public function index(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.products.view')) {
            return $response;
        }

        $query = Product::query()->with(['category', 'supplier', 'region']);

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->input('category_id'));
        }

        if ($request->filled('region_id')) {
            $query->where('region_id', (int) $request->input('region_id'));
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

        if ($request->filled('is_deleted')) {
            $isDeleted = filter_var(
                $request->input('is_deleted'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );

            if ($isDeleted !== null) {
                $query->where('is_deleted', $isDeleted);
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
            ->with(['category', 'supplier', 'region'])
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
        $data['is_deleted'] = $data['is_deleted'] ?? false;

        $product = Product::query()->create($data);

        return response()->json([
            'message' => 'Product created successfully.',
            'data' => $product->load(['category', 'supplier', 'region']),
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

        $data = $request->validated();

        if (($data['is_active'] ?? false) === true && ! array_key_exists('is_deleted', $data)) {
            $data['is_deleted'] = false;
        }

        $product->update($data);

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => $product->load(['category', 'supplier', 'region']),
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

        $isActive = $request->boolean('is_active');

        $product->update([
            'is_active' => $isActive,
            'is_deleted' => $request->has('is_deleted')
                ? $request->boolean('is_deleted')
                : ($isActive ? false : $product->is_deleted),
        ]);

        return response()->json([
            'message' => 'Product status updated successfully.',
            'data' => $product->load(['category', 'supplier', 'region']),
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

        $product->markDeleted();

        return response()->json([
            'message' => 'Product deleted successfully.',
            'data' => $product->refresh()->load(['category', 'supplier', 'region']),
        ], 200);
    }
}
