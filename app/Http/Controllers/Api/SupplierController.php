<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\EnsuresAdminAccess;
use App\Http\Controllers\Api\Concerns\PaginatesApiResults;
use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use EnsuresAdminAccess;
    use PaginatesApiResults;

    /**
     * Get list of all active suppliers (public endpoint).
     *
     * @queryParam per_page int Number of items per page. Default: 15. Example: 15
     * @queryParam page int Page number. Default: 1. Example: 1
     */
    public function index(Request $request): JsonResponse
    {
        $suppliers = Supplier::query()
            ->available()
            ->paginate($this->perPage($request));

        return response()->json($suppliers);
    }

    /**
     * Get list of all suppliers for admin management, including inactive ones.
     */
    public function adminIndex(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdminWithAnyPermission($request, [
            'admin.suppliers.create',
            'admin.suppliers.update',
            'admin.suppliers.delete',
        ])) {
            return $response;
        }

        $suppliers = Supplier::query()
            ->orderByDesc('id')
            ->paginate($this->perPage($request));

        return response()->json($suppliers);
    }

    /**
     * Get supplier details with product count (public endpoint).
     *
     * @urlParam id int The supplier ID. Example: 1
     */
    public function show(Supplier $supplier): JsonResponse
    {
        $supplierWithCount = Supplier::query()
            ->available()
            ->withCount('products')
            ->find($supplier->id);

        if (! $supplierWithCount) {
            return response()->json([
                'message' => 'Supplier not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Supplier retrieved successfully.',
            'data' => $supplierWithCount,
        ]);
    }

    /**
     * Get products supplied by a supplier.
     *
     * @urlParam supplier int The supplier ID. Example: 1
     * @queryParam per_page int Number of items per page. Default: 15. Example: 15
     */
    public function getProducts(Supplier $supplier, Request $request): JsonResponse
    {
        if (! $supplier->is_active || $supplier->is_deleted) {
            return response()->json([
                'message' => 'Supplier not found.',
            ], 404);
        }

        $products = $supplier->products()
            ->available()
            ->paginate($this->perPage($request));

        return response()->json($products);
    }

    /**
     * Create a new supplier (admin only).
     */
    public function store(SupplierRequest $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.suppliers.create')) {
            return $response;
        }

        $supplier = Supplier::query()->create($this->supplierPayload($request->validated()));

        return response()->json([
            'message' => 'Supplier created successfully.',
            'data' => $supplier,
        ], 201);
    }

    /**
     * Update a supplier (admin only).
     *
     * @urlParam id int The supplier ID. Example: 1
     */
    public function update(SupplierRequest $request, Supplier $supplier): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.suppliers.update')) {
            return $response;
        }

        $attributes = $request->validated();

        if (($attributes['is_active'] ?? false) === true && ! array_key_exists('is_deleted', $attributes)) {
            $attributes['is_deleted'] = false;
        }

        $supplier->update($this->supplierPayload($attributes, $supplier));
        $updatedSupplier = $supplier->refresh();

        return response()->json([
            'message' => 'Supplier updated successfully.',
            'data' => $updatedSupplier,
        ]);
    }

    /**
     * Delete a supplier (admin only - soft delete).
     *
     * @urlParam id int The supplier ID. Example: 1
     */
    public function destroy(Request $request, Supplier $supplier): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.suppliers.delete')) {
            return $response;
        }

        try {
            $supplier->markDeleted();

            return response()->json([
                'message' => 'Supplier deleted successfully.',
                'data' => $supplier->refresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * @param array<int, string> $permissionKeys
     */
    private function ensureAdminWithAnyPermission(Request $request, array $permissionKeys): ?JsonResponse
    {
        return $this->ensureAdmin($request);
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function supplierPayload(array $attributes, ?Supplier $supplier = null): array
    {
        return [
            'supplier_code' => $attributes['supplier_code'] ?? $supplier?->supplier_code,
            'name' => $attributes['name'] ?? $supplier?->name,
            'contact_name' => $attributes['contact_name'] ?? $supplier?->contact_name,
            'phone' => $attributes['phone'] ?? $supplier?->phone,
            'email' => $attributes['email'] ?? $supplier?->email,
            'address' => $attributes['address'] ?? $supplier?->address,
            'is_active' => $attributes['is_active'] ?? $supplier?->is_active ?? true,
            'is_deleted' => $attributes['is_deleted'] ?? $supplier?->is_deleted ?? false,
        ];
    }
}
