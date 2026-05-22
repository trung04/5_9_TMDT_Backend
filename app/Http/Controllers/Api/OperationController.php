<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\PaginatesApiResults;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentStatusHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OperationController extends Controller
{
    use PaginatesApiResults;

    public function inventory(Request $request): JsonResponse
    {
        $items = DB::table('inventory_items')
            ->join('inventories', 'inventories.id', '=', 'inventory_items.inventory_id')
            ->join('products', 'products.id', '=', 'inventory_items.product_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'products.supplier_id')
            ->leftJoin('prices', function ($join): void {
                $join->on('prices.product_id', '=', 'products.id')
                    ->where('prices.is_active', true);
            })
            ->select([
                'inventory_items.id',
                'inventories.name as inventory_name',
                'inventories.location as inventory_location',
                'products.id as product_id',
                'products.sku',
                'products.name as product_name',
                'products.supplier_id',
                'suppliers.name as supplier_name',
                'suppliers.address as supplier_location',
                'inventory_items.quantity_on_hand',
                'inventory_items.reorder_level',
                'inventory_items.safety_stock',
                'prices.cost_price',
                'inventory_items.last_counted_at',
                'inventory_items.updated_at',
            ])
            ->orderBy('products.sku')
            ->paginate($this->perPage($request));

        return response()->json(
            $this->transformPaginator($items, function ($item): array {
                $onHand = (int) $item->quantity_on_hand;
                $reorder = (int) $item->reorder_level;
                $safety = (int) $item->safety_stock;

                return [
                    'id' => (int) $item->id,
                    'sku' => (string) $item->sku,
                    'product_id' => (int) $item->product_id,
                    'product_name' => (string) $item->product_name,
                    'supplier_id' => $item->supplier_id ? (int) $item->supplier_id : null,
                    'supplier_name' => $item->supplier_name,
                    'supplier_location' => $item->supplier_location,
                    'inventory_name' => $item->inventory_name,
                    'inventory_location' => $item->inventory_location,
                    'quantity_on_hand' => $onHand,
                    'reserved' => max(0, min($onHand, (int) floor($onHand * 0.12))),
                    'reorder_level' => $reorder,
                    'safety_stock' => $safety,
                    'purchase_price' => (float) ($item->cost_price ?? 0),
                    'aisle' => $this->aisleFor((int) $item->id),
                    'status' => $this->inventoryStatus($onHand, $reorder, $safety),
                    'last_counted_at' => $item->last_counted_at,
                    'updated_at' => $item->updated_at,
                ];
            })
        );
    }

    public function requisitions(Request $request): JsonResponse
    {
        return response()->json(
            $this->transformPaginator(
                $this->requisitionQuery()->paginate($this->perPage($request)),
                fn ($item): array => $this->requisitionPayload($item)
            )
        );
    }

    public function storeRequisition(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'requested_qty' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $id = DB::table('delivery_requests')->insertGetId([
            'requested_by_user_id' => $request->user()->id,
            'product_id' => $validated['product_id'],
            'requested_qty' => $validated['requested_qty'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'PENDING',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $record = $this->requisitionQuery()->where('delivery_requests.id', $id)->first();

        return response()->json([
            'message' => 'Operation requisition created successfully.',
            'data' => $this->requisitionPayload($record),
        ], 201);
    }

    public function updateRequisitionStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:APPROVED,FULFILLED,REJECTED,CANCELLED,approved,received,cancelled,rejected'],
        ]);

        $status = match (strtolower((string) $validated['status'])) {
            'approved' => 'APPROVED',
            'received' => 'FULFILLED',
            'cancelled' => 'CANCELLED',
            'rejected' => 'REJECTED',
            default => strtoupper((string) $validated['status']),
        };

        DB::table('delivery_requests')
            ->where('id', $id)
            ->update([
                'status' => $status,
                'approved_by_user_id' => in_array($status, ['APPROVED', 'FULFILLED'], true) ? $request->user()->id : null,
                'updated_at' => now(),
            ]);

        $record = $this->requisitionQuery()->where('delivery_requests.id', $id)->first();

        return response()->json([
            'message' => 'Operation requisition updated successfully.',
            'data' => $this->requisitionPayload($record),
        ]);
    }

    public function supplierOrders(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with(['items.product.supplier', 'user', 'payment', 'statusHistory'])
            ->orderByDesc('id')
            ->paginate($this->perPage($request));

        return response()->json(
            $this->transformPaginator($orders, fn (Order $order): array => $this->orderPayload($order))
        );
    }

    public function fulfillmentTasks(Request $request): JsonResponse
    {
        $tasks = Order::query()
            ->with(['statusHistory', 'user'])
            ->whereIn('status', [
                Order::STATUS_PENDING,
                Order::STATUS_CONFIRMED,
                Order::STATUS_PACKED,
                Order::STATUS_SHIPPED,
            ])
            ->orderBy('created_at')
            ->paginate($this->perPage($request));

        return response()->json(
            $this->transformPaginator($tasks, fn (Order $order): array => $this->fulfillmentTaskPayload($order))
        );
    }

    public function updateOrderDeliveryStatus(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'delivery_status' => ['required', 'string', 'in:processing,ready_to_ship,in_transit,delivered,disputed'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $nextStatus = match ($validated['delivery_status']) {
            'ready_to_ship' => Order::STATUS_PACKED,
            'in_transit' => Order::STATUS_SHIPPED,
            'delivered' => Order::STATUS_DELIVERED,
            'disputed' => Order::STATUS_DELIVERY_FAILED,
            default => Order::STATUS_CONFIRMED,
        };

        $this->moveOrderStatus($order, $nextStatus, $request->user()->id, $validated['note'] ?? null);

        return response()->json([
            'message' => 'Operation order updated successfully.',
            'data' => $this->orderPayload($order->refresh()->load(['items.product.supplier', 'user', 'payment', 'statusHistory'])),
        ]);
    }

    public function advanceFulfillmentTask(Request $request, Order $order): JsonResponse
    {
        $nextStatus = match ($order->status) {
            Order::STATUS_PENDING,
            Order::STATUS_CONFIRMED => Order::STATUS_PACKED,
            Order::STATUS_PACKED => Order::STATUS_SHIPPED,
            default => $order->status,
        };

        if ($nextStatus !== $order->status) {
            $this->moveOrderStatus($order, $nextStatus, $request->user()->id, $request->input('note'));
        }

        return response()->json([
            'message' => 'Operation fulfillment task advanced successfully.',
            'data' => $this->fulfillmentTaskPayload($order->refresh()->load(['statusHistory', 'user'])),
        ]);
    }

    private function requisitionQuery()
    {
        return DB::table('delivery_requests')
            ->join('products', 'products.id', '=', 'delivery_requests.product_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'products.supplier_id')
            ->select([
                'delivery_requests.*',
                'products.sku',
                'products.name as product_name',
                'products.supplier_id',
                'suppliers.name as supplier_name',
            ])
            ->orderByDesc('delivery_requests.id');
    }

    /**
     * @param object|null $record
     *
     * @return array<string, mixed>
     */
    private function requisitionPayload($record): array
    {
        $status = match ($record?->status) {
            'PENDING' => 'submitted',
            'APPROVED' => 'approved',
            'FULFILLED' => 'received',
            'CANCELLED', 'REJECTED' => 'cancelled',
            default => 'submitted',
        };

        return [
            'id' => (string) ($record?->id ?? ''),
            'inventory_sku' => (string) ($record?->sku ?? ''),
            'product_id' => $record?->product_id ? (int) $record->product_id : null,
            'product_name' => $record?->product_name,
            'supplier_id' => $record?->supplier_id ? (int) $record->supplier_id : null,
            'supplier_name' => $record?->supplier_name,
            'requested_qty' => (int) ($record?->requested_qty ?? 0),
            'approved_qty' => in_array($record?->status, ['APPROVED', 'FULFILLED'], true) ? (int) $record->requested_qty : null,
            'eta_days' => match ($record?->status) {
                'FULFILLED' => 0,
                'APPROVED' => 3,
                default => 5,
            },
            'status' => $status,
            'note' => $record?->reason,
            'created_at' => $record?->created_at,
            'updated_at' => $record?->updated_at,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function orderPayload(Order $order): array
    {
        $suppliers = $order->items
            ->map(fn ($item) => $item->product?->supplier)
            ->filter()
            ->unique('id')
            ->values();
        $supplier = $suppliers->first();

        return [
            'id' => (string) $order->id,
            'order_no' => $order->order_no,
            'customer_name' => $order->recipient_name,
            'customer_id' => $order->user_id ? (string) $order->user_id : null,
            'supplier_name' => $suppliers->pluck('name')->implode(', ') ?: 'Heritage Harvest',
            'supplier_id' => $supplier?->id ? (string) $supplier->id : null,
            'date' => optional($order->created_at)->toISOString(),
            'total' => (float) $order->total_amount,
            'payment_status' => $this->paymentStatus($order),
            'delivery_status' => $this->deliveryStatus($order->status),
            'shipping_tier' => $this->shippingTier($order),
            'address' => $order->shipping_address,
            'note' => $order->note,
            'items' => $order->items->map(fn ($item): array => [
                'product_id' => (string) $item->product_id,
                'product_name' => $item->product_name_snapshot,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
            ])->values(),
            'timeline' => $order->statusHistory->map(fn (OrderStatusHistory $history): array => [
                'id' => (string) $history->id,
                'order_id' => (string) $order->id,
                'label' => $history->note ?? $history->to_status,
                'timestamp' => optional($history->changed_at)->toISOString(),
                'completed' => true,
            ])->values(),
            'status_history' => $order->statusHistory->map(fn (OrderStatusHistory $history): array => [
                'id' => (string) $history->id,
                'actor' => $history->changed_by_user_id ? "User #{$history->changed_by_user_id}" : 'System',
                'label' => $history->note ?? $history->to_status,
                'created_at' => optional($history->changed_at)->toISOString(),
            ])->values(),
            'assigned_warehouse_zone' => $this->zoneFor((int) $order->id),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fulfillmentTaskPayload(Order $order): array
    {
        return [
            'id' => (string) $order->id,
            'order_id' => (string) $order->id,
            'customer_name' => $order->recipient_name,
            'shipping_tier' => $this->shippingTier($order),
            'status' => $this->fulfillmentStatus($order->status),
            'priority' => $order->shipping_fee > 25000 ? 'rush' : 'standard',
            'assigned_zone' => $this->zoneFor((int) $order->id),
            'eta_label' => $order->shipping_fee > 25000 ? 'Uu tien trong ngay' : 'Tieu chuan 24h',
            'notes' => $order->note,
            'status_history' => $order->statusHistory->map(fn (OrderStatusHistory $history): array => [
                'id' => (string) $history->id,
                'actor' => $history->changed_by_user_id ? "User #{$history->changed_by_user_id}" : 'System',
                'label' => $history->note ?? $history->to_status,
                'created_at' => optional($history->changed_at)->toISOString(),
            ])->values(),
        ];
    }

    private function moveOrderStatus(Order $order, string $nextStatus, ?int $actorId, ?string $note = null): void
    {
        $fromStatus = $order->status;

        $updates = [
            'status' => $nextStatus,
            'updated_at' => now(),
        ];

        if ($nextStatus === Order::STATUS_SHIPPED && ! $order->shipped_at) {
            $updates['shipped_at'] = now();
            $updates['shipping_carrier'] = $order->shipping_carrier ?: 'GHN';
            $updates['shipping_code'] = $order->shipping_code ?: "GHN-{$order->order_no}";
        }

        if ($nextStatus === Order::STATUS_DELIVERED) {
            $updates['delivered_at'] = now();
        }

        if ($nextStatus === Order::STATUS_CANCELLED) {
            $updates['cancelled_at'] = now();
        }

        $order->update($updates);

        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'changed_by_user_id' => $actorId,
            'from_status' => $fromStatus,
            'to_status' => $nextStatus,
            'note' => $note ?: "Cap nhat tu operations portal sang {$nextStatus}.",
            'changed_at' => now(),
        ]);

        if ($nextStatus === Order::STATUS_DELIVERED && $order->payment) {
            $payment = $order->payment;

            if ($payment->payment_status !== Payment::STATUS_SUCCESS) {
                $fromPaymentStatus = $payment->payment_status;
                $payment->update([
                    'payment_status' => Payment::STATUS_SUCCESS,
                    'paid_at' => $payment->paid_at ?: now(),
                ]);

                PaymentStatusHistory::query()->create([
                    'payment_id' => $payment->id,
                    'order_id' => $order->id,
                    'changed_by_user_id' => $actorId,
                    'from_status' => $fromPaymentStatus,
                    'to_status' => Payment::STATUS_SUCCESS,
                    'note' => 'Operations portal confirmed delivery payment.',
                    'changed_at' => now(),
                ]);
            }
        }
    }

    private function inventoryStatus(int $onHand, int $reorder, int $safety): string
    {
        if ($onHand <= $safety) {
            return 'critical';
        }

        if ($onHand <= $reorder) {
            return 'low';
        }

        return 'healthy';
    }

    private function paymentStatus(Order $order): string
    {
        if ($order->payment?->payment_status === Payment::STATUS_SUCCESS) {
            return 'paid';
        }

        if ($order->payment_method === Order::PAYMENT_METHOD_COD) {
            return 'cod';
        }

        return 'pending';
    }

    private function deliveryStatus(string $status): string
    {
        return match ($status) {
            Order::STATUS_PACKED => 'ready_to_ship',
            Order::STATUS_SHIPPED => 'in_transit',
            Order::STATUS_DELIVERED => 'delivered',
            Order::STATUS_DELIVERY_FAILED,
            Order::STATUS_CANCELLED => 'disputed',
            default => 'processing',
        };
    }

    private function fulfillmentStatus(string $status): string
    {
        return match ($status) {
            Order::STATUS_PACKED => 'packing',
            Order::STATUS_SHIPPED => 'shipped',
            default => 'picking',
        };
    }

    private function shippingTier(Order $order): string
    {
        if ($order->shipping_fee >= 35000) {
            return 'priority';
        }

        if ($order->shipping_fee >= 25000) {
            return 'express';
        }

        return 'standard';
    }

    private function aisleFor(int $id): string
    {
        $zones = ['A', 'B', 'C', 'D'];

        return $zones[$id % count($zones)].'-'.str_pad((string) (($id % 18) + 1), 2, '0', STR_PAD_LEFT);
    }

    private function zoneFor(int $id): string
    {
        $zones = ['HN-A1', 'HN-B2', 'HCM-C1', 'DN-D2'];

        return $zones[$id % count($zones)];
    }
}
