


SET NAMES utf8mb4;
-- USE `ecommerce_db`;

SET FOREIGN_KEY_CHECKS = 0;

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
(1, 'Nguyen Van Admin', 'admin@shop.local', '0900000001', '$2y$10$FhHMK0IzDB8ZxIYG5OkLG.vVrOHkZlhnAnL9xICDXh1/l.q.Egg0y', '12 Nguyen Hue', 'Ha Noi', 'Tay Bac', NULL, FALSE, FALSE, TRUE, TRUE, 0, 'Bronze', 500, 'ADMIN', 'ACTIVE', TRUE, '2026-01-01 08:00:00', '2026-01-01 08:00:00'),
(2, 'Tran Thi Customer', 'customer1@shop.local', '0900000002', '$2y$10$FhHMK0IzDB8ZxIYG5OkLG.vVrOHkZlhnAnL9xICDXh1/l.q.Egg0y', '101 Le Duan', 'Ha Noi', 'Dong Bac', NULL, TRUE, TRUE, TRUE, TRUE, 720, 'Silver', 1000, 'CUSTOMER', 'ACTIVE', TRUE, '2026-01-02 09:00:00', '2026-01-02 09:00:00'),
(3, 'Le Van Kho', 'warehouse@shop.local', '0900000003', '$2y$10$warehousehashdemo00000000000000000000000000000000000', 'KCN Bac Tu Liem', 'Ha Noi', NULL, NULL, FALSE, TRUE, TRUE, TRUE, 0, 'Bronze', 500, 'WAREHOUSE_STAFF', 'ACTIVE', TRUE, '2026-01-03 10:00:00', '2026-01-03 10:00:00'),
(4, 'Pham Thi NCC', 'supplieruser@shop.local', '0900000004', '$2y$10$supplierhashdemo000000000000000000000000000000000000', '45 Vo Van Tan', 'TP.HCM', NULL, NULL, FALSE, TRUE, TRUE, TRUE, 0, 'Bronze', 500, 'SUPPLIER', 'ACTIVE', TRUE, '2026-01-04 11:00:00', '2026-01-04 11:00:00'),
(5, 'Do Minh Khach', 'customer2@shop.local', '0900000005', '$2y$10$customerhashdemo2222222222222222222222222222222222222', '22 Dien Bien Phu', 'TP.HCM', 'Nam Bo', NULL, TRUE, FALSE, TRUE, TRUE, 340, 'Bronze', 500, 'CUSTOMER', 'ACTIVE', TRUE, '2026-01-05 12:00:00', '2026-01-05 12:00:00'),
(6, 'Active User', 'active@example.com', '0901111111', '$2y$10$FhHMK0IzDB8ZxIYG5OkLG.vVrOHkZlhnAnL9xICDXh1/l.q.Egg0y', '1 Tran Phu', 'Da Nang', NULL, NULL, FALSE, FALSE, TRUE, TRUE, 0, 'Bronze', 500, 'CUSTOMER', 'ACTIVE', TRUE, '2026-01-06 08:00:00', '2026-01-06 08:00:00'),
(7, 'Blocked User', 'blocked@example.com', '0902222222', '$2y$10$V16cweZ4GbJErB4o5Ofpxe6l4XEcKLZObVq.c3HviLflPzjXHIdYC', NULL, NULL, NULL, NULL, FALSE, FALSE, TRUE, TRUE, 0, 'Bronze', 500, 'CUSTOMER', 'BLOCKED', TRUE, '2026-01-06 08:05:00', '2026-01-06 08:05:00'),
(8, 'Inactive User', 'inactive@example.com', '0903333333', '$2y$10$vfS7C5p/ImE1xzQi7Mok9Odn4UxJwgYb7qkXmD1jqPhSbipqg.dES', NULL, NULL, NULL, NULL, FALSE, FALSE, TRUE, TRUE, 0, 'Bronze', 500, 'CUSTOMER', 'INACTIVE', TRUE, '2026-01-06 08:10:00', '2026-01-06 08:10:00');

INSERT INTO `user_addresses` (`id`, `user_id`, `label`, `recipient`, `phone`, `line1`, `city`, `note`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 2, 'Nha rieng', 'Tran Thi Customer', '0900000002', '101 Le Duan', 'Ha Noi', 'Giao sau 18h neu co the.', TRUE, '2026-01-03 09:00:00', '2026-01-03 09:00:00'),
(2, 2, 'Van phong', 'Tran Thi Customer', '0900000002', '18 Duy Tan', 'Ha Noi', NULL, FALSE, '2026-01-04 09:00:00', '2026-01-04 09:00:00'),
(3, 5, 'Can ho', 'Do Minh Khach', '0900000005', '22 Dien Bien Phu', 'TP.HCM', NULL, TRUE, '2026-01-05 12:30:00', '2026-01-05 12:30:00');

