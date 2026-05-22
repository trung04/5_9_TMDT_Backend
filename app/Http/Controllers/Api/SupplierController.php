<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\EnsuresAdminAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    use EnsuresAdminAccess;

    private SupplierService $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    /**
     * Get list of all active suppliers (public endpoint).
     *
     * @queryParam per_page int Number of items per page. Default: 15. Example: 15
     * @queryParam page int Page number. Default: 1. Example: 1
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);
        $suppliers = $this->supplierService->getAllSuppliers($perPage);

        return response()->json([
            'message' => 'Suppliers retrieved successfully.',
            'data' => $suppliers->items(),
            'pagination' => [
                'total' => $suppliers->total(),
                'per_page' => $suppliers->perPage(),
                'current_page' => $suppliers->currentPage(),
                'last_page' => $suppliers->lastPage(),
            ],
        ]);
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

        $perPage = (int) $request->query('per_page', 100);
        $perPage = min(max($perPage, 1), 200);

        $suppliers = Supplier::query()
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'message' => 'Admin suppliers retrieved successfully.',
            'data' => $suppliers->items(),
            'pagination' => [
                'total' => $suppliers->total(),
                'per_page' => $suppliers->perPage(),
                'current_page' => $suppliers->currentPage(),
                'last_page' => $suppliers->lastPage(),
            ],
        ]);
    }

    /**
     * Get supplier details with product count (public endpoint).
     *
     * @urlParam id int The supplier ID. Example: 1
     */
    public function show(Supplier $supplier): JsonResponse
    {
        if (! $supplier->is_active || $supplier->is_deleted) {
            return response()->json([
                'message' => 'Supplier not found.',
            ], 404);
        }

        $supplierWithCount = $this->supplierService->getSupplierById($supplier->id);

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

        $perPage = $request->query('per_page', 15);
        $products = $this->supplierService->getProductsBySupplier($supplier->id, $perPage);

        return response()->json([
            'message' => 'Supplier products retrieved successfully.',
            'data' => $products->items(),
            'pagination' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ],
        ]);
    }

    /**
     * Create a new supplier (admin only).
     */
    public function store(SupplierRequest $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.suppliers.create')) {
            return $response;
        }

        $supplier = $this->supplierService->createSupplier($request->validated());

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

        $updatedSupplier = $this->supplierService->updateSupplier(
            $supplier,
            $request->validated()
        );

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
            $this->supplierService->deleteSupplier($supplier);

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
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $user = $request->user();

        foreach ($permissionKeys as $permissionKey) {
            if ($user->hasAdminPermission($permissionKey)) {
                return null;
            }
        }

        return response()->json([
            'message' => 'You do not have permission to access this resource.',
        ], 403);
    }
}
