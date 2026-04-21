-- Seed dữ liệu mẫu cho schema ecommerce_db
-- Tương thích: MySQL 8.0+

SET NAMES utf8mb4;
-- USE `ecommerce_db`;

SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE `reviews`;
TRUNCATE TABLE `complaints`;
TRUNCATE TABLE `payments`;
TRUNCATE TABLE `order_status_history`;
TRUNCATE TABLE `order_items`;
TRUNCATE TABLE `cart_items`;
TRUNCATE TABLE `delivery_requests`;
TRUNCATE TABLE `supply_order_items`;
TRUNCATE TABLE `supply_orders`;
TRUNCATE TABLE `inventory_items`;
TRUNCATE TABLE `notifications`;
TRUNCATE TABLE `orders`;
TRUNCATE TABLE `carts`;
TRUNCATE TABLE `prices`;
TRUNCATE TABLE `products`;
TRUNCATE TABLE `inventories`;
TRUNCATE TABLE `suppliers`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `users`;

SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password_hash`, `role`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Nguyen Van Admin', 'admin@shop.local', '0900000001', '$2y$10$adminhashdemo000000000000000000000000000000000000000000', 'ADMIN', 'ACTIVE', TRUE, '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(2, 'Tran Thi Customer', 'customer1@shop.local', '0900000002', '$2y$10$FhHMK0IzDB8ZxIYG5OkLG.vVrOHkZlhnAnL9xICDXh1/l.q.Egg0y', 'CUSTOMER', 'ACTIVE', TRUE, '2026-01-02 09:00:00', '2026-01-02 09:00:00'),
(3, 'Le Van Kho', 'warehouse@shop.local', '0900000003', '$2y$10$warehousehashdemo00000000000000000000000000000000000', 'WAREHOUSE_STAFF', 'ACTIVE', TRUE, '2026-01-03 10:00:00', '2026-01-03 10:00:00'),
(4, 'Pham Thi NCC', 'supplieruser@shop.local', '0900000004', '$2y$10$supplierhashdemo000000000000000000000000000000000000', 'SUPPLIER', 'ACTIVE', TRUE, '2026-01-04 11:00:00', '2026-01-04 11:00:00'),
(5, 'Do Minh Khach', 'customer2@shop.local', '0900000005', '$2y$10$customerhashdemo2222222222222222222222222222222222222', 'CUSTOMER', 'ACTIVE', TRUE, '2026-01-05 12:00:00', '2026-01-05 12:00:00'),
(6, 'Active User', 'active@example.com', '0901111111', '$2y$10$FhHMK0IzDB8ZxIYG5OkLG.vVrOHkZlhnAnL9xICDXh1/l.q.Egg0y', 'CUSTOMER', 'ACTIVE', TRUE, '2026-01-06 08:00:00', '2026-01-06 08:00:00'),
(7, 'Blocked User', 'blocked@example.com', '0902222222', '$2y$10$V16cweZ4GbJErB4o5Ofpxe6l4XEcKLZObVq.c3HviLflPzjXHIdYC', 'CUSTOMER', 'BLOCKED', TRUE, '2026-01-06 08:05:00', '2026-01-06 08:05:00'),
(8, 'Inactive User', 'inactive@example.com', '0903333333', '$2y$10$vfS7C5p/ImE1xzQi7Mok9Odn4UxJwgYb7qkXmD1jqPhSbipqg.dES', 'CUSTOMER', 'INACTIVE', TRUE, '2026-01-06 08:10:00', '2026-01-06 08:10:00');

INSERT INTO `categories` (`id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Dien thoai', 'Danh muc dien thoai thong minh.', TRUE, '2026-01-01 08:10:00', '2026-01-01 08:10:00'),
(2, 'Laptop', 'Danh muc may tinh xach tay.', TRUE, '2026-01-01 08:11:00', '2026-01-01 08:11:00'),
(3, 'Phu kien', 'Danh muc phu kien cong nghe.', TRUE, '2026-01-01 08:12:00', '2026-01-01 08:12:00'),
(4, 'Do gia dung thong minh', 'Thiet bi gia dung ket noi.', TRUE, '2026-01-01 08:13:00', '2026-01-01 08:13:00');

