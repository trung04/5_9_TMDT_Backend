<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\PaginatesApiResults;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use App\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use PaginatesApiResults;

    public function __construct(private readonly AccountService $accountService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json(
            $this->accountService->listNotifications($user, $this->perPage($request))
        );
    }

    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $updated = $this->accountService->markNotificationRead($user, $notification);

        return response()->json([
            'message' => 'Notification marked as read.',
            'data' => $this->accountService->notificationPayload($updated),
        ]);
    }
}
