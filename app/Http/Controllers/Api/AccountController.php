<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\RedeemRewardRequest;
use App\Http\Requests\StoreWishlistItemRequest;
use App\Http\Requests\UpdateAccountProfileRequest;
use App\Http\Requests\UserAddressRequest;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(private readonly AccountService $accountService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'message' => 'Account profile retrieved successfully.',
            'data' => $this->accountService->profilePayload($user),
        ]);
    }

    public function update(UpdateAccountProfileRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $updated = $this->accountService->updateProfile($user, $request->validated());

        return response()->json([
            'message' => 'Account profile updated successfully.',
            'data' => $this->accountService->profilePayload($updated),
        ]);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->accountService->changePassword(
            $user,
            (string) $request->input('current_password'),
            (string) $request->input('new_password'),
        );

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

    public function storeAddress(UserAddressRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->accountService->createAddress($user, $request->validated());

        return response()->json([
            'message' => 'Address created successfully.',
            'data' => $this->accountService->profilePayload($user->refresh()),
        ], 201);
    }

    public function updateAddress(UserAddressRequest $request, UserAddress $address): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->accountService->updateAddress($user, $address, $request->validated());

        return response()->json([
            'message' => 'Address updated successfully.',
            'data' => $this->accountService->profilePayload($user->refresh()),
        ]);
    }

    public function destroyAddress(Request $request, UserAddress $address): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->accountService->deleteAddress($user, $address);

        return response()->json([
            'message' => 'Address deleted successfully.',
            'data' => $this->accountService->profilePayload($user->refresh()),
        ]);
    }

    public function setDefaultAddress(Request $request, UserAddress $address): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->accountService->setDefaultAddress($user, $address);

        return response()->json([
            'message' => 'Default address updated successfully.',
            'data' => $this->accountService->profilePayload($user->refresh()),
        ]);
    }

    public function redeemReward(RedeemRewardRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $updated = $this->accountService->redeemReward(
            $user,
            (string) $request->input('title'),
            (int) $request->input('points_cost'),
        );

        return response()->json([
            'message' => 'Reward redeemed successfully.',
            'data' => $this->accountService->profilePayload($updated),
        ]);
    }

    public function wishlist(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'message' => 'Wishlist retrieved successfully.',
            'data' => $this->accountService->listWishlist($user),
        ]);
    }

    public function storeWishlistItem(StoreWishlistItemRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $product = Product::query()->findOrFail((int) $request->input('product_id'));

        return response()->json([
            'message' => 'Wishlist updated successfully.',
            'data' => $this->accountService->addWishlistItem($user, $product),
        ], 201);
    }

    public function destroyWishlistItem(Request $request, Product $product): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'message' => 'Wishlist updated successfully.',
            'data' => $this->accountService->removeWishlistItem($user, $product),
        ]);
    }
}
