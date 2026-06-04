<?php

namespace App\Support;

use App\Models\AdminRole;

class AdminWebLabel
{
    public static function metric(string $key): string
    {
        return match ($key) {
            'revenue' => 'Doanh thu',
            'today_revenue' => 'Doanh thu hôm nay',
            'monthly_revenue' => 'Doanh thu tháng',
            'range_revenue' => 'Doanh thu theo khoảng',
            'successful_orders' => 'Đơn thành công',
            'processing_orders' => 'Đơn đang xử lý',
            'pending_orders' => 'Đơn chờ xác nhận',
            'bank_transfer_pending' => 'Chờ xác nhận chuyển khoản',
            'customer_reported_transfer' => 'Khách đã báo chuyển khoản',
            'shipping_orders' => 'Đơn đang giao',
            'delivery_failed_orders' => 'Đơn giao thất bại',
            'low_stock_products' => 'Sản phẩm sắp hết hàng',
            'low_stock_threshold' => 'Ngưỡng tồn kho thấp',
            'supplier_count' => 'Số nhà cung cấp',
            'product_count' => 'Số sản phẩm',
            'average_order_value' => 'Giá trị đơn trung bình',
            'complaint_count' => 'Số khiếu nại',
            default => ucfirst(str_replace('_', ' ', $key)),
        };
    }

    public static function orderStatus(?string $status): string
    {
        return match (strtoupper((string) $status)) {
            'PENDING' => 'Chờ xác nhận',
            'CONFIRMED' => 'Đã xác nhận',
            'PACKED' => 'Đã đóng gói',
            'SHIPPED' => 'Đang giao',
            'DELIVERED' => 'Đã giao',
            'DELIVERY_FAILED' => 'Giao thất bại',
            'CANCELLED' => 'Đã hủy',
            default => (string) $status,
        };
    }

    public static function paymentStatus(?string $status): string
    {
        if ($status === null || $status === '') {
            return 'Không có';
        }

        return match (strtoupper($status)) {
            'PENDING' => 'Chờ thanh toán',
            'SUCCESS' => 'Thành công',
            'FAILED' => 'Thất bại',
            'REFUNDED' => 'Đã hoàn tiền',
            default => $status,
        };
    }

    public static function paymentMethod(?string $method): string
    {
        if ($method === null || $method === '') {
            return 'Không có';
        }

        return match (strtoupper($method)) {
            'COD' => 'Thanh toán khi nhận hàng',
            'BANK_TRANSFER' => 'Chuyển khoản ngân hàng',
            default => $method,
        };
    }

    public static function postStatus(?string $status): string
    {
        return match (strtoupper((string) $status)) {
            'DRAFT' => 'Bản nháp',
            'PUBLISHED' => 'Đã xuất bản',
            default => (string) $status,
        };
    }

    public static function commentStatus(?string $status): string
    {
        return match (strtoupper((string) $status)) {
            'VISIBLE' => 'Hiển thị',
            'HIDDEN' => 'Ẩn',
            default => (string) $status,
        };
    }

    public static function invitationStatus(?string $status): string
    {
        return match (strtoupper((string) $status)) {
            'DRAFT' => 'Bản nháp',
            'SENT' => 'Đã gửi',
            default => (string) $status,
        };
    }

    public static function shippingProvider(?string $provider): string
    {
        return match (strtoupper((string) $provider)) {
            'MANUAL' => 'Thủ công',
            'GHN' => 'GHN',
            default => (string) $provider,
        };
    }

    public static function active(bool $value): string
    {
        return $value ? 'Đang hoạt động' : 'Ngừng hoạt động';
    }

    public static function yesNo(bool $value): string
    {
        return $value ? 'Có' : 'Không';
    }

    public static function enabled(bool $value): string
    {
        return $value ? 'Bật' : 'Tắt';
    }

    public static function bulkOrderAction(string $action): string
    {
        return match ($action) {
            'CONFIRM' => 'Xác nhận đơn',
            'SHIP' => 'Chuyển sang giao hàng',
            'DELIVER' => 'Đánh dấu đã giao',
            'MARK_DELIVERY_FAILED' => 'Đánh dấu giao thất bại',
            'CANCEL' => 'Hủy đơn',
            'RESHIP' => 'Giao lại',
            default => $action,
        };
    }

