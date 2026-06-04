<?php

namespace App\Http\Controllers\AdminWeb;

use App\Http\Requests\Admin\CreateOrderShipmentRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Http\Requests\Admin\UpdatePaymentStatusRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ShippingCarrier;
use App\Services\OrderService;
use App\Services\OrderShipmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends AdminWebController
{
    public function __construct(
        \App\Support\AdminNavigation $navigation,
        private readonly OrderService $orderService,
        private readonly OrderShipmentService $shipmentService
    ) {
        parent::__construct($navigation);
    }

    public function index(Request $request)
    {
        $queueOptions = $this->orderService->adminQueueOptions();
        $activeQueue = $request->query('queue');
        $activeQueue = is_string($activeQueue) && array_key_exists($activeQueue, $queueOptions)
            ? $activeQueue
            : null;

        return $this->render('admin-web.orders.index', [
            'orders' => $this->orderService->listAdminOrders([
                'queue' => $activeQueue,
                'status' => $request->query('status'),
                'keyword' => $request->query('keyword'),
            ], (int) $request->integer('per_page', 20))->withQueryString(),
            'statuses' => Order::allowedStatuses(),
            'queueOptions' => $queueOptions,
            'activeQueueLabel' => $activeQueue ? $queueOptions[$activeQueue] : null,
        ]);
    }

    public function show(int $order)
    {
        $orderModel = $this->orderService->findAdminOrder($order);
        abort_if(! $orderModel, 404, 'Không tìm thấy đơn hàng.');

        $orderModel->loadMissing(['payment', 'shipment.carrier']);

        return $this->render('admin-web.orders.show', [
            'order' => $orderModel,
            'orderPayload' => $this->orderService->orderDetailPayload($orderModel),
            'allowedStatuses' => $this->orderService->allowedNextStatuses($orderModel),
            'allowedPaymentStatuses' => $orderModel->payment
                ? $this->orderService->allowedNextPaymentStatuses($orderModel->payment, $orderModel)
                : [],
            'shippingCarriers' => ShippingCarrier::query()->available()->orderBy('name')->get(),
            'allStatuses' => Order::allowedStatuses(),
            'allPaymentStatuses' => Payment::allowedStatuses(),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $order): RedirectResponse
    {
        $orderModel = $this->orderService->findAdminOrder($order);
        abort_if(! $orderModel, 404, 'Không tìm thấy đơn hàng.');

        $this->orderService->updateOrderStatus(
            $orderModel,
            (string) $request->input('status'),
            $this->adminUser(),
            $request->filled('note') ? (string) $request->input('note') : null,
            [
                'restock_inventory' => $request->has('restock_inventory')
                    ? $request->boolean('restock_inventory')
                    : null,
            ],
        );

        return redirect()
            ->route('admin-web.orders.show', $order)
            ->with('status', 'Đã cập nhật trạng thái đơn hàng thành công.');
    }

    public function updatePaymentStatus(UpdatePaymentStatusRequest $request, int $order): RedirectResponse
    {
        $orderModel = $this->orderService->findAdminOrder($order);
        abort_if(! $orderModel, 404, 'Không tìm thấy đơn hàng.');

        $this->orderService->updatePaymentStatus(
            $orderModel,
            (string) $request->input('payment_status'),
            $this->adminUser(),
            $request->filled('note') ? (string) $request->input('note') : null,
        );

        return redirect()
            ->route('admin-web.orders.show', $order)
            ->with('status', 'Đã cập nhật trạng thái thanh toán thành công.');
    }

    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'orderIds' => ['required', 'array', 'min:1'],
            'orderIds.*' => ['integer', 'distinct', 'min:1'],
            'action' => [
                'required',
                'string',
                Rule::in(['CONFIRM', 'SHIP', 'DELIVER', 'MARK_DELIVERY_FAILED', 'CANCEL', 'RESHIP']),
            ],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->orderService->bulkUpdateStatuses(
            $this->adminUser(),
            array_map('intval', $validated['orderIds']),
            (string) $validated['action'],
            ! empty($validated['note']) ? (string) $validated['note'] : null,
        );

        return redirect()
            ->route('admin-web.orders.index')
            ->with('status', 'Đã xử lý cập nhật hàng loạt trạng thái đơn hàng.');
    }

    public function storeShipment(CreateOrderShipmentRequest $request, Order $order): RedirectResponse
    {
        $this->shipmentService->createShipment($order, $this->adminUser(), $request->validated());

        return redirect()
            ->route('admin-web.orders.show', $order)
            ->with('status', 'Đã tạo vận đơn thành công.');
    }

    public function syncShipment(Order $order): RedirectResponse
    {
        $this->shipmentService->syncShipment($order, $this->adminUser());

        return redirect()
            ->route('admin-web.orders.show', $order)
            ->with('status', 'Đã đồng bộ vận đơn thành công.');
    }

    public function destroyShipment(Order $order): RedirectResponse
    {
        $this->shipmentService->cancelShipment($order, $this->adminUser());

        return redirect()
            ->route('admin-web.orders.show', $order)
            ->with('status', 'Đã hủy vận đơn thành công.');
    }
}
