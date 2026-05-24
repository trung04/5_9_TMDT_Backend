SET NAMES utf8mb4;
USE `ecommerce_db`;

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `post_likes`;
TRUNCATE TABLE `post_comments`;
TRUNCATE TABLE `posts`;
TRUNCATE TABLE `support_tickets`;
TRUNCATE TABLE `newsletter_subscriptions`;
TRUNCATE TABLE `payment_status_history`;
TRUNCATE TABLE `order_shipments`;
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
TRUNCATE TABLE `shipping_carriers`;
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
TRUNCATE TABLE `regions`;
TRUNCATE TABLE `inventories`;
TRUNCATE TABLE `suppliers`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `users`;

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password_hash`, `address`, `city`, `favorite_region`, `avatar_url`, `newsletter`, `sms_alerts`, `order_email`, `security_alerts`, `reward_points`, `reward_tier`, `next_tier_points`, `role`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'Nguyễn Văn Admin', 'admin@shop.local', '0900000001', '$2y$10$ce7ubt0LylfseDirp.DoN.HGxACLy6f7VekTno./rqHKJOOA6zuKq', '12 Nguyễn Huệ', 'Hà Nội', 'Tây Bắc', NULL, FALSE, FALSE, TRUE, TRUE, 0, 'Bronze', 500, 'ADMIN', TRUE, FALSE, '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(2, 'Trần Thị Customer', 'customer1@shop.local', '0900000002', '$2y$10$ce7ubt0LylfseDirp.DoN.HGxACLy6f7VekTno./rqHKJOOA6zuKq', '101 Lê Duẩn', 'Hà Nội', 'Đông Bắc', NULL, TRUE, TRUE, TRUE, TRUE, 720, 'Silver', 1000, 'CUSTOMER', TRUE, FALSE, '2026-01-02 09:00:00', '2026-01-02 09:00:00'),
(3, 'Lê Văn Kho', 'warehouse@shop.local', '0900000003', '$2y$10$ce7ubt0LylfseDirp.DoN.HGxACLy6f7VekTno./rqHKJOOA6zuKq', 'KCN Bắc Từ Liêm', 'Hà Nội', NULL, NULL, FALSE, TRUE, TRUE, TRUE, 0, 'Bronze', 500, 'WAREHOUSE_STAFF', TRUE, FALSE, '2026-01-03 10:00:00', '2026-01-03 10:00:00'),
(4, 'Phạm Thị NCC', 'supplieruser@shop.local', '0900000004', '$2y$10$ce7ubt0LylfseDirp.DoN.HGxACLy6f7VekTno./rqHKJOOA6zuKq', '45 Võ Văn Tần', 'TP.HCM', NULL, NULL, FALSE, TRUE, TRUE, TRUE, 0, 'Bronze', 500, 'SUPPLIER', TRUE, FALSE, '2026-01-04 11:00:00', '2026-01-04 11:00:00'),
(5, 'Đỗ Minh Khách', 'customer2@shop.local', '0900000005', '$2y$10$ce7ubt0LylfseDirp.DoN.HGxACLy6f7VekTno./rqHKJOOA6zuKq', '22 Điện Biên Phủ', 'TP.HCM', 'Nam Bộ', NULL, TRUE, FALSE, TRUE, TRUE, 340, 'Bronze', 500, 'CUSTOMER', TRUE, FALSE, '2026-01-05 12:00:00', '2026-01-05 12:00:00'),
(6, 'Active User', 'active@example.com', '0901111111', '$2y$10$ce7ubt0LylfseDirp.DoN.HGxACLy6f7VekTno./rqHKJOOA6zuKq', '1 Trần Phú', 'Đà Nẵng', NULL, NULL, FALSE, FALSE, TRUE, TRUE, 0, 'Bronze', 500, 'CUSTOMER', TRUE, FALSE, '2026-01-06 08:00:00', '2026-01-06 08:00:00'),
(7, 'Blocked User', 'blocked@example.com', '0902222222', '$2y$10$ce7ubt0LylfseDirp.DoN.HGxACLy6f7VekTno./rqHKJOOA6zuKq', NULL, NULL, NULL, NULL, FALSE, FALSE, TRUE, TRUE, 0, 'Bronze', 500, 'CUSTOMER', FALSE, FALSE, '2026-01-06 08:05:00', '2026-01-06 08:05:00'),
(8, 'Inactive User', 'inactive@example.com', '0903333333', '$2y$10$ce7ubt0LylfseDirp.DoN.HGxACLy6f7VekTno./rqHKJOOA6zuKq', NULL, NULL, NULL, NULL, FALSE, FALSE, TRUE, TRUE, 0, 'Bronze', 500, 'CUSTOMER', FALSE, FALSE, '2026-01-06 08:10:00', '2026-01-06 08:10:00');

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

INSERT INTO `admin_permissions` (`id`, `key`, `name`, `group`, `description`, `created_at`, `updated_at`) VALUES
(20, 'admin.community.posts.view', 'View posts', 'Community', 'View admin post data.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(21, 'admin.community.posts.create', 'Create posts', 'Community', 'Create community posts.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(22, 'admin.community.posts.update', 'Update posts', 'Community', 'Update community posts and publish status.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(23, 'admin.community.posts.delete', 'Delete posts', 'Community', 'Delete community posts.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(24, 'admin.community.comments.moderate', 'Moderate post comments', 'Community', 'Hide or restore customer comments on posts.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(25, 'admin.users.view', 'View users', 'Users', 'View customer accounts in the admin user directory.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(26, 'admin.users.create', 'Create users', 'Users', 'Create customer accounts from the admin user directory.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(27, 'admin.users.update', 'Update users', 'Users', 'Update customer account profile and status fields.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(28, 'admin.users.delete', 'Delete users', 'Users', 'Block customer accounts without removing their order history.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(29, 'admin.supplier.inventory.view', 'View supplier inventory portal', 'Supplier Portal', 'View the supplier inventory screen inside the admin shell.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(30, 'admin.supplier.requisitions.view', 'View supplier requisitions portal', 'Supplier Portal', 'View supplier requisition workflows inside the admin shell.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(31, 'admin.supplier.processing.view', 'View supplier processing portal', 'Supplier Portal', 'View supplier order processing workflows inside the admin shell.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(32, 'admin.supplier.orders.view', 'View supplier orders portal', 'Supplier Portal', 'View supplier order management inside the admin shell.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(33, 'admin.supplier.help.view', 'View supplier help portal', 'Supplier Portal', 'View supplier support content inside the admin shell.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(34, 'admin.warehouse.inventory.view', 'View warehouse inventory portal', 'Warehouse Portal', 'View warehouse inventory inside the admin shell.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(35, 'admin.warehouse.requisitions.view', 'View warehouse requisitions portal', 'Warehouse Portal', 'View warehouse requisition workflows inside the admin shell.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(36, 'admin.warehouse.fulfillment.view', 'View warehouse fulfillment portal', 'Warehouse Portal', 'View warehouse fulfillment workflows inside the admin shell.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(37, 'admin.warehouse.supplier_orders.view', 'View warehouse supplier orders portal', 'Warehouse Portal', 'View warehouse supplier order workflows inside the admin shell.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(38, 'admin.warehouse.help.view', 'View warehouse help portal', 'Warehouse Portal', 'View warehouse support content inside the admin shell.', '2026-01-01 08:00:00', '2026-01-01 08:00:00');

INSERT INTO `admin_role_permission` (`admin_role_id`, `admin_permission_id`) VALUES
(1, 20), (1, 21), (1, 22), (1, 23), (1, 24), (1, 25), (1, 26), (1, 27), (1, 28), (1, 29), (1, 30), (1, 31), (1, 32), (1, 33), (1, 34), (1, 35), (1, 36), (1, 37), (1, 38);

INSERT INTO `admin_permissions` (`id`, `key`, `name`, `group`, `description`, `created_at`, `updated_at`) VALUES
(39, 'admin.shipping_carriers.view', 'View shipping carriers', 'Shipping', 'View configured shipping carriers.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(40, 'admin.shipping_carriers.create', 'Create shipping carriers', 'Shipping', 'Create shipping carrier records.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(41, 'admin.shipping_carriers.update', 'Update shipping carriers', 'Shipping', 'Update shipping carrier records.', '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(42, 'admin.shipping_carriers.delete', 'Delete shipping carriers', 'Shipping', 'Deactivate shipping carrier records.', '2026-01-01 08:00:00', '2026-01-01 08:00:00');

INSERT INTO `admin_role_permission` (`admin_role_id`, `admin_permission_id`) VALUES
(1, 39), (1, 40), (1, 41), (1, 42);

UPDATE `users`
SET `admin_role_id` = 1
WHERE `email` = 'admin@shop.local' AND `role` = 'ADMIN';

INSERT INTO `shipping_carriers` (`id`, `code`, `name`, `provider`, `tracking_url_template`, `default_weight`, `default_length`, `default_width`, `default_height`, `default_service_type_id`, `default_payment_type_id`, `default_required_note`, `pickup_name`, `pickup_phone`, `pickup_address`, `pickup_ward_code`, `pickup_ward_name`, `pickup_district_id`, `pickup_district_name`, `pickup_province_id`, `pickup_province_name`, `settings`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'GHN', 'Giao Hang Nhanh', 'GHN', 'https://donhang.ghn.vn/?order_code={code}', 1000, 20, 20, 10, 2, 1, 'KHONGCHOXEMHANG', 'Heritage Harvest', '0900000999', 'Kho chinh Ha Noi', NULL, 'Phuong Dich Vong Hau', NULL, 'Quan Cau Giay', NULL, 'Ha Noi', JSON_OBJECT('shop_id_configured', TRUE), TRUE, FALSE, '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(2, 'MANUAL', 'Van chuyen thu cong', 'MANUAL', NULL, 1000, 20, 20, 10, 2, 1, 'KHONGCHOXEMHANG', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, TRUE, FALSE, '2026-01-01 08:00:00', '2026-01-01 08:00:00');

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

INSERT INTO `categories` (`id`, `name`, `description`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'Gạo - Nông sản đặc sản', 'Các loại gạo, nông sản và thực phẩm khô đặc sản vùng miền.', TRUE, FALSE, '2026-01-01 08:10:00', '2026-01-01 08:10:00'),
(2, 'Món ăn truyền thống', 'Các món ăn vặt, bánh và món ăn truyền thống đóng gói sẵn.', TRUE, FALSE, '2026-01-01 08:11:00', '2026-01-01 08:11:00'),
(3, 'Mật ong - Đặc sản rừng', 'Mật ong, sản vật tự nhiên và đặc sản rừng núi.', TRUE, FALSE, '2026-01-01 08:12:00', '2026-01-01 08:12:00'),
(4, 'Trà - Cà phê đặc sản', 'Trà, cà phê và các sản phẩm làm quà tặng đặc sản.', TRUE, FALSE, '2026-01-01 08:13:00', '2026-01-01 08:13:00');

INSERT INTO `suppliers` (`id`, `supplier_code`, `name`, `contact_name`, `phone`, `email`, `address`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 'SUP-TN01', 'HTX Chè Tân Cương Thái Nguyên', 'Nguyễn Văn Kiên', '0911000001', 'tanCuong@dacsan.vn', 'Tân Cương, Thái Nguyên', TRUE, FALSE, '2026-01-01 08:20:00', '2026-01-01 08:20:00'),
(2, 'SUP-ST25', 'Cơ sở Gạo Đặc Sản Sóc Trăng', 'Trần Quốc Minh', '0911000002', 'st25@dacsan.vn', 'Sóc Trăng', TRUE, FALSE, '2026-01-01 08:21:00', '2026-01-01 08:21:00'),
(3, 'SUP-DL01', 'Nông Sản Đà Lạt Premium', 'Lê Thu Tâm', '0911000003', 'dalat@dacsan.vn', 'Đà Lạt, Lâm Đồng', TRUE, FALSE, '2026-01-01 08:22:00', '2026-01-01 08:22:00'),
(4, 'SUP-TQ01', 'Đặc Sản Miền Núi Tây Bắc', 'Phạm Thị Hương', '0911000004', 'taybac@dacsan.vn', 'Sơn La', TRUE, FALSE, '2026-01-01 08:23:00', '2026-01-01 08:23:00');

INSERT INTO `inventories` (`id`, `name`, `location`, `created_at`, `updated_at`) VALUES
(1, 'Kho Hà Nội', 'KCN Bắc Từ Liêm, Hà Nội', '2026-01-01 08:30:00', '2026-01-01 08:30:00'),
(2, 'Kho TP HCM', 'Thủ Đức, TP.HCM', '2026-01-01 08:31:00', '2026-01-01 08:31:00');

INSERT INTO `products` (`id`, `category_id`, `supplier_id`, `sku`, `name`, `description`, `sale_price`, `stock_quantity`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 'TRA-TC-200', 'Trà Tân Cương Thái Nguyên 200g', 'Trà xanh Thái Nguyên hương cốm non, nước xanh, vị chát dịu và hậu ngọt.', 229000.00, 30, TRUE, FALSE, '2026-01-01 09:00:00', '2026-01-01 09:00:00'),
(2, 1, 2, 'GAO-ST25-5KG', 'Gạo thơm đặc sản ST25 5kg', 'Gạo thơm hạt dài, cơm dẻo mềm, phù hợp bữa ăn gia đình.', 290000.00, 24, TRUE, FALSE, '2026-01-01 09:05:00', '2026-01-01 09:05:00'),
(3, 4, 1, 'TRA-TC-PRE-500', 'Trà Tân Cương thượng hạng 500g', 'Trà Tân Cương loại thượng hạng, đóng túi đẹp, phù hợp làm quà biếu.', 459000.00, 18, TRUE, FALSE, '2026-01-01 09:10:00', '2026-01-01 09:10:00'),
(4, 4, 3, 'CF-DL-500', 'Cà phê rang xay Đà Lạt 500g', 'Cà phê rang xay nguyên chất, mùi thơm đậm, hậu vị hài hòa.', 189000.00, 80, TRUE, FALSE, '2026-01-01 09:15:00', '2026-01-01 09:15:00'),
(5, 2, 3, 'BANH-SAMOSA-10', 'Bánh samosa truyền thống hộp 10 cái', 'Bánh chiên nhân đậm đà, thích hợp ăn nhẹ và đãi khách.', 99000.00, 60, TRUE, FALSE, '2026-01-01 09:20:00', '2026-01-01 09:20:00'),
(6, 3, 4, 'MAT-ONG-RUNG-500', 'Mật ong rừng nguyên chất 500ml', 'Mật ong nguyên chất màu hổ phách, vị ngọt thanh, thích hợp bồi bổ sức khỏe.', 390000.00, 20, TRUE, FALSE, '2026-01-01 09:25:00', '2026-01-01 09:25:00');

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

INSERT INTO `order_shipments` (`id`, `order_id`, `shipping_carrier_id`, `provider`, `status`, `tracking_code`, `tracking_url`, `service_type_id`, `payment_type_id`, `required_note`, `weight`, `length`, `width`, `height`, `shipping_fee`, `cod_amount`, `expected_delivery_time`, `raw_request`, `raw_response`, `synced_at`, `cancelled_at`, `created_by_user_id`, `updated_by_user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'GHN', 'delivered', 'GHN-ORD-20260001', 'https://donhang.ghn.vn/?order_code=GHN-ORD-20260001', 2, 1, 'KHONGCHOXEMHANG', 1000, 20, 20, 10, 30000.00, 649000.00, NULL, JSON_OBJECT('seeded', TRUE), JSON_OBJECT('seeded', TRUE, 'status', 'delivered'), '2026-02-07 17:00:00', NULL, 1, 1, '2026-02-05 10:00:00', '2026-02-07 17:00:00'),
(2, 3, 2, 'MANUAL', 'shipping', 'GHTK-ORD-20260003', NULL, NULL, NULL, NULL, 1000, 20, 20, 10, 25000.00, 400000.00, NULL, NULL, JSON_OBJECT('seeded', TRUE, 'status', 'shipping'), '2026-02-13 08:00:00', NULL, 3, 3, '2026-02-12 17:00:00', '2026-02-13 08:00:00');

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

