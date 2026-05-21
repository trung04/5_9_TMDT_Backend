SET NAMES utf8mb4;
USE `ecommerce_db`;

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `payment_status_history`;
TRUNCATE TABLE `reviews`;
TRUNCATE TABLE `complaints`;
TRUNCATE TABLE `supplier_invitations`;
TRUNCATE TABLE `reward_redemptions`;
TRUNCATE TABLE `user_addresses`;
TRUNCATE TABLE `payments`;
TRUNCATE TABLE `order_status_history`;
TRUNCATE TABLE `order_items`;
TRUNCATE TABLE `cart_items`;
TRUNCATE TABLE `delivery_requests`;
TRUNCATE TABLE `supply_order_items`;
TRUNCATE TABLE `supply_orders`;
TRUNCATE TABLE `inventory_items`;
TRUNCATE TABLE `notifications`;
TRUNCATE TABLE `wishlist_items`;
TRUNCATE TABLE `admin_settings`;
TRUNCATE TABLE `admin_role_permission`;
TRUNCATE TABLE `admin_permissions`;
TRUNCATE TABLE `admin_roles`;
TRUNCATE TABLE `personal_access_tokens`;
TRUNCATE TABLE `password_reset_tokens`;
TRUNCATE TABLE `sessions`;
TRUNCATE TABLE `cache_locks`;
TRUNCATE TABLE `cache`;
TRUNCATE TABLE `failed_jobs`;
TRUNCATE TABLE `job_batches`;
TRUNCATE TABLE `jobs`;
TRUNCATE TABLE `orders`;
TRUNCATE TABLE `carts`;
TRUNCATE TABLE `prices`;
TRUNCATE TABLE `products`;
TRUNCATE TABLE `inventories`;
TRUNCATE TABLE `suppliers`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `users`;

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password_hash`, `address`, `city`, `favorite_region`, `avatar_url`, `newsletter`, `sms_alerts`, `order_email`, `security_alerts`, `reward_points`, `reward_tier`, `next_tier_points`, `role`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Nguyễn Văn Admin', 'admin@shop.local', '0900000001', '$2y$10$ce7ubt0LylfseDirp.DoN.HGxACLy6f7VekTno./rqHKJOOA6zuKq', '12 Nguyễn Huệ', 'Hà Nội', 'Tây Bắc', NULL, FALSE, FALSE, TRUE, TRUE, 0, 'Bronze', 500, 'ADMIN', 'ACTIVE', TRUE, '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(2, 'Trần Thị Customer', 'customer1@shop.local', '0900000002', '$2y$10$ce7ubt0LylfseDirp.DoN.HGxACLy6f7VekTno./rqHKJOOA6zuKq', '101 Lê Duẩn', 'Hà Nội', 'Đông Bắc', NULL, TRUE, TRUE, TRUE, TRUE, 720, 'Silver', 1000, 'CUSTOMER', 'ACTIVE', TRUE, '2026-01-02 09:00:00', '2026-01-02 09:00:00'),
(3, 'Lê Văn Kho', 'warehouse@shop.local', '0900000003', '$2y$10$ce7ubt0LylfseDirp.DoN.HGxACLy6f7VekTno./rqHKJOOA6zuKq', 'KCN Bắc Từ Liêm', 'Hà Nội', NULL, NULL, FALSE, TRUE, TRUE, TRUE, 0, 'Bronze', 500, 'WAREHOUSE_STAFF', 'ACTIVE', TRUE, '2026-01-03 10:00:00', '2026-01-03 10:00:00'),
(4, 'Phạm Thị NCC', 'supplieruser@shop.local', '0900000004', '$2y$10$ce7ubt0LylfseDirp.DoN.HGxACLy6f7VekTno./rqHKJOOA6zuKq', '45 Võ Văn Tần', 'TP.HCM', NULL, NULL, FALSE, TRUE, TRUE, TRUE, 0, 'Bronze', 500, 'SUPPLIER', 'ACTIVE', TRUE, '2026-01-04 11:00:00', '2026-01-04 11:00:00'),
(5, 'Đỗ Minh Khách', 'customer2@shop.local', '0900000005', '$2y$10$ce7ubt0LylfseDirp.DoN.HGxACLy6f7VekTno./rqHKJOOA6zuKq', '22 Điện Biên Phủ', 'TP.HCM', 'Nam Bộ', NULL, TRUE, FALSE, TRUE, TRUE, 340, 'Bronze', 500, 'CUSTOMER', 'ACTIVE', TRUE, '2026-01-05 12:00:00', '2026-01-05 12:00:00'),
(6, 'Active User', 'active@example.com', '0901111111', '$2y$10$ce7ubt0LylfseDirp.DoN.HGxACLy6f7VekTno./rqHKJOOA6zuKq', '1 Trần Phú', 'Đà Nẵng', NULL, NULL, FALSE, FALSE, TRUE, TRUE, 0, 'Bronze', 500, 'CUSTOMER', 'ACTIVE', TRUE, '2026-01-06 08:00:00', '2026-01-06 08:00:00'),
(7, 'Blocked User', 'blocked@example.com', '0902222222', '$2y$10$ce7ubt0LylfseDirp.DoN.HGxACLy6f7VekTno./rqHKJOOA6zuKq', NULL, NULL, NULL, NULL, FALSE, FALSE, TRUE, TRUE, 0, 'Bronze', 500, 'CUSTOMER', 'BLOCKED', TRUE, '2026-01-06 08:05:00', '2026-01-06 08:05:00'),
(8, 'Inactive User', 'inactive@example.com', '0903333333', '$2y$10$ce7ubt0LylfseDirp.DoN.HGxACLy6f7VekTno./rqHKJOOA6zuKq', NULL, NULL, NULL, NULL, FALSE, FALSE, TRUE, TRUE, 0, 'Bronze', 500, 'CUSTOMER', 'INACTIVE', TRUE, '2026-01-06 08:10:00', '2026-01-06 08:10:00');

INSERT INTO `admin_roles` (`id`, `name`, `slug`, `description`, `is_super`, `is_system`, `created_by_admin_id`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'super_admin', 'System role with unrestricted admin access.', TRUE, TRUE, NULL, '2026-01-01 08:00:00', '2026-01-01 08:00:00');

INSERT INTO `admin_permissions` (`id`, `key`, `name`, `group`, `description`, `created_at`, `updated_at`) VALUES
(1, 'admin.dashboard.view', 'View dashboard', 'Dashboard', 'View admin dashboard metrics and queues.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(2, 'admin.community.view', 'View community', 'Community', 'View community and supplier invitation data.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(3, 'admin.community.invitation.create', 'Create supplier invitations', 'Community', 'Create supplier invitation records.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(4, 'admin.settings.view', 'View admin settings', 'Settings', 'View admin settings for the current account.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(5, 'admin.settings.update', 'Update admin settings', 'Settings', 'Update admin settings for the current account.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(6, 'admin.products.view', 'View products', 'Catalog', 'View products in the admin catalog.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(7, 'admin.products.create', 'Create products', 'Catalog', 'Create products in the admin catalog.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(8, 'admin.products.update', 'Update products', 'Catalog', 'Update products and product status.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(9, 'admin.products.delete', 'Delete products', 'Catalog', 'Delete products when no related data exists.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(10, 'admin.categories.create', 'Create categories', 'Catalog', 'Create catalog categories.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(11, 'admin.categories.update', 'Update categories', 'Catalog', 'Update catalog categories.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(12, 'admin.categories.delete', 'Delete categories', 'Catalog', 'Delete catalog categories.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(13, 'admin.suppliers.create', 'Create suppliers', 'Catalog', 'Create supplier records.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(14, 'admin.suppliers.update', 'Update suppliers', 'Catalog', 'Update supplier records.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(15, 'admin.suppliers.delete', 'Delete suppliers', 'Catalog', 'Delete or deactivate supplier records.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(16, 'admin.orders.view', 'View orders', 'Orders', 'View admin order lists and order details.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(17, 'admin.orders.status.update', 'Update order status', 'Orders', 'Update fulfillment status for orders.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(18, 'admin.orders.payment.update', 'Update payment status', 'Orders', 'Update payment status for orders.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(19, 'admin.orders.bulk.update', 'Bulk update orders', 'Orders', 'Run bulk order status actions.', '2026-01-01 08:00:00', '2026-01-01 08:00:00');

INSERT INTO `admin_role_permission` (`admin_role_id`, `admin_permission_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10), (1, 11), (1, 12), (1, 13), (1, 14), (1, 15), (1, 16), (1, 17), (1, 18), (1, 19);