INSERT INTO `suppliers` (`id`, `supplier_code`, `name`, `contact_name`, `phone`, `email`, `address`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'SUP-APL', 'Cong ty A Phat Logistics', 'Nguyen Hai', '0911000001', 'contact@aphat.vn', '12 Nguyen Trai, Ha Noi', TRUE, '2026-01-01 08:20:00', '2026-01-01 08:20:00'),
(2, 'SUP-MIN', 'Minh Nguyen Distribution', 'Tran Quoc Minh', '0911000002', 'sales@minhnguyen.vn', '45 Vo Van Tan, TP.HCM', TRUE, '2026-01-01 08:21:00', '2026-01-01 08:21:00'),
(3, 'SUP-TAM', 'Tam Tin Trading', 'Le Thu Tam', '0911000003', 'support@tamtin.vn', '88 Le Loi, Da Nang', TRUE, '2026-01-01 08:22:00', '2026-01-01 08:22:00');

INSERT INTO `inventories` (`id`, `name`, `location`, `created_at`, `updated_at`) VALUES
(1, 'Kho Ha Noi', 'KCN Bac Tu Liem, Ha Noi', '2026-01-01 08:30:00', '2026-01-01 08:30:00'),
(2, 'Kho TP HCM', 'Thu Duc, TP.HCM', '2026-01-01 08:31:00', '2026-01-01 08:31:00');

INSERT INTO `products` (`id`, `category_id`, `supplier_id`, `sku`, `name`, `description`, `sale_price`, `stock_quantity`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'DT-IP15-128', 'iPhone 15 128GB', 'Dien thoai flagship ban 128GB.', 21990000.00, 30, TRUE, '2026-01-01 09:00:00', '2026-01-01 09:00:00'),
(2, 1, 2, 'DT-SSS24-256', 'Samsung S24 256GB', 'Dien thoai Android cao cap.', 19990000.00, 24, TRUE, '2026-01-01 09:05:00', '2026-01-01 09:05:00'),
(3, 2, 2, 'LT-MBA-M2', 'MacBook Air M2', 'Laptop mong nhe cho van phong.', 25990000.00, 12, TRUE, '2026-01-01 09:10:00', '2026-01-01 09:10:00'),
(4, 3, 3, 'PK-CHR-20W', 'Cu sac nhanh 20W', 'Cu sac USB-C 20W.', 390000.00, 80, TRUE, '2026-01-01 09:15:00', '2026-01-01 09:15:00'),
(5, 3, 3, 'PK-CBL-C2L', 'Cap USB-C to Lightning', 'Cap sac va dong bo du lieu.', 290000.00, 120, TRUE, '2026-01-01 09:20:00', '2026-01-01 09:20:00'),
(6, 4, 1, 'GD-AIR-PURE', 'May loc khong khi PureHome', 'May loc khong khi gia dinh.', 3490000.00, 18, TRUE, '2026-01-01 09:25:00', '2026-01-01 09:25:00');

INSERT INTO `prices` (`id`, `product_id`, `supplier_id`, `cost_price`, `effective_from`, `effective_to`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 19800000.00, '2026-01-01 09:30:00', NULL, TRUE, '2026-01-01 09:30:00', '2026-01-01 09:30:00'),
(2, 2, 2, 17950000.00, '2026-01-01 09:31:00', NULL, TRUE, '2026-01-01 09:31:00', '2026-01-01 09:31:00'),
(3, 3, 2, 23100000.00, '2026-01-01 09:32:00', NULL, TRUE, '2026-01-01 09:32:00', '2026-01-01 09:32:00'),
(4, 4, 3, 250000.00, '2026-01-01 09:33:00', NULL, TRUE, '2026-01-01 09:33:00', '2026-01-01 09:33:00'),
(5, 5, 3, 180000.00, '2026-01-01 09:34:00', NULL, TRUE, '2026-01-01 09:34:00', '2026-01-01 09:34:00'),
(6, 6, 1, 2950000.00, '2026-01-01 09:35:00', NULL, TRUE, '2026-01-01 09:35:00', '2026-01-01 09:35:00');

