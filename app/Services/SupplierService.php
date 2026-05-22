<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class SupplierService
{
    /**
     * Get all active suppliers with optional pagination.
     */
    public function getAllSuppliers(int $perPage = null): Collection|LengthAwarePaginator
    {
        $query = Supplier::query()->available();

        if ($perPage) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    /**
     * Get supplier by ID with product count.
     */
    public function getSupplierById(int $id): ?Supplier
    {
        return Supplier::query()
            ->available()
            ->withCount('products')
            ->find($id);
    }

    /**
     * Create a new supplier.
     */
    public function createSupplier(array $data): Supplier
    {
        return Supplier::create([
            'supplier_code' => $data['supplier_code'],
            'name' => $data['name'],
            'contact_name' => $data['contact_name'] ?? null,
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'is_deleted' => $data['is_deleted'] ?? false,
        ]);
    }

    /**
     * Update a supplier.
     */
    public function updateSupplier(Supplier $supplier, array $data): Supplier
    {
        if (($data['is_active'] ?? false) === true && ! array_key_exists('is_deleted', $data)) {
            $data['is_deleted'] = false;
        }

        $supplier->update([
            'supplier_code' => $data['supplier_code'] ?? $supplier->supplier_code,
            'name' => $data['name'] ?? $supplier->name,
            'contact_name' => $data['contact_name'] ?? $supplier->contact_name,
            'phone' => $data['phone'] ?? $supplier->phone,
            'email' => $data['email'] ?? $supplier->email,
            'address' => $data['address'] ?? $supplier->address,
            'is_active' => $data['is_active'] ?? $supplier->is_active,
            'is_deleted' => $data['is_deleted'] ?? $supplier->is_deleted,
        ]);

        return $supplier;
    }

    /**
     * Delete a supplier (soft delete by setting is_active to false).
     */
    public function deleteSupplier(Supplier $supplier): bool
    {
        return $supplier->markDeleted();
    }

    /**
     * Check if supplier code already exists (for validation).
     */
    public function supplierCodeExists(string $supplierCode, ?int $excludeId = null): bool
    {
        $query = Supplier::where('supplier_code', $supplierCode);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get products by supplier ID.
     */
    public function getProductsBySupplier(int $supplierId, int $perPage = 15): LengthAwarePaginator
    {
        return Supplier::findOrFail($supplierId)
            ->products()
            ->available()
            ->paginate($perPage);
    }
}
