<?php

namespace App\Support\UserWeb;

use App\Models\Cart;
use App\Models\Complaint;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class UserWebPresenter
{
    /**
     * @return array<string, string>
     */
    public function orderStatusLabels(): array
    {
        return [
            Order::STATUS_PENDING => 'Chờ xác nhận',
            Order::STATUS_CONFIRMED => 'Đã xác nhận',
            Order::STATUS_PACKED => 'Đã đóng gói',
            Order::STATUS_SHIPPED => 'Đang giao',
            Order::STATUS_DELIVERED => 'Đã giao',
            Order::STATUS_DELIVERY_FAILED => 'Giao thất bại',
            Order::STATUS_CANCELLED => 'Đã hủy',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function paymentStatusLabels(): array
    {
        return [
            Payment::STATUS_PENDING => 'Chờ thanh toán',
            Payment::STATUS_SUCCESS => 'Thanh toán thành công',
            Payment::STATUS_FAILED => 'Thanh toán thất bại',
            Payment::STATUS_REFUNDED => 'Đã hoàn tiền',
            'CANCELLED' => 'Đã hủy',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function paymentMethodLabels(): array
    {
        return [
            Order::PAYMENT_METHOD_COD => 'Thanh toán khi nhận hàng',
            Order::PAYMENT_METHOD_BANK_TRANSFER => 'Chuyển khoản ngân hàng',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function notificationStatusLabels(): array
    {
        return [
            Notification::STATUS_PENDING => 'Chờ gửi',
            Notification::STATUS_SENT => 'Đã gửi',
            Notification::STATUS_FAILED => 'Gửi thất bại',
            Notification::STATUS_READ => 'Đã đọc',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function complaintStatusLabels(): array
    {
        return [
            Complaint::STATUS_OPEN => 'Đang mở',
            Complaint::STATUS_IN_REVIEW => 'Đang xem xét',
            Complaint::STATUS_RESOLVED => 'Đã xử lý',
            Complaint::STATUS_REJECTED => 'Đã từ chối',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function supportTicketStatusLabels(): array
    {
        return [
            SupportTicket::STATUS_OPEN => 'Đang mở',
            SupportTicket::STATUS_RESOLVED => 'Đã xử lý',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function rewardTierLabels(): array
    {
        return [
            'Bronze' => 'Đồng',
            'Silver' => 'Bạc',
            'Gold' => 'Vàng',
            'Diamond' => 'Kim cương',
        ];
    }

    public function orderStatusLabel(?string $status): string
    {
        return $this->orderStatusLabels()[$status] ?? $this->fallbackLabel($status);
    }

    public function paymentStatusLabel(?string $status): string
    {
        return $this->paymentStatusLabels()[$status] ?? $this->fallbackLabel($status);
    }

    public function paymentMethodLabel(?string $method): string
    {
        return $this->paymentMethodLabels()[$method] ?? $this->fallbackLabel($method);
    }

    public function notificationStatusLabel(?string $status): string
    {
        return $this->notificationStatusLabels()[$status] ?? $this->fallbackLabel($status);
    }

    public function complaintStatusLabel(?string $status): string
    {
        return $this->complaintStatusLabels()[$status] ?? $this->fallbackLabel($status);
    }

    public function supportTicketStatusLabel(?string $status): string
    {
        return $this->supportTicketStatusLabels()[$status] ?? $this->fallbackLabel($status);
    }

    public function rewardTierLabel(?string $tier): string
    {
        return $this->rewardTierLabels()[$tier] ?? ($tier ?: 'Đồng');
    }

    public function money(float|int|string|null $value): string
    {
        return number_format((float) ($value ?? 0), 0, ',', '.').' đ';
    }

    public function compactMoney(float|int|string|null $value): string
    {
        $amount = (float) ($value ?? 0);

        if ($amount >= 1000000) {
            return rtrim(rtrim(number_format($amount / 1000000, 1, ',', '.'), '0'), ',').' tr đ';
        }

        if ($amount >= 1000) {
            return rtrim(rtrim(number_format($amount / 1000, 1, ',', '.'), '0'), ',').'k đ';
        }

        return $this->money($amount);
    }

    public function date(mixed $value): string
    {
        if (! $value) {
            return '--';
        }

        try {
            return Carbon::parse($value)->locale('vi')->translatedFormat('d M Y');
        } catch (\Throwable) {
            return '--';
        }
    }

    public function productUrl(Product|array $product): string
    {
        $id = $product instanceof Product ? $product->id : ($product['id'] ?? null);
        $slug = $product instanceof Product ? $product->slug : ($product['slug'] ?? null);
        $name = $product instanceof Product ? $product->name : ($product['name'] ?? '');

        return route('user-web.products.show', [
            'slug' => $slug ?: $this->storefrontSlug((int) $id, (string) $name),
        ]);
    }

    public function storefrontSlug(int $productId, string $name): string
    {
        return Str::slug($name).'-'.$productId;
    }

    public function stockLabel(Product $product): string
    {
        if (! $product->is_active || $product->stock_quantity <= 0) {
            return 'Tạm hết hàng';
        }

        if ($product->stock_quantity <= 5) {
            return 'Sắp hết hàng';
        }

        return 'Còn hàng';
    }

    public function cartQuantity(?Cart $cart): int
    {
        if (! $cart) {
            return 0;
        }

        $cart->loadMissing('items');

        return (int) $cart->items->sum('quantity');
    }

    public function redirectForUser(?User $user): string
    {
        if (! $user) {
            return route('user-web.login');
        }

        return match ($user->role) {
            User::ROLE_ADMIN => route('admin-web.dashboard'),
            User::ROLE_CUSTOMER => route('user-web.account.profile'),
            User::ROLE_SUPPLIER => route('user-web.supplier.inventory'),
            User::ROLE_WAREHOUSE_STAFF => route('user-web.warehouse.inventory'),
            default => route('user-web.login'),
        };
    }

    public function activePath(string $path, string $activeClass = 'border-b-2 border-primary text-primary', string $inactiveClass = 'text-zinc-500 hover:text-green-800'): string
    {
        return request()->path() === trim($path, '/') || request()->is(trim($path, '/').'/*')
            ? $activeClass
            : $inactiveClass;
    }

    /**
     * @return list<int>
     */
    public function pageWindow(LengthAwarePaginator $paginator): array
    {
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $start = max(1, $current - 2);
        $end = min($last, $current + 2);

        return range($start, max($start, $end));
    }

    private function fallbackLabel(?string $value): string
    {
        if (! $value) {
            return '--';
        }

        return Str::of($value)->lower()->replace('_', ' ')->headline()->value();
    }
}