UPDATE `users`
SET `password_hash` = '$2y$10$FuMP3Oak0j6ShAxjSBnup.KsYwR4I3AXUGJDHWak8eMdqeY.mk6GC'
WHERE `id` BETWEEN 1 AND 8;

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password_hash`, `address`, `city`, `favorite_region`, `avatar_url`, `newsletter`, `sms_alerts`, `order_email`, `security_alerts`, `reward_points`, `reward_tier`, `next_tier_points`, `role`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(9, 'Nguyễn Thị Điều Phối', 'ops@shop.local', '0900000009', '$2y$10$FuMP3Oak0j6ShAxjSBnup.KsYwR4I3AXUGJDHWak8eMdqeY.mk6GC', '18 Lý Thường Kiệt', 'Hà Nội', 'Đồng bằng Bắc Bộ', NULL, FALSE, TRUE, TRUE, TRUE, 0, 'Bronze', 500, 'WAREHOUSE_STAFF', TRUE, FALSE, '2026-01-07 08:00:00', '2026-01-07 08:00:00'),
(10, 'Vũ Minh Kho Nam', 'warehouse2@shop.local', '0900000010', '$2y$10$FuMP3Oak0j6ShAxjSBnup.KsYwR4I3AXUGJDHWak8eMdqeY.mk6GC', 'Kho Thủ Đức', 'TP.HCM', 'Nam Bộ', NULL, FALSE, TRUE, TRUE, TRUE, 0, 'Bronze', 500, 'WAREHOUSE_STAFF', TRUE, FALSE, '2026-01-07 09:00:00', '2026-01-07 09:00:00'),
(11, 'Hoàng Thị Nhà Vườn', 'supplier2@shop.local', '0900000011', '$2y$10$FuMP3Oak0j6ShAxjSBnup.KsYwR4I3AXUGJDHWak8eMdqeY.mk6GC', 'Mộc Châu', 'Sơn La', 'Tây Bắc', NULL, FALSE, TRUE, TRUE, TRUE, 0, 'Bronze', 500, 'SUPPLIER', TRUE, FALSE, '2026-01-07 10:00:00', '2026-01-07 10:00:00'),
(12, 'Mai Văn Xưởng', 'supplier3@shop.local', '0900000012', '$2y$10$FuMP3Oak0j6ShAxjSBnup.KsYwR4I3AXUGJDHWak8eMdqeY.mk6GC', 'Phú Quốc', 'Kiên Giang', 'Nam Bộ', NULL, FALSE, TRUE, TRUE, TRUE, 0, 'Bronze', 500, 'SUPPLIER', TRUE, FALSE, '2026-01-07 11:00:00', '2026-01-07 11:00:00'),
(13, 'Nguyễn An Nhiên', 'annhien@example.com', '0900000013', '$2y$10$FuMP3Oak0j6ShAxjSBnup.KsYwR4I3AXUGJDHWak8eMdqeY.mk6GC', '9 Phan Đình Phùng', 'Hà Nội', 'Tây Bắc', NULL, TRUE, TRUE, TRUE, TRUE, 1280, 'Gold', 2000, 'CUSTOMER', TRUE, FALSE, '2026-01-08 08:00:00', '2026-01-08 08:00:00'),
(14, 'Lê Bảo Trâm', 'baotram@example.com', '0900000014', '$2y$10$FuMP3Oak0j6ShAxjSBnup.KsYwR4I3AXUGJDHWak8eMdqeY.mk6GC', '42 Pasteur', 'Đà Nẵng', 'Miền Trung', NULL, TRUE, FALSE, TRUE, TRUE, 860, 'Silver', 1000, 'CUSTOMER', TRUE, FALSE, '2026-01-08 09:00:00', '2026-01-08 09:00:00'),
(15, 'Phạm Gia Hân', 'giahan@example.com', '0900000015', '$2y$10$FuMP3Oak0j6ShAxjSBnup.KsYwR4I3AXUGJDHWak8eMdqeY.mk6GC', '88 Hai Bà Trưng', 'TP.HCM', 'Nam Bộ', NULL, TRUE, TRUE, TRUE, TRUE, 450, 'Bronze', 500, 'CUSTOMER', TRUE, FALSE, '2026-01-08 10:00:00', '2026-01-08 10:00:00'),
(16, 'Trương Đức Minh', 'ducminh@example.com', '0900000016', '$2y$10$FuMP3Oak0j6ShAxjSBnup.KsYwR4I3AXUGJDHWak8eMdqeY.mk6GC', '16 Nguyễn Văn Cừ', 'Cần Thơ', 'Đồng bằng sông Cửu Long', NULL, TRUE, FALSE, TRUE, TRUE, 230, 'Bronze', 500, 'CUSTOMER', TRUE, FALSE, '2026-01-08 11:00:00', '2026-01-08 11:00:00'),
(17, 'Đặng Khánh Linh', 'khanhlinh@example.com', '0900000017', '$2y$10$FuMP3Oak0j6ShAxjSBnup.KsYwR4I3AXUGJDHWak8eMdqeY.mk6GC', '5 Lê Lợi', 'Huế', 'Miền Trung', NULL, TRUE, TRUE, TRUE, TRUE, 1540, 'Gold', 2000, 'CUSTOMER', TRUE, FALSE, '2026-01-09 08:00:00', '2026-01-09 08:00:00'),
(18, 'Bùi Nhật Nam', 'nhatnam@example.com', '0900000018', '$2y$10$FuMP3Oak0j6ShAxjSBnup.KsYwR4I3AXUGJDHWak8eMdqeY.mk6GC', '19 Trần Hưng Đạo', 'Hải Phòng', 'Đông Bắc', NULL, FALSE, TRUE, TRUE, TRUE, 720, 'Silver', 1000, 'CUSTOMER', TRUE, FALSE, '2026-01-09 09:00:00', '2026-01-09 09:00:00'),
(19, 'Võ Thanh Mai', 'thanhmai@example.com', '0900000019', '$2y$10$FuMP3Oak0j6ShAxjSBnup.KsYwR4I3AXUGJDHWak8eMdqeY.mk6GC', '73 Nguyễn Trãi', 'Nha Trang', 'Duyên hải Nam Trung Bộ', NULL, TRUE, FALSE, TRUE, TRUE, 90, 'Bronze', 500, 'CUSTOMER', TRUE, FALSE, '2026-01-09 10:00:00', '2026-01-09 10:00:00'),
(20, 'Hồ Minh Quân', 'minhquan@example.com', '0900000020', '$2y$10$FuMP3Oak0j6ShAxjSBnup.KsYwR4I3AXUGJDHWak8eMdqeY.mk6GC', '102 Cách Mạng Tháng Tám', 'Buôn Ma Thuột', 'Tây Nguyên', NULL, TRUE, TRUE, TRUE, TRUE, 610, 'Silver', 1000, 'CUSTOMER', TRUE, FALSE, '2026-01-09 11:00:00', '2026-01-09 11:00:00'),
(21, 'Lý Hoàng Yến', 'hoangyen@example.com', '0900000021', '$2y$10$FuMP3Oak0j6ShAxjSBnup.KsYwR4I3AXUGJDHWak8eMdqeY.mk6GC', '12 Bạch Đằng', 'Quy Nhơn', 'Duyên hải Nam Trung Bộ', NULL, TRUE, FALSE, TRUE, TRUE, 360, 'Bronze', 500, 'CUSTOMER', TRUE, FALSE, '2026-01-10 08:00:00', '2026-01-10 08:00:00'),
(22, 'Ngô Gia Bảo', 'giabao@example.com', '0900000022', '$2y$10$FuMP3Oak0j6ShAxjSBnup.KsYwR4I3AXUGJDHWak8eMdqeY.mk6GC', '44 Hùng Vương', 'Đà Lạt', 'Tây Nguyên', NULL, TRUE, TRUE, TRUE, TRUE, 980, 'Silver', 1000, 'CUSTOMER', TRUE, FALSE, '2026-01-10 09:00:00', '2026-01-10 09:00:00'),
(23, 'Tạ Minh Châu', 'minhchau@example.com', '0900000023', '$2y$10$FuMP3Oak0j6ShAxjSBnup.KsYwR4I3AXUGJDHWak8eMdqeY.mk6GC', '61 Võ Thị Sáu', 'Vũng Tàu', 'Nam Bộ', NULL, FALSE, FALSE, TRUE, TRUE, 120, 'Bronze', 500, 'CUSTOMER', TRUE, FALSE, '2026-01-10 10:00:00', '2026-01-10 10:00:00'),
(24, 'Dương Bích Ngọc', 'bichngoc@example.com', '0900000024', '$2y$10$FuMP3Oak0j6ShAxjSBnup.KsYwR4I3AXUGJDHWak8eMdqeY.mk6GC', '8 Điện Biên Phủ', 'Sa Pa', 'Tây Bắc', NULL, TRUE, TRUE, TRUE, TRUE, 2400, 'Platinum', 4000, 'CUSTOMER', TRUE, FALSE, '2026-01-10 11:00:00', '2026-01-10 11:00:00'),
(25, 'Đinh Quốc Việt', 'quocviet@example.com', '0900000025', '$2y$10$FuMP3Oak0j6ShAxjSBnup.KsYwR4I3AXUGJDHWak8eMdqeY.mk6GC', '33 Nguyễn Huệ', 'Sóc Trăng', 'Đồng bằng sông Cửu Long', NULL, TRUE, FALSE, TRUE, TRUE, 540, 'Silver', 1000, 'CUSTOMER', TRUE, FALSE, '2026-01-11 08:00:00', '2026-01-11 08:00:00'),
(26, 'Cao Thu Phương', 'thuphuong@example.com', '0900000026', '$2y$10$FuMP3Oak0j6ShAxjSBnup.KsYwR4I3AXUGJDHWak8eMdqeY.mk6GC', '21 Lý Tự Trọng', 'Cần Thơ', 'Đồng bằng sông Cửu Long', NULL, TRUE, TRUE, TRUE, TRUE, 130, 'Bronze', 500, 'CUSTOMER', TRUE, FALSE, '2026-01-11 09:00:00', '2026-01-11 09:00:00');

INSERT INTO `regions` (`id`, `slug`, `name`, `description`, `image_url`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'tay-bac', 'Tây Bắc', 'Núi cao, khí hậu mát và những nông sản có hương vị đậm vùng cao.', 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80', TRUE, '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(2, 'dong-bac', 'Đông Bắc', 'Vùng chè cổ thụ, mật ong bạc hà và thảo mộc rừng đặc sắc.', 'https://images.unsplash.com/photo-1549880338-65ddcdfd017b?auto=format&fit=crop&w=1200&q=80', TRUE, '2026-01-01 08:01:00', '2026-01-01 08:01:00'),
(3, 'dong-bang-bac-bo', 'Đồng bằng Bắc Bộ', 'Làng nghề, cốm, bánh mứt và văn hóa quà tặng truyền thống.', 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=1200&q=80', TRUE, '2026-01-01 08:02:00', '2026-01-01 08:02:00'),
(4, 'mien-trung', 'Miền Trung', 'Gia vị, mè xửng, trà sen và sản vật khô gắn với bếp miền Trung.', 'https://images.unsplash.com/photo-1528181304800-259b08848526?auto=format&fit=crop&w=1200&q=80', TRUE, '2026-01-01 08:03:00', '2026-01-01 08:03:00'),
(5, 'tay-nguyen', 'Tây Nguyên', 'Cà phê, ca cao, tiêu và nông sản cao nguyên giàu hương thơm.', 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?auto=format&fit=crop&w=1200&q=80', TRUE, '2026-01-01 08:04:00', '2026-01-01 08:04:00'),
(6, 'duyen-hai-nam-trung-bo', 'Duyên hải Nam Trung Bộ', 'Hải sản khô, yến sào và quà biển từ miền nắng gió.', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', TRUE, '2026-01-01 08:05:00', '2026-01-01 08:05:00'),
(7, 'nam-bo', 'Nam Bộ', 'Nước mắm, hạt điều, trái cây sấy và sản vật miền vườn.', 'https://images.unsplash.com/photo-1502082553048-f009c37129b9?auto=format&fit=crop&w=1200&q=80', TRUE, '2026-01-01 08:06:00', '2026-01-01 08:06:00'),
(8, 'dong-bang-song-cuu-long', 'Đồng bằng sông Cửu Long', 'Gạo thơm, bánh pía, mắm và đặc sản phù sa miền Tây.', 'https://images.unsplash.com/photo-1470114716159-e389f8712fda?auto=format&fit=crop&w=1200&q=80', TRUE, '2026-01-01 08:07:00', '2026-01-01 08:07:00');

INSERT INTO `categories` (`id`, `name`, `description`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(5, 'Nước mắm - Gia vị', 'Nước mắm, tiêu, muối chấm và gia vị đặc trưng vùng miền.', TRUE, FALSE, '2026-01-01 08:14:00', '2026-01-01 08:14:00'),
(6, 'Trái cây - Mứt sấy', 'Trái cây sấy, mứt thủ công và quà vặt theo mùa.', TRUE, FALSE, '2026-01-01 08:15:00', '2026-01-01 08:15:00'),
(7, 'Hạt - Đậu - Dinh dưỡng', 'Hạt điều, hạt mắc ca, đậu và sản phẩm ăn nhẹ tốt cho sức khỏe.', TRUE, FALSE, '2026-01-01 08:16:00', '2026-01-01 08:16:00'),
(8, 'Quà tặng đặc sản', 'Hộp quà, combo biếu tặng và sản phẩm đóng gói cao cấp.', TRUE, FALSE, '2026-01-01 08:17:00', '2026-01-01 08:17:00');

INSERT INTO `suppliers` (`id`, `supplier_code`, `name`, `contact_name`, `phone`, `email`, `address`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(5, 'SUP-PQ01', 'Nhà Thùng Nước Mắm Phú Quốc An Hải', 'Mai Văn Xưởng', '0911000005', 'phuquoc@dacsan.vn', 'Dương Đông, Phú Quốc', TRUE, FALSE, '2026-01-01 08:24:00', '2026-01-01 08:24:00'),
(6, 'SUP-HG01', 'HTX Mật Ong Bạc Hà Đồng Văn', 'Vàng Mí Sính', '0911000006', 'bacha@dacsan.vn', 'Đồng Văn, Hà Giang', TRUE, FALSE, '2026-01-01 08:25:00', '2026-01-01 08:25:00'),
(7, 'SUP-MC01', 'Nhà Vườn Mộc Châu Farm', 'Hoàng Thị Nhà Vườn', '0911000007', 'mocchau@dacsan.vn', 'Mộc Châu, Sơn La', TRUE, FALSE, '2026-01-01 08:26:00', '2026-01-01 08:26:00'),
(8, 'SUP-HUE01', 'Cơ Sở Mè Xửng Thiên Hương Huế', 'Tôn Nữ Lan', '0911000008', 'mexung@dacsan.vn', 'Huế', TRUE, FALSE, '2026-01-01 08:27:00', '2026-01-01 08:27:00'),
(9, 'SUP-BMT01', 'Cà Phê Buôn Ma Thuột Heritage', 'Y Nguyên Êban', '0911000009', 'bmtcoffee@dacsan.vn', 'Buôn Ma Thuột, Đắk Lắk', TRUE, FALSE, '2026-01-01 08:28:00', '2026-01-01 08:28:00'),
(10, 'SUP-BP01', 'Hạt Điều Bình Phước Xanh', 'Đặng Quốc Huy', '0911000010', 'hatdieu@dacsan.vn', 'Phước Long, Bình Phước', TRUE, FALSE, '2026-01-01 08:29:00', '2026-01-01 08:29:00'),
(11, 'SUP-ST02', 'Lò Bánh Pía Tân Huê Viên', 'Lâm Ngọc Phát', '0911000011', 'banhpia@dacsan.vn', 'Sóc Trăng', TRUE, FALSE, '2026-01-01 08:30:00', '2026-01-01 08:30:00'),
(12, 'SUP-TV01', 'Trà Sen Tây Hồ Cổ Truyền', 'Nguyễn Hương Sen', '0911000012', 'trasen@dacsan.vn', 'Tây Hồ, Hà Nội', TRUE, FALSE, '2026-01-01 08:31:00', '2026-01-01 08:31:00');

UPDATE `products`
SET
  `region_id` = CASE `id` WHEN 1 THEN 2 WHEN 2 THEN 8 WHEN 3 THEN 2 WHEN 4 THEN 5 WHEN 5 THEN 4 WHEN 6 THEN 1 ELSE `region_id` END,
  `slug` = CASE `id` WHEN 1 THEN 'tra-tan-cuong-thai-nguyen-200g' WHEN 2 THEN 'gao-thom-st25-5kg' WHEN 3 THEN 'tra-tan-cuong-thuong-hang-500g' WHEN 4 THEN 'ca-phe-rang-xay-da-lat-500g' WHEN 5 THEN 'banh-samosa-truyen-thong-hop-10-cai' WHEN 6 THEN 'mat-ong-rung-nguyen-chat-500ml' ELSE `slug` END,
  `short_description` = CASE `id` WHEN 1 THEN 'Trà xanh Tân Cương thơm hương cốm non.' WHEN 2 THEN 'Gạo ST25 hạt dài, cơm dẻo thơm.' WHEN 3 THEN 'Trà thượng hạng đóng gói làm quà.' WHEN 4 THEN 'Cà phê rang xay nguyên chất từ cao nguyên.' WHEN 5 THEN 'Bánh samosa hộp nhỏ cho tiệc trà.' WHEN 6 THEN 'Mật ong rừng màu hổ phách, ngọt thanh.' ELSE `short_description` END,
  `origin` = CASE `id` WHEN 1 THEN 'Tân Cương, Thái Nguyên' WHEN 2 THEN 'Sóc Trăng' WHEN 3 THEN 'Tân Cương, Thái Nguyên' WHEN 4 THEN 'Đà Lạt, Lâm Đồng' WHEN 5 THEN 'Miền Trung' WHEN 6 THEN 'Sơn La' ELSE `origin` END,
  `weight` = CASE `id` WHEN 1 THEN '200g' WHEN 2 THEN '5kg' WHEN 3 THEN '500g' WHEN 4 THEN '500g' WHEN 5 THEN '10 cái' WHEN 6 THEN '500ml' ELSE `weight` END,
  `shelf_life` = CASE `id` WHEN 2 THEN '12 tháng' WHEN 6 THEN '24 tháng' ELSE '18 tháng' END,
  `certifications` = JSON_ARRAY('Nguồn gốc rõ ràng', 'Đóng gói Heritage Harvest'),
  `gallery` = JSON_ARRAY(COALESCE(`image_url`, ''))
WHERE `id` IN (1, 2, 3, 4, 5, 6);

INSERT INTO `products` (`id`, `category_id`, `supplier_id`, `region_id`, `sku`, `slug`, `name`, `description`, `short_description`, `sale_price`, `stock_quantity`, `image_url`, `origin`, `weight`, `shelf_life`, `certifications`, `gallery`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(7, 4, 9, 5, 'CF-BMT-ROB-500', 'ca-phe-robusta-buon-ma-thuot-500g', 'Cà phê Robusta Buôn Ma Thuột 500g', 'Cà phê robusta rang mộc, vị đậm, hương cacao và hậu vị kéo dài.', 'Robusta rang mộc vị đậm cao nguyên.', 219000.00, 70, 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?auto=format&fit=crop&w=900&q=80', 'Buôn Ma Thuột, Đắk Lắk', '500g', '12 tháng', JSON_ARRAY('Rang mộc', 'Không hương liệu'), JSON_ARRAY('https://images.unsplash.com/photo-1447933601403-0c6688de566e?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 09:00:00', '2026-01-02 09:00:00'),
(8, 5, 5, 7, 'NM-PQ-40N-500', 'nuoc-mam-phu-quoc-40-do-dam-500ml', 'Nước mắm Phú Quốc 40 độ đạm 500ml', 'Nước mắm truyền thống ủ chượp cá cơm, vị mặn dịu và hậu ngọt tự nhiên.', 'Nước mắm Phú Quốc ủ chượp truyền thống.', 179000.00, 90, 'https://images.unsplash.com/photo-1472476443507-c7a5948772fc?auto=format&fit=crop&w=900&q=80', 'Phú Quốc, Kiên Giang', '500ml', '24 tháng', JSON_ARRAY('Ủ chượp truyền thống', 'Đóng chai thủy tinh'), JSON_ARRAY('https://images.unsplash.com/photo-1472476443507-c7a5948772fc?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 09:05:00', '2026-01-02 09:05:00'),
(9, 3, 6, 2, 'MAT-BACHA-500', 'mat-ong-bac-ha-ha-giang-500ml', 'Mật ong bạc hà Hà Giang 500ml', 'Mật ong bạc hà vùng cao nguyên đá, hương thơm mát và vị ngọt thanh.', 'Mật ong bạc hà cao nguyên đá Đồng Văn.', 429000.00, 26, 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?auto=format&fit=crop&w=900&q=80', 'Đồng Văn, Hà Giang', '500ml', '24 tháng', JSON_ARRAY('Mùa hoa bạc hà', 'Sản phẩm HTX'), JSON_ARRAY('https://images.unsplash.com/photo-1587049352846-4a222e784d38?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 09:10:00', '2026-01-02 09:10:00'),
(10, 6, 7, 1, 'MAN-MC-SAY-250', 'man-say-moc-chau-250g', 'Mận sấy Mộc Châu 250g', 'Mận hậu Mộc Châu sấy dẻo, vị chua ngọt cân bằng, hợp dùng cùng trà nóng.', 'Mận hậu sấy dẻo chua ngọt.', 135000.00, 55, 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=900&q=80', 'Mộc Châu, Sơn La', '250g', '9 tháng', JSON_ARRAY('Sấy dẻo', 'Không phẩm màu'), JSON_ARRAY('https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 09:15:00', '2026-01-02 09:15:00'),
(11, 2, 11, 8, 'BANH-PIA-ST-6', 'banh-pia-soc-trang-hop-6-cai', 'Bánh pía Sóc Trăng hộp 6 cái', 'Bánh pía nhân đậu xanh sầu riêng, vỏ mỏng nhiều lớp, đóng hộp làm quà.', 'Bánh pía Sóc Trăng nhân đậu xanh sầu riêng.', 149000.00, 64, 'https://images.unsplash.com/photo-1600617953089-7fefd62d97de?auto=format&fit=crop&w=900&q=80', 'Sóc Trăng', '6 cái', '45 ngày', JSON_ARRAY('Đặc sản Sóc Trăng', 'Hộp quà'), JSON_ARRAY('https://images.unsplash.com/photo-1600617953089-7fefd62d97de?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 09:20:00', '2026-01-02 09:20:00'),
(12, 2, 8, 4, 'MEX-HUE-300', 'me-xung-hue-300g', 'Mè xửng Huế 300g', 'Mè xửng dẻo thơm, phủ mè rang, vị ngọt thanh dùng cùng trà sen.', 'Mè xửng dẻo thơm vị Huế.', 79000.00, 75, 'https://images.unsplash.com/photo-1603569283847-aa295f0d016a?auto=format&fit=crop&w=900&q=80', 'Huế', '300g', '6 tháng', JSON_ARRAY('Làng nghề Huế', 'Thủ công'), JSON_ARRAY('https://images.unsplash.com/photo-1603569283847-aa295f0d016a?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 09:25:00', '2026-01-02 09:25:00'),
(13, 7, 10, 7, 'DIEU-BP-500', 'hat-dieu-binh-phuoc-rang-muoi-500g', 'Hạt điều Bình Phước rang muối 500g', 'Hạt điều nguyên hạt rang muối, bùi béo, đóng lon tiện bảo quản.', 'Hạt điều rang muối nguyên hạt.', 219000.00, 88, 'https://images.unsplash.com/photo-1563412885-139e4045eb8a?auto=format&fit=crop&w=900&q=80', 'Bình Phước', '500g', '12 tháng', JSON_ARRAY('Nguyên hạt', 'Rang muối'), JSON_ARRAY('https://images.unsplash.com/photo-1563412885-139e4045eb8a?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 09:30:00', '2026-01-02 09:30:00'),
(14, 5, 5, 7, 'TIEU-PQ-200', 'tieu-den-phu-quoc-200g', 'Tiêu đen Phú Quốc 200g', 'Tiêu đen nguyên hạt, thơm cay rõ, dùng cho bếp gia đình và quà tặng.', 'Tiêu đen nguyên hạt Phú Quốc.', 155000.00, 48, 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=900&q=80', 'Phú Quốc, Kiên Giang', '200g', '18 tháng', JSON_ARRAY('Nguyên hạt', 'Sấy khô tự nhiên'), JSON_ARRAY('https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 09:35:00', '2026-01-02 09:35:00'),
(15, 5, 3, 5, 'MUOI-OT-DL-120', 'muoi-ot-xanh-da-lat-120g', 'Muối ớt xanh Đà Lạt 120g', 'Muối chấm ớt xanh cay thơm, hợp trái cây, hải sản và món nướng.', 'Muối ớt xanh cay thơm.', 59000.00, 120, 'https://images.unsplash.com/photo-1506368249639-73a05d6f6488?auto=format&fit=crop&w=900&q=80', 'Đà Lạt, Lâm Đồng', '120g', '9 tháng', JSON_ARRAY('Gia vị thủ công', 'Không chất bảo quản'), JSON_ARRAY('https://images.unsplash.com/photo-1506368249639-73a05d6f6488?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 09:40:00', '2026-01-02 09:40:00'),
(16, 4, 12, 3, 'TRA-SEN-100', 'tra-sen-tay-ho-100g', 'Trà sen Tây Hồ 100g', 'Trà ướp gạo sen Tây Hồ, hương thanh, phù hợp thưởng trà và biếu tặng.', 'Trà ướp gạo sen Tây Hồ.', 269000.00, 32, 'https://images.unsplash.com/photo-1564890369478-c89ca6d9cde9?auto=format&fit=crop&w=900&q=80', 'Tây Hồ, Hà Nội', '100g', '12 tháng', JSON_ARRAY('Ướp sen truyền thống', 'Hộp giấy'), JSON_ARRAY('https://images.unsplash.com/photo-1564890369478-c89ca6d9cde9?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 09:45:00', '2026-01-02 09:45:00'),
(17, 4, 4, 1, 'TRA-SHAN-200', 'tra-shan-tuyet-suoi-giang-200g', 'Trà Shan tuyết Suối Giàng 200g', 'Búp trà Shan tuyết cổ thụ, nước vàng mật, hậu ngọt sâu.', 'Trà Shan tuyết cổ thụ vùng cao.', 319000.00, 22, 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=900&q=80', 'Suối Giàng, Yên Bái', '200g', '18 tháng', JSON_ARRAY('Cổ thụ', 'Thu hái thủ công'), JSON_ARRAY('https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 09:50:00', '2026-01-02 09:50:00'),
(18, 2, 12, 3, 'COM-LV-300', 'com-lang-vong-300g', 'Cốm làng Vòng 300g', 'Cốm xanh dẻo thơm, đóng túi hút chân không dùng làm quà Hà Nội.', 'Cốm xanh dẻo thơm Hà Nội.', 129000.00, 34, 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&w=900&q=80', 'Hà Nội', '300g', '20 ngày', JSON_ARRAY('Mùa thu Hà Nội', 'Hút chân không'), JSON_ARRAY('https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 09:55:00', '2026-01-02 09:55:00'),
(19, 5, 3, 7, 'MUOI-TOM-TN-250', 'muoi-tom-tay-ninh-250g', 'Muối tôm Tây Ninh 250g', 'Muối tôm cay thơm, hạt tơi, hợp chấm trái cây và bánh tráng.', 'Muối tôm Tây Ninh cay thơm.', 69000.00, 110, 'https://images.unsplash.com/photo-1518110925495-5fe2fda0442c?auto=format&fit=crop&w=900&q=80', 'Tây Ninh', '250g', '12 tháng', JSON_ARRAY('Gia vị miền Nam', 'Hũ tiện dụng'), JSON_ARRAY('https://images.unsplash.com/photo-1518110925495-5fe2fda0442c?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 10:00:00', '2026-01-02 10:00:00'),
(20, 1, 2, 8, 'GAO-ST24-5KG', 'gao-thom-st24-5kg', 'Gạo thơm ST24 5kg', 'Gạo thơm ST24 hạt dài, cơm mềm, vị ngọt tự nhiên.', 'Gạo ST24 thơm mềm cho bữa cơm gia đình.', 255000.00, 42, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=900&q=80', 'Sóc Trăng', '5kg', '12 tháng', JSON_ARRAY('Lúa thơm', 'Bao 5kg'), JSON_ARRAY('https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 10:05:00', '2026-01-02 10:05:00'),
(21, 6, 7, 1, 'XOAI-SAY-MC-300', 'xoai-say-deo-moc-chau-300g', 'Xoài sấy dẻo Mộc Châu 300g', 'Xoài chín sấy dẻo, vị ngọt dịu, màu vàng tự nhiên.', 'Xoài sấy dẻo vàng thơm.', 125000.00, 60, 'https://images.unsplash.com/photo-1553279768-865429fa0078?auto=format&fit=crop&w=900&q=80', 'Mộc Châu, Sơn La', '300g', '9 tháng', JSON_ARRAY('Sấy dẻo', 'Không phẩm màu'), JSON_ARRAY('https://images.unsplash.com/photo-1553279768-865429fa0078?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 10:10:00', '2026-01-02 10:10:00'),
(22, 7, 3, 5, 'MACCA-DL-400', 'hat-macca-da-lat-400g', 'Hạt mắc ca Đà Lạt 400g', 'Hạt mắc ca nứt vỏ, bùi béo, đóng túi zip giữ hương.', 'Mắc ca nứt vỏ Đà Lạt.', 245000.00, 36, 'https://images.unsplash.com/photo-1608797178974-15b35a64ede9?auto=format&fit=crop&w=900&q=80', 'Đà Lạt, Lâm Đồng', '400g', '12 tháng', JSON_ARRAY('Nứt vỏ', 'Túi zip'), JSON_ARRAY('https://images.unsplash.com/photo-1608797178974-15b35a64ede9?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 10:15:00', '2026-01-02 10:15:00'),
(23, 8, 12, 3, 'GIFT-TRA-SEN-01', 'hop-qua-tra-sen-tay-ho', 'Hộp quà trà sen Tây Hồ', 'Hộp quà gồm trà sen, bánh mè xửng và thiệp câu chuyện vùng nguyên liệu.', 'Hộp quà trà sen trang nhã.', 520000.00, 18, 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?auto=format&fit=crop&w=900&q=80', 'Hà Nội - Huế', '1 hộp', '12 tháng', JSON_ARRAY('Hộp quà', 'Thiệp câu chuyện'), JSON_ARRAY('https://images.unsplash.com/photo-1549465220-1a8b9238cd48?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 10:20:00', '2026-01-02 10:20:00'),
(24, 8, 5, 7, 'GIFT-PQ-02', 'hop-qua-gia-vi-phu-quoc', 'Hộp quà gia vị Phú Quốc', 'Combo nước mắm, tiêu đen và muối chấm đóng hộp gỗ nhẹ.', 'Hộp quà gia vị Phú Quốc.', 650000.00, 14, 'https://images.unsplash.com/photo-1607349913338-fca6f7fc42d0?auto=format&fit=crop&w=900&q=80', 'Phú Quốc, Kiên Giang', '1 hộp', '18 tháng', JSON_ARRAY('Hộp quà', 'Sản phẩm biển đảo'), JSON_ARRAY('https://images.unsplash.com/photo-1607349913338-fca6f7fc42d0?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 10:25:00', '2026-01-02 10:25:00'),
(25, 3, 4, 1, 'THAO-MOC-TB-150', 'tra-thao-moc-tay-bac-150g', 'Trà thảo mộc Tây Bắc 150g', 'Phối trộn atiso đỏ, cỏ ngọt và thảo mộc vùng cao, vị dịu dễ uống.', 'Trà thảo mộc vùng cao.', 165000.00, 44, 'https://images.unsplash.com/photo-1515823064-d6e0c04616a7?auto=format&fit=crop&w=900&q=80', 'Sơn La', '150g', '12 tháng', JSON_ARRAY('Thảo mộc', 'Không caffeine'), JSON_ARRAY('https://images.unsplash.com/photo-1515823064-d6e0c04616a7?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 10:30:00', '2026-01-02 10:30:00'),
(26, 6, 3, 5, 'DAU-SAY-DL-200', 'dau-tay-say-deo-da-lat-200g', 'Dâu tây sấy dẻo Đà Lạt 200g', 'Dâu tây Đà Lạt sấy dẻo, vị chua ngọt, dùng làm topping hoặc ăn nhẹ.', 'Dâu tây sấy dẻo Đà Lạt.', 159000.00, 28, 'https://images.unsplash.com/photo-1464965911861-746a04b4bca6?auto=format&fit=crop&w=900&q=80', 'Đà Lạt, Lâm Đồng', '200g', '9 tháng', JSON_ARRAY('Trái cây sấy', 'Túi zip'), JSON_ARRAY('https://images.unsplash.com/photo-1464965911861-746a04b4bca6?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 10:35:00', '2026-01-02 10:35:00'),
(27, 2, 8, 4, 'KEO-CU-DO-250', 'keo-cu-do-ha-tinh-250g', 'Kẹo cu đơ Hà Tĩnh 250g', 'Kẹo lạc mật mía giòn thơm, vị gừng nhẹ, ăn cùng trà nóng.', 'Kẹo lạc mật mía giòn thơm.', 89000.00, 58, 'https://images.unsplash.com/photo-1587132137056-bfbf0166836e?auto=format&fit=crop&w=900&q=80', 'Hà Tĩnh', '250g', '6 tháng', JSON_ARRAY('Mật mía', 'Lạc rang'), JSON_ARRAY('https://images.unsplash.com/photo-1587132137056-bfbf0166836e?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 10:40:00', '2026-01-02 10:40:00'),
(28, 1, 2, 8, 'GAO-LUT-ST-2KG', 'gao-lut-do-soc-trang-2kg', 'Gạo lứt đỏ Sóc Trăng 2kg', 'Gạo lứt đỏ giàu chất xơ, hạt chắc, phù hợp thực đơn lành mạnh.', 'Gạo lứt đỏ giàu chất xơ.', 145000.00, 39, 'https://images.unsplash.com/photo-1536304993881-ff6e9eefa2a6?auto=format&fit=crop&w=900&q=80', 'Sóc Trăng', '2kg', '12 tháng', JSON_ARRAY('Ngũ cốc nguyên cám', 'Bao giấy'), JSON_ARRAY('https://images.unsplash.com/photo-1536304993881-ff6e9eefa2a6?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 10:45:00', '2026-01-02 10:45:00'),
(29, 7, 10, 7, 'DIEU-MATONG-300', 'hat-dieu-rang-mat-ong-300g', 'Hạt điều rang mật ong 300g', 'Hạt điều Bình Phước áo mật ong nhẹ, giòn bùi, hộp thiếc nhỏ.', 'Hạt điều rang mật ong giòn bùi.', 185000.00, 66, 'https://images.unsplash.com/photo-1615485737651-580c9159c89d?auto=format&fit=crop&w=900&q=80', 'Bình Phước', '300g', '10 tháng', JSON_ARRAY('Hộp thiếc', 'Ăn nhẹ'), JSON_ARRAY('https://images.unsplash.com/photo-1615485737651-580c9159c89d?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 10:50:00', '2026-01-02 10:50:00'),
(30, 4, 1, 2, 'TRA-LAI-TN-200', 'tra-nhai-lai-thai-nguyen-200g', 'Trà nhài Thái Nguyên 200g', 'Trà xanh ướp hoa nhài tự nhiên, hương nhẹ và nước trà trong.', 'Trà xanh ướp hoa nhài tự nhiên.', 199000.00, 46, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=900&q=80', 'Thái Nguyên', '200g', '18 tháng', JSON_ARRAY('Ướp hoa nhài', 'Túi giấy'), JSON_ARRAY('https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 10:55:00', '2026-01-02 10:55:00'),
(31, 5, 8, 4, 'RUOC-HUE-180', 'ruoc-hue-180g', 'Ruốc Huế 180g', 'Mắm ruốc Huế thơm đậm, dùng nấu bún bò hoặc chấm rau củ.', 'Mắm ruốc Huế thơm đậm.', 99000.00, 40, 'https://images.unsplash.com/photo-1604908812861-c65db0a6898b?auto=format&fit=crop&w=900&q=80', 'Huế', '180g', '12 tháng', JSON_ARRAY('Gia vị Huế', 'Hũ thủy tinh'), JSON_ARRAY('https://images.unsplash.com/photo-1604908812861-c65db0a6898b?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 11:00:00', '2026-01-02 11:00:00'),
(32, 6, 7, 1, 'DAO-SAY-SP-250', 'dao-sapa-say-deo-250g', 'Đào Sa Pa sấy dẻo 250g', 'Đào Sa Pa sấy dẻo, miếng mềm, vị chua ngọt nhẹ.', 'Đào Sa Pa sấy dẻo.', 142000.00, 31, 'https://images.unsplash.com/photo-1532704868953-d85f24176d73?auto=format&fit=crop&w=900&q=80', 'Sa Pa, Lào Cai', '250g', '9 tháng', JSON_ARRAY('Trái cây sấy', 'Mùa vụ Tây Bắc'), JSON_ARRAY('https://images.unsplash.com/photo-1532704868953-d85f24176d73?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 11:05:00', '2026-01-02 11:05:00'),
(33, 8, 9, 5, 'GIFT-CAFE-BMT', 'hop-qua-ca-phe-buon-ma-thuot', 'Hộp quà cà phê Buôn Ma Thuột', 'Hộp quà gồm cà phê robusta, phin nhôm và thiệp câu chuyện nông hộ.', 'Hộp quà cà phê cao nguyên.', 590000.00, 16, 'https://images.unsplash.com/photo-1511920170033-f8396924c348?auto=format&fit=crop&w=900&q=80', 'Buôn Ma Thuột, Đắk Lắk', '1 hộp', '12 tháng', JSON_ARRAY('Hộp quà', 'Kèm phin'), JSON_ARRAY('https://images.unsplash.com/photo-1511920170033-f8396924c348?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 11:10:00', '2026-01-02 11:10:00'),
(34, 3, 6, 2, 'MAT-GUNG-HG-350', 'mat-ong-gung-ha-giang-350ml', 'Mật ong gừng Hà Giang 350ml', 'Mật ong bạc hà phối gừng già, hợp pha trà ấm vào buổi sáng.', 'Mật ong gừng ấm dịu.', 289000.00, 25, 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?auto=format&fit=crop&w=900&q=80', 'Hà Giang', '350ml', '18 tháng', JSON_ARRAY('Mật ong', 'Gừng già'), JSON_ARRAY('https://images.unsplash.com/photo-1587049352846-4a222e784d38?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 11:15:00', '2026-01-02 11:15:00'),
(35, 8, 2, 8, 'GIFT-GAO-ST', 'combo-gao-thom-mien-tay', 'Combo gạo thơm miền Tây', 'Combo ST25, ST24 và gạo lứt đỏ cho gia đình dùng thử.', 'Combo gạo thơm miền Tây.', 690000.00, 20, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=900&q=80', 'Sóc Trăng', '3 túi', '12 tháng', JSON_ARRAY('Combo gia đình', 'Bao giấy'), JSON_ARRAY('https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=900&q=80'), TRUE, FALSE, '2026-01-02 11:20:00', '2026-01-02 11:20:00');

INSERT INTO `user_addresses` (`id`, `user_id`, `label`, `recipient`, `phone`, `line1`, `city`, `note`, `is_default`, `created_at`, `updated_at`) VALUES
(4, 13, 'Nhà riêng', 'Nguyễn An Nhiên', '0900000013', '9 Phan Đình Phùng', 'Hà Nội', 'Giao giờ hành chính.', TRUE, '2026-01-12 08:00:00', '2026-01-12 08:00:00'),
(5, 14, 'Căn hộ', 'Lê Bảo Trâm', '0900000014', '42 Pasteur', 'Đà Nẵng', NULL, TRUE, '2026-01-12 08:05:00', '2026-01-12 08:05:00'),
(6, 15, 'Nhà riêng', 'Phạm Gia Hân', '0900000015', '88 Hai Bà Trưng', 'TP.HCM', NULL, TRUE, '2026-01-12 08:10:00', '2026-01-12 08:10:00'),
(7, 16, 'Nhà riêng', 'Trương Đức Minh', '0900000016', '16 Nguyễn Văn Cừ', 'Cần Thơ', NULL, TRUE, '2026-01-12 08:15:00', '2026-01-12 08:15:00'),
(8, 17, 'Cửa hàng', 'Đặng Khánh Linh', '0900000017', '5 Lê Lợi', 'Huế', 'Gọi trước khi giao.', TRUE, '2026-01-12 08:20:00', '2026-01-12 08:20:00'),
(9, 20, 'Văn phòng', 'Hồ Minh Quân', '0900000020', '102 Cách Mạng Tháng Tám', 'Buôn Ma Thuột', NULL, TRUE, '2026-01-12 08:25:00', '2026-01-12 08:25:00'),
(10, 24, 'Nhà riêng', 'Dương Bích Ngọc', '0900000024', '8 Điện Biên Phủ', 'Sa Pa', NULL, TRUE, '2026-01-12 08:30:00', '2026-01-12 08:30:00'),
(11, 25, 'Nhà riêng', 'Đinh Quốc Việt', '0900000025', '33 Nguyễn Huệ', 'Sóc Trăng', NULL, TRUE, '2026-01-12 08:35:00', '2026-01-12 08:35:00');

INSERT INTO `orders` (`id`, `user_id`, `order_no`, `recipient_name`, `recipient_phone`, `shipping_address`, `payment_method`, `status`, `subtotal`, `shipping_fee`, `discount_amount`, `total_amount`, `stock_deducted`, `stock_deducted_at`, `shipping_carrier`, `shipping_code`, `shipped_at`, `delivered_at`, `cancelled_at`, `note`, `created_at`, `updated_at`) VALUES
(4, 13, 'ORD-20260004', 'Nguyễn An Nhiên', '0900000013', '9 Phan Đình Phùng, Hà Nội', 'BANK_TRANSFER', 'DELIVERED', 648000.00, 25000.00, 30000.00, 643000.00, TRUE, '2026-02-15 09:10:00', 'GHN', 'GHN-ORD-20260004', '2026-02-16 08:00:00', '2026-02-17 17:00:00', NULL, 'Hộp quà giao nguyên vẹn.', '2026-02-15 09:00:00', '2026-02-17 17:00:00'),
(5, 14, 'ORD-20260005', 'Lê Bảo Trâm', '0900000014', '42 Pasteur, Đà Nẵng', 'COD', 'SHIPPED', 358000.00, 30000.00, 0.00, 388000.00, TRUE, '2026-02-16 10:20:00', 'GHTK', 'GHTK-ORD-20260005', '2026-02-17 08:30:00', NULL, NULL, 'Giao buổi chiều.', '2026-02-16 10:00:00', '2026-02-17 08:30:00'),
(6, 15, 'ORD-20260006', 'Phạm Gia Hân', '0900000015', '88 Hai Bà Trưng, TP.HCM', 'BANK_TRANSFER', 'PACKED', 429000.00, 25000.00, 0.00, 454000.00, TRUE, '2026-02-17 11:15:00', NULL, NULL, NULL, NULL, NULL, 'Khách mua mật ong bạc hà.', '2026-02-17 11:00:00', '2026-02-17 15:00:00'),
(7, 16, 'ORD-20260007', 'Trương Đức Minh', '0900000016', '16 Nguyễn Văn Cừ, Cần Thơ', 'COD', 'CONFIRMED', 255000.00, 25000.00, 0.00, 280000.00, TRUE, '2026-02-18 09:45:00', NULL, NULL, NULL, NULL, NULL, 'Đơn gạo ST24.', '2026-02-18 09:30:00', '2026-02-18 09:45:00'),
(8, 17, 'ORD-20260008', 'Đặng Khánh Linh', '0900000017', '5 Lê Lợi, Huế', 'BANK_TRANSFER', 'PENDING', 269000.00, 25000.00, 0.00, 294000.00, FALSE, NULL, NULL, NULL, NULL, NULL, NULL, 'Chờ xác nhận thanh toán.', '2026-02-18 12:00:00', '2026-02-18 12:00:00'),
(9, 18, 'ORD-20260009', 'Bùi Nhật Nam', '0900000018', '19 Trần Hưng Đạo, Hải Phòng', 'COD', 'CANCELLED', 219000.00, 25000.00, 0.00, 244000.00, FALSE, NULL, NULL, NULL, NULL, NULL, '2026-02-19 08:40:00', 'Khách đổi địa chỉ sau khi đặt.', '2026-02-19 08:00:00', '2026-02-19 08:40:00'),
(10, 19, 'ORD-20260010', 'Võ Thanh Mai', '0900000019', '73 Nguyễn Trãi, Nha Trang', 'COD', 'DELIVERY_FAILED', 155000.00, 30000.00, 0.00, 185000.00, TRUE, '2026-02-19 10:15:00', 'GHN', 'GHN-ORD-20260010', '2026-02-20 08:00:00', NULL, NULL, 'Không liên hệ được khách.', '2026-02-19 10:00:00', '2026-02-20 18:00:00'),
(11, 20, 'ORD-20260011', 'Hồ Minh Quân', '0900000020', '102 Cách Mạng Tháng Tám, Buôn Ma Thuột', 'BANK_TRANSFER', 'DELIVERED', 438000.00, 0.00, 20000.00, 418000.00, TRUE, '2026-02-20 09:20:00', 'GHN', 'GHN-ORD-20260011', '2026-02-21 07:30:00', '2026-02-22 16:00:00', NULL, 'Khách đặt cà phê cho văn phòng.', '2026-02-20 09:00:00', '2026-02-22 16:00:00'),
(12, 21, 'ORD-20260012', 'Lý Hoàng Yến', '0900000021', '12 Bạch Đằng, Quy Nhơn', 'COD', 'SHIPPED', 650000.00, 35000.00, 50000.00, 635000.00, TRUE, '2026-02-21 13:10:00', 'GHTK', 'GHTK-ORD-20260012', '2026-02-22 09:00:00', NULL, NULL, 'Hộp quà gia vị Phú Quốc.', '2026-02-21 13:00:00', '2026-02-22 09:00:00'),
(13, 22, 'ORD-20260013', 'Ngô Gia Bảo', '0900000022', '44 Hùng Vương, Đà Lạt', 'BANK_TRANSFER', 'PACKED', 404000.00, 25000.00, 0.00, 429000.00, TRUE, '2026-02-22 09:30:00', NULL, NULL, NULL, NULL, NULL, 'Trái cây sấy Đà Lạt.', '2026-02-22 09:00:00', '2026-02-22 12:00:00'),
(14, 23, 'ORD-20260014', 'Tạ Minh Châu', '0900000023', '61 Võ Thị Sáu, Vũng Tàu', 'COD', 'CONFIRMED', 59000.00, 25000.00, 0.00, 84000.00, TRUE, '2026-02-22 15:20:00', NULL, NULL, NULL, NULL, NULL, 'Muối ớt xanh.', '2026-02-22 15:00:00', '2026-02-22 15:20:00'),
(15, 24, 'ORD-20260015', 'Dương Bích Ngọc', '0900000024', '8 Điện Biên Phủ, Sa Pa', 'BANK_TRANSFER', 'DELIVERED', 461000.00, 35000.00, 20000.00, 476000.00, TRUE, '2026-02-23 08:20:00', 'GHN', 'GHN-ORD-20260015', '2026-02-24 07:30:00', '2026-02-25 17:30:00', NULL, 'Đơn vùng cao, cần đóng gói chắc.', '2026-02-23 08:00:00', '2026-02-25 17:30:00'),
(16, 25, 'ORD-20260016', 'Đinh Quốc Việt', '0900000025', '33 Nguyễn Huệ, Sóc Trăng', 'COD', 'PENDING', 690000.00, 0.00, 30000.00, 660000.00, FALSE, NULL, NULL, NULL, NULL, NULL, NULL, 'Combo gạo cho gia đình.', '2026-02-23 11:00:00', '2026-02-23 11:00:00'),
(17, 26, 'ORD-20260017', 'Cao Thu Phương', '0900000026', '21 Lý Tự Trọng, Cần Thơ', 'BANK_TRANSFER', 'DELIVERED', 228000.00, 25000.00, 0.00, 253000.00, TRUE, '2026-02-24 10:10:00', 'GHN', 'GHN-ORD-20260017', '2026-02-25 08:00:00', '2026-02-26 16:20:00', NULL, 'Bánh pía và mè xửng.', '2026-02-24 10:00:00', '2026-02-26 16:20:00'),
(18, 13, 'ORD-20260018', 'Nguyễn An Nhiên', '0900000013', '9 Phan Đình Phùng, Hà Nội', 'BANK_TRANSFER', 'SHIPPED', 590000.00, 30000.00, 0.00, 620000.00, TRUE, '2026-02-25 09:10:00', 'GHN', 'GHN-ORD-20260018', '2026-02-26 08:30:00', NULL, NULL, 'Hộp quà cà phê.', '2026-02-25 09:00:00', '2026-02-26 08:30:00'),
(19, 14, 'ORD-20260019', 'Lê Bảo Trâm', '0900000014', '42 Pasteur, Đà Nẵng', 'COD', 'PACKED', 284000.00, 25000.00, 0.00, 309000.00, TRUE, '2026-02-25 14:10:00', NULL, NULL, NULL, NULL, NULL, 'Kẹo cu đơ và đào sấy.', '2026-02-25 14:00:00', '2026-02-25 16:00:00'),
(20, 15, 'ORD-20260020', 'Phạm Gia Hân', '0900000015', '88 Hai Bà Trưng, TP.HCM', 'BANK_TRANSFER', 'CONFIRMED', 520000.00, 0.00, 20000.00, 500000.00, TRUE, '2026-02-26 09:45:00', NULL, NULL, NULL, NULL, NULL, 'Hộp quà trà sen.', '2026-02-26 09:30:00', '2026-02-26 09:45:00');

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name_snapshot`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES
(7, 4, 13, 'Hạt điều Bình Phước rang muối 500g', 1, 219000.00, 219000.00, '2026-02-15 09:05:00', '2026-02-15 09:05:00'),
(8, 4, 9, 'Mật ong bạc hà Hà Giang 500ml', 1, 429000.00, 429000.00, '2026-02-15 09:06:00', '2026-02-15 09:06:00'),
(9, 5, 8, 'Nước mắm Phú Quốc 40 độ đạm 500ml', 2, 179000.00, 358000.00, '2026-02-16 10:05:00', '2026-02-16 10:05:00'),
(10, 6, 9, 'Mật ong bạc hà Hà Giang 500ml', 1, 429000.00, 429000.00, '2026-02-17 11:05:00', '2026-02-17 11:05:00'),
(11, 7, 20, 'Gạo thơm ST24 5kg', 1, 255000.00, 255000.00, '2026-02-18 09:35:00', '2026-02-18 09:35:00'),
(12, 8, 16, 'Trà sen Tây Hồ 100g', 1, 269000.00, 269000.00, '2026-02-18 12:05:00', '2026-02-18 12:05:00'),
(13, 9, 13, 'Hạt điều Bình Phước rang muối 500g', 1, 219000.00, 219000.00, '2026-02-19 08:05:00', '2026-02-19 08:05:00'),
(14, 10, 14, 'Tiêu đen Phú Quốc 200g', 1, 155000.00, 155000.00, '2026-02-19 10:05:00', '2026-02-19 10:05:00'),
(15, 11, 7, 'Cà phê Robusta Buôn Ma Thuột 500g', 2, 219000.00, 438000.00, '2026-02-20 09:05:00', '2026-02-20 09:05:00'),
(16, 12, 24, 'Hộp quà gia vị Phú Quốc', 1, 650000.00, 650000.00, '2026-02-21 13:05:00', '2026-02-21 13:05:00'),
(17, 13, 26, 'Dâu tây sấy dẻo Đà Lạt 200g', 1, 159000.00, 159000.00, '2026-02-22 09:05:00', '2026-02-22 09:05:00'),
(18, 13, 22, 'Hạt mắc ca Đà Lạt 400g', 1, 245000.00, 245000.00, '2026-02-22 09:06:00', '2026-02-22 09:06:00'),
(19, 14, 15, 'Muối ớt xanh Đà Lạt 120g', 1, 59000.00, 59000.00, '2026-02-22 15:05:00', '2026-02-22 15:05:00'),
(20, 15, 17, 'Trà Shan tuyết Suối Giàng 200g', 1, 319000.00, 319000.00, '2026-02-23 08:05:00', '2026-02-23 08:05:00'),
(21, 15, 32, 'Đào Sa Pa sấy dẻo 250g', 1, 142000.00, 142000.00, '2026-02-23 08:06:00', '2026-02-23 08:06:00'),
(22, 16, 35, 'Combo gạo thơm miền Tây', 1, 690000.00, 690000.00, '2026-02-23 11:05:00', '2026-02-23 11:05:00'),
(23, 17, 11, 'Bánh pía Sóc Trăng hộp 6 cái', 1, 149000.00, 149000.00, '2026-02-24 10:05:00', '2026-02-24 10:05:00'),
(24, 17, 12, 'Mè xửng Huế 300g', 1, 79000.00, 79000.00, '2026-02-24 10:06:00', '2026-02-24 10:06:00'),
(25, 18, 33, 'Hộp quà cà phê Buôn Ma Thuột', 1, 590000.00, 590000.00, '2026-02-25 09:05:00', '2026-02-25 09:05:00'),
(26, 19, 27, 'Kẹo cu đơ Hà Tĩnh 250g', 1, 89000.00, 89000.00, '2026-02-25 14:05:00', '2026-02-25 14:05:00'),
(27, 19, 32, 'Đào Sa Pa sấy dẻo 250g', 1, 142000.00, 142000.00, '2026-02-25 14:06:00', '2026-02-25 14:06:00'),
(28, 19, 19, 'Muối tôm Tây Ninh 250g', 1, 69000.00, 69000.00, '2026-02-25 14:07:00', '2026-02-25 14:07:00'),
(29, 20, 23, 'Hộp quà trà sen Tây Hồ', 1, 520000.00, 520000.00, '2026-02-26 09:35:00', '2026-02-26 09:35:00');

