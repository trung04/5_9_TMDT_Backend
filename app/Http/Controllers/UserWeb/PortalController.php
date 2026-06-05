<?php

namespace App\Http\Controllers\UserWeb;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\PaymentStatusHistory;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PortalController extends Controller
{
    public function unauthorized(): View
    {
        return view('user-web.portal.unauthorized');
    }

    public function supplierInventory(Request $request): View
    {
        $inventory = $this->inventoryPaginator($request);
        $selectedSku = $this->selectedValue($request, 'supplier_inventory_sku', $inventory->getCollection()->first()['sku'] ?? null);

        return view('user-web.portal.inventory', [
            'variant' => 'supplier',
            'inventory' => $inventory,
            'requisitions' => $this->requisitionCollection(),
            'stats' => $this->inventoryStats($request),
            'query' => (string) $request->query('search', ''),
            'selectedSku' => $selectedSku,
            'activeInventoryItem' => $inventory->getCollection()->firstWhere('sku', $selectedSku) ?? $inventory->getCollection()->first(),
        ]);
    }

    public function warehouseInventory(Request $request): View
    {
        $inventory = $this->inventoryPaginator($request);
        $selectedSku = $this->selectedValue($request, 'warehouse_inventory_sku', $inventory->getCollection()->first()['sku'] ?? null);

        return view('user-web.portal.inventory', [
            'variant' => 'warehouse',
            'inventory' => $inventory,
            'requisitions' => $this->requisitionCollection(),
            'stats' => $this->inventoryStats($request),
            'query' => (string) $request->query('search', ''),
            'selectedSku' => $selectedSku,
            'activeInventoryItem' => $inventory->getCollection()->firstWhere('sku', $selectedSku) ?? $inventory->getCollection()->first(),
        ]);
    }