UPDATE `users`
SET `admin_role_id` = 1
WHERE `email` = 'admin@shop.local' AND `role` = 'ADMIN';

INSERT INTO `user_addresses` (`id`, `user_id`, `label`, `recipient`, `phone`, `line1`, `city`, `note`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 2, 'Nhà riêng', 'Trần Thị Customer', '0900000002', '101 Lê Duẩn', 'Hà Nội', 'Giao sau 18h nếu có thể.', TRUE, '2026-01-03 09:00:00', '2026-01-03 09:00:00'),
(2, 2, 'Văn phòng', 'Trần Thị Customer', '0900000002', '18 Duy Tân', 'Hà Nội', NULL, FALSE, '2026-01-04 09:00:00', '2026-01-04 09:00:00'),
(3, 5, 'Căn hộ', 'Đỗ Minh Khách', '0900000005', '22 Điện Biên Phủ', 'TP.HCM', NULL, TRUE, '2026-01-05 12:30:00', '2026-01-05 12:30:00');

INSERT INTO `reward_redemptions` (`id`, `user_id`, `title`, `points_used`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'Miễn phí vận chuyển đơn kế tiếp', 300, 'COMPLETED', '2026-02-08 18:00:00', '2026-02-08 18:00:00'),
(2, 2, 'Quà mẫu theo mùa', 180, 'COMPLETED', '2026-02-12 19:00:00', '2026-02-12 19:00:00');