INSERT INTO `carts` (`id`, `user_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'ACTIVE', '2026-02-01 10:00:00', '2026-02-01 10:00:00'),
(2, 5, 'CHECKED_OUT', '2026-02-02 11:00:00', '2026-02-03 09:00:00');

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 21990000.00, 21990000.00, '2026-02-01 10:05:00', '2026-02-01 10:05:00'),
(2, 1, 4, 2, 390000.00, 780000.00, '2026-02-01 10:06:00', '2026-02-01 10:06:00'),
(3, 2, 6, 1, 3490000.00, 3490000.00, '2026-02-02 11:05:00', '2026-02-02 11:05:00');

INSERT INTO `orders` (`id`, `user_id`, `order_no`, `recipient_name`, `recipient_phone`, `shipping_address`, `payment_method`, `status`, `subtotal`, `shipping_fee`, `discount_amount`, `total_amount`, `note`, `created_at`, `updated_at`) VALUES
(1, 2, 'ORD-20260001', 'Tran Thi Customer', '0900000002', '101 Le Duan, Ha Noi', 'COD', 'DELIVERED', 22380000.00, 30000.00, 0.00, 22410000.00, 'Giao gio hanh chinh.', '2026-02-05 08:00:00', '2026-02-07 17:00:00'),
(2, 5, 'ORD-20260002', 'Do Minh Khach', '0900000005', '22 Dien Bien Phu, TP.HCM', 'BANK_TRANSFER', 'PAID', 3490000.00, 0.00, 100000.00, 3390000.00, 'Khach da chuyen khoan.', '2026-02-10 09:00:00', '2026-02-10 09:30:00'),
(3, 2, 'ORD-20260003', 'Tran Thi Customer', '0900000002', '101 Le Duan, Ha Noi', 'E_WALLET', 'SHIPPED', 25990000.00, 50000.00, 500000.00, 25540000.00, 'Giao nhanh trong ngay.', '2026-02-12 14:00:00', '2026-02-13 08:00:00');

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name_snapshot`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'iPhone 15 128GB', 1, 21990000.00, 21990000.00, '2026-02-05 08:05:00', '2026-02-05 08:05:00'),
(2, 1, 4, 'Cu sac nhanh 20W', 1, 390000.00, 390000.00, '2026-02-05 08:06:00', '2026-02-05 08:06:00'),
(3, 2, 6, 'May loc khong khi PureHome', 1, 3490000.00, 3490000.00, '2026-02-10 09:05:00', '2026-02-10 09:05:00'),
(4, 3, 3, 'MacBook Air M2', 1, 25990000.00, 25990000.00, '2026-02-12 14:05:00', '2026-02-12 14:05:00');

INSERT INTO `order_status_history` (`id`, `order_id`, `changed_by_user_id`, `from_status`, `to_status`, `note`, `changed_at`) VALUES
(1, 1, 1, NULL, 'PENDING', 'Don hang moi tao.', '2026-02-05 08:00:00'),
(2, 1, 1, 'PENDING', 'CONFIRMED', 'Da xac nhan don.', '2026-02-05 08:10:00'),
(3, 1, 3, 'CONFIRMED', 'PACKED', 'Da dong goi.', '2026-02-05 10:00:00'),
(4, 1, 3, 'PACKED', 'SHIPPED', 'Ban giao don vi van chuyen.', '2026-02-06 08:00:00'),
(5, 1, 1, 'SHIPPED', 'DELIVERED', 'Giao thanh cong.', '2026-02-07 17:00:00'),
(6, 2, 1, NULL, 'PENDING', 'Don hang moi tao.', '2026-02-10 09:00:00'),
(7, 2, 1, 'PENDING', 'PAID', 'Da nhan chuyen khoan.', '2026-02-10 09:30:00'),
(8, 3, 1, NULL, 'PENDING', 'Don hang moi tao.', '2026-02-12 14:00:00'),
(9, 3, 1, 'PENDING', 'CONFIRMED', 'Da xac nhan don.', '2026-02-12 14:15:00'),
(10, 3, 3, 'CONFIRMED', 'PACKED', 'Da dong goi.', '2026-02-12 17:00:00'),
(11, 3, 3, 'PACKED', 'SHIPPED', 'Da giao cho doi tac van chuyen.', '2026-02-13 08:00:00');

