<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\EnsuresCustomerAccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use EnsuresCustomerAccess;

    public function __construct(private readonly CartService $cartService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        if ($response = $this->ensureCustomer($request)) {
            return $response;
        }

        /** @var User $user */
        $user = $request->user();
        $cart = $this->cartService->getOrCreateActiveCart($user);

        return response()->json([
            'message' => 'Cart retrieved successfully.',
            'data' => $this->cartService->cartPayload($cart),
        ]);
    }

    public function storeItem(AddCartItemRequest $request): JsonResponse
    {
        if ($response = $this->ensureCustomer($request)) {
            return $response;
        }

        /** @var User $user */
        $user = $request->user();
        $cart = $this->cartService->addItem(
            $user,
            (int) $request->input('product_id'),
            (int) $request->input('quantity')
        );

        return response()->json([
            'message' => 'Cart item saved successfully.',
            'data' => $this->cartService->cartPayload($cart),
        ], 200);
    }

    public function updateItem(UpdateCartItemRequest $request, int $cartItem): JsonResponse
    {
        if ($response = $this->ensureCustomer($request)) {
            return $response;
        }

        /** @var User $user */
        $user = $request->user();
        $cartItemModel = $this->cartService->findUserActiveCartItem($user, $cartItem);

        if (! $cartItemModel) {
            return response()->json([
                'message' => 'Cart item not found.',
            ], 404);
        }

        $cart = $this->cartService->updateItem($cartItemModel, (int) $request->input('quantity'));

        return response()->json([
            'message' => 'Cart item updated successfully.',
            'data' => $this->cartService->cartPayload($cart),
        ]);
    }

    public function destroyItem(Request $request, int $cartItem): JsonResponse
    {
        if ($response = $this->ensureCustomer($request)) {
            return $response;
        }

        /** @var User $user */
        $user = $request->user();
        $cartItemModel = $this->cartService->findUserActiveCartItem($user, $cartItem);

        if (! $cartItemModel) {
            return response()->json([
                'message' => 'Cart item not found.',
            ], 404);
        }

        $cart = $this->cartService->removeItem($cartItemModel);

        return response()->json([
            'message' => 'Cart item removed successfully.',
            'data' => $this->cartService->cartPayload($cart),
        ]);
    }
}
