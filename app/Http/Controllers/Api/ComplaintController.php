<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComplaintRequest;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function __construct(private readonly AccountService $accountService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'message' => 'Complaints retrieved successfully.',
            'data' => $this->accountService->listComplaints($user),
        ]);
    }

    public function store(StoreComplaintRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $complaint = $this->accountService->createComplaint($user, $request->validated());

        return response()->json([
            'message' => 'Complaint created successfully.',
            'data' => $this->accountService->complaintPayload($complaint),
        ], 201);
    }
}