    public static function permissionGroup(?string $group): string
    {
        return match (strtolower(trim((string) $group))) {
            'dashboard' => 'Tổng quan',
            'community' => 'Cộng đồng',
            'settings' => 'Cài đặt',
            'users' => 'Người dùng',
            'catalog' => 'Danh mục',
            'orders' => 'Đơn hàng',
            'shipping' => 'Vận chuyển',
            'supplier portal' => 'Cổng nhà cung cấp',
            'warehouse portal' => 'Cổng kho',
            default => (string) $group,
        };
    }

    public static function permissionName(?string $key, ?string $fallback = null): string
    {
        $label = match ((string) $key) {
            'admin.dashboard.view' => 'Xem bảng điều khiển',
            'admin.community.view' => 'Xem cộng đồng',
            'admin.community.invitation.create' => 'Tạo lời mời nhà cung cấp',
            'admin.community.posts.view' => 'Xem bài viết',
            'admin.community.posts.create' => 'Tạo bài viết',
            'admin.community.posts.update' => 'Cập nhật bài viết',
            'admin.community.posts.delete' => 'Xóa bài viết',
            'admin.community.comments.moderate' => 'Kiểm duyệt bình luận bài viết',
            'admin.settings.view' => 'Xem cài đặt quản trị',
            'admin.settings.update' => 'Cập nhật cài đặt quản trị',
            'admin.users.view' => 'Xem người dùng',
            'admin.users.create' => 'Tạo người dùng',
            'admin.users.update' => 'Cập nhật người dùng',
            'admin.users.delete' => 'Xóa người dùng',
            'admin.products.view' => 'Xem sản phẩm',
            'admin.products.create' => 'Tạo sản phẩm',
            'admin.products.update' => 'Cập nhật sản phẩm',
            'admin.products.delete' => 'Xóa sản phẩm',
            'admin.categories.create' => 'Tạo danh mục',
            'admin.categories.update' => 'Cập nhật danh mục',
            'admin.categories.delete' => 'Xóa danh mục',
            'admin.suppliers.create' => 'Tạo nhà cung cấp',
            'admin.suppliers.update' => 'Cập nhật nhà cung cấp',
            'admin.suppliers.delete' => 'Xóa nhà cung cấp',
            'admin.orders.view' => 'Xem đơn hàng',
            'admin.orders.status.update' => 'Cập nhật trạng thái đơn hàng',
            'admin.orders.payment.update' => 'Cập nhật trạng thái thanh toán',
            'admin.orders.bulk.update' => 'Cập nhật hàng loạt đơn hàng',
            'admin.shipping_carriers.view' => 'Xem đơn vị vận chuyển',
            'admin.shipping_carriers.create' => 'Tạo đơn vị vận chuyển',
            'admin.shipping_carriers.update' => 'Cập nhật đơn vị vận chuyển',
            'admin.shipping_carriers.delete' => 'Xóa đơn vị vận chuyển',
            'admin.supplier.inventory.view' => 'Xem cổng tồn kho nhà cung cấp',
            'admin.supplier.requisitions.view' => 'Xem cổng yêu cầu nhập của nhà cung cấp',
            'admin.supplier.processing.view' => 'Xem cổng xử lý nhà cung cấp',
            'admin.supplier.orders.view' => 'Xem cổng đơn hàng nhà cung cấp',
            'admin.supplier.help.view' => 'Xem cổng hỗ trợ nhà cung cấp',
            'admin.warehouse.inventory.view' => 'Xem cổng tồn kho',
            'admin.warehouse.requisitions.view' => 'Xem cổng yêu cầu kho',
            'admin.warehouse.fulfillment.view' => 'Xem cổng hoàn tất đơn',
            'admin.warehouse.supplier_orders.view' => 'Xem cổng đơn nhà cung cấp của kho',
            'admin.warehouse.help.view' => 'Xem cổng hỗ trợ kho',
            default => null,
        };

        return $label ?? ($fallback ?: (string) $key);
    }

    public static function roleName(?string $slug, ?string $name = null): string
    {
        if ($slug === AdminRole::SUPER_ADMIN_SLUG) {
            return 'Quản trị tối cao';
        }

        return $name ?: 'Chưa có vai trò';
    }
}
