<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\EnsuresCustomerAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutOrderRequest;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    use EnsuresCustomerAccess;

    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function checkout(CheckoutOrderRequest $request): JsonResponse
    {
        if ($response = $this->ensureCustomer($request)) {
            return $response;
        }

        /** @var User $user */
        $user = $request->user();
        $order = $this->orderService->checkout($user, $request->validated());

        return response()->json([
            'message' => 'Order created successfully.',
            'data' => $this->orderService->orderDetailPayload($order),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        if ($response = $this->ensureCustomer($request)) {
            return $response;
        }

        /** @var User $user */
        $user = $request->user();
        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);
        $orders = $this->orderService->listUserOrders($user, $perPage);

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
        if ($response = $this->ensureCustomer($request)) {
            return $response;
        }

        /** @var User $user */
        $user = $request->user();
        $orderModel = $this->orderService->findUserOrder($user, $order);

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

    public function cancel(Request $request, int $order): JsonResponse
    {
        if ($response = $this->ensureCustomer($request)) {
            return $response;
        }

        /** @var User $user */
        $user = $request->user();
        $orderModel = $this->orderService->findUserOrder($user, $order);

        if (! $orderModel) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        $validated = $request->validate([
            'reason' => [
                'required',
                'string',
                Rule::in([
                    'Đặt nhầm sản phẩm',
                    'Muốn thay đổi địa chỉ',
                    'Muốn thay đổi phương thức thanh toán',
                    'Không còn nhu cầu',
                    'Lý do khác',
                ]),
            ],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $updatedOrder = $this->orderService->cancelOrderByCustomer(
            $orderModel,
            $user,
            (string) $validated['reason'],
            ! empty($validated['note']) ? (string) $validated['note'] : null,
        );

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'data' => $this->orderService->orderDetailPayload($updatedOrder),
        ]);
    }

    public function confirmBankTransferSubmitted(Request $request, int $order): JsonResponse
    {
        if ($response = $this->ensureCustomer($request)) {
            return $response;
        }

        /** @var User $user */
        $user = $request->user();
        $orderModel = $this->orderService->findUserOrder($user, $order);

        if (! $orderModel) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $updatedOrder = $this->orderService->confirmBankTransferSubmitted(
            $orderModel,
            $user,
            ! empty($validated['note']) ? (string) $validated['note'] : null,
        );

        return response()->json([
            'message' => 'Bank transfer confirmation submitted successfully.',
            'data' => $this->orderService->orderDetailPayload($updatedOrder),
        ]);
    }

    public function confirmDelivery(Request $request, int $order): JsonResponse
    {
        if ($response = $this->ensureCustomer($request)) {
            return $response;
        }

        /** @var User $user */
        $user = $request->user();
        $orderModel = $this->orderService->findUserOrder($user, $order);

        if (! $orderModel) {
            return response()->json([
                'message' => 'Order not found.',
            ], 404);
        }

        $updatedOrder = $this->orderService->confirmDeliveredByCustomer($orderModel, $user);

        return response()->json([
            'message' => 'Order delivery confirmed successfully.',
            'data' => $this->orderService->orderDetailPayload($updatedOrder),
        ]);
    }
}
