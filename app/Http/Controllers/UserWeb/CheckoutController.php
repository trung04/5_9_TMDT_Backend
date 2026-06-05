<?php

namespace App\Http\Controllers\UserWeb;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\AccountService;
use App\Services\CartService;
use App\Services\GuestCartService;
use App\Services\OrderService;
use App\Support\LocalGhnLocationCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly AccountService $accountService,
        private readonly CartService $cartService,
        private readonly GuestCartService $guestCartService,
        private readonly OrderService $orderService,
        private readonly LocalGhnLocationCatalog $locationCatalog,
    ) {
    }

    public function show(): View|RedirectResponse
    {
        $user = Auth::guard('web')->user();

        if ($user && ($user->role !== User::ROLE_CUSTOMER || ! $user->canAuthenticate())) {
            return redirect()->route('user-web.unauthorized');
        }

        $cart = $user ? $this->cartService->getOrCreateActiveCart($user) : null;
        $items = $user ? $this->customerCartItems($cart) : $this->guestCartItems();
        $subtotal = (float) collect($items)->sum('line_total');

        return view('user-web.checkout.show', [
            'cart' => $cart,
            'items' => $items,
            'subtotal' => $subtotal,
            'itemCount' => (int) collect($items)->sum('quantity'),
            'profile' => $user ? $this->accountService->profilePayload($user) : $this->guestProfile(),
            'provinces' => $this->locationCatalog->provinces(),
            'isGuestCheckout' => ! $user,
        ]);
    }

    public function place(Request $request): RedirectResponse
    {
        if (! $this->customer()) {
            return redirect()->guest(route('user-web.login', [
                'redirect' => route('user-web.checkout.show', absolute: false),
            ]));
        }

        $request->merge([
            'shipping_address' => $this->shippingAddress($request),
            'recipient_name' => trim((string) $request->input('recipient_name')),
            'recipient_phone' => trim((string) $request->input('recipient_phone')),
            'note' => trim((string) $request->input('note', '')),
        ]);

        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:120'],
            'recipient_phone' => ['required', 'string', 'max:20'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'shipping_line1' => ['nullable', 'string', 'max:255'],
            'shipping_province_id' => ['nullable', 'integer', 'min:1'],
            'shipping_province_name' => ['nullable', 'string', 'max:120'],
            'shipping_district_id' => ['nullable', 'integer', 'min:1'],
            'shipping_district_name' => ['nullable', 'string', 'max:120'],
            'shipping_ward_code' => ['nullable', 'string', 'max:30'],
            'shipping_ward_name' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'string', Rule::in(Order::allowedPaymentMethods())],
        ]);

        $order = $this->orderService->checkout($this->customer(), $validated);

        return redirect()
            ->route('user-web.checkout.success', ['order' => $order->id])
            ->with('status', $order->payment_method === Order::PAYMENT_METHOD_BANK_TRANSFER
                ? 'Đơn hàng đã tạo. Vui lòng chuyển khoản theo hướng dẫn.'
                : 'Đặt hàng thành công.');
    }

    public function success(int $order): View
    {
        $orderModel = $this->orderService->findUserOrder($this->customer(), $order);
        abort_unless($orderModel, 404);

        return view('user-web.checkout.success', [
            'order' => $orderModel,
        ]);
    }

    public function confirmTransfer(Request $request, int $order): RedirectResponse
    {
        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $orderModel = $this->orderService->findUserOrder($this->customer(), $order);
        abort_unless($orderModel, 404);

        $this->orderService->confirmBankTransferSubmitted(
            $orderModel,
            $this->customer(),
            ! empty($validated['note']) ? (string) $validated['note'] : null,
        );

        return back()->with('status', 'Đã ghi nhận xác nhận chuyển khoản.');
    }

    private function shippingAddress(Request $request): string
    {
        if (trim((string) $request->input('shipping_address')) !== '') {
            return trim((string) $request->input('shipping_address'));
        }

        return collect([
            $request->input('shipping_line1'),
            $request->input('shipping_ward_name'),
            $request->input('shipping_district_name'),
            $request->input('shipping_province_name'),
        ])
            ->filter(fn ($part): bool => is_string($part) && trim($part) !== '')
            ->map(fn (string $part): string => trim($part))
            ->implode(', ');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function customerCartItems($cart): array
    {
        if (! $cart) {
            return [];
        }

        $cart->loadMissing(['items.product.category', 'items.product.supplier', 'items.product.region']);

        return $cart->items
            ->map(fn ($item): array => [
                'id' => $item->id,
                'cart_item_id' => $item->id,
                'product_id' => $item->product_id,
                'product' => $item->product,
                'quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
                'is_guest' => false,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function guestCartItems(): array
    {
        return $this->guestCartService->items()->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function guestProfile(): array
    {
        return [
            'name' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'city' => '',
            'addresses' => [],
        ];
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
}