INSERT INTO `supplier_invitations` (`id`, `supplier_name`, `contact_name`, `email`, `categories`, `note`, `status`, `created_by_user_id`, `created_at`, `updated_at`) VALUES
(1, 'Hợp tác xã Gạo Mường', 'Nguyễn Thị Lan', 'lienhe@gaomuong.vn', JSON_ARRAY('Gạo đặc sản', 'Quà tặng địa phương'), 'Mời tham gia bộ sưu tập mùa hè.', 'SENT', 1, '2026-02-11 09:00:00', '2026-02-11 09:00:00'),
(2, 'Nhà vườn Trà Cổ', 'Lê Hữu Phúc', 'hello@traco.vn', JSON_ARRAY('Trà', 'Đặc sản rừng'), 'Cần bổ sung nguồn hàng Tây Bắc.', 'SENT', 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00');

INSERT INTO `categories` (`id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Gạo - Nông sản đặc sản', 'Các loại gạo, nông sản và thực phẩm khô đặc sản vùng miền.', TRUE, '2026-01-01 08:10:00', '2026-01-01 08:10:00'),
(2, 'Món ăn truyền thống', 'Các món ăn vặt, bánh và món ăn truyền thống đóng gói sẵn.', TRUE, '2026-01-01 08:11:00', '2026-01-01 08:11:00'),
(3, 'Mật ong - Đặc sản rừng', 'Mật ong, sản vật tự nhiên và đặc sản rừng núi.', TRUE, '2026-01-01 08:12:00', '2026-01-01 08:12:00'),
(4, 'Trà - Cà phê đặc sản', 'Trà, cà phê và các sản phẩm làm quà tặng đặc sản.', TRUE, '2026-01-01 08:13:00', '2026-01-01 08:13:00');

INSERT INTO `suppliers` (`id`, `supplier_code`, `name`, `contact_name`, `phone`, `email`, `address`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'SUP-TN01', 'HTX Chè Tân Cương Thái Nguyên', 'Nguyễn Văn Kiên', '0911000001', 'tanCuong@dacsan.vn', 'Tân Cương, Thái Nguyên', TRUE, '2026-01-01 08:20:00', '2026-01-01 08:20:00'),
(2, 'SUP-ST25', 'Cơ sở Gạo Đặc Sản Sóc Trăng', 'Trần Quốc Minh', '0911000002', 'st25@dacsan.vn', 'Sóc Trăng', TRUE, '2026-01-01 08:21:00', '2026-01-01 08:21:00'),
(3, 'SUP-DL01', 'Nông Sản Đà Lạt Premium', 'Lê Thu Tâm', '0911000003', 'dalat@dacsan.vn', 'Đà Lạt, Lâm Đồng', TRUE, '2026-01-01 08:22:00', '2026-01-01 08:22:00'),
(4, 'SUP-TQ01', 'Đặc Sản Miền Núi Tây Bắc', 'Phạm Thị Hương', '0911000004', 'taybac@dacsan.vn', 'Sơn La', TRUE, '2026-01-01 08:23:00', '2026-01-01 08:23:00');

INSERT INTO `inventories` (`id`, `name`, `location`, `created_at`, `updated_at`) VALUES
(1, 'Kho Hà Nội', 'KCN Bắc Từ Liêm, Hà Nội', '2026-01-01 08:30:00', '2026-01-01 08:30:00'),
(2, 'Kho TP HCM', 'Thủ Đức, TP.HCM', '2026-01-01 08:31:00', '2026-01-01 08:31:00');

INSERT INTO `products` (`id`, `category_id`, `supplier_id`, `sku`, `name`, `description`, `sale_price`, `stock_quantity`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 'TRA-TC-200', 'Trà Tân Cương Thái Nguyên 200g', 'Trà xanh Thái Nguyên hương cốm non, nước xanh, vị chát dịu và hậu ngọt.', 229000.00, 30, TRUE, '2026-01-01 09:00:00', '2026-01-01 09:00:00'),
(2, 1, 2, 'GAO-ST25-5KG', 'Gạo thơm đặc sản ST25 5kg', 'Gạo thơm hạt dài, cơm dẻo mềm, phù hợp bữa ăn gia đình.', 290000.00, 24, TRUE, '2026-01-01 09:05:00', '2026-01-01 09:05:00'),
(3, 4, 1, 'TRA-TC-PRE-500', 'Trà Tân Cương thượng hạng 500g', 'Trà Tân Cương loại thượng hạng, đóng túi đẹp, phù hợp làm quà biếu.', 459000.00, 18, TRUE, '2026-01-01 09:10:00', '2026-01-01 09:10:00'),
(4, 4, 3, 'CF-DL-500', 'Cà phê rang xay Đà Lạt 500g', 'Cà phê rang xay nguyên chất, mùi thơm đậm, hậu vị hài hòa.', 189000.00, 80, TRUE, '2026-01-01 09:15:00', '2026-01-01 09:15:00'),
(5, 2, 3, 'BANH-SAMOSA-10', 'Bánh samosa truyền thống hộp 10 cái', 'Bánh chiên nhân đậm đà, thích hợp ăn nhẹ và đãi khách.', 99000.00, 60, TRUE, '2026-01-01 09:20:00', '2026-01-01 09:20:00'),
(6, 3, 4, 'MAT-ONG-RUNG-500', 'Mật ong rừng nguyên chất 500ml', 'Mật ong nguyên chất màu hổ phách, vị ngọt thanh, thích hợp bồi bổ sức khỏe.', 390000.00, 20, TRUE, '2026-01-01 09:25:00', '2026-01-01 09:25:00');

INSERT INTO `prices` (`id`, `product_id`, `supplier_id`, `cost_price`, `effective_from`, `effective_to`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 170000.00, '2026-01-01 09:30:00', NULL, TRUE, '2026-01-01 09:30:00', '2026-01-01 09:30:00'),
(2, 2, 2, 235000.00, '2026-01-01 09:31:00', NULL, TRUE, '2026-01-01 09:31:00', '2026-01-01 09:31:00'),
(3, 3, 1, 350000.00, '2026-01-01 09:32:00', NULL, TRUE, '2026-01-01 09:32:00', '2026-01-01 09:32:00'),
(4, 4, 3, 135000.00, '2026-01-01 09:33:00', NULL, TRUE, '2026-01-01 09:33:00', '2026-01-01 09:33:00'),
(5, 5, 3, 65000.00, '2026-01-01 09:34:00', NULL, TRUE, '2026-01-01 09:34:00', '2026-01-01 09:34:00'),
(6, 6, 4, 300000.00, '2026-01-01 09:35:00', NULL, TRUE, '2026-01-01 09:35:00', '2026-01-01 09:35:00');

INSERT INTO `carts` (`id`, `user_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'ACTIVE', '2026-02-01 10:00:00', '2026-02-01 10:00:00'),
(2, 5, 'CHECKED_OUT', '2026-02-02 11:00:00', '2026-02-03 09:00:00');

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 229000.00, 229000.00, '2026-02-01 10:05:00', '2026-02-01 10:05:00'),
(2, 1, 6, 1, 390000.00, 390000.00, '2026-02-01 10:06:00', '2026-02-01 10:06:00'),
(3, 2, 4, 2, 189000.00, 378000.00, '2026-02-02 11:05:00', '2026-02-02 11:05:00');

