<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\EnsuresAdminAccess;
use App\Http\Controllers\Api\Concerns\PaginatesApiResults;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ShippingCarrierRequest;
use App\Models\ShippingCarrier;
use App\Services\ShippingCarrierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingCarrierController extends Controller
{
    use EnsuresAdminAccess;
    use PaginatesApiResults;

    public function __construct(private readonly ShippingCarrierService $carrierService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.shipping_carriers.view')) {
            return $response;
        }

        $carriers = $this->carrierService->list([
            'keyword' => $request->query('keyword'),
            'active_only' => $request->boolean('active_only'),
        ], $this->perPage($request, 50));

        return response()->json(
            $this->transformPaginator(
                $carriers,
                fn (ShippingCarrier $carrier): array => $this->carrierService->payloadForResponse($carrier)
            )
        );
    }

    public function store(ShippingCarrierRequest $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.shipping_carriers.create')) {
            return $response;
        }

        $carrier = $this->carrierService->create($request->validated());

        return response()->json([
            'message' => 'Shipping carrier created successfully.',
            'data' => $this->carrierService->payloadForResponse($carrier),
        ], 201);
    }

    public function update(ShippingCarrierRequest $request, ShippingCarrier $carrier): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.shipping_carriers.update')) {
            return $response;
        }

        $updated = $this->carrierService->update($carrier, $request->validated());

        return response()->json([
            'message' => 'Shipping carrier updated successfully.',
            'data' => $this->carrierService->payloadForResponse($updated),
        ]);
    }

    public function destroy(Request $request, ShippingCarrier $carrier): JsonResponse
    {
        if ($response = $this->ensureAdmin($request, 'admin.shipping_carriers.delete')) {
            return $response;
        }

        $deleted = $this->carrierService->delete($carrier);

        return response()->json([
            'message' => 'Shipping carrier deleted successfully.',
            'data' => $this->carrierService->payloadForResponse($deleted),
        ]);
    }
}