INSERT INTO `order_status_history` (`id`, `order_id`, `changed_by_user_id`, `from_status`, `to_status`, `note`, `changed_at`) VALUES
(12, 4, 1, NULL, 'DELIVERED', 'Đơn đã hoàn tất giao hàng.', '2026-02-17 17:00:00'),
(13, 5, 1, NULL, 'SHIPPED', 'Đơn đang vận chuyển.', '2026-02-17 08:30:00'),
(14, 6, 3, NULL, 'PACKED', 'Kho đã đóng gói.', '2026-02-17 15:00:00'),
(15, 7, 1, NULL, 'CONFIRMED', 'Đơn đã xác nhận.', '2026-02-18 09:45:00'),
(16, 8, 1, NULL, 'PENDING', 'Chờ thanh toán.', '2026-02-18 12:00:00'),
(17, 9, 1, NULL, 'CANCELLED', 'Khách hủy đơn.', '2026-02-19 08:40:00'),
(18, 10, 3, NULL, 'DELIVERY_FAILED', 'Giao hàng không thành công.', '2026-02-20 18:00:00'),
(19, 11, 1, NULL, 'DELIVERED', 'Giao thành công.', '2026-02-22 16:00:00'),
(20, 12, 3, NULL, 'SHIPPED', 'Đã bàn giao vận chuyển.', '2026-02-22 09:00:00'),
(21, 13, 3, NULL, 'PACKED', 'Đã đóng gói.', '2026-02-22 12:00:00'),
(22, 14, 1, NULL, 'CONFIRMED', 'Đã xác nhận đơn.', '2026-02-22 15:20:00'),
(23, 15, 1, NULL, 'DELIVERED', 'Đơn vùng cao đã giao.', '2026-02-25 17:30:00'),
(24, 16, 1, NULL, 'PENDING', 'Đơn mới tạo.', '2026-02-23 11:00:00'),
(25, 17, 1, NULL, 'DELIVERED', 'Giao thành công.', '2026-02-26 16:20:00'),
(26, 18, 3, NULL, 'SHIPPED', 'Đang vận chuyển.', '2026-02-26 08:30:00'),
(27, 19, 3, NULL, 'PACKED', 'Đã đóng gói.', '2026-02-25 16:00:00'),
(28, 20, 1, NULL, 'CONFIRMED', 'Đã xác nhận thanh toán.', '2026-02-26 09:45:00');