INSERT INTO `reward_redemptions` (`id`, `user_id`, `title`, `points_used`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'Mien phi van chuyen don ke tiep', 300, 'COMPLETED', '2026-02-08 18:00:00', '2026-02-08 18:00:00'),
(2, 2, 'Qua mau theo mua', 180, 'COMPLETED', '2026-02-12 19:00:00', '2026-02-12 19:00:00');

INSERT INTO `supplier_invitations` (`id`, `supplier_name`, `contact_name`, `email`, `categories`, `note`, `status`, `created_by_user_id`, `created_at`, `updated_at`) VALUES
(1, 'Hop tac xa Gao Muong', 'Nguyen Thi Lan', 'lienhe@gaomuong.vn', JSON_ARRAY('Gao dac san', 'Qua tang dia phuong'), 'Moi tham gia bo suu tap mua he.', 'SENT', 1, '2026-02-11 09:00:00', '2026-02-11 09:00:00'),
(2, 'Nha vuon Tra Co', 'Le Huu Phuc', 'hello@traco.vn', JSON_ARRAY('Tra', 'Dac san rung'), 'Can bo sung nguon hang tay bac.', 'SENT', 1, '2026-02-13 10:00:00', '2026-02-13 10:00:00');

INSERT INTO `categories` (`id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Gao - Nong san dac san', 'Cac loai gao, nong san va thuc pham kho dac san vung mien.', TRUE, '2026-01-01 08:10:00', '2026-01-01 08:10:00'),
(2, 'Mon an truyen thong', 'Cac mon an vat, banh va mon an truyen thong dong goi san.', TRUE, '2026-01-01 08:11:00', '2026-01-01 08:11:00'),
(3, 'Mat ong - Dac san rung', 'Mat ong, san vat tu nhien va dac san rung nui.', TRUE, '2026-01-01 08:12:00', '2026-01-01 08:12:00'),
(4, 'Tra - Ca phe dac san', 'Tra, ca phe va cac san pham lam qua tang dac san.', TRUE, '2026-01-01 08:13:00', '2026-01-01 08:13:00');


INSERT INTO `suppliers` (`id`, `supplier_code`, `name`, `contact_name`, `phone`, `email`, `address`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'SUP-TN01', 'HTX Che Tan Cuong Thai Nguyen', 'Nguyen Van Kien', '0911000001', 'tanCuong@dacsan.vn', 'Tan Cuong, Thai Nguyen', TRUE, '2026-01-01 08:20:00', '2026-01-01 08:20:00'),
(2, 'SUP-ST25', 'Co so Gao Dac San Soc Trang', 'Tran Quoc Minh', '0911000002', 'st25@dacsan.vn', 'Soc Trang', TRUE, '2026-01-01 08:21:00', '2026-01-01 08:21:00'),
(3, 'SUP-DL01', 'Nong San Da Lat Premium', 'Le Thu Tam', '0911000003', 'dalat@dacsan.vn', 'Da Lat, Lam Dong', TRUE, '2026-01-01 08:22:00', '2026-01-01 08:22:00'),
(4, 'SUP-TQ01', 'Dac San Mien Nui Tay Bac', 'Pham Thi Huong', '0911000004', 'taybac@dacsan.vn', 'Son La', TRUE, '2026-01-01 08:23:00', '2026-01-01 08:23:00');


INSERT INTO `inventories` (`id`, `name`, `location`, `created_at`, `updated_at`) VALUES
(1, 'Kho Ha Noi', 'KCN Bac Tu Liem, Ha Noi', '2026-01-01 08:30:00', '2026-01-01 08:30:00'),
(2, 'Kho TP HCM', 'Thu Duc, TP.HCM', '2026-01-01 08:31:00', '2026-01-01 08:31:00');


INSERT INTO `products` (`id`, `category_id`, `supplier_id`, `sku`, `name`, `description`, `sale_price`, `stock_quantity`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 'TRA-TC-200', 'Tra Tan Cuong Thai Nguyen 200g', 'Tra xanh Thai Nguyen huong com non, nuoc xanh, vi chat diu va hau ngot.', 229000.00, 30, TRUE, '2026-01-01 09:00:00', '2026-01-01 09:00:00'),
(2, 1, 2, 'GAO-ST25-5KG', 'Gao thom dac san ST25 5kg', 'Gao thom hat dai, com deo mem, phu hop bua an gia dinh.', 290000.00, 24, TRUE, '2026-01-01 09:05:00', '2026-01-01 09:05:00'),
(3, 4, 1, 'TRA-TC-PRE-500', 'Tra Tan Cuong thuong hang 500g', 'Tra Tan Cuong loai thuong hang, dong tui dep, phu hop lam qua bieu.', 459000.00, 18, TRUE, '2026-01-01 09:10:00', '2026-01-01 09:10:00'),
(4, 4, 3, 'CF-DL-500', 'Ca phe rang xay Da Lat 500g', 'Ca phe rang xay nguyen chat, mui thom dam, hau vi hai hoa.', 189000.00, 80, TRUE, '2026-01-01 09:15:00', '2026-01-01 09:15:00'),
(5, 2, 3, 'BANH-SAMOSA-10', 'Banh samosa truyen thong hop 10 cai', 'Banh chien nhan dam da, thich hop an nhe va dai khach.', 99000.00, 60, TRUE, '2026-01-01 09:20:00', '2026-01-01 09:20:00'),
(6, 3, 4, 'MAT-ONG-RUNG-500', 'Mat ong rung nguyen chat 500ml', 'Mat ong nguyen chat mau ho phach, vi ngot thanh, thich hop boi bo suc khoe.', 390000.00, 20, TRUE, '2026-01-01 09:25:00', '2026-01-01 09:25:00');


INSERT INTO `prices` (`id`, `product_id`, `supplier_id`, `cost_price`, `effective_from`, `effective_to`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 170000.00, '2026-01-01 09:30:00', NULL, TRUE, '2026-01-01 09:30:00', '2026-01-01 09:30:00'),
(2, 2, 2, 235000.00, '2026-01-01 09:31:00', NULL, TRUE, '2026-01-01 09:31:00', '2026-01-01 09:31:00'),
(3, 3, 1, 350000.00, '2026-01-01 09:32:00', NULL, TRUE, '2026-01-01 09:32:00', '2026-01-01 09:32:00'),
(4, 4, 3, 135000.00, '2026-01-01 09:33:00', NULL, TRUE, '2026-01-01 09:33:00', '2026-01-01 09:33:00'),
(5, 5, 3, 65000.00,  '2026-01-01 09:34:00', NULL, TRUE, '2026-01-01 09:34:00', '2026-01-01 09:34:00'),
(6, 6, 4, 300000.00, '2026-01-01 09:35:00', NULL, TRUE, '2026-01-01 09:35:00', '2026-01-01 09:35:00');


INSERT INTO `carts` (`id`, `user_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'ACTIVE', '2026-02-01 10:00:00', '2026-02-01 10:00:00'),
(2, 5, 'CHECKED_OUT', '2026-02-02 11:00:00', '2026-02-03 09:00:00');

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 229000.00, 229000.00, '2026-02-01 10:05:00', '2026-02-01 10:05:00'),
(2, 1, 6, 1, 390000.00, 390000.00, '2026-02-01 10:06:00', '2026-02-01 10:06:00'),
(3, 2, 4, 2, 189000.00, 378000.00, '2026-02-02 11:05:00', '2026-02-02 11:05:00');


