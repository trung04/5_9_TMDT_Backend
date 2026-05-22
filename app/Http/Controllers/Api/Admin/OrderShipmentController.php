<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\EnsuresAdminAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateOrderShipmentRequest;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use App\Services\OrderShipmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderShipmentController extends Controller
{
    use EnsuresAdminAccess;

    public function __construct(
        private readonly OrderService $orderService,
        private readonly OrderShipmentService $shipmentService
    ) {
    }

    public function store(CreateOrderShipmentRequest $request, Order $order): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.orders.status.update')) {
            return $response;
        }

        /** @var User $user */
        $user = $request->user();
        $updatedOrder = $this->shipmentService->createShipment($order, $user, $request->validated());

        return response()->json([
            'message' => 'Order shipment created successfully.',
            'data' => $this->orderService->orderDetailPayload($updatedOrder),
        ], 201);
    }

    public function sync(Request $request, Order $order): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.orders.status.update')) {
            return $response;
        }

        /** @var User $user */
        $user = $request->user();
        $updatedOrder = $this->shipmentService->syncShipment($order, $user);

        return response()->json([
            'message' => 'Order shipment synced successfully.',
            'data' => $this->orderService->orderDetailPayload($updatedOrder),
        ]);
    }

    public function destroy(Request $request, Order $order): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.orders.status.update')) {
            return $response;
        }

        /** @var User $user */
        $user = $request->user();
        $updatedOrder = $this->shipmentService->cancelShipment($order, $user);

        return response()->json([
            'message' => 'Order shipment cancelled successfully.',
            'data' => $this->orderService->orderDetailPayload($updatedOrder),
        ]);
    }
}
