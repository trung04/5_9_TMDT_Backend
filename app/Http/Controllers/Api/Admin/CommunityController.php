<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\EnsuresAdminAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSupplierInvitationRequest;
use App\Models\User;
use App\Services\AdminInsightService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    use EnsuresAdminAccess;

    public function __construct(private readonly AdminInsightService $adminInsightService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        return response()->json([
            'message' => 'Community data retrieved successfully.',
            'data' => $this->adminInsightService->communityPayload(),
        ]);
    }

    public function storeInvitation(StoreSupplierInvitationRequest $request): JsonResponse
    {
        if ($response = $this->ensureAdmin($request)) {
            return $response;
        }

        /** @var User $user */
        $user = $request->user();
        $invitation = $this->adminInsightService->createSupplierInvitation($request->validated(), $user);

        return response()->json([
            'message' => 'Supplier invitation created successfully.',
            'data' => [
                'id' => $invitation->id,
                'supplier_name' => $invitation->supplier_name,
                'contact_name' => $invitation->contact_name,
                'email' => $invitation->email,
                'categories' => $invitation->categories,
                'note' => $invitation->note,
                'status' => $invitation->status,
                'created_at' => optional($invitation->created_at)->toISOString(),
            ],
        ], 201);
    }
}