INSERT INTO `payments` (`id`, `order_id`, `transaction_code`, `payment_method`, `payment_status`, `amount`, `gateway_name`, `gateway_reference`, `paid_at`, `raw_payload`, `created_at`, `updated_at`) VALUES
(4, 4, 'TXN-BANK-20260004', 'BANK_TRANSFER', 'SUCCESS', 643000.00, 'VCB', 'VCB-REF-20260004', '2026-02-15 09:05:00', JSON_OBJECT('bank', 'VCB', 'confirmed', TRUE), '2026-02-15 09:00:00', '2026-02-15 09:05:00'),
(5, 5, 'TXN-COD-20260005', 'COD', 'PENDING', 388000.00, NULL, NULL, NULL, JSON_OBJECT('status', 'pending_cod'), '2026-02-16 10:00:00', '2026-02-16 10:00:00'),
(6, 6, 'TXN-BANK-20260006', 'BANK_TRANSFER', 'SUCCESS', 454000.00, 'TCB', 'TCB-REF-20260006', '2026-02-17 11:10:00', JSON_OBJECT('bank', 'TCB', 'confirmed', TRUE), '2026-02-17 11:00:00', '2026-02-17 11:10:00'),
(7, 7, 'TXN-COD-20260007', 'COD', 'PENDING', 280000.00, NULL, NULL, NULL, JSON_OBJECT('status', 'pending_cod'), '2026-02-18 09:30:00', '2026-02-18 09:30:00'),
(8, 8, 'TXN-BANK-20260008', 'BANK_TRANSFER', 'PENDING', 294000.00, 'MB', 'MB-REF-20260008', NULL, JSON_OBJECT('bank', 'MB', 'confirmed', FALSE), '2026-02-18 12:00:00', '2026-02-18 12:00:00'),
(9, 9, 'TXN-COD-20260009', 'COD', 'FAILED', 244000.00, NULL, NULL, NULL, JSON_OBJECT('cancelled', TRUE), '2026-02-19 08:00:00', '2026-02-19 08:40:00'),
(10, 10, 'TXN-COD-20260010', 'COD', 'PENDING', 185000.00, NULL, NULL, NULL, JSON_OBJECT('delivery_failed', TRUE), '2026-02-19 10:00:00', '2026-02-20 18:00:00'),
(11, 11, 'TXN-BANK-20260011', 'BANK_TRANSFER', 'SUCCESS', 418000.00, 'VCB', 'VCB-REF-20260011', '2026-02-20 09:05:00', JSON_OBJECT('bank', 'VCB', 'confirmed', TRUE), '2026-02-20 09:00:00', '2026-02-20 09:05:00'),
(12, 12, 'TXN-COD-20260012', 'COD', 'PENDING', 635000.00, NULL, NULL, NULL, JSON_OBJECT('status', 'pending_cod'), '2026-02-21 13:00:00', '2026-02-21 13:00:00'),
(13, 13, 'TXN-BANK-20260013', 'BANK_TRANSFER', 'SUCCESS', 429000.00, 'ACB', 'ACB-REF-20260013', '2026-02-22 09:10:00', JSON_OBJECT('bank', 'ACB', 'confirmed', TRUE), '2026-02-22 09:00:00', '2026-02-22 09:10:00'),
(14, 14, 'TXN-COD-20260014', 'COD', 'PENDING', 84000.00, NULL, NULL, NULL, JSON_OBJECT('status', 'pending_cod'), '2026-02-22 15:00:00', '2026-02-22 15:00:00'),
(15, 15, 'TXN-BANK-20260015', 'BANK_TRANSFER', 'SUCCESS', 476000.00, 'VCB', 'VCB-REF-20260015', '2026-02-23 08:05:00', JSON_OBJECT('bank', 'VCB', 'confirmed', TRUE), '2026-02-23 08:00:00', '2026-02-23 08:05:00'),
(16, 16, 'TXN-COD-20260016', 'COD', 'PENDING', 660000.00, NULL, NULL, NULL, JSON_OBJECT('status', 'pending_cod'), '2026-02-23 11:00:00', '2026-02-23 11:00:00'),
(17, 17, 'TXN-BANK-20260017', 'BANK_TRANSFER', 'SUCCESS', 253000.00, 'BIDV', 'BIDV-REF-20260017', '2026-02-24 10:05:00', JSON_OBJECT('bank', 'BIDV', 'confirmed', TRUE), '2026-02-24 10:00:00', '2026-02-24 10:05:00'),
(18, 18, 'TXN-BANK-20260018', 'BANK_TRANSFER', 'SUCCESS', 620000.00, 'VCB', 'VCB-REF-20260018', '2026-02-25 09:05:00', JSON_OBJECT('bank', 'VCB', 'confirmed', TRUE), '2026-02-25 09:00:00', '2026-02-25 09:05:00'),
(19, 19, 'TXN-COD-20260019', 'COD', 'PENDING', 309000.00, NULL, NULL, NULL, JSON_OBJECT('status', 'pending_cod'), '2026-02-25 14:00:00', '2026-02-25 14:00:00'),
(20, 20, 'TXN-BANK-20260020', 'BANK_TRANSFER', 'SUCCESS', 500000.00, 'TCB', 'TCB-REF-20260020', '2026-02-26 09:35:00', JSON_OBJECT('bank', 'TCB', 'confirmed', TRUE), '2026-02-26 09:30:00', '2026-02-26 09:35:00');