INSERT INTO `payments` (`id`, `order_id`, `transaction_code`, `payment_method`, `payment_status`, `amount`, `gateway_name`, `gateway_reference`, `paid_at`, `raw_payload`, `created_at`, `updated_at`) VALUES
(1, 1, 'TXN-COD-20260001', 'COD', 'SUCCESS', 22410000.00, NULL, NULL, '2026-02-07 17:00:00', JSON_OBJECT('collected_by', 'shipper', 'note', 'cash on delivery'), '2026-02-05 08:00:00', '2026-02-07 17:00:00'),
(2, 2, 'TXN-BANK-20260002', 'BANK_TRANSFER', 'SUCCESS', 3390000.00, 'VCB', 'VCB-REF-20260002', '2026-02-10 09:25:00', JSON_OBJECT('bank', 'VCB', 'confirmed', TRUE), '2026-02-10 09:00:00', '2026-02-10 09:25:00'),
(3, 3, 'TXN-EWALLET-20260003', 'E_WALLET', 'PENDING', 25540000.00, 'MoMo', 'MOMO-REF-20260003', NULL, JSON_OBJECT('gateway', 'MoMo', 'status', 'pending'), '2026-02-12 14:00:00', '2026-02-12 14:00:00');

INSERT INTO `complaints` (`id`, `order_id`, `user_id`, `product_id`, `reason`, `content`, `image_url`, `status`, `resolution_note`, `resolved_by_user_id`, `resolved_at`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 4, 'San pham khong dung cong suat', 'Khach phan anh cu sac nong bat thuong khi su dung.', 'https://example.com/images/complaint-1.jpg', 'RESOLVED', 'Da doi san pham moi cho khach.', 1, '2026-02-09 10:00:00', '2026-02-08 09:00:00', '2026-02-09 10:00:00'),
(2, 3, 2, 3, 'Giao hang cham', 'Khach yeu cau kiem tra don vi van chuyen.', NULL, 'IN_REVIEW', NULL, NULL, NULL, '2026-02-13 09:30:00', '2026-02-13 09:30:00');

INSERT INTO `reviews` (`id`, `order_item_id`, `user_id`, `product_id`, `rating`, `comment`, `is_visible`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 5, 'May dep, pin tot, giao nhanh.', TRUE, '2026-02-08 20:00:00', '2026-02-08 20:00:00'),
(2, 3, 5, 6, 4, 'May chay em, loc on, dong goi can than.', TRUE, '2026-02-11 18:00:00', '2026-02-11 18:00:00');

INSERT INTO `inventory_items` (`id`, `inventory_id`, `product_id`, `quantity_on_hand`, `reorder_level`, `safety_stock`, `last_counted_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 18, 5, 3, '2026-02-01 08:00:00', '2026-01-01 10:00:00', '2026-02-01 08:00:00'),
(2, 2, 1, 12, 5, 3, '2026-02-01 08:30:00', '2026-01-01 10:01:00', '2026-02-01 08:30:00'),
(3, 1, 2, 10, 4, 2, '2026-02-01 08:00:00', '2026-01-01 10:02:00', '2026-02-01 08:00:00'),
(4, 2, 2, 14, 4, 2, '2026-02-01 08:30:00', '2026-01-01 10:03:00', '2026-02-01 08:30:00'),
(5, 1, 3, 5, 2, 1, '2026-02-01 08:00:00', '2026-01-01 10:04:00', '2026-02-01 08:00:00'),
(6, 2, 3, 7, 2, 1, '2026-02-01 08:30:00', '2026-01-01 10:05:00', '2026-02-01 08:30:00'),
(7, 1, 4, 50, 15, 10, '2026-02-01 08:00:00', '2026-01-01 10:06:00', '2026-02-01 08:00:00'),
(8, 2, 4, 30, 15, 10, '2026-02-01 08:30:00', '2026-01-01 10:07:00', '2026-02-01 08:30:00'),
(9, 1, 5, 70, 20, 12, '2026-02-01 08:00:00', '2026-01-01 10:08:00', '2026-02-01 08:00:00'),
(10, 2, 5, 50, 20, 12, '2026-02-01 08:30:00', '2026-01-01 10:09:00', '2026-02-01 08:30:00'),
(11, 1, 6, 8, 3, 2, '2026-02-01 08:00:00', '2026-01-01 10:10:00', '2026-02-01 08:00:00'),
(12, 2, 6, 10, 3, 2, '2026-02-01 08:30:00', '2026-01-01 10:11:00', '2026-02-01 08:30:00');

INSERT INTO `supply_orders` (`id`, `supplier_id`, `order_no`, `status`, `expected_date`, `received_date`, `total_amount`, `created_by_user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 'SO-20260001', 'RECEIVED', '2026-01-20', '2026-01-19', 59000000.00, 3, '2026-01-15 09:00:00', '2026-01-19 16:00:00'),
(2, 3, 'SO-20260002', 'CONFIRMED', '2026-02-20', NULL, 21500000.00, 3, '2026-02-14 09:30:00', '2026-02-14 10:00:00');

