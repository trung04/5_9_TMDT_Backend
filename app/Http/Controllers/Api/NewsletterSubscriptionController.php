<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:160'],
            'source' => ['nullable', 'string', 'max:80'],
        ]);

        $subscription = NewsletterSubscription::query()->updateOrCreate(
            [
                'email' => mb_strtolower(trim((string) $validated['email'])),
                'source' => $validated['source'] ?? 'storefront',
            ],
            []
        );

        return response()->json([
            'message' => 'Newsletter subscription saved successfully.',
            'data' => $subscription,
        ], $subscription->wasRecentlyCreated ? 201 : 200);
    }
}
