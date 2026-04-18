<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
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
     * Get supplier details with product count (public endpoint).
     *
     * @urlParam id int The supplier ID. Example: 1
     */
    public function show(Supplier $supplier): JsonResponse
    {
        if (! $supplier->is_active) {
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
        if (! $supplier->is_active) {
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
    public function destroy(Supplier $supplier): JsonResponse
    {
        try {
            $this->supplierService->deleteSupplier($supplier);

            return response()->json([
                'message' => 'Supplier deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}