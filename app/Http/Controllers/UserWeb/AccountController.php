<?php

namespace App\Http\Controllers\UserWeb;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;
use App\Services\AccountService;
use App\Services\CartService;
use App\Services\OrderService;
use App\Support\LocalGhnLocationCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    private const CANCEL_REASONS = [
        'Đặt nhầm sản phẩm',
        'Muốn thay đổi địa chỉ',
        'Muốn thay đổi phương thức thanh toán',
        'Không còn nhu cầu',
        'Lý do khác',
    ];

    public function __construct(
        private readonly AccountService $accountService,
        private readonly CartService $cartService,
        private readonly OrderService $orderService,
        private readonly LocalGhnLocationCatalog $locationCatalog,
    ) {
    }

    public function profile(): View
    {
        return $this->accountView('user-web.account.profile');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'favorite_region' => ['nullable', 'string', 'max:120'],
            'avatar' => ['nullable', 'string'],
            'newsletter' => ['sometimes', 'boolean'],
            'sms_alerts' => ['sometimes', 'boolean'],
            'order_email' => ['sometimes', 'boolean'],
            'security_alerts' => ['sometimes', 'boolean'],
        ]);

        foreach (['newsletter', 'sms_alerts', 'order_email', 'security_alerts'] as $field) {
            $validated[$field] = $request->has($field)
                ? $request->boolean($field)
                : (bool) $this->customer()->getAttribute($field);
        }

        $this->accountService->updateProfile($this->customer(), $validated);

        return back()->with('status', 'Đã cập nhật thông tin cá nhân.');
    }

    public function security(): View
    {
        return $this->accountView('user-web.account.security');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $this->accountService->changePassword(
            $this->customer(),
            (string) $validated['current_password'],
            (string) $validated['new_password'],
        );

        return back()->with('status', 'Đã cập nhật mật khẩu.');
    }

    public function notifications(): View
    {
        return $this->accountView('user-web.account.notifications', [
            'notifications' => $this->accountService->listNotifications($this->customer(), 10),
        ]);
    }

    public function markNotificationRead(Notification $notification): RedirectResponse
    {
        $this->accountService->markNotificationRead($this->customer(), $notification);

        return back()->with('status', 'Đã đánh dấu thông báo đã đọc.');
    }

    public function addresses(): View
    {
        return $this->accountView('user-web.account.addresses', [
            'provinces' => $this->locationCatalog->provinces(),
        ]);
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $this->accountService->createAddress($this->customer(), $this->addressPayload($request));

        return back()->with('status', 'Đã thêm địa chỉ.');
    }

    public function updateAddress(Request $request, UserAddress $address): RedirectResponse
    {
        $this->accountService->updateAddress($this->customer(), $address, $this->addressPayload($request));

        return back()->with('status', 'Đã cập nhật địa chỉ.');
    }

    public function destroyAddress(UserAddress $address): RedirectResponse
    {
        $this->accountService->deleteAddress($this->customer(), $address);

        return back()->with('status', 'Đã xóa địa chỉ.');
    }

    public function setDefaultAddress(UserAddress $address): RedirectResponse
    {
        $this->accountService->setDefaultAddress($this->customer(), $address);

        return back()->with('status', 'Đã đặt địa chỉ mặc định.');
    }

    public function rewards(): View
    {
        return $this->accountView('user-web.account.rewards');
    }

    public function redeemReward(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'points_cost' => ['required', 'integer', 'min:1'],
        ]);

        $this->accountService->redeemReward($this->customer(), (string) $validated['title'], (int) $validated['points_cost']);

        return back()->with('status', 'Đã đổi ưu đãi.');
    }

    public function wishlist(): View
    {
        $wishlist = $this->accountService->listWishlist($this->customer());

        return $this->accountView('user-web.account.wishlist', [
            'wishlistProducts' => Product::query()
                ->available()
                ->with(['category', 'supplier', 'region'])
                ->whereIn('id', $wishlist['product_ids'])
                ->get(),
        ]);
    }

    public function addWishlist(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::query()->available()->findOrFail((int) $validated['product_id']);
        $this->accountService->addWishlistItem($this->customer(), $product);

        return back()->with('status', 'Đã thêm vào danh sách yêu thích.');
    }

    public function removeWishlist(Product $product): RedirectResponse
    {
        $this->accountService->removeWishlistItem($this->customer(), $product);

        return back()->with('status', 'Đã xóa khỏi danh sách yêu thích.');
    }

    public function orders(Request $request, ?int $order = null): View
    {
        $status = (string) $request->query('status', 'all');
        $search = trim((string) $request->query('q', ''));
        $activeOrder = $order ? $this->orderService->findUserOrder($this->customer(), $order) : null;
        abort_if($order && ! $activeOrder, 404);

        $query = Order::query()
            ->where('user_id', $this->customer()->id)
            ->with(['items', 'payment', 'shipment.carrier'])
            ->orderByDesc('id');

        if ($status !== 'all' && $status !== '') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->where('order_no', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")
                    ->orWhere('recipient_phone', 'like', "%{$search}%")
                    ->orWhere('shipping_address', 'like', "%{$search}%")
                    ->orWhereHas('payment', function ($paymentQuery) use ($search): void {
                        $paymentQuery->where('payment_status', 'like', "%{$search}%");
                    });
            });
        }

        return $this->accountView('user-web.account.orders', [
            'orders' => $query->paginate(10)->withQueryString(),
            'activeOrder' => $activeOrder,
            'cancelReasons' => self::CANCEL_REASONS,
            'orderFilters' => [
                'status' => $status,
                'q' => $search,
            ],
            'availableStatuses' => Order::query()
                ->where('user_id', $this->customer()->id)
                ->select('status')
                ->distinct()
                ->orderBy('status')
                ->pluck('status')
                ->all(),
        ]);
    }

    public function cancelOrder(Request $request, int $order): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', Rule::in(self::CANCEL_REASONS)],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $orderModel = $this->orderService->findUserOrder($this->customer(), $order);
        abort_unless($orderModel, 404);

        $this->orderService->cancelOrderByCustomer(
            $orderModel,
            $this->customer(),
            (string) $validated['reason'],
            ! empty($validated['note']) ? (string) $validated['note'] : null,
        );

        return back()->with('status', 'Đã gửi yêu cầu hủy đơn.');
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

        return back()->with('status', 'Đã xác nhận thông tin chuyển khoản.');
    }

    public function confirmDelivery(int $order): RedirectResponse
    {
        $orderModel = $this->orderService->findUserOrder($this->customer(), $order);
        abort_unless($orderModel, 404);

        $this->orderService->confirmDeliveredByCustomer($orderModel, $this->customer());

        return back()->with('status', 'Đã xác nhận đã nhận hàng.');
    }

    public function reorder(Request $request, int $order): RedirectResponse
    {
        $orderModel = $this->orderService->findUserOrder($this->customer(), $order);
        abort_unless($orderModel, 404);

        foreach ($orderModel->items as $item) {
            if ($item->product_id) {
                $this->cartService->addItem($this->customer(), (int) $item->product_id, (int) $item->quantity);
            }
        }

        if ($request->boolean('go_to_checkout')) {
            return redirect()->route('user-web.checkout.show')->with('status', 'Đã thêm lại sản phẩm vào giỏ.');
        }

        return back()->with('status', 'Đã thêm lại sản phẩm vào giỏ.');
    }

    public function disputes(): View
    {
        return $this->accountView('user-web.account.disputes', [
            'complaints' => $this->accountService->listComplaints($this->customer(), 10),
            'orders' => Order::query()
                ->where('user_id', $this->customer()->id)
                ->with('items')
                ->orderByDesc('id')
                ->take(20)
                ->get(),
        ]);
    }

    public function storeDispute(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer'],
            'product_id' => ['required', 'integer'],
            'reason' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image_url' => ['nullable', 'url', 'max:255'],
        ]);

        $this->accountService->createComplaint($this->customer(), $validated);

        return back()->with('status', 'Đã gửi khiếu nại.');
    }

    private function accountView(string $view, array $data = []): View
    {
        $profile = $this->accountService->profilePayload($this->customer());

        return view($view, array_merge([
            'profile' => $profile,
            'rewardSnapshot' => $profile['reward_snapshot'],
        ], $data));
    }

    /**
     * @return array<string, mixed>
     */
    private function addressPayload(Request $request): array
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'recipient' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:20'],
            'line1' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'ghn_province_id' => ['nullable', 'integer', 'min:1'],
            'ghn_province_name' => ['nullable', 'string', 'max:120'],
            'ghn_district_id' => ['nullable', 'integer', 'min:1'],
            'ghn_district_name' => ['nullable', 'string', 'max:120'],
            'ghn_ward_code' => ['nullable', 'string', 'max:30'],
            'ghn_ward_name' => ['nullable', 'string', 'max:120'],
            'note' => ['nullable', 'string', 'max:500'],
            'is_default' => ['sometimes', 'boolean'],
        ]);

        $validated['is_default'] = $request->boolean('is_default');

        return $validated;
    }

    private function customer(): User
    {
        /** @var User $user */
        $user = Auth::guard('web')->user();

        return $user;
    }
}