INSERT INTO `orders` (`id`, `user_id`, `order_no`, `recipient_name`, `recipient_phone`, `shipping_address`, `payment_method`, `status`, `subtotal`, `shipping_fee`, `discount_amount`, `total_amount`, `stock_deducted`, `stock_deducted_at`, `shipping_carrier`, `shipping_code`, `shipped_at`, `delivered_at`, `cancelled_at`, `note`, `created_at`, `updated_at`) VALUES
(1, 2, 'ORD-20260001', 'Trần Thị Customer', '0900000002', '101 Lê Duẩn, Hà Nội', 'COD', 'DELIVERED', 619000.00, 30000.00, 0.00, 649000.00, TRUE, '2026-02-05 08:10:00', 'GHN', 'GHN-ORD-20260001', '2026-02-06 08:00:00', '2026-02-07 17:00:00', NULL, 'Giao giờ hành chính, đóng gói cẩn thận.', '2026-02-05 08:00:00', '2026-02-07 17:00:00'),
(2, 5, 'ORD-20260002', 'Đỗ Minh Khách', '0900000005', '22 Điện Biên Phủ, TP.HCM', 'BANK_TRANSFER', 'CONFIRMED', 489000.00, 0.00, 30000.00, 459000.00, TRUE, '2026-02-10 09:30:00', NULL, NULL, NULL, NULL, NULL, 'Khách đã chuyển khoản, đơn hàng làm quà tặng.', '2026-02-10 09:00:00', '2026-02-10 09:30:00'),
(3, 2, 'ORD-20260003', 'Trần Thị Customer', '0900000002', '101 Lê Duẩn, Hà Nội', 'COD', 'SHIPPED', 388000.00, 25000.00, 13000.00, 400000.00, TRUE, '2026-02-12 14:15:00', 'GHTK', 'GHTK-ORD-20260003', '2026-02-13 08:00:00', NULL, NULL, 'Giao nhanh trong ngày nếu kịp tuyến.', '2026-02-12 14:00:00', '2026-02-13 08:00:00');

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name_snapshot`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Trà Tân Cương Thái Nguyên 200g', 1, 229000.00, 229000.00, '2026-02-05 08:05:00', '2026-02-05 08:05:00'),
(2, 1, 6, 'Mật ong rừng nguyên chất 500ml', 1, 390000.00, 390000.00, '2026-02-05 08:06:00', '2026-02-05 08:06:00'),
(3, 2, 3, 'Trà Tân Cương thượng hạng 500g', 1, 459000.00, 459000.00, '2026-02-10 09:05:00', '2026-02-10 09:05:00'),
(4, 2, 5, 'Bánh samosa truyền thống hộp 10 cái', 1, 99000.00, 99000.00, '2026-02-10 09:06:00', '2026-02-10 09:06:00'),
(5, 3, 4, 'Cà phê rang xay Đà Lạt 500g', 1, 189000.00, 189000.00, '2026-02-12 14:05:00', '2026-02-12 14:05:00'),
(6, 3, 5, 'Bánh samosa truyền thống hộp 10 cái', 2, 99000.00, 198000.00, '2026-02-12 14:06:00', '2026-02-12 14:06:00');

INSERT INTO `order_status_history` (`id`, `order_id`, `changed_by_user_id`, `from_status`, `to_status`, `note`, `changed_at`) VALUES
(1, 1, 1, NULL, 'PENDING', 'Đơn hàng mới tạo.', '2026-02-05 08:00:00'),
(2, 1, 1, 'PENDING', 'CONFIRMED', 'Đã xác nhận đơn.', '2026-02-05 08:10:00'),
(3, 1, 3, 'CONFIRMED', 'PACKED', 'Đã đóng gói cẩn thận.', '2026-02-05 10:00:00'),
(4, 1, 3, 'PACKED', 'SHIPPED', 'Bàn giao đơn vị vận chuyển.', '2026-02-06 08:00:00'),
(5, 1, 1, 'SHIPPED', 'DELIVERED', 'Giao thành công.', '2026-02-07 17:00:00'),
(6, 2, 1, NULL, 'PENDING', 'Đơn hàng mới tạo.', '2026-02-10 09:00:00'),
(7, 2, 1, 'PENDING', 'CONFIRMED', 'Đã xác nhận đơn sau khi nhận chuyển khoản.', '2026-02-10 09:30:00'),
(8, 3, 1, NULL, 'PENDING', 'Đơn hàng mới tạo.', '2026-02-12 14:00:00'),
(9, 3, 1, 'PENDING', 'CONFIRMED', 'Đã xác nhận đơn.', '2026-02-12 14:15:00'),
(10, 3, 3, 'CONFIRMED', 'PACKED', 'Đã đóng gói.', '2026-02-12 17:00:00'),
(11, 3, 3, 'PACKED', 'SHIPPED', 'Đã giao cho đối tác vận chuyển.', '2026-02-13 08:00:00');

INSERT INTO `payments` (`id`, `order_id`, `transaction_code`, `payment_method`, `payment_status`, `amount`, `gateway_name`, `gateway_reference`, `paid_at`, `raw_payload`, `created_at`, `updated_at`) VALUES
(1, 1, 'TXN-COD-20260001', 'COD', 'SUCCESS', 649000.00, NULL, NULL, '2026-02-07 17:00:00', JSON_OBJECT('collected_by', 'shipper', 'note', 'cash on delivery'), '2026-02-05 08:00:00', '2026-02-07 17:00:00'),
(2, 2, 'TXN-BANK-20260002', 'BANK_TRANSFER', 'SUCCESS', 459000.00, 'VCB', 'VCB-REF-20260002', '2026-02-10 09:25:00', JSON_OBJECT('bank', 'VCB', 'confirmed', TRUE), '2026-02-10 09:00:00', '2026-02-10 09:25:00'),
(3, 3, 'TXN-COD-20260003', 'COD', 'PENDING', 400000.00, NULL, NULL, NULL, JSON_OBJECT('instructions', 'Thanh toán tiền mặt khi đơn hàng được giao thành công.', 'status', 'pending_cod'), '2026-02-12 14:00:00', '2026-02-12 14:00:00');

INSERT INTO `payment_status_history` (`id`, `payment_id`, `order_id`, `changed_by_user_id`, `from_status`, `to_status`, `note`, `changed_at`) VALUES
(1, 1, 1, 2, NULL, 'PENDING', 'Khởi tạo trạng thái thanh toán khi khách đặt hàng.', '2026-02-05 08:00:00'),
(2, 1, 1, 1, 'PENDING', 'SUCCESS', 'Tự động xác nhận thanh toán COD khi đơn đã giao thành công.', '2026-02-07 17:00:00'),
(3, 2, 2, 5, NULL, 'PENDING', 'Khởi tạo trạng thái thanh toán khi khách đặt hàng.', '2026-02-10 09:00:00'),
(4, 2, 2, 1, 'PENDING', 'SUCCESS', 'Đã nhận chuyển khoản.', '2026-02-10 09:25:00'),
(5, 3, 3, 2, NULL, 'PENDING', 'Khởi tạo trạng thái thanh toán COD.', '2026-02-12 14:00:00');

INSERT INTO `complaints` (`id`, `order_id`, `user_id`, `product_id`, `reason`, `content`, `image_url`, `status`, `resolution_note`, `resolved_by_user_id`, `resolved_at`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 6, 'Vỏ chai bị rò nhẹ', 'Khách phản ánh nắp chai mật ong có dấu hiệu rò nhẹ khi nhận hàng.', 'https://example.com/images/complaint-1.jpg', 'RESOLVED', 'Đã đổi sản phẩm mới cho khách.', 1, '2026-02-09 10:00:00', '2026-02-08 09:00:00', '2026-02-09 10:00:00'),
(2, 3, 2, 4, 'Giao hàng chậm', 'Khách yêu cầu kiểm tra tình trạng đơn vị vận chuyển.', NULL, 'IN_REVIEW', NULL, NULL, NULL, '2026-02-13 09:30:00', '2026-02-13 09:30:00');

INSERT INTO `reviews` (`id`, `order_item_id`, `user_id`, `product_id`, `rating`, `comment`, `is_visible`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 5, 'Trà thơm, nước xanh, đóng gói đẹp, rất hợp mua làm quà.', TRUE, '2026-02-08 20:00:00', '2026-02-08 20:00:00'),
(2, 3, 5, 3, 5, 'Trà chất lượng tốt, vị đậm và hậu ngọt dễ chịu.', TRUE, '2026-02-11 18:00:00', '2026-02-11 18:00:00'),
(3, 5, 2, 4, 4, 'Cà phê thơm, dễ pha, giá hợp lý.', TRUE, '2026-02-13 19:00:00', '2026-02-13 19:00:00');

INSERT INTO `inventory_items` (`id`, `inventory_id`, `product_id`, `quantity_on_hand`, `reorder_level`, `safety_stock`, `last_counted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 18, 5, 3, '2026-02-01 08:00:00', '2026-01-01 10:00:00', '2026-02-01 08:00:00'),
(2, 2, 1, 12, 5, 3, '2026-02-01 08:30:00', '2026-01-01 10:01:00', '2026-02-01 08:30:00'),
(3, 1, 2, 10, 4, 2, '2026-02-01 08:00:00', '2026-01-01 10:02:00', '2026-02-01 08:00:00'),
(4, 2, 2, 14, 4, 2, '2026-02-01 08:30:00', '2026-01-01 10:03:00', '2026-02-01 08:30:00'),
(5, 1, 3, 8, 3, 2, '2026-02-01 08:00:00', '2026-01-01 10:04:00', '2026-02-01 08:00:00'),
(6, 2, 3, 10, 3, 2, '2026-02-01 08:30:00', '2026-01-01 10:05:00', '2026-02-01 08:30:00'),
(7, 1, 4, 35, 10, 6, '2026-02-01 08:00:00', '2026-01-01 10:06:00', '2026-02-01 08:00:00'),
(8, 2, 4, 45, 10, 6, '2026-02-01 08:30:00', '2026-01-01 10:07:00', '2026-02-01 08:30:00'),
(9, 1, 5, 25, 8, 5, '2026-02-01 08:00:00', '2026-01-01 10:08:00', '2026-02-01 08:00:00'),
(10, 2, 5, 35, 8, 5, '2026-02-01 08:30:00', '2026-01-01 10:09:00', '2026-02-01 08:30:00'),
(11, 1, 6, 8, 3, 2, '2026-02-01 08:00:00', '2026-01-01 10:10:00', '2026-02-01 08:00:00'),
(12, 2, 6, 12, 3, 2, '2026-02-01 08:30:00', '2026-01-01 10:11:00', '2026-02-01 08:30:00');

