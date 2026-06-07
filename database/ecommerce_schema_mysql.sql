-- MySQL schema snapshot for the TMDT backend.
-- Compatible with MySQL 8.0+ and the current Laravel models/migrations.

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `ecommerce_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `ecommerce_db`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `address` VARCHAR(255) NULL,
  `city` VARCHAR(120) NULL,
  `favorite_region` VARCHAR(120) NULL,
  `avatar_url` TEXT NULL,
  `newsletter` BOOLEAN NOT NULL DEFAULT FALSE,
  `sms_alerts` BOOLEAN NOT NULL DEFAULT FALSE,
  `order_email` BOOLEAN NOT NULL DEFAULT TRUE,
  `security_alerts` BOOLEAN NOT NULL DEFAULT TRUE,
  `reward_points` INT UNSIGNED NOT NULL DEFAULT 0,
  `reward_tier` VARCHAR(50) NOT NULL DEFAULT 'Bronze',
  `next_tier_points` INT UNSIGNED NOT NULL DEFAULT 500,
  `role` ENUM('CUSTOMER','ADMIN','WAREHOUSE_STAFF','SUPPLIER') NOT NULL,
  `admin_role_id` BIGINT UNSIGNED NULL,
  `created_by_admin_id` BIGINT UNSIGNED NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `is_deleted` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`),
  UNIQUE KEY `uk_users_phone` (`phone`),
  KEY `idx_users_role_active_deleted` (`role`, `is_active`, `is_deleted`),
  KEY `idx_users_admin_role_id` (`admin_role_id`),
  KEY `idx_users_created_by_admin_id` (`created_by_admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` VARCHAR(255) NOT NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `payload` LONGTEXT NOT NULL,
  `last_activity` INT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sessions_user_id` (`user_id`),
  KEY `idx_sessions_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_roles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `description` TEXT NULL,
  `is_super` BOOLEAN NOT NULL DEFAULT FALSE,
  `is_system` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_by_admin_id` BIGINT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_admin_roles_slug` (`slug`),
  KEY `idx_admin_roles_created_by_admin_id` (`created_by_admin_id`),
  CONSTRAINT `fk_admin_roles_created_by_admin`
    FOREIGN KEY (`created_by_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_permissions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` VARCHAR(120) NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `group` VARCHAR(80) NOT NULL,
  `description` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_admin_permissions_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_role_permission` (
  `admin_role_id` BIGINT UNSIGNED NOT NULL,
  `admin_permission_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`admin_role_id`, `admin_permission_id`),
  KEY `idx_admin_role_permission_permission_id` (`admin_permission_id`),
  CONSTRAINT `fk_admin_role_permission_role`
    FOREIGN KEY (`admin_role_id`) REFERENCES `admin_roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_admin_role_permission_permission`
    FOREIGN KEY (`admin_permission_id`) REFERENCES `admin_permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_admin_role`
    FOREIGN KEY (`admin_role_id`) REFERENCES `admin_roles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_users_created_by_admin`
    FOREIGN KEY (`created_by_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

CREATE TABLE IF NOT EXISTS `cache` (
  `key` VARCHAR(255) NOT NULL,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`),
  KEY `idx_cache_expiration` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` VARCHAR(255) NOT NULL,
  `owner` VARCHAR(255) NOT NULL,
  `expiration` INT NOT NULL,
  PRIMARY KEY (`key`),
  KEY `idx_cache_locks_expiration` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` VARCHAR(255) NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `attempts` TINYINT UNSIGNED NOT NULL,
  `reserved_at` INT UNSIGNED NULL,
  `available_at` INT UNSIGNED NOT NULL,
  `created_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_jobs_queue` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` VARCHAR(255) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `total_jobs` INT NOT NULL,
  `pending_jobs` INT NOT NULL,
  `failed_jobs` INT NOT NULL,
  `failed_job_ids` LONGTEXT NOT NULL,
  `options` MEDIUMTEXT NULL,
  `cancelled_at` INT NULL,
  `created_at` INT NOT NULL,
  `finished_at` INT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(255) NOT NULL,
  `connection` TEXT NOT NULL,
  `queue` TEXT NOT NULL,
  `payload` LONGTEXT NOT NULL,
  `exception` LONGTEXT NOT NULL,
  `failed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_failed_jobs_uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` VARCHAR(255) NOT NULL,
  `tokenable_id` BIGINT UNSIGNED NOT NULL,
  `name` TEXT NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `abilities` TEXT NULL,
  `last_used_at` TIMESTAMP NULL,
  `expires_at` TIMESTAMP NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_personal_access_tokens_token` (`token`),
  KEY `idx_personal_access_tokens_tokenable` (`tokenable_type`, `tokenable_id`),
  KEY `idx_personal_access_tokens_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_addresses` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `label` VARCHAR(120) NOT NULL,
  `recipient` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `line1` VARCHAR(255) NOT NULL,
  `city` VARCHAR(120) NOT NULL,
  `ghn_province_id` INT UNSIGNED NULL,
  `ghn_province_name` VARCHAR(120) NULL,
  `ghn_district_id` INT UNSIGNED NULL,
  `ghn_district_name` VARCHAR(120) NULL,
  `ghn_ward_code` VARCHAR(30) NULL,
  `ghn_ward_name` VARCHAR(120) NULL,
  `note` TEXT NULL,
  `is_default` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_addresses_user_id` (`user_id`),
  CONSTRAINT `fk_user_addresses_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reward_redemptions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `points_used` INT UNSIGNED NOT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'COMPLETED',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reward_redemptions_user_id` (`user_id`),
  CONSTRAINT `fk_reward_redemptions_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `description` TEXT NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `is_deleted` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_categories_name` (`name`),
  KEY `idx_categories_active_deleted` (`is_active`, `is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `supplier_code` VARCHAR(50) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `contact_name` VARCHAR(120) NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(120) NULL,
  `address` VARCHAR(255) NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `is_deleted` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_suppliers_supplier_code` (`supplier_code`),
  UNIQUE KEY `uk_suppliers_phone` (`phone`),
  UNIQUE KEY `uk_suppliers_email` (`email`),
  KEY `idx_suppliers_active_deleted` (`is_active`, `is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `regions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(120) NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `description` TEXT NULL,
  `image_url` TEXT NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_regions_slug` (`slug`),
  UNIQUE KEY `uk_regions_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `products` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `supplier_id` BIGINT UNSIGNED NULL,
  `region_id` BIGINT UNSIGNED NULL,
  `sku` VARCHAR(80) NOT NULL,
  `slug` VARCHAR(180) NULL,
  `name` VARCHAR(180) NOT NULL,
  `description` TEXT NULL,
  `image_url` TEXT NULL,
  `gallery` TEXT NULL,
  `sale_price` DECIMAL(15,2) NOT NULL,
  `stock_quantity` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `is_deleted` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_products_sku` (`sku`),
  UNIQUE KEY `uk_products_slug` (`slug`),
  KEY `idx_products_category_active_deleted` (`category_id`, `is_active`, `is_deleted`),
  KEY `idx_products_supplier_active_deleted` (`supplier_id`, `is_active`, `is_deleted`),
  KEY `idx_products_region_id` (`region_id`),
  CONSTRAINT `chk_products_sale_price_nonnegative` CHECK (`sale_price` >= 0),
  CONSTRAINT `fk_products_category`
    FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_products_supplier`
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_products_region`
    FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prices` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `supplier_id` BIGINT UNSIGNED NOT NULL,
  `cost_price` DECIMAL(15,2) NOT NULL,
  `effective_from` DATETIME NOT NULL,
  `effective_to` DATETIME NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_prices_product_id` (`product_id`),
  KEY `idx_prices_supplier_id` (`supplier_id`),
  CONSTRAINT `chk_prices_cost_price_nonnegative` CHECK (`cost_price` >= 0),
  CONSTRAINT `fk_prices_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_prices_supplier`
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_images` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `image_url` TEXT NOT NULL,
  `path` VARCHAR(2048) NOT NULL,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `is_primary` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_images_product_order` (`product_id`, `sort_order`),
  KEY `idx_product_images_product_primary` (`product_id`, `is_primary`),
  CONSTRAINT `fk_product_images_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `carts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'ACTIVE',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_carts_user_status` (`user_id`, `status`),
  CONSTRAINT `fk_carts_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `orders` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NULL,
  `order_no` VARCHAR(30) NULL,
  `recipient_name` VARCHAR(120) NOT NULL,
  `recipient_phone` VARCHAR(20) NOT NULL,
  `shipping_address` VARCHAR(255) NOT NULL,
  `shipping_line1` VARCHAR(255) NULL,
  `shipping_province_id` INT UNSIGNED NULL,
  `shipping_province_name` VARCHAR(120) NULL,
  `shipping_district_id` INT UNSIGNED NULL,
  `shipping_district_name` VARCHAR(120) NULL,
  `shipping_ward_code` VARCHAR(30) NULL,
  `shipping_ward_name` VARCHAR(120) NULL,
  `payment_method` VARCHAR(30) NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'PENDING',
  `subtotal` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `shipping_fee` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `discount_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `stock_deducted` BOOLEAN NOT NULL DEFAULT FALSE,
  `stock_deducted_at` DATETIME NULL,
  `shipping_carrier` VARCHAR(80) NULL,
  `shipping_code` VARCHAR(80) NULL,
  `shipped_at` DATETIME NULL,
  `delivered_at` DATETIME NULL,
  `cancelled_at` DATETIME NULL,
  `note` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_orders_order_no` (`order_no`),
  KEY `idx_orders_user_status` (`user_id`, `status`),
  KEY `idx_orders_status_created_at` (`status`, `created_at`),
  KEY `idx_orders_created_at` (`created_at`),
  KEY `idx_orders_delivered_at` (`delivered_at`),
  CONSTRAINT `chk_orders_subtotal_nonnegative` CHECK (`subtotal` >= 0),
  CONSTRAINT `chk_orders_shipping_fee_nonnegative` CHECK (`shipping_fee` >= 0),
  CONSTRAINT `chk_orders_discount_amount_nonnegative` CHECK (`discount_amount` >= 0),
  CONSTRAINT `chk_orders_total_amount_nonnegative` CHECK (`total_amount` >= 0),
  CONSTRAINT `fk_orders_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shipping_carriers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(40) NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `provider` VARCHAR(40) NOT NULL DEFAULT 'MANUAL',
  `tracking_url_template` VARCHAR(255) NULL,
  `default_weight` INT UNSIGNED NOT NULL DEFAULT 1000,
  `default_length` INT UNSIGNED NOT NULL DEFAULT 20,
  `default_width` INT UNSIGNED NOT NULL DEFAULT 20,
  `default_height` INT UNSIGNED NOT NULL DEFAULT 10,
  `default_service_type_id` TINYINT UNSIGNED NOT NULL DEFAULT 2,
  `default_payment_type_id` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `default_required_note` VARCHAR(40) NOT NULL DEFAULT 'KHONGCHOXEMHANG',
  `pickup_name` VARCHAR(120) NULL,
  `pickup_phone` VARCHAR(20) NULL,
  `pickup_address` VARCHAR(255) NULL,
  `pickup_ward_code` VARCHAR(30) NULL,
  `pickup_ward_name` VARCHAR(120) NULL,
  `pickup_district_id` INT UNSIGNED NULL,
  `pickup_district_name` VARCHAR(120) NULL,
  `pickup_province_id` INT UNSIGNED NULL,
  `pickup_province_name` VARCHAR(120) NULL,
  `settings` JSON NULL,
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  `is_deleted` BOOLEAN NOT NULL DEFAULT FALSE,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_shipping_carriers_code` (`code`),
  KEY `idx_shipping_carriers_provider_active_deleted` (`provider`, `is_active`, `is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_shipments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `shipping_carrier_id` BIGINT UNSIGNED NOT NULL,
  `provider` VARCHAR(40) NOT NULL,
  `status` VARCHAR(60) NOT NULL DEFAULT 'created',
  `tracking_code` VARCHAR(120) NULL,
  `tracking_url` VARCHAR(255) NULL,
  `service_type_id` TINYINT UNSIGNED NULL,
  `payment_type_id` TINYINT UNSIGNED NULL,
  `required_note` VARCHAR(40) NULL,
  `weight` INT UNSIGNED NULL,
  `length` INT UNSIGNED NULL,
  `width` INT UNSIGNED NULL,
  `height` INT UNSIGNED NULL,
  `shipping_fee` DECIMAL(15,2) NULL,
  `cod_amount` DECIMAL(15,2) NULL,
  `expected_delivery_time` DATETIME NULL,
  `raw_request` JSON NULL,
  `raw_response` JSON NULL,
  `synced_at` DATETIME NULL,
  `cancelled_at` DATETIME NULL,
  `created_by_user_id` BIGINT UNSIGNED NULL,
  `updated_by_user_id` BIGINT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_order_shipments_order_id` (`order_id`),
  KEY `idx_order_shipments_carrier_id` (`shipping_carrier_id`),
  KEY `idx_order_shipments_provider_status` (`provider`, `status`),
  KEY `idx_order_shipments_tracking_code` (`tracking_code`),
  KEY `idx_order_shipments_created_by_user_id` (`created_by_user_id`),
  KEY `idx_order_shipments_updated_by_user_id` (`updated_by_user_id`),
  CONSTRAINT `fk_order_shipments_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_shipments_carrier`
    FOREIGN KEY (`shipping_carrier_id`) REFERENCES `shipping_carriers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_order_shipments_created_by_user`
    FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_order_shipments_updated_by_user`
    FOREIGN KEY (`updated_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inventories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `location` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_inventories_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `channel` VARCHAR(50) NOT NULL DEFAULT 'SYSTEM',
  `status` VARCHAR(50) NOT NULL DEFAULT 'PENDING',
  `sent_at` DATETIME NULL,
  `read_at` DATETIME NULL,
  `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user_id` (`user_id`),
  CONSTRAINT `fk_notifications_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `cart_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `quantity` INT UNSIGNED NOT NULL,
  `unit_price` DECIMAL(15,2) NOT NULL,
  `line_total` DECIMAL(15,2) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_cart_items_cart_product` (`cart_id`, `product_id`),
  KEY `idx_cart_items_product_id` (`product_id`),
  CONSTRAINT `chk_cart_items_quantity_positive` CHECK (`quantity` > 0),
  CONSTRAINT `chk_cart_items_unit_price_nonnegative` CHECK (`unit_price` >= 0),
  CONSTRAINT `chk_cart_items_line_total_nonnegative` CHECK (`line_total` >= 0),
  CONSTRAINT `fk_cart_items_cart`
    FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_items_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `product_name_snapshot` VARCHAR(180) NOT NULL,
  `quantity` INT UNSIGNED NOT NULL,
  `unit_price` DECIMAL(15,2) NOT NULL,
  `line_total` DECIMAL(15,2) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order_id` (`order_id`),
  KEY `idx_order_items_product_id` (`product_id`),
  CONSTRAINT `chk_order_items_quantity_positive` CHECK (`quantity` > 0),
  CONSTRAINT `chk_order_items_unit_price_nonnegative` CHECK (`unit_price` >= 0),
  CONSTRAINT `chk_order_items_line_total_nonnegative` CHECK (`line_total` >= 0),
  CONSTRAINT `fk_order_items_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_status_history` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `changed_by_user_id` BIGINT UNSIGNED NULL,
  `from_status` VARCHAR(30) NULL,
  `to_status` VARCHAR(30) NOT NULL,
  `note` TEXT NULL,
  `changed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order_status_history_order_changed` (`order_id`, `changed_at`),
  KEY `idx_order_status_history_changed_by_user_id` (`changed_by_user_id`),
  CONSTRAINT `fk_order_status_history_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_status_history_changed_by_user`
    FOREIGN KEY (`changed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `transaction_code` VARCHAR(80) NULL,
  `payment_method` VARCHAR(30) NOT NULL,
  `payment_status` VARCHAR(30) NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `gateway_name` VARCHAR(60) NULL,
  `gateway_reference` VARCHAR(120) NULL,
  `paid_at` DATETIME NULL,
  `raw_payload` JSON NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_payments_order_id` (`order_id`),
  UNIQUE KEY `uk_payments_transaction_code` (`transaction_code`),
  KEY `idx_payments_payment_status` (`payment_status`),
  CONSTRAINT `chk_payments_amount_nonnegative` CHECK (`amount` >= 0),
  CONSTRAINT `fk_payments_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payment_status_history` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_id` BIGINT UNSIGNED NOT NULL,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `changed_by_user_id` BIGINT UNSIGNED NULL,
  `from_status` VARCHAR(30) NULL,
  `to_status` VARCHAR(30) NOT NULL,
  `note` VARCHAR(255) NULL,
  `changed_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payment_status_history_payment_changed` (`payment_id`, `changed_at`),
  KEY `idx_payment_status_history_order_changed` (`order_id`, `changed_at`),
  KEY `idx_payment_status_history_changed_by_user_id` (`changed_by_user_id`),
  CONSTRAINT `fk_payment_status_history_payment`
    FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payment_status_history_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payment_status_history_changed_by_user`
    FOREIGN KEY (`changed_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `complaints` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `reason` TEXT NOT NULL,
  `content` TEXT NOT NULL,
  `image_url` TEXT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'OPEN',
  `resolution_note` TEXT NULL,
  `resolved_by_user_id` BIGINT UNSIGNED NULL,
  `resolved_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_complaints_order_id` (`order_id`),
  KEY `idx_complaints_user_id` (`user_id`),
  KEY `idx_complaints_product_id` (`product_id`),
  KEY `idx_complaints_resolved_by_user_id` (`resolved_by_user_id`),
  CONSTRAINT `fk_complaints_order`
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_complaints_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_complaints_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_complaints_resolved_by_user`
    FOREIGN KEY (`resolved_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reviews` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_item_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL,
  `comment` TEXT NULL,
  `is_visible` BOOLEAN NOT NULL DEFAULT TRUE,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_reviews_order_item_id` (`order_item_id`),
  KEY `idx_reviews_user_id` (`user_id`),
  KEY `idx_reviews_product_id` (`product_id`),
  CONSTRAINT `chk_reviews_rating_range` CHECK (`rating` BETWEEN 1 AND 5),
  CONSTRAINT `fk_reviews_order_item`
    FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reviews_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `inventory_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `inventory_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `quantity_on_hand` INT UNSIGNED NOT NULL DEFAULT 0,
  `reorder_level` INT UNSIGNED NOT NULL DEFAULT 0,
  `safety_stock` INT UNSIGNED NOT NULL DEFAULT 0,
  `last_counted_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inventory_items_inventory_id` (`inventory_id`),
  KEY `idx_inventory_items_product_id` (`product_id`),
  CONSTRAINT `fk_inventory_items_inventory`
    FOREIGN KEY (`inventory_id`) REFERENCES `inventories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_inventory_items_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `supply_orders` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `supplier_id` BIGINT UNSIGNED NOT NULL,
  `order_no` VARCHAR(50) NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'PENDING',
  `expected_date` DATE NULL,
  `received_date` DATE NULL,
  `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `created_by_user_id` BIGINT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_supply_orders_order_no` (`order_no`),
  KEY `idx_supply_orders_supplier_id` (`supplier_id`),
  KEY `idx_supply_orders_created_by_user_id` (`created_by_user_id`),
  CONSTRAINT `chk_supply_orders_total_amount_nonnegative` CHECK (`total_amount` >= 0),
  CONSTRAINT `fk_supply_orders_supplier`
    FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_supply_orders_created_by_user`
    FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `supply_order_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `supply_order_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `quantity` INT UNSIGNED NOT NULL,
  `unit_cost` DECIMAL(15,2) NOT NULL,
  `line_total` DECIMAL(15,2) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_supply_order_items_supply_order_id` (`supply_order_id`),
  KEY `idx_supply_order_items_product_id` (`product_id`),
  CONSTRAINT `chk_supply_order_items_quantity_positive` CHECK (`quantity` > 0),
  CONSTRAINT `chk_supply_order_items_unit_cost_nonnegative` CHECK (`unit_cost` >= 0),
  CONSTRAINT `chk_supply_order_items_line_total_nonnegative` CHECK (`line_total` >= 0),
  CONSTRAINT `fk_supply_order_items_supply_order`
    FOREIGN KEY (`supply_order_id`) REFERENCES `supply_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_supply_order_items_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `delivery_requests` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `requested_by_user_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `requested_qty` INT UNSIGNED NOT NULL,
  `reason` VARCHAR(255) NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'PENDING',
  `approved_by_user_id` BIGINT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_delivery_requests_requested_by_user_id` (`requested_by_user_id`),
  KEY `idx_delivery_requests_product_id` (`product_id`),
  KEY `idx_delivery_requests_approved_by_user_id` (`approved_by_user_id`),
  CONSTRAINT `chk_delivery_requests_requested_qty_positive` CHECK (`requested_qty` > 0),
  CONSTRAINT `fk_delivery_requests_requested_by_user`
    FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_delivery_requests_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_delivery_requests_approved_by_user`
    FOREIGN KEY (`approved_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wishlist_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_wishlist_items_user_product` (`user_id`, `product_id`),
  KEY `idx_wishlist_items_product_id` (`product_id`),
  CONSTRAINT `fk_wishlist_items_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wishlist_items_product`
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_settings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `store_name` VARCHAR(150) NOT NULL DEFAULT 'Heritage Harvest',
  `support_email` VARCHAR(120) NULL,
  `support_phone` VARCHAR(20) NULL,
  `low_stock_threshold` INT UNSIGNED NOT NULL DEFAULT 5,
  `dashboard_refresh_seconds` INT UNSIGNED NOT NULL DEFAULT 60,
  `order_auto_confirm` BOOLEAN NOT NULL DEFAULT FALSE,
  `send_daily_summary` BOOLEAN NOT NULL DEFAULT TRUE,
  `maintenance_mode` BOOLEAN NOT NULL DEFAULT FALSE,
  `notes` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_admin_settings_user_id` (`user_id`),
  CONSTRAINT `fk_admin_settings_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `supplier_invitations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `supplier_name` VARCHAR(255) NOT NULL,
  `contact_name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `categories` JSON NULL,
  `note` TEXT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'DRAFT',
  `created_by_user_id` BIGINT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_supplier_invitations_created_by_user_id` (`created_by_user_id`),
  CONSTRAINT `fk_supplier_invitations_created_by_user`
    FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `posts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_by_user_id` BIGINT UNSIGNED NULL,
  `title` VARCHAR(180) NOT NULL,
  `excerpt` TEXT NULL,
  `body` LONGTEXT NOT NULL,
  `cover_image_url` TEXT NULL,
  `status` VARCHAR(40) NOT NULL DEFAULT 'DRAFT',
  `published_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_posts_status_published_at` (`status`, `published_at`),
  KEY `idx_posts_created_by_status` (`created_by_user_id`, `status`),
  CONSTRAINT `fk_posts_created_by_user`
    FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `post_comments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `content` TEXT NOT NULL,
  `status` VARCHAR(40) NOT NULL DEFAULT 'VISIBLE',
  `hidden_by_user_id` BIGINT UNSIGNED NULL,
  `hidden_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_post_comments_post_status_created` (`post_id`, `status`, `created_at`),
  KEY `idx_post_comments_user_created` (`user_id`, `created_at`),
  KEY `idx_post_comments_hidden_by_user_id` (`hidden_by_user_id`),
  CONSTRAINT `fk_post_comments_post`
    FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_post_comments_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_post_comments_hidden_by_user`
    FOREIGN KEY (`hidden_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `post_likes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_post_likes_post_user` (`post_id`, `user_id`),
  KEY `idx_post_likes_user_created` (`user_id`, `created_at`),
  CONSTRAINT `fk_post_likes_post`
    FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_post_likes_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `newsletter_subscriptions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(160) NOT NULL,
  `source` VARCHAR(80) NOT NULL DEFAULT 'storefront',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_newsletter_subscriptions_email_source` (`email`, `source`),
  KEY `idx_newsletter_subscriptions_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `support_tickets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NULL,
  `subject` VARCHAR(180) NOT NULL,
  `message` TEXT NOT NULL,
  `channel` VARCHAR(30) NOT NULL,
  `status` VARCHAR(30) NOT NULL DEFAULT 'OPEN',
  `resolved_by_user_id` BIGINT UNSIGNED NULL,
  `resolved_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_support_tickets_channel_status` (`channel`, `status`),
  KEY `idx_support_tickets_user_id` (`user_id`),
  KEY `idx_support_tickets_resolved_by_user_id` (`resolved_by_user_id`),
  CONSTRAINT `fk_support_tickets_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_support_tickets_resolved_by_user`
    FOREIGN KEY (`resolved_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