INSERT INTO `payment_status_history` (`id`, `payment_id`, `order_id`, `changed_by_user_id`, `from_status`, `to_status`, `note`, `changed_at`) VALUES
(6, 4, 4, 13, NULL, 'SUCCESS', 'Khách chuyển khoản thành công.', '2026-02-15 09:05:00'),
(7, 5, 5, 14, NULL, 'PENDING', 'Thanh toán COD khi giao.', '2026-02-16 10:00:00'),
(8, 6, 6, 15, NULL, 'SUCCESS', 'Đã nhận chuyển khoản.', '2026-02-17 11:10:00'),
(9, 7, 7, 16, NULL, 'PENDING', 'Thanh toán COD khi giao.', '2026-02-18 09:30:00'),
(10, 8, 8, 17, NULL, 'PENDING', 'Chờ xác nhận chuyển khoản.', '2026-02-18 12:00:00'),
(11, 9, 9, 18, NULL, 'FAILED', 'Đơn đã hủy.', '2026-02-19 08:40:00'),
(12, 10, 10, 19, NULL, 'PENDING', 'Chờ xử lý giao thất bại.', '2026-02-20 18:00:00'),
(13, 11, 11, 20, NULL, 'SUCCESS', 'Đã nhận chuyển khoản.', '2026-02-20 09:05:00'),
(14, 12, 12, 21, NULL, 'PENDING', 'Thanh toán COD khi giao.', '2026-02-21 13:00:00'),
(15, 13, 13, 22, NULL, 'SUCCESS', 'Đã nhận chuyển khoản.', '2026-02-22 09:10:00'),
(16, 14, 14, 23, NULL, 'PENDING', 'Thanh toán COD khi giao.', '2026-02-22 15:00:00'),
(17, 15, 15, 24, NULL, 'SUCCESS', 'Đã nhận chuyển khoản.', '2026-02-23 08:05:00'),
(18, 16, 16, 25, NULL, 'PENDING', 'Thanh toán COD khi giao.', '2026-02-23 11:00:00'),
(19, 17, 17, 26, NULL, 'SUCCESS', 'Đã nhận chuyển khoản.', '2026-02-24 10:05:00'),
(20, 18, 18, 13, NULL, 'SUCCESS', 'Đã nhận chuyển khoản.', '2026-02-25 09:05:00'),
(21, 19, 19, 14, NULL, 'PENDING', 'Thanh toán COD khi giao.', '2026-02-25 14:00:00'),
(22, 20, 20, 15, NULL, 'SUCCESS', 'Đã nhận chuyển khoản.', '2026-02-26 09:35:00');

