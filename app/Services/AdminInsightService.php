<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Order;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierInvitation;
use App\Models\User;

class AdminInsightService
{
    /**
     * @return array<string, mixed>
     */
    public function dashboardPayload(): array
    {
        $orders = Order::query()->with(['payment', 'user'])->latest('id')->get();
        $suppliers = Supplier::query()->where('is_active', true)->latest('id')->get();
        $products = Product::query()->where('is_active', true)->latest('id')->limit(3)->get();
        $complaintCount = Complaint::query()->count();

        $revenue = (float) $orders->sum('total_amount');
        $delivered = $orders->where('status', Order::STATUS_DELIVERED)->count();
        $processing = $orders->whereIn('status', [
            Order::STATUS_PENDING,
            Order::STATUS_CONFIRMED,
            Order::STATUS_PACKED,
            Order::STATUS_SHIPPED,
        ])->count();
        $average = $orders->count() > 0 ? round($revenue / $orders->count(), 2) : 0;

        return [
            'metrics' => [
                'revenue' => $revenue,
                'delivered_orders' => $delivered,
                'processing_orders' => $processing,
                'supplier_count' => $suppliers->count(),
                'product_count' => Product::query()->where('is_active', true)->count(),
                'average_order_value' => $average,
                'complaint_count' => $complaintCount,
            ],
            'recent_orders' => $orders->take(5)->map(function (Order $order): array {
                return [
                    'id' => $order->id,
                    'order_no' => $order->order_no,
                    'status' => $order->status,
                    'payment_method' => $order->payment_method,
                    'total_amount' => $order->total_amount,
                    'created_at' => optional($order->created_at)->toISOString(),
                    'customer' => $order->user ? [
                        'id' => $order->user->id,
                        'full_name' => $order->user->full_name,
                        'email' => $order->user->email,
                    ] : null,
                ];
            })->all(),
            'featured_suppliers' => $suppliers->take(5)->map(fn (Supplier $supplier): array => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'contact_name' => $supplier->contact_name,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
            ])->all(),
            'featured_products' => $products->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'description' => $product->description,
                'sale_price' => $product->sale_price,
                'stock_quantity' => $product->stock_quantity,
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function communityPayload(): array
    {
        $suppliers = Supplier::query()->where('is_active', true)->withCount('products')->get();
        $customers = User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->withCount('orders')
            ->withSum('orders', 'total_amount')
            ->latest('id')
            ->get();
        $invitations = SupplierInvitation::query()->latest('id')->get();

        return [
            'suppliers' => $suppliers->map(fn (Supplier $supplier): array => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'contact_name' => $supplier->contact_name,
                'phone' => $supplier->phone,
                'email' => $supplier->email,
                'address' => $supplier->address,
                'product_count' => $supplier->products_count ?? 0,
                'status' => $supplier->is_active ? 'ACTIVE' : 'INACTIVE',
            ])->all(),
            'customers' => $customers->map(fn (User $customer): array => [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'order_count' => $customer->orders_count ?? 0,
                'total_spend' => $customer->orders_sum_total_amount ?? 0,
                'status' => $customer->status,
            ])->all(),
            'invitations' => $invitations->map(fn (SupplierInvitation $invitation): array => [
                'id' => $invitation->id,
                'supplier_name' => $invitation->supplier_name,
                'contact_name' => $invitation->contact_name,
                'email' => $invitation->email,
                'categories' => $invitation->categories ?? [],
                'note' => $invitation->note,
                'status' => $invitation->status,
                'created_at' => optional($invitation->created_at)->toISOString(),
            ])->all(),
        ];
    }

    public function createSupplierInvitation(array $attributes, User $actor): SupplierInvitation
    {
        return SupplierInvitation::query()->create([
            'supplier_name' => $attributes['supplier_name'],
            'contact_name' => $attributes['contact_name'],
            'email' => $attributes['email'],
            'categories' => $attributes['categories'] ?? [],
            'note' => $attributes['note'] ?? null,
            'status' => SupplierInvitation::STATUS_SENT,
            'created_by_user_id' => $actor->id,
        ]);
    }
}