INSERT INTO `supply_order_items` (`id`, `supply_order_id`, `product_id`, `quantity`, `unit_cost`, `line_total`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 19800000.00, 39600000.00, '2026-01-15 09:05:00', '2026-01-15 09:05:00'),
(2, 1, 6, 6, 3233333.33, 19400000.00, '2026-01-15 09:06:00', '2026-01-15 09:06:00'),
(3, 2, 4, 50, 250000.00, 12500000.00, '2026-02-14 09:35:00', '2026-02-14 09:35:00'),
(4, 2, 5, 50, 180000.00, 9000000.00, '2026-02-14 09:36:00', '2026-02-14 09:36:00');

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `channel`, `status`, `sent_at`, `read_at`, `created_at`) VALUES
(1, 2, 'Don hang da giao', 'Don hang ORD-20260001 da duoc giao thanh cong.', 'SYSTEM', 'READ', '2026-02-07 17:05:00', '2026-02-07 17:20:00', '2026-02-07 17:05:00'),
(2, 5, 'Xac nhan thanh toan', 'He thong da ghi nhan thanh toan cho don ORD-20260002.', 'EMAIL', 'SENT', '2026-02-10 09:35:00', NULL, '2026-02-10 09:30:00'),
(3, 3, 'Yeu cau nhap hang moi', 'Co yeu cau nhap them cap va cu sac.', 'SYSTEM', 'PENDING', NULL, NULL, '2026-02-14 11:00:00');

INSERT INTO `delivery_requests` (`id`, `requested_by_user_id`, `product_id`, `requested_qty`, `reason`, `status`, `approved_by_user_id`, `created_at`, `updated_at`) VALUES
(1, 3, 4, 50, 'Ton kho tai kho HN xuong duoi nguong canh bao.', 'APPROVED', 1, '2026-02-14 10:30:00', '2026-02-14 11:00:00'),
(2, 3, 5, 50, 'Can bo sung ton kho cho chuong trinh khuyen mai.', 'PENDING', NULL, '2026-02-14 10:35:00', '2026-02-14 10:35:00'),
(3, 3, 3, 5, 'MacBook Air can nhap bo sung trong tuan toi.', 'FULFILLED', 1, '2026-02-01 08:00:00', '2026-02-05 16:00:00');

-- Dong bo AUTO_INCREMENT cho cac bang chinh khi can
ALTER TABLE `users` AUTO_INCREMENT = 9;
ALTER TABLE `categories` AUTO_INCREMENT = 5;
ALTER TABLE `suppliers` AUTO_INCREMENT = 4;
ALTER TABLE `inventories` AUTO_INCREMENT = 3;
ALTER TABLE `products` AUTO_INCREMENT = 7;
ALTER TABLE `prices` AUTO_INCREMENT = 7;
ALTER TABLE `carts` AUTO_INCREMENT = 3;
ALTER TABLE `orders` AUTO_INCREMENT = 4;
ALTER TABLE `order_items` AUTO_INCREMENT = 5;
ALTER TABLE `order_status_history` AUTO_INCREMENT = 12;
ALTER TABLE `payments` AUTO_INCREMENT = 4;
ALTER TABLE `complaints` AUTO_INCREMENT = 3;
ALTER TABLE `reviews` AUTO_INCREMENT = 3;
ALTER TABLE `inventory_items` AUTO_INCREMENT = 13;
ALTER TABLE `supply_orders` AUTO_INCREMENT = 3;
ALTER TABLE `supply_order_items` AUTO_INCREMENT = 5;
ALTER TABLE `notifications` AUTO_INCREMENT = 4;
ALTER TABLE `delivery_requests` AUTO_INCREMENT = 4;
ALTER TABLE `cart_items` AUTO_INCREMENT = 4;

-- Kiem tra nhanh
-- SELECT COUNT(*) FROM users;
-- SELECT COUNT(*) FROM products;
-- SELECT COUNT(*) FROM orders;
