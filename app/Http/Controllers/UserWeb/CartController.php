<?php

namespace App\Http\Controllers\UserWeb;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CartService;
use App\Services\GuestCartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly GuestCartService $guestCartService,
    ) {
    }

    public function store(Request $request): RedirectResponse
    {
        if ($this->authenticatedNonCustomer()) {
            return redirect()->route('user-web.unauthorized');
        }

        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'redirect_to_checkout' => ['nullable', 'boolean'],
        ]);

        if ($customer = $this->customer()) {
            $this->cartService->addItem($customer, (int) $validated['product_id'], (int) $validated['quantity']);
        } else {
            $this->guestCartService->addItem((int) $validated['product_id'], (int) $validated['quantity']);
        }

        if ($request->boolean('redirect_to_checkout')) {
            return redirect()->route('user-web.checkout.show')->with('status', 'Đã thêm sản phẩm vào giỏ.');
        }

        return back()->with('status', 'Đã thêm sản phẩm vào giỏ.');
    }

    public function update(Request $request, int $cartItem): RedirectResponse
    {
        if ($this->authenticatedNonCustomer()) {
            return redirect()->route('user-web.unauthorized');
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:999'],
        ]);

        if (! $customer = $this->customer()) {
            $this->guestCartService->updateItem($cartItem, (int) $validated['quantity']);

            return back()->with('status', 'Đã cập nhật giỏ hàng.');
        }

        $item = $this->cartService->findUserActiveCartItem($customer, $cartItem);
        abort_unless($item, 404);

        $this->cartService->updateItem($item, (int) $validated['quantity']);

        return back()->with('status', 'Đã cập nhật giỏ hàng.');
    }

    public function destroy(int $cartItem): RedirectResponse
    {
        if ($this->authenticatedNonCustomer()) {
            return redirect()->route('user-web.unauthorized');
        }

        if (! $customer = $this->customer()) {
            $this->guestCartService->removeItem($cartItem);

            return back()->with('status', 'Đã xóa sản phẩm khỏi giỏ.');
        }

        $item = $this->cartService->findUserActiveCartItem($customer, $cartItem);
        abort_unless($item, 404);

        $this->cartService->removeItem($item);

        return back()->with('status', 'Đã xóa sản phẩm khỏi giỏ.');
    }

    private function customer(): ?User
    {
        /** @var User|null $user */
        $user = Auth::guard('web')->user();

        if (! $user || $user->role !== User::ROLE_CUSTOMER || ! $user->canAuthenticate()) {
            return null;
        }

        return $user;
    }

    private function authenticatedNonCustomer(): bool
    {
        /** @var User|null $user */
        $user = Auth::guard('web')->user();

        return $user && ($user->role !== User::ROLE_CUSTOMER || ! $user->canAuthenticate());
    }
}