    public function exportInventory(Request $request): StreamedResponse
    {
        $items = $this->inventoryQuery($request)->get()->map(fn ($item): array => $this->inventoryPayload($item));

        return response()->streamDownload(function () use ($items): void {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['sku', 'product', 'status', 'onHand', 'reserved']);

            foreach ($items as $item) {
                fputcsv($stream, [
                    $item['sku'],
                    $item['product_name'] ?: $item['product_id'],
                    $this->inventoryHealthLabels()[$item['status']] ?? $item['status'],
                    $item['quantity_on_hand'],
                    $item['reserved'],
                ]);
            }

            fclose($stream);
        }, 'supplier-inventory.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function supplierRequisitions(): View
    {
        return view('user-web.portal.requisitions', [
            'variant' => 'supplier',
            'requisitions' => $this->requisitionPaginator(),
        ]);
    }

    public function warehouseRequisitions(): View
    {
        return view('user-web.portal.requisitions', [
            'variant' => 'warehouse',
            'requisitions' => $this->requisitionPaginator(),
        ]);
    }

    public function supplierProcessing(): View
    {
        return view('user-web.portal.processing', [
            'orders' => $this->ordersPaginator(),
        ]);
    }

    public function supplierOrders(Request $request): View
    {
        $filter = (string) $request->query('filter', 'all');
        $orders = $this->ordersPaginator($filter);
        $selectedOrderId = $this->selectedValue($request, 'supplier_order', $orders->getCollection()->first()['id'] ?? null);

        return view('user-web.portal.supplier-orders', [
            'orders' => $orders,
            'stats' => $this->orderStats(),
            'filter' => $filter,
            'selectedOrderId' => $selectedOrderId,
            'activeOrder' => $orders->getCollection()->firstWhere('id', $selectedOrderId) ?? $orders->getCollection()->first(),
        ]);
    }

    public function warehouseFulfillment(Request $request): View
    {
        $tasks = $this->fulfillmentPaginator();
        $orders = $this->orderCollection();
        $selectedTaskId = $this->selectedValue($request, 'warehouse_fulfillment_task', $tasks->getCollection()->first()['id'] ?? null);
        $activeTask = $tasks->getCollection()->firstWhere('id', $selectedTaskId) ?? $tasks->getCollection()->first();

        return view('user-web.portal.fulfillment', [
            'tasks' => $tasks,
            'orders' => $orders,
            'stats' => $this->fulfillmentStats(),
            'selectedTaskId' => $selectedTaskId,
            'activeTask' => $activeTask,
            'relatedOrder' => $activeTask ? $orders->firstWhere('id', $activeTask['order_id']) : null,
        ]);
    }

    public function warehouseSupplierOrders(Request $request): View
    {
        $orders = $this->ordersPaginator();
        $selectedOrderId = $this->selectedValue($request, 'warehouse_supplier_order', $orders->getCollection()->first()['id'] ?? null);

        return view('user-web.portal.warehouse-supplier-orders', [
            'orders' => $orders,
            'selectedOrderId' => $selectedOrderId,
            'activeOrder' => $orders->getCollection()->firstWhere('id', $selectedOrderId) ?? $orders->getCollection()->first(),
        ]);
    }

    public function supplierHelp(): View
    {
        return view('user-web.portal.help', [
            'variant' => 'supplier',
            'tickets' => $this->supportTickets('supplier'),
        ]);
    }

    public function warehouseHelp(): View
    {
        return view('user-web.portal.help', [
            'variant' => 'warehouse',
            'tickets' => $this->supportTickets('warehouse'),
        ]);
    }

    public function storeRequisition(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'requested_qty' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        /** @var User $user */
        $user = Auth::guard('web')->user();

        $id = DB::table('delivery_requests')->insertGetId([
            'requested_by_user_id' => $user->id,
            'product_id' => $validated['product_id'],
            'requested_qty' => $validated['requested_qty'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'PENDING',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('status', "Đã tạo phiếu nhập #{$id}.");
    }

    public function updateRequisitionStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:APPROVED,FULFILLED,REJECTED,CANCELLED,approved,received,cancelled,rejected'],
        ]);

        /** @var User $user */
        $user = Auth::guard('web')->user();
        $status = $this->normalizeRequisitionStatus((string) $validated['status']);

        DB::table('delivery_requests')
            ->where('id', $id)
            ->update([
                'status' => $status,
                'approved_by_user_id' => in_array($status, ['APPROVED', 'FULFILLED'], true) ? $user->id : null,
                'updated_at' => now(),
            ]);

        return back()->with('status', "Đã cập nhật phiếu #{$id}.");
    }

    public function updateOrderDeliveryStatus(Request $request, Order $order): RedirectResponse
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

        try {
            $this->moveOrderStatus($order, $nextStatus, Auth::id(), $validated['note'] ?? null);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', "Đã cập nhật đơn {$order->order_no}.");
    }

    public function advanceFulfillmentTask(Request $request, Order $order): RedirectResponse
    {
        $nextStatus = match ($order->status) {
            Order::STATUS_PENDING,
            Order::STATUS_CONFIRMED => Order::STATUS_PACKED,
            Order::STATUS_PACKED => Order::STATUS_SHIPPED,
            default => $order->status,
        };

        if ($nextStatus !== $order->status) {
            try {
                $this->moveOrderStatus($order, $nextStatus, Auth::id(), $request->input('note'));
            } catch (ValidationException $exception) {
                return back()->withErrors($exception->errors());
            }
        }

        return back()->with('status', "Đã cập nhật fulfillment cho đơn {$order->order_no}.");
    }

    public function storeSupportTicket(Request $request, string $channel): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string'],
        ]);

        SupportTicket::query()->create([
            'user_id' => Auth::id(),
            'subject' => trim((string) $validated['subject']),
            'message' => trim((string) $validated['message']),
            'channel' => strtoupper($channel),
            'status' => SupportTicket::STATUS_OPEN,
        ]);

        return back()->with('status', 'Đã tạo phiếu hỗ trợ.');
    }

    public function resolveSupportTicket(SupportTicket $ticket): RedirectResponse
    {
        $ticket->update([
            'status' => SupportTicket::STATUS_RESOLVED,
            'resolved_by_user_id' => Auth::id(),
            'resolved_at' => now(),
        ]);

        return back()->with('status', 'Đã đánh dấu phiếu hỗ trợ đã xử lý.');
    }

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    private function inventoryPaginator(Request $request): LengthAwarePaginator
    {
        return $this->inventoryQuery($request)
            ->paginate($this->perPage($request, 8))
            ->withQueryString()
            ->through(fn ($item): array => $this->inventoryPayload($item));
    }

    private function inventoryQuery(Request $request): Builder
    {
        $inventoryMetaSubquery = DB::table('inventory_items')
            ->leftJoin('inventories', 'inventories.id', '=', 'inventory_items.inventory_id')
            ->selectRaw('
                inventory_items.product_id,
                MIN(inventories.name) as inventory_name,
                MIN(inventories.location) as inventory_location,
                MAX(inventory_items.reorder_level) as reorder_level,
                MAX(inventory_items.safety_stock) as safety_stock,
                MAX(inventory_items.last_counted_at) as last_counted_at,
                MAX(inventory_items.updated_at) as inventory_updated_at
            ')
            ->groupBy('inventory_items.product_id');

        $latestPriceSubquery = DB::table('prices')
            ->selectRaw('product_id, MAX(id) as latest_price_id')
            ->where('is_active', true)
            ->groupBy('product_id');

        $query = DB::table('products')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'products.supplier_id')
            ->leftJoinSub($inventoryMetaSubquery, 'inventory_meta', function ($join): void {
                $join->on('inventory_meta.product_id', '=', 'products.id');
            })
            ->leftJoinSub($latestPriceSubquery, 'latest_prices', function ($join): void {
                $join->on('latest_prices.product_id', '=', 'products.id');
            })
            ->leftJoin('prices as current_price', 'current_price.id', '=', 'latest_prices.latest_price_id')
            ->select([
                'products.id',
                'inventory_meta.inventory_name',
                'inventory_meta.inventory_location',
                'products.id as product_id',
                'products.sku',
                'products.name as product_name',
                'products.supplier_id',
                'suppliers.name as supplier_name',
                'suppliers.address as supplier_location',
                'products.stock_quantity',
                'inventory_meta.reorder_level',
                'inventory_meta.safety_stock',
                'current_price.cost_price',
                'inventory_meta.last_counted_at',
                DB::raw('COALESCE(inventory_meta.inventory_updated_at, products.updated_at) as updated_at'),
            ])
            ->where('products.is_deleted', false);

        $search = trim((string) $request->query('search', ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('products.sku', 'like', "%{$search}%")
                    ->orWhere('products.name', 'like', "%{$search}%")
                    ->orWhere('suppliers.name', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('products.sku');
    }

    /**
     * @return array<string, mixed>
     */
    private function inventoryPayload(object $item): array
    {
        $onHand = (int) $item->stock_quantity;
        $reorder = (int) ($item->reorder_level ?? 5);

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
            'reserved' => 0,
            'reorder_level' => $reorder,
            'safety_stock' => (int) ($item->safety_stock ?? 2),
            'purchase_price' => (float) ($item->cost_price ?? 0),
            'aisle' => $this->aisleFor((int) $item->id),
            'status' => $this->inventoryStatus($onHand),
            'last_counted_at' => $item->last_counted_at,
            'updated_at' => $item->updated_at,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function inventoryStats(Request $request): array
    {
        $items = $this->inventoryQuery($request)
            ->get()
            ->map(fn ($item): array => $this->inventoryPayload($item));

        return [
            'sku_count' => $items->count(),
            'alert_count' => $items->where('status', '!=', 'healthy')->count(),
            'inventory_value' => $items->sum(fn (array $item): float => $item['quantity_on_hand'] * $item['purchase_price']),
            'requisition_count' => DB::table('delivery_requests')->count(),
        ];
    }

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    private function requisitionPaginator(): LengthAwarePaginator
    {
        return $this->requisitionQuery()
            ->paginate(8)
            ->withQueryString()
            ->through(fn ($item): array => $this->requisitionPayload($item));
    }

    private function requisitionCollection()
    {
        return $this->requisitionQuery()
            ->limit(200)
            ->get()
            ->map(fn ($item): array => $this->requisitionPayload($item));
    }

    private function requisitionQuery(): Builder
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
     * @return array<string, mixed>
     */
    private function requisitionPayload(object $record): array
    {
        $status = match ($record->status) {
            'PENDING' => 'submitted',
            'APPROVED' => 'approved',
            'FULFILLED' => 'received',
            'CANCELLED', 'REJECTED' => 'cancelled',
            default => 'submitted',
        };

        return [
            'id' => (string) $record->id,
            'inventory_sku' => (string) $record->sku,
            'product_id' => $record->product_id ? (int) $record->product_id : null,
            'product_name' => $record->product_name,
            'supplier_id' => $record->supplier_id ? (int) $record->supplier_id : null,
            'supplier_name' => $record->supplier_name,
            'requested_qty' => (int) $record->requested_qty,
            'approved_qty' => in_array($record->status, ['APPROVED', 'FULFILLED'], true) ? (int) $record->requested_qty : null,
            'eta_days' => match ($record->status) {
                'FULFILLED' => 0,
                'APPROVED' => 3,
                default => 5,
            },
            'status' => $status,
            'note' => $record->reason,
            'created_at' => $record->created_at,
            'updated_at' => $record->updated_at,
        ];
    }

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    private function ordersPaginator(string $filter = 'all'): LengthAwarePaginator
    {
        $query = Order::query()
            ->with(['items.product.supplier', 'user', 'payment', 'shipment.carrier', 'statusHistory'])
            ->orderByDesc('id');

        if ($filter === 'awaiting') {
            $query->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED, Order::STATUS_PACKED]);
        }

        return $query
            ->paginate(8)
            ->withQueryString()
            ->through(fn (Order $order): array => $this->orderPayload($order));
    }

    private function orderCollection()
    {
        return Order::query()
            ->with(['items.product.supplier', 'user', 'payment', 'shipment.carrier', 'statusHistory'])
            ->orderByDesc('id')
            ->limit(200)
            ->get()
            ->map(fn (Order $order): array => $this->orderPayload($order));
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
            'order_no' => $order->order_no ?: '#'.$order->id,
            'customer_name' => $order->recipient_name,
            'customer_id' => $order->user_id ? (string) $order->user_id : null,
            'supplier_name' => $suppliers->pluck('name')->implode(', ') ?: 'Heritage Harvest',
            'supplier_id' => $supplier?->id ? (string) $supplier->id : null,
            'date' => $order->created_at,
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
                'label' => $history->note ?? $history->to_status,
                'timestamp' => $history->changed_at,
                'completed' => true,
            ])->values(),
            'status_history' => $order->statusHistory->map(fn (OrderStatusHistory $history): array => [
                'id' => (string) $history->id,
                'actor' => $history->changed_by_user_id ? "User #{$history->changed_by_user_id}" : 'System',
                'label' => $history->note ?? $history->to_status,
                'created_at' => $history->changed_at,
            ])->values(),
            'assigned_warehouse_zone' => $this->zoneFor((int) $order->id),
            'shipment' => $order->shipment ? [
                'tracking_code' => $order->shipment->tracking_code,
                'status' => $order->shipment->status,
                'provider' => $order->shipment->provider,
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function orderStats(): array
    {
        $orders = $this->orderCollection();

        return [
            'pending' => $orders->where('delivery_status', 'processing')->count(),
            'transit' => $orders->where('delivery_status', 'in_transit')->count(),
            'revenue' => $orders->sum('total'),
        ];
    }

    /**
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    private function fulfillmentPaginator(): LengthAwarePaginator
    {
        return Order::query()
            ->with(['statusHistory', 'user', 'items.product.supplier'])
            ->whereIn('status', [
                Order::STATUS_PENDING,
                Order::STATUS_CONFIRMED,
                Order::STATUS_PACKED,
                Order::STATUS_SHIPPED,
            ])
            ->orderBy('created_at')
            ->paginate(8)
            ->withQueryString()
            ->through(fn (Order $order): array => $this->fulfillmentTaskPayload($order));
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
            'eta_label' => $order->shipping_fee > 25000 ? 'Ưu tiên trong ngày' : 'Tiêu chuẩn 24h',
            'notes' => $order->note,
            'status_history' => $order->statusHistory->map(fn (OrderStatusHistory $history): array => [
                'id' => (string) $history->id,
                'actor' => $history->changed_by_user_id ? "User #{$history->changed_by_user_id}" : 'System',
                'label' => $history->note ?? $history->to_status,
                'created_at' => $history->changed_at,
            ])->values(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fulfillmentStats(): array
    {
        $tasks = Order::query()
            ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_CONFIRMED, Order::STATUS_PACKED, Order::STATUS_SHIPPED])
            ->get()
            ->map(fn (Order $order): array => [
                'status' => $this->fulfillmentStatus($order->status),
                'priority' => $order->shipping_fee > 25000 ? 'rush' : 'standard',
                'assigned_zone' => $this->zoneFor((int) $order->id),
            ]);

        return [
            'pending' => $tasks->where('status', '!=', 'shipped')->count(),
            'rush' => $tasks->where('priority', 'rush')->count(),
            'zones' => $tasks->pluck('assigned_zone')->unique()->count(),
            'shipped' => $tasks->where('status', 'shipped')->count(),
        ];
    }

    /**
     * @return LengthAwarePaginator<int, SupportTicket>
     */
    private function supportTickets(string $channel): LengthAwarePaginator
    {
        return SupportTicket::query()
            ->with(['user:id,full_name,email,role', 'resolver:id,full_name,email,role'])
            ->where('channel', strtoupper($channel))
            ->orderByDesc('id')
            ->paginate(8)
            ->withQueryString();
    }

    private function moveOrderStatus(Order $order, string $nextStatus, ?int $actorId, ?string $note = null): void
    {
        $order = Order::query()->with(['payment', 'shipment'])->findOrFail($order->id);
        $fromStatus = $order->status;

        $updates = [
            'status' => $nextStatus,
            'updated_at' => now(),
        ];

        if ($nextStatus === Order::STATUS_PACKED) {
            if (! $order->shipment || $order->shipment->cancelled_at || ! $order->shipment->tracking_code) {
                throw ValidationException::withMessages([
                    'shipment' => ['Cần tạo vận đơn trước khi chuyển đơn sang trạng thái đóng gói.'],
                ]);
            }
        }

        if ($nextStatus === Order::STATUS_SHIPPED && ! $order->shipped_at) {
            if (! $order->shipment || $order->shipment->cancelled_at || ! $order->shipment->tracking_code) {
                throw ValidationException::withMessages([
                    'shipment' => ['Cần tạo vận đơn trước khi bàn giao cho vận chuyển.'],
                ]);
            }

            $updates['shipped_at'] = now();
            $updates['shipping_carrier'] = $order->shipping_carrier ?: $order->shipment->carrier?->name;
            $updates['shipping_code'] = $order->shipping_code ?: $order->shipment->tracking_code;
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
            'note' => $note ?: "Cập nhật từ cổng vận hành sang {$nextStatus}.",
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
                    'note' => 'Cổng vận hành đã xác nhận thanh toán sau giao hàng.',
                    'changed_at' => now(),
                ]);
            }
        }
    }

    private function normalizeRequisitionStatus(string $status): string
    {
        return match (strtolower($status)) {
            'approved' => 'APPROVED',
            'received' => 'FULFILLED',
            'cancelled' => 'CANCELLED',
            'rejected' => 'REJECTED',
            default => strtoupper($status),
        };
    }

    private function inventoryStatus(int $onHand): string
    {
        $lowStockThreshold = (int) (DB::table('admin_settings')->value('low_stock_threshold') ?? 5);

        if ($onHand <= 0) {
            return 'critical';
        }

        if ($onHand <= $lowStockThreshold) {
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

    /**
     * @return array<string, string>
     */
    private function inventoryHealthLabels(): array
    {
        return [
            'healthy' => 'On dinh',
            'low' => 'Canh bao',
            'critical' => 'Khan cap',
        ];
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

    private function perPage(Request $request, int $default = 8): int
    {
        return max(1, min(50, (int) $request->integer('per_page', $default)));
    }

    private function selectedValue(Request $request, string $sessionKey, mixed $fallback): ?string
    {
        $selected = trim((string) $request->query('selected', ''));

        if ($selected === '') {
            $selected = (string) $request->session()->get('user_web.portal.'.$sessionKey, $fallback ?? '');
        }

        if ($selected !== '') {
            $request->session()->put('user_web.portal.'.$sessionKey, $selected);

            return $selected;
        }

        return $fallback ? (string) $fallback : null;
    }
}