INSERT INTO `reviews` (`id`, `order_item_id`, `user_id`, `product_id`, `rating`, `comment`, `is_visible`, `created_at`, `updated_at`) VALUES
(4, 7, 13, 13, 5, 'Hạt điều giòn, hộp chắc chắn, rất hợp làm quà.', TRUE, '2026-02-18 20:00:00', '2026-02-18 20:00:00'),
(5, 8, 13, 9, 5, 'Mật ong thơm nhẹ, pha trà rất ngon.', TRUE, '2026-02-18 20:05:00', '2026-02-18 20:05:00'),
(6, 15, 20, 7, 5, 'Cà phê đậm đúng gu Buôn Ma Thuột.', TRUE, '2026-02-23 08:00:00', '2026-02-23 08:00:00'),
(7, 20, 24, 17, 5, 'Trà Shan tuyết nước vàng đẹp, hậu ngọt.', TRUE, '2026-02-26 19:00:00', '2026-02-26 19:00:00'),
(8, 21, 24, 32, 4, 'Đào sấy mềm, vị tự nhiên.', TRUE, '2026-02-26 19:05:00', '2026-02-26 19:05:00'),
(9, 23, 26, 11, 5, 'Bánh pía thơm, đóng gói cẩn thận.', TRUE, '2026-02-27 18:00:00', '2026-02-27 18:00:00'),
(10, 24, 26, 12, 4, 'Mè xửng dẻo và không quá ngọt.', TRUE, '2026-02-27 18:05:00', '2026-02-27 18:05:00');

INSERT INTO `complaints` (`id`, `order_id`, `user_id`, `product_id`, `reason`, `content`, `image_url`, `status`, `resolution_note`, `resolved_by_user_id`, `resolved_at`, `created_at`, `updated_at`) VALUES
(3, 10, 19, 14, 'Không liên hệ được khi giao', 'Khách báo số điện thoại có lúc mất sóng, cần hẹn giao lại.', NULL, 'IN_REVIEW', NULL, NULL, NULL, '2026-02-21 09:00:00', '2026-02-21 09:00:00'),
(4, 12, 21, 24, 'Móp nhẹ góc hộp', 'Hộp quà gia vị bị móp góc sau vận chuyển.', NULL, 'OPEN', NULL, NULL, NULL, '2026-02-23 10:30:00', '2026-02-23 10:30:00'),
(5, 15, 24, 32, 'Túi sấy bị hở mép', 'Một túi đào sấy có mép dán chưa kín.', NULL, 'RESOLVED', 'Đã gửi bù một túi đào sấy mới.', 1, '2026-02-27 09:00:00', '2026-02-26 09:00:00', '2026-02-27 09:00:00');