INSERT INTO `orders` (`id`, `user_id`, `order_no`, `recipient_name`, `recipient_phone`, `shipping_address`, `payment_method`, `status`, `subtotal`, `shipping_fee`, `discount_amount`, `total_amount`, `note`, `created_at`, `updated_at`) VALUES
(1, 2, 'ORD-20260001', 'Tran Thi Customer', '0900000002', '101 Le Duan, Ha Noi', 'COD', 'DELIVERED', 619000.00, 30000.00, 0.00, 649000.00, 'Giao gio hanh chinh, dong goi can than.', '2026-02-05 08:00:00', '2026-02-07 17:00:00'),
(2, 5, 'ORD-20260002', 'Do Minh Khach', '0900000005', '22 Dien Bien Phu, TP.HCM', 'BANK_TRANSFER', 'PAID', 489000.00, 0.00, 30000.00, 459000.00, 'Khach da chuyen khoan, don hang lam qua tang.', '2026-02-10 09:00:00', '2026-02-10 09:30:00'),
(3, 2, 'ORD-20260003', 'Tran Thi Customer', '0900000002', '101 Le Duan, Ha Noi', 'E_WALLET', 'SHIPPED', 388000.00, 25000.00, 13000.00, 400000.00, 'Giao nhanh trong ngay neu kip tuyen.', '2026-02-12 14:00:00', '2026-02-13 08:00:00');

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name_snapshot`, `quantity`, `unit_price`, `line_total`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Tra Tan Cuong Thai Nguyen 200g', 1, 229000.00, 229000.00, '2026-02-05 08:05:00', '2026-02-05 08:05:00'),
(2, 1, 6, 'Mat ong rung nguyen chat 500ml', 1, 390000.00, 390000.00, '2026-02-05 08:06:00', '2026-02-05 08:06:00'),
(3, 2, 3, 'Tra Tan Cuong thuong hang 500g', 1, 459000.00, 459000.00, '2026-02-10 09:05:00', '2026-02-10 09:05:00'),
(4, 2, 5, 'Banh samosa truyen thong hop 10 cai', 1, 99000.00, 99000.00, '2026-02-10 09:06:00', '2026-02-10 09:06:00'),
(5, 3, 4, 'Ca phe rang xay Da Lat 500g', 1, 189000.00, 189000.00, '2026-02-12 14:05:00', '2026-02-12 14:05:00'),
(6, 3, 5, 'Banh samosa truyen thong hop 10 cai', 2, 99000.00, 198000.00, '2026-02-12 14:06:00', '2026-02-12 14:06:00');


INSERT INTO `order_status_history` (`id`, `order_id`, `changed_by_user_id`, `from_status`, `to_status`, `note`, `changed_at`) VALUES
(1, 1, 1, NULL, 'PENDING', 'Don hang moi tao.', '2026-02-05 08:00:00'),
(2, 1, 1, 'PENDING', 'CONFIRMED', 'Da xac nhan don.', '2026-02-05 08:10:00'),
(3, 1, 3, 'CONFIRMED', 'PACKED', 'Da dong goi can than.', '2026-02-05 10:00:00'),
(4, 1, 3, 'PACKED', 'SHIPPED', 'Ban giao don vi van chuyen.', '2026-02-06 08:00:00'),
(5, 1, 1, 'SHIPPED', 'DELIVERED', 'Giao thanh cong.', '2026-02-07 17:00:00'),
(6, 2, 1, NULL, 'PENDING', 'Don hang moi tao.', '2026-02-10 09:00:00'),
(7, 2, 1, 'PENDING', 'PAID', 'Da nhan chuyen khoan.', '2026-02-10 09:30:00'),
(8, 3, 1, NULL, 'PENDING', 'Don hang moi tao.', '2026-02-12 14:00:00'),
(9, 3, 1, 'PENDING', 'CONFIRMED', 'Da xac nhan don.', '2026-02-12 14:15:00'),
(10, 3, 3, 'CONFIRMED', 'PACKED', 'Da dong goi.', '2026-02-12 17:00:00'),
(11, 3, 3, 'PACKED', 'SHIPPED', 'Da giao cho doi tac van chuyen.', '2026-02-13 08:00:00');


INSERT INTO `payments` (`id`, `order_id`, `transaction_code`, `payment_method`, `payment_status`, `amount`, `gateway_name`, `gateway_reference`, `paid_at`, `raw_payload`, `created_at`, `updated_at`) VALUES
(1, 1, 'TXN-COD-20260001', 'COD', 'SUCCESS', 649000.00, NULL, NULL, '2026-02-07 17:00:00', JSON_OBJECT('collected_by', 'shipper', 'note', 'cash on delivery'), '2026-02-05 08:00:00', '2026-02-07 17:00:00'),
(2, 2, 'TXN-BANK-20260002', 'BANK_TRANSFER', 'SUCCESS', 459000.00, 'VCB', 'VCB-REF-20260002', '2026-02-10 09:25:00', JSON_OBJECT('bank', 'VCB', 'confirmed', TRUE), '2026-02-10 09:00:00', '2026-02-10 09:25:00'),
(3, 3, 'TXN-EWALLET-20260003', 'E_WALLET', 'PENDING', 400000.00, 'MoMo', 'MOMO-REF-20260003', NULL, JSON_OBJECT('gateway', 'MoMo', 'status', 'pending'), '2026-02-12 14:00:00', '2026-02-12 14:00:00');


INSERT INTO `complaints` (`id`, `order_id`, `user_id`, `product_id`, `reason`, `content`, `image_url`, `status`, `resolution_note`, `resolved_by_user_id`, `resolved_at`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 6, 'Vo chai bi ro nhe', 'Khach phan anh nap chai mat ong co dau hieu ro nhe khi nhan hang.', 'https://example.com/images/complaint-1.jpg', 'RESOLVED', 'Da doi san pham moi cho khach.', 1, '2026-02-09 10:00:00', '2026-02-08 09:00:00', '2026-02-09 10:00:00'),
(2, 3, 2, 4, 'Giao hang cham', 'Khach yeu cau kiem tra tinh trang don vi van chuyen.', NULL, 'IN_REVIEW', NULL, NULL, NULL, '2026-02-13 09:30:00', '2026-02-13 09:30:00');


INSERT INTO `reviews` (`id`, `order_item_id`, `user_id`, `product_id`, `rating`, `comment`, `is_visible`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, 5, 'Tra thom, nuoc xanh, dong goi dep, rat hop mua lam qua.', TRUE, '2026-02-08 20:00:00', '2026-02-08 20:00:00'),
(2, 3, 5, 3, 5, 'Tra chat luong tot, vi dam va hau ngot de chiu.', TRUE, '2026-02-11 18:00:00', '2026-02-11 18:00:00'),
(3, 5, 2, 4, 4, 'Ca phe thom, de pha, gia hop ly.', TRUE, '2026-02-13 19:00:00', '2026-02-13 19:00:00');


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
(1, 2, 'Don hang da giao', 'Don hang ORD-20260001 da duoc giao thanh cong.', 'SYSTEM', 'READ', '2026-02-07 17:05:00', '2026-02-07 17:20:00', '2026-02-07 17:05:00'),
(2, 5, 'Xac nhan thanh toan', 'He thong da ghi nhan thanh toan cho don ORD-20260002.', 'EMAIL', 'SENT', '2026-02-10 09:35:00', NULL, '2026-02-10 09:30:00'),
(3, 3, 'Yeu cau nhap hang moi', 'Co yeu cau nhap them ca phe, mat ong va banh samosa.', 'SYSTEM', 'PENDING', NULL, NULL, '2026-02-14 11:00:00');

INSERT INTO `delivery_requests` (`id`, `requested_by_user_id`, `product_id`, `requested_qty`, `reason`, `status`, `approved_by_user_id`, `created_at`, `updated_at`) VALUES
(1, 3, 6, 10, 'Ton kho mat ong tai kho HN xuong duoi nguong canh bao.', 'APPROVED', 1, '2026-02-14 10:30:00', '2026-02-14 11:00:00'),
(2, 3, 5, 30, 'Can bo sung ton kho banh samosa cho chuong trinh khuyen mai.', 'PENDING', NULL, '2026-02-14 10:35:00', '2026-02-14 10:35:00'),
(3, 3, 3, 10, 'Tra thuong hang can nhap bo sung trong tuan toi.', 'FULFILLED', 1, '2026-02-01 08:00:00', '2026-02-05 16:00:00');


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
ALTER TABLE `complaints` AUTO_INCREMENT = 3;
ALTER TABLE `reviews` AUTO_INCREMENT = 4;
ALTER TABLE `inventory_items` AUTO_INCREMENT = 13;
ALTER TABLE `supply_orders` AUTO_INCREMENT = 3;
ALTER TABLE `supply_order_items` AUTO_INCREMENT = 7;
ALTER TABLE `notifications` AUTO_INCREMENT = 4;
ALTER TABLE `delivery_requests` AUTO_INCREMENT = 4;
ALTER TABLE `cart_items` AUTO_INCREMENT = 4;



-- PHAN THEM: DU LIEU MAU BO SUNG CHO CAC BANG MOI


INSERT IGNORE INTO `wishlist_items` (`id`, `user_id`, `product_id`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '2026-02-14 09:00:00', '2026-02-14 09:00:00'),
(2, 2, 6, '2026-02-14 09:05:00', '2026-02-14 09:05:00'),
(3, 5, 3, '2026-02-14 09:10:00', '2026-02-14 09:10:00');

INSERT IGNORE INTO `admin_settings` (
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

ALTER TABLE `wishlist_items` AUTO_INCREMENT = 4;
ALTER TABLE `admin_settings` AUTO_INCREMENT = 2;