INSERT INTO `supply_orders` (`id`, `supplier_id`, `order_no`, `status`, `expected_date`, `received_date`, `total_amount`, `created_by_user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 'SO-20260001', 'RECEIVED', '2026-01-20', '2026-01-19', 6900000.00, 3, '2026-01-15 09:00:00', '2026-01-19 16:00:00'),
(2, 3, 'SO-20260002', 'CONFIRMED', '2026-02-20', NULL, 10050000.00, 3, '2026-02-14 09:30:00', '2026-02-14 10:00:00');

INSERT INTO `supply_order_items` (`id`, `supply_order_id`, `product_id`, `quantity`, `unit_cost`, `line_total`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 20, 170000.00, 3400000.00, '2026-01-15 09:05:00', '2026-01-15 09:05:00'),
(2, 1, 3, 10, 350000.00, 3500000.00, '2026-01-15 09:06:00', '2026-01-15 09:06:00'),
(3, 2, 4, 45, 135000.00, 6075000.00, '2026-02-14 09:35:00', '2026-02-14 09:35:00'),
(4, 2, 5, 30, 65000.00, 1950000.00, '2026-02-14 09:36:00', '2026-02-14 09:36:00'),
(5, 2, 6, 6, 300000.00, 1800000.00, '2026-02-14 09:37:00', '2026-02-14 09:37:00'),
(6, 2, 2, 1, 225000.00, 225000.00, '2026-02-14 09:38:00', '2026-02-14 09:38:00');

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `channel`, `status`, `sent_at`, `read_at`, `created_at`) VALUES
(1, 2, 'Đơn hàng đã giao', 'Đơn hàng ORD-20260001 đã được giao thành công.', 'SYSTEM', 'READ', '2026-02-07 17:05:00', '2026-02-07 17:20:00', '2026-02-07 17:05:00'),
(2, 5, 'Xác nhận thanh toán', 'Hệ thống đã ghi nhận thanh toán cho đơn ORD-20260002.', 'EMAIL', 'SENT', '2026-02-10 09:35:00', NULL, '2026-02-10 09:30:00'),
(3, 3, 'Yêu cầu nhập hàng mới', 'Có yêu cầu nhập thêm cà phê, mật ong và bánh samosa.', 'SYSTEM', 'PENDING', NULL, NULL, '2026-02-14 11:00:00');

INSERT INTO `delivery_requests` (`id`, `requested_by_user_id`, `product_id`, `requested_qty`, `reason`, `status`, `approved_by_user_id`, `created_at`, `updated_at`) VALUES
(1, 3, 6, 10, 'Tồn kho mật ong tại kho HN xuống dưới ngưỡng cảnh báo.', 'APPROVED', 1, '2026-02-14 10:30:00', '2026-02-14 11:00:00'),
(2, 3, 5, 30, 'Cần bổ sung tồn kho bánh samosa cho chương trình khuyến mãi.', 'PENDING', NULL, '2026-02-14 10:35:00', '2026-02-14 10:35:00'),
(3, 3, 3, 10, 'Trà thượng hạng cần nhập bổ sung trong tuần tới.', 'FULFILLED', 1, '2026-02-01 08:00:00', '2026-02-05 16:00:00');

INSERT INTO `wishlist_items` (`id`, `user_id`, `product_id`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '2026-02-14 09:00:00', '2026-02-14 09:00:00'),
(2, 2, 6, '2026-02-14 09:05:00', '2026-02-14 09:05:00'),
(3, 5, 3, '2026-02-14 09:10:00', '2026-02-14 09:10:00');

INSERT INTO `admin_settings` (
  `id`,
  `user_id`,
  `store_name`,
  `support_email`,
  `support_phone`,
  `low_stock_threshold`,
  `dashboard_refresh_seconds`,
  `order_auto_confirm`,
  `send_daily_summary`,
  `maintenance_mode`,
  `notes`,
  `created_at`,
  `updated_at`
) VALUES
(
  1,
  1,
  'Heritage Harvest',
  'admin@shop.local',
  '0900000001',
  5,
  60,
  FALSE,
  TRUE,
  FALSE,
  'Cau hinh mac dinh cho tai khoan admin seed.',
  '2026-02-14 09:15:00',
  '2026-02-14 09:15:00'
);

UPDATE `products`
SET `image_url` = CASE `id`
  WHEN 1 THEN 'https://lh3.googleusercontent.com/aida-public/AB6AXuApEI8i5c5-lvSG4Dlzqoz_ycN0juCHIwgJu3FfozzIkOyIxQ9ptojAza2j8UI8hasmu7TOlgionfq-cY3H6PlEL8ywo7Q9ShEQzH3cLKFRf8Dni86n_WyOFH8nGRQG2nzf-wYHGbnmtVeIrVo6FoEtT4R5xELI2ROxWdoUp_rn8TYN8mY9qqAYcT6LXQWlZ1LhaniJBZQAaNAsEJ5jQH2O4pELA7gdA392tj2seqHbnk4X5_jOuW0CE3KuGtJwvRGuED9r7cA_DWY'
  WHEN 2 THEN 'https://lh3.googleusercontent.com/aida-public/AB6AXuBXARbJdlJ8F7HyasnEA736osCFFBEhb6zthedKoGTDiJtEjHXc4Ydqzy52t_ZdPg6RZfEsFD0ItAwXgk9t8Kw2PnMBi_XQbcZF9q7u3qnwkg5WEh6v6RkKVyuoO0HCWIYGxfOz81VAm0Gp9MOM8A_BIZamsb4UfIUkNkhwR5utNGKDnL6MuO-H9SzYqbA6oRMRNWpPUU0uFVitmuLATpMzxwU5xE0k_5oKSVn6I8vz18Vxm01TZDUdc2D1VqI3iSFk9PCV8_WFsHM'
  WHEN 3 THEN 'https://lh3.googleusercontent.com/aida-public/AB6AXuApEI8i5c5-lvSG4Dlzqoz_ycN0juCHIwgJu3FfozzIkOyIxQ9ptojAza2j8UI8hasmu7TOlgionfq-cY3H6PlEL8ywo7Q9ShEQzH3cLKFRf8Dni86n_WyOFH8nGRQG2nzf-wYHGbnmtVeIrVo6FoEtT4R5xELI2ROxWdoUp_rn8TYN8mY9qqAYcT6LXQWlZ1LhaniJBZQAaNAsEJ5jQH2O4pELA7gdA392tj2seqHbnk4X5_jOuW0CE3KuGtJwvRGuED9r7cA_DWY'
  WHEN 4 THEN 'https://lh3.googleusercontent.com/aida-public/AB6AXuCDSY18Y0pt9VoQJ_P711qNJumIYKNBIvCAWZ4zIyZsSAVxoRY3SVTwzOvIR_5xTiEdFJaYvQm8wM7Y-MDZKT8UKChNN-S89uNRgMAUMUIoeYrGidq32shCSv3Z6C-4Jb-OofC0pTkfyfoSD5VA7GHqiz13_oTiQVm2sPQxABzP8nshexcJ0vBLOrQSyQYF6T5L1Oq6lAncWJq9BtyKQlwIyrLXEWegJMs0W0882sywLHO5UHGhPEPIyo7QCgnm0QvlyWaed-txavU'
  WHEN 5 THEN 'https://images.unsplash.com/photo-1512058564366-18510be2db19?auto=format&fit=crop&w=900&q=80'
  WHEN 6 THEN 'https://lh3.googleusercontent.com/aida-public/AB6AXuCv2k_8RGESM2ZD-gXIaW6UJCASXFuT4NpabZ9daLOGFiIhIrRMtPVS0GcNsW71X2L51GGxkzW_8FYny4NEkFHs2cmOlBuKHnRbLouGrpOaL3gwMuXzrDA3lfTNhK3sQfEnhRjjtN2SQZMcTZwhIiKkLdwwAlHwLj7ZOuumSDgaj8WLNCFqmqDrkyR6X4IbJ5aZeAb2Tns_uY0FR4GCNiBGWMZ8XD6CZ3KskjbQlcgziLzuiXvNDnFgNYM8yapM8SItBX7kQOE931w'
  ELSE `image_url`
END
WHERE `id` IN (1, 2, 3, 4, 5, 6);

ALTER TABLE `users` AUTO_INCREMENT = 9;
ALTER TABLE `admin_roles` AUTO_INCREMENT = 2;
ALTER TABLE `admin_permissions` AUTO_INCREMENT = 20;
ALTER TABLE `categories` AUTO_INCREMENT = 5;
ALTER TABLE `suppliers` AUTO_INCREMENT = 5;
ALTER TABLE `inventories` AUTO_INCREMENT = 3;
ALTER TABLE `products` AUTO_INCREMENT = 7;
ALTER TABLE `prices` AUTO_INCREMENT = 7;
ALTER TABLE `carts` AUTO_INCREMENT = 3;
ALTER TABLE `orders` AUTO_INCREMENT = 4;
ALTER TABLE `order_items` AUTO_INCREMENT = 7;
ALTER TABLE `order_status_history` AUTO_INCREMENT = 12;
ALTER TABLE `payments` AUTO_INCREMENT = 4;
ALTER TABLE `payment_status_history` AUTO_INCREMENT = 6;
ALTER TABLE `complaints` AUTO_INCREMENT = 3;
ALTER TABLE `reviews` AUTO_INCREMENT = 4;
ALTER TABLE `inventory_items` AUTO_INCREMENT = 13;
ALTER TABLE `supply_orders` AUTO_INCREMENT = 3;
ALTER TABLE `supply_order_items` AUTO_INCREMENT = 7;
ALTER TABLE `notifications` AUTO_INCREMENT = 4;
ALTER TABLE `delivery_requests` AUTO_INCREMENT = 4;
ALTER TABLE `cart_items` AUTO_INCREMENT = 4;
ALTER TABLE `wishlist_items` AUTO_INCREMENT = 4;
ALTER TABLE `admin_settings` AUTO_INCREMENT = 2;