INSERT INTO `inventory_items` (`id`, `inventory_id`, `product_id`, `quantity_on_hand`, `reorder_level`, `safety_stock`, `last_counted_at`, `created_at`, `updated_at`) VALUES
(13, 1, 7, 42, 10, 6, '2026-02-20 08:00:00', '2026-01-03 10:00:00', '2026-02-20 08:00:00'),
(14, 2, 8, 64, 12, 8, '2026-02-20 08:05:00', '2026-01-03 10:01:00', '2026-02-20 08:05:00'),
(15, 1, 9, 6, 8, 4, '2026-02-20 08:10:00', '2026-01-03 10:02:00', '2026-02-20 08:10:00'),
(16, 1, 10, 18, 10, 5, '2026-02-20 08:15:00', '2026-01-03 10:03:00', '2026-02-20 08:15:00'),
(17, 2, 11, 36, 12, 8, '2026-02-20 08:20:00', '2026-01-03 10:04:00', '2026-02-20 08:20:00'),
(18, 1, 12, 2, 10, 4, '2026-02-20 08:25:00', '2026-01-03 10:05:00', '2026-02-20 08:25:00'),
(19, 2, 13, 54, 15, 8, '2026-02-20 08:30:00', '2026-01-03 10:06:00', '2026-02-20 08:30:00'),
(20, 2, 14, 9, 10, 5, '2026-02-20 08:35:00', '2026-01-03 10:07:00', '2026-02-20 08:35:00'),
(21, 1, 15, 0, 12, 6, '2026-02-20 08:40:00', '2026-01-03 10:08:00', '2026-02-20 08:40:00'),
(22, 1, 16, 20, 6, 4, '2026-02-20 08:45:00', '2026-01-03 10:09:00', '2026-02-20 08:45:00'),
(23, 1, 17, 5, 8, 4, '2026-02-20 08:50:00', '2026-01-03 10:10:00', '2026-02-20 08:50:00'),
(24, 2, 18, 14, 7, 3, '2026-02-20 08:55:00', '2026-01-03 10:11:00', '2026-02-20 08:55:00'),
(25, 2, 19, 72, 15, 8, '2026-02-20 09:00:00', '2026-01-03 10:12:00', '2026-02-20 09:00:00'),
(26, 2, 20, 28, 8, 5, '2026-02-20 09:05:00', '2026-01-03 10:13:00', '2026-02-20 09:05:00'),
(27, 1, 21, 31, 10, 5, '2026-02-20 09:10:00', '2026-01-03 10:14:00', '2026-02-20 09:10:00'),
(28, 1, 22, 7, 8, 4, '2026-02-20 09:15:00', '2026-01-03 10:15:00', '2026-02-20 09:15:00'),
(29, 1, 23, 8, 5, 3, '2026-02-20 09:20:00', '2026-01-03 10:16:00', '2026-02-20 09:20:00'),
(30, 2, 24, 4, 6, 3, '2026-02-20 09:25:00', '2026-01-03 10:17:00', '2026-02-20 09:25:00'),
(31, 1, 25, 21, 7, 4, '2026-02-20 09:30:00', '2026-01-03 10:18:00', '2026-02-20 09:30:00'),
(32, 1, 26, 12, 8, 4, '2026-02-20 09:35:00', '2026-01-03 10:19:00', '2026-02-20 09:35:00'),
(33, 2, 27, 26, 8, 4, '2026-02-20 09:40:00', '2026-01-03 10:20:00', '2026-02-20 09:40:00'),
(34, 2, 28, 19, 8, 4, '2026-02-20 09:45:00', '2026-01-03 10:21:00', '2026-02-20 09:45:00'),
(35, 2, 29, 43, 10, 5, '2026-02-20 09:50:00', '2026-01-03 10:22:00', '2026-02-20 09:50:00'),
(36, 1, 30, 17, 7, 4, '2026-02-20 09:55:00', '2026-01-03 10:23:00', '2026-02-20 09:55:00'),
(37, 1, 31, 11, 8, 4, '2026-02-20 10:00:00', '2026-01-03 10:24:00', '2026-02-20 10:00:00'),
(38, 1, 32, 3, 9, 5, '2026-02-20 10:05:00', '2026-01-03 10:25:00', '2026-02-20 10:05:00'),
(39, 2, 33, 9, 4, 2, '2026-02-20 10:10:00', '2026-01-03 10:26:00', '2026-02-20 10:10:00'),
(40, 1, 34, 13, 6, 3, '2026-02-20 10:15:00', '2026-01-03 10:27:00', '2026-02-20 10:15:00'),
(41, 2, 35, 10, 4, 2, '2026-02-20 10:20:00', '2026-01-03 10:28:00', '2026-02-20 10:20:00');

INSERT INTO `supply_orders` (`id`, `supplier_id`, `order_no`, `status`, `expected_date`, `received_date`, `total_amount`, `created_by_user_id`, `created_at`, `updated_at`) VALUES
(3, 5, 'SO-20260003', 'PENDING', '2026-03-05', NULL, 2168000.00, 3, '2026-02-20 11:00:00', '2026-02-20 11:00:00'),
(4, 6, 'SO-20260004', 'CONFIRMED', '2026-03-06', NULL, 2480000.00, 3, '2026-02-20 11:05:00', '2026-02-20 11:30:00'),
(5, 7, 'SO-20260005', 'RECEIVED', '2026-02-25', '2026-02-24', 1832000.00, 9, '2026-02-18 10:00:00', '2026-02-24 16:00:00'),
(6, 8, 'SO-20260006', 'PENDING', '2026-03-08', NULL, 1562000.00, 9, '2026-02-21 09:30:00', '2026-02-21 09:30:00'),
(7, 9, 'SO-20260007', 'CONFIRMED', '2026-03-07', NULL, 5550000.00, 3, '2026-02-21 10:00:00', '2026-02-21 11:00:00'),
(8, 10, 'SO-20260008', 'RECEIVED', '2026-02-26', '2026-02-25', 4250000.00, 10, '2026-02-18 13:00:00', '2026-02-25 15:00:00'),
(9, 11, 'SO-20260009', 'PENDING', '2026-03-09', NULL, 1900000.00, 9, '2026-02-22 08:30:00', '2026-02-22 08:30:00'),
(10, 12, 'SO-20260010', 'CONFIRMED', '2026-03-10', NULL, 3010000.00, 3, '2026-02-22 09:00:00', '2026-02-22 10:00:00'),
(11, 2, 'SO-20260011', 'PENDING', '2026-03-12', NULL, 5030000.00, 10, '2026-02-23 08:30:00', '2026-02-23 08:30:00'),
(12, 4, 'SO-20260012', 'CONFIRMED', '2026-03-11', NULL, 3280000.00, 3, '2026-02-23 10:00:00', '2026-02-23 10:30:00');

INSERT INTO `supply_order_items` (`id`, `supply_order_id`, `product_id`, `quantity`, `unit_cost`, `line_total`, `created_at`, `updated_at`) VALUES
(7, 3, 8, 12, 118000.00, 1416000.00, '2026-02-20 11:05:00', '2026-02-20 11:05:00'),
(8, 3, 14, 8, 94000.00, 752000.00, '2026-02-20 11:06:00', '2026-02-20 11:06:00'),
(9, 4, 9, 8, 310000.00, 2480000.00, '2026-02-20 11:10:00', '2026-02-20 11:10:00'),
(10, 5, 10, 12, 88000.00, 1056000.00, '2026-02-18 10:05:00', '2026-02-18 10:05:00'),
(11, 5, 32, 8, 97000.00, 776000.00, '2026-02-18 10:06:00', '2026-02-18 10:06:00'),
(12, 6, 12, 20, 48000.00, 960000.00, '2026-02-21 09:35:00', '2026-02-21 09:35:00'),
(13, 6, 31, 10, 60200.00, 602000.00, '2026-02-21 09:36:00', '2026-02-21 09:36:00'),
(14, 7, 7, 25, 145000.00, 3625000.00, '2026-02-21 10:05:00', '2026-02-21 10:05:00'),
(15, 7, 33, 5, 385000.00, 1925000.00, '2026-02-21 10:06:00', '2026-02-21 10:06:00'),
(16, 8, 13, 20, 150000.00, 3000000.00, '2026-02-18 13:05:00', '2026-02-18 13:05:00'),
(17, 8, 29, 10, 125000.00, 1250000.00, '2026-02-18 13:06:00', '2026-02-18 13:06:00'),
(18, 9, 11, 20, 95000.00, 1900000.00, '2026-02-22 08:35:00', '2026-02-22 08:35:00'),
(19, 10, 16, 10, 190000.00, 1900000.00, '2026-02-22 09:05:00', '2026-02-22 09:05:00'),
(20, 10, 23, 3, 370000.00, 1110000.00, '2026-02-22 09:06:00', '2026-02-22 09:06:00'),
(21, 11, 20, 15, 190000.00, 2850000.00, '2026-02-23 08:35:00', '2026-02-23 08:35:00'),
(22, 11, 35, 4, 545000.00, 2180000.00, '2026-02-23 08:36:00', '2026-02-23 08:36:00'),
(23, 12, 17, 10, 230000.00, 2300000.00, '2026-02-23 10:05:00', '2026-02-23 10:05:00'),
(24, 12, 25, 10, 98000.00, 980000.00, '2026-02-23 10:06:00', '2026-02-23 10:06:00');

INSERT INTO `delivery_requests` (`id`, `requested_by_user_id`, `product_id`, `requested_qty`, `reason`, `status`, `approved_by_user_id`, `created_at`, `updated_at`) VALUES
(4, 3, 9, 8, 'Mật ong bạc hà còn thấp sau đợt khuyến mãi.', 'APPROVED', 1, '2026-02-20 09:00:00', '2026-02-20 10:00:00'),
(5, 9, 12, 20, 'Mè xửng Huế xuống mức critical tại kho Hà Nội.', 'PENDING', NULL, '2026-02-20 09:10:00', '2026-02-20 09:10:00'),
(6, 3, 15, 24, 'Muối ớt xanh hết hàng tại kệ TMĐT.', 'PENDING', NULL, '2026-02-20 09:20:00', '2026-02-20 09:20:00'),
(7, 10, 17, 10, 'Trà Shan tuyết cần bổ sung cho đơn quà tặng.', 'APPROVED', 1, '2026-02-20 09:30:00', '2026-02-20 10:30:00'),
(8, 9, 22, 12, 'Mắc ca Đà Lạt sắp dưới ngưỡng an toàn.', 'FULFILLED', 1, '2026-02-19 08:00:00', '2026-02-21 15:00:00'),
(9, 3, 24, 6, 'Hộp quà Phú Quốc cần nhập thêm trước lễ.', 'APPROVED', 1, '2026-02-21 08:30:00', '2026-02-21 09:00:00'),
(10, 10, 30, 12, 'Trà nhài Thái Nguyên bán nhanh.', 'PENDING', NULL, '2026-02-21 09:10:00', '2026-02-21 09:10:00'),
(11, 3, 32, 16, 'Đào sấy Sa Pa còn thấp.', 'FULFILLED', 1, '2026-02-18 08:30:00', '2026-02-22 14:00:00'),
(12, 9, 33, 5, 'Hộp quà cà phê cần chuẩn bị cho corporate order.', 'APPROVED', 1, '2026-02-22 08:30:00', '2026-02-22 09:10:00'),
(13, 10, 35, 5, 'Combo gạo miền Tây cho chương trình gia đình.', 'PENDING', NULL, '2026-02-22 09:20:00', '2026-02-22 09:20:00'),
(14, 3, 21, 12, 'Xoài sấy dẻo bổ sung cho catalog mới.', 'CANCELLED', NULL, '2026-02-18 09:00:00', '2026-02-18 12:00:00'),
(15, 9, 26, 15, 'Dâu tây sấy cho chiến dịch Đà Lạt.', 'APPROVED', 1, '2026-02-22 10:00:00', '2026-02-22 10:30:00');

