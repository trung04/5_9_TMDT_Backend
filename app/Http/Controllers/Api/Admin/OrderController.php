<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\EnsuresAdminAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePaymentStatusRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use EnsuresAdminAccess;

    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);
        $orders = $this->orderService->listAdminOrders([
            'status' => $request->query('status'),
            'keyword' => $request->query('keyword'),
        ], $perPage);

        return response()->json([
            'message' => 'Orders retrieved successfully.',
            'data' => $orders->getCollection()->map(
                fn ($order): array => $this->orderService->orderSummaryPayload($order)
            )->values()->all(),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, int $order): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $orderModel = $this->orderService->findAdminOrder($order);

        if (! $orderModel) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Order retrieved successfully.',
            'data' => $this->orderService->orderDetailPayload($orderModel),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $order): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $orderModel = $this->orderService->findAdminOrder($order);

        if (! $orderModel) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        /** @var User $user */
        $user = $request->user();
        $updatedOrder = $this->orderService->updateOrderStatus(
            $orderModel,
            (string) $request->input('status'),
            $user,
            $request->filled('note') ? (string) $request->input('note') : null,
        );

        return response()->json([
            'message' => 'Order status updated successfully.',
            'data' => $this->orderService->orderDetailPayload($updatedOrder),
        ]);
    }

    public function updatePaymentStatus(UpdatePaymentStatusRequest $request, int $order): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        $orderModel = $this->orderService->findAdminOrder($order);

        if (! $orderModel) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        /** @var User $user */
        $user = $request->user();
        $updatedOrder = $this->orderService->updatePaymentStatus(
            $orderModel,
            (string) $request->input('payment_status'),
            $user,
            $request->filled('note') ? (string) $request->input('note') : null,
        );

        return response()->json([
            'message' => 'Payment status updated successfully.',
            'data' => $this->orderService->orderDetailPayload($updatedOrder),
        ]);
    }
}