INSERT INTO `posts` (`id`, `created_by_user_id`, `title`, `excerpt`, `body`, `cover_image_url`, `status`, `published_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Hành trình trà Shan tuyết Suối Giàng', 'Theo dấu búp trà cổ thụ từ vùng cao Tây Bắc.', 'Bài viết kể về vùng trà cổ thụ, quy trình hái búp và cách Heritage Harvest chọn lô trà mùa mới.', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=1200&q=80', 'PUBLISHED', '2026-02-01 08:00:00', '2026-02-01 08:00:00', '2026-02-01 08:00:00'),
(2, 1, 'Bên trong nhà thùng nước mắm Phú Quốc', 'Câu chuyện ủ chượp cá cơm truyền thống.', 'Bài viết giới thiệu cách chọn cá, muối và thời gian ủ chượp tạo nên vị nước mắm Phú Quốc.', 'https://images.unsplash.com/photo-1472476443507-c7a5948772fc?auto=format&fit=crop&w=1200&q=80', 'PUBLISHED', '2026-02-02 08:00:00', '2026-02-02 08:00:00', '2026-02-02 08:00:00'),
(3, 1, 'Mùa hoa bạc hà Hà Giang', 'Mật ong bạc hà và lịch mùa vụ cao nguyên đá.', 'Bài viết tóm tắt mùa hoa bạc hà, cách bảo quản mật ong và mẹo pha trà ấm.', 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?auto=format&fit=crop&w=1200&q=80', 'PUBLISHED', '2026-02-03 08:00:00', '2026-02-03 08:00:00', '2026-02-03 08:00:00'),
(4, 1, 'Gạo ST25 trong bữa cơm miền Tây', 'Từ ruộng lúa Sóc Trăng đến bàn ăn gia đình.', 'Bài viết giới thiệu hạt gạo thơm, cách nấu và gợi ý phối món.', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=1200&q=80', 'PUBLISHED', '2026-02-04 08:00:00', '2026-02-04 08:00:00', '2026-02-04 08:00:00'),
(5, 1, 'Cà phê Buôn Ma Thuột rang mộc', 'Vì sao robusta rang mộc có vị đậm khác biệt.', 'Bài viết mô tả vùng nguyên liệu, hồ sơ rang và cách pha phin.', 'https://images.unsplash.com/photo-1447933601403-0c6688de566e?auto=format&fit=crop&w=1200&q=80', 'PUBLISHED', '2026-02-05 08:00:00', '2026-02-05 08:00:00', '2026-02-05 08:00:00'),
(6, 1, 'Hộp quà đặc sản cho dịp tri ân', 'Gợi ý kết hợp trà, bánh và gia vị vùng miền.', 'Bài viết tư vấn chọn hộp quà theo người nhận và ngân sách.', 'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?auto=format&fit=crop&w=1200&q=80', 'PUBLISHED', '2026-02-06 08:00:00', '2026-02-06 08:00:00', '2026-02-06 08:00:00'),
(7, 1, 'Mè xửng Huế và chén trà chiều', 'Một món ngọt nhỏ nhưng nhiều ký ức.', 'Bài viết kể về mè xửng, mè rang và cách thưởng thức cùng trà sen.', 'https://images.unsplash.com/photo-1603569283847-aa295f0d016a?auto=format&fit=crop&w=1200&q=80', 'PUBLISHED', '2026-02-07 08:00:00', '2026-02-07 08:00:00', '2026-02-07 08:00:00'),
(8, 1, 'Trái cây sấy Mộc Châu', 'Cách giữ vị chua ngọt tự nhiên sau mùa thu hoạch.', 'Bài viết nói về mận, xoài, đào sấy và tiêu chuẩn đóng gói.', 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=1200&q=80', 'PUBLISHED', '2026-02-08 08:00:00', '2026-02-08 08:00:00', '2026-02-08 08:00:00');

INSERT INTO `post_comments` (`id`, `post_id`, `user_id`, `content`, `status`, `hidden_by_user_id`, `hidden_at`, `created_at`, `updated_at`) VALUES
(1, 1, 13, 'Mình rất thích phần kể về cây trà cổ thụ.', 'VISIBLE', NULL, NULL, '2026-02-08 09:00:00', '2026-02-08 09:00:00'),
(2, 2, 15, 'Nước mắm Phú Quốc dùng kho cá rất hợp.', 'VISIBLE', NULL, NULL, '2026-02-08 09:10:00', '2026-02-08 09:10:00'),
(3, 3, 24, 'Mật ong bạc hà pha với gừng rất thơm.', 'VISIBLE', NULL, NULL, '2026-02-08 09:20:00', '2026-02-08 09:20:00'),
(4, 4, 16, 'Bài viết giúp mình chọn đúng loại gạo cho gia đình.', 'VISIBLE', NULL, NULL, '2026-02-08 09:30:00', '2026-02-08 09:30:00'),
(5, 5, 20, 'Cà phê robusta rang mộc rất đúng gu.', 'VISIBLE', NULL, NULL, '2026-02-08 09:40:00', '2026-02-08 09:40:00'),
(6, 6, 17, 'Hộp quà nhìn trang nhã, phù hợp biếu khách.', 'VISIBLE', NULL, NULL, '2026-02-08 09:50:00', '2026-02-08 09:50:00'),
(7, 7, 26, 'Mè xửng Huế ăn với trà sen rất hợp.', 'VISIBLE', NULL, NULL, '2026-02-08 10:00:00', '2026-02-08 10:00:00'),
(8, 8, 14, 'Mận sấy Mộc Châu là món mình hay mua.', 'VISIBLE', NULL, NULL, '2026-02-08 10:10:00', '2026-02-08 10:10:00'),
(9, 1, 18, 'Ảnh vùng trà rất đẹp.', 'VISIBLE', NULL, NULL, '2026-02-08 10:20:00', '2026-02-08 10:20:00'),
(10, 6, 21, 'Mong có thêm combo quà miền Trung.', 'VISIBLE', NULL, NULL, '2026-02-08 10:30:00', '2026-02-08 10:30:00');

INSERT INTO `post_likes` (`id`, `post_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 13, '2026-02-08 11:00:00', '2026-02-08 11:00:00'),
(2, 1, 14, '2026-02-08 11:01:00', '2026-02-08 11:01:00'),
(3, 2, 15, '2026-02-08 11:02:00', '2026-02-08 11:02:00'),
(4, 3, 24, '2026-02-08 11:03:00', '2026-02-08 11:03:00'),
(5, 4, 16, '2026-02-08 11:04:00', '2026-02-08 11:04:00'),
(6, 5, 20, '2026-02-08 11:05:00', '2026-02-08 11:05:00'),
(7, 6, 17, '2026-02-08 11:06:00', '2026-02-08 11:06:00'),
(8, 7, 26, '2026-02-08 11:07:00', '2026-02-08 11:07:00'),
(9, 8, 14, '2026-02-08 11:08:00', '2026-02-08 11:08:00'),
(10, 6, 21, '2026-02-08 11:09:00', '2026-02-08 11:09:00');

INSERT INTO `newsletter_subscriptions` (`id`, `email`, `source`, `created_at`, `updated_at`) VALUES
(1, 'annhien@example.com', 'home-hero', '2026-02-10 08:00:00', '2026-02-10 08:00:00'),
(2, 'baotram@example.com', 'footer-default', '2026-02-10 08:10:00', '2026-02-10 08:10:00'),
(3, 'giftbuyer@example.com', 'catalog', '2026-02-10 08:20:00', '2026-02-10 08:20:00'),
(4, 'coffeeclub@example.com', 'footer-catalog', '2026-02-10 08:30:00', '2026-02-10 08:30:00');

INSERT INTO `support_tickets` (`id`, `user_id`, `subject`, `message`, `channel`, `status`, `resolved_by_user_id`, `resolved_at`, `created_at`, `updated_at`) VALUES
(1, 4, 'Cần xác nhận ETA hộp quà Phú Quốc', 'Nhà cung cấp cần biết lịch lấy hàng cho lô hộp quà gia vị.', 'supplier', 'OPEN', NULL, NULL, '2026-02-21 10:00:00', '2026-02-21 10:00:00'),
(2, 11, 'Bổ sung thông tin lô mận sấy', 'Nhờ kho cập nhật số lượng nhận thực tế cho lô mận sấy Mộc Châu.', 'supplier', 'RESOLVED', 1, '2026-02-22 09:00:00', '2026-02-21 11:00:00', '2026-02-22 09:00:00'),
(3, 3, 'Cần thêm nhân sự đóng gói hộp quà', 'Lượng đơn quà tăng nhanh, đề nghị điều phối thêm nhân sự ca chiều.', 'warehouse', 'OPEN', NULL, NULL, '2026-02-22 14:00:00', '2026-02-22 14:00:00'),
(4, 10, 'In lại tem vận đơn', 'Một số tem vận đơn bị mờ mã vạch tại kho TP HCM.', 'warehouse', 'OPEN', NULL, NULL, '2026-02-23 08:00:00', '2026-02-23 08:00:00');

INSERT INTO `wishlist_items` (`id`, `user_id`, `product_id`, `created_at`, `updated_at`) VALUES
(4, 13, 23, '2026-02-14 09:20:00', '2026-02-14 09:20:00'),
(5, 14, 24, '2026-02-14 09:25:00', '2026-02-14 09:25:00'),
(6, 17, 16, '2026-02-14 09:30:00', '2026-02-14 09:30:00'),
(7, 20, 33, '2026-02-14 09:35:00', '2026-02-14 09:35:00'),
(8, 24, 17, '2026-02-14 09:40:00', '2026-02-14 09:40:00');


INSERT INTO `prices` (`id`, `product_id`, `supplier_id`, `cost_price`, `effective_from`, `effective_to`, `is_active`, `created_at`, `updated_at`) VALUES
(7, 7, 9, 145000.00, '2026-01-02 12:00:00', NULL, TRUE, '2026-01-02 12:00:00', '2026-01-02 12:00:00'),
(8, 8, 5, 118000.00, '2026-01-02 12:01:00', NULL, TRUE, '2026-01-02 12:01:00', '2026-01-02 12:01:00'),
(9, 9, 6, 310000.00, '2026-01-02 12:02:00', NULL, TRUE, '2026-01-02 12:02:00', '2026-01-02 12:02:00'),
(10, 10, 7, 88000.00, '2026-01-02 12:03:00', NULL, TRUE, '2026-01-02 12:03:00', '2026-01-02 12:03:00'),
(11, 11, 11, 95000.00, '2026-01-02 12:04:00', NULL, TRUE, '2026-01-02 12:04:00', '2026-01-02 12:04:00'),
(12, 12, 8, 48000.00, '2026-01-02 12:05:00', NULL, TRUE, '2026-01-02 12:05:00', '2026-01-02 12:05:00'),
(13, 13, 10, 150000.00, '2026-01-02 12:06:00', NULL, TRUE, '2026-01-02 12:06:00', '2026-01-02 12:06:00'),
(14, 14, 5, 98000.00, '2026-01-02 12:07:00', NULL, TRUE, '2026-01-02 12:07:00', '2026-01-02 12:07:00'),
(15, 15, 3, 33000.00, '2026-01-02 12:08:00', NULL, TRUE, '2026-01-02 12:08:00', '2026-01-02 12:08:00'),
(16, 16, 12, 190000.00, '2026-01-02 12:09:00', NULL, TRUE, '2026-01-02 12:09:00', '2026-01-02 12:09:00'),
(17, 17, 4, 230000.00, '2026-01-02 12:10:00', NULL, TRUE, '2026-01-02 12:10:00', '2026-01-02 12:10:00'),
(18, 18, 12, 82000.00, '2026-01-02 12:11:00', NULL, TRUE, '2026-01-02 12:11:00', '2026-01-02 12:11:00'),
(19, 19, 3, 42000.00, '2026-01-02 12:12:00', NULL, TRUE, '2026-01-02 12:12:00', '2026-01-02 12:12:00'),
(20, 20, 2, 190000.00, '2026-01-02 12:13:00', NULL, TRUE, '2026-01-02 12:13:00', '2026-01-02 12:13:00'),
(21, 21, 7, 76000.00, '2026-01-02 12:14:00', NULL, TRUE, '2026-01-02 12:14:00', '2026-01-02 12:14:00'),
(22, 22, 3, 175000.00, '2026-01-02 12:15:00', NULL, TRUE, '2026-01-02 12:15:00', '2026-01-02 12:15:00'),
(23, 23, 12, 365000.00, '2026-01-02 12:16:00', NULL, TRUE, '2026-01-02 12:16:00', '2026-01-02 12:16:00'),
(24, 24, 5, 465000.00, '2026-01-02 12:17:00', NULL, TRUE, '2026-01-02 12:17:00', '2026-01-02 12:17:00'),
(25, 25, 4, 98000.00, '2026-01-02 12:18:00', NULL, TRUE, '2026-01-02 12:18:00', '2026-01-02 12:18:00'),
(26, 26, 3, 104000.00, '2026-01-02 12:19:00', NULL, TRUE, '2026-01-02 12:19:00', '2026-01-02 12:19:00'),
(27, 27, 8, 57000.00, '2026-01-02 12:20:00', NULL, TRUE, '2026-01-02 12:20:00', '2026-01-02 12:20:00'),
(28, 28, 2, 98000.00, '2026-01-02 12:21:00', NULL, TRUE, '2026-01-02 12:21:00', '2026-01-02 12:21:00'),
(29, 29, 10, 125000.00, '2026-01-02 12:22:00', NULL, TRUE, '2026-01-02 12:22:00', '2026-01-02 12:22:00'),
(30, 30, 1, 138000.00, '2026-01-02 12:23:00', NULL, TRUE, '2026-01-02 12:23:00', '2026-01-02 12:23:00'),
(31, 31, 8, 61000.00, '2026-01-02 12:24:00', NULL, TRUE, '2026-01-02 12:24:00', '2026-01-02 12:24:00'),
(32, 32, 7, 93000.00, '2026-01-02 12:25:00', NULL, TRUE, '2026-01-02 12:25:00', '2026-01-02 12:25:00'),
(33, 33, 9, 410000.00, '2026-01-02 12:26:00', NULL, TRUE, '2026-01-02 12:26:00', '2026-01-02 12:26:00'),
(34, 34, 6, 198000.00, '2026-01-02 12:27:00', NULL, TRUE, '2026-01-02 12:27:00', '2026-01-02 12:27:00'),
(35, 35, 2, 515000.00, '2026-01-02 12:28:00', NULL, TRUE, '2026-01-02 12:28:00', '2026-01-02 12:28:00');

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

ALTER TABLE `users` AUTO_INCREMENT = 27;
ALTER TABLE `admin_roles` AUTO_INCREMENT = 2;
ALTER TABLE `admin_permissions` AUTO_INCREMENT = 39;
ALTER TABLE `categories` AUTO_INCREMENT = 9;
ALTER TABLE `regions` AUTO_INCREMENT = 9;
ALTER TABLE `suppliers` AUTO_INCREMENT = 13;
ALTER TABLE `inventories` AUTO_INCREMENT = 3;
ALTER TABLE `products` AUTO_INCREMENT = 36;
ALTER TABLE `prices` AUTO_INCREMENT = 36;
ALTER TABLE `carts` AUTO_INCREMENT = 3;
ALTER TABLE `orders` AUTO_INCREMENT = 21;
ALTER TABLE `order_items` AUTO_INCREMENT = 30;
ALTER TABLE `order_status_history` AUTO_INCREMENT = 29;
ALTER TABLE `payments` AUTO_INCREMENT = 21;
ALTER TABLE `payment_status_history` AUTO_INCREMENT = 23;
ALTER TABLE `complaints` AUTO_INCREMENT = 6;
ALTER TABLE `reviews` AUTO_INCREMENT = 11;
ALTER TABLE `inventory_items` AUTO_INCREMENT = 42;
ALTER TABLE `supply_orders` AUTO_INCREMENT = 13;
ALTER TABLE `supply_order_items` AUTO_INCREMENT = 25;
ALTER TABLE `notifications` AUTO_INCREMENT = 4;
ALTER TABLE `delivery_requests` AUTO_INCREMENT = 16;
ALTER TABLE `cart_items` AUTO_INCREMENT = 4;
ALTER TABLE `posts` AUTO_INCREMENT = 9;
ALTER TABLE `post_comments` AUTO_INCREMENT = 11;
ALTER TABLE `post_likes` AUTO_INCREMENT = 11;
ALTER TABLE `newsletter_subscriptions` AUTO_INCREMENT = 5;
ALTER TABLE `support_tickets` AUTO_INCREMENT = 5;
ALTER TABLE `wishlist_items` AUTO_INCREMENT = 9;
ALTER TABLE `admin_settings` AUTO_INCREMENT = 2;
