# 5_9_TMDT Backend

Backend Laravel cho dự án thương mại điện tử nông sản.

## 1. Yêu cầu môi trường

- PHP >= 8.2
- Composer
- Node.js >= 18 và npm
- MySQL 8+ (nếu chạy theo MySQL)

## 2. Cài đặt dự án

Mở terminal trong thư mục gốc dự án, sau đó chạy:

```bash
composer install
npm install
```

## 3. Cấu hình biến môi trường

```bash
copy .env.example .env
php artisan key:generate
```

## 4. Chạy thử nhanh (khuyến nghị) với SQLite

Mặc định `.env.example` đã để `DB_CONNECTION=sqlite`.

### Bước 1: Tạo file SQLite

```bash
if (!(Test-Path .\database\database.sqlite)) { New-Item .\database\database.sqlite -ItemType File }
```

### Bước 2: Migrate

```bash
php artisan migrate
```

### Bước 3: Chạy server

```bash
php artisan serve
```

Truy cập: http://127.0.0.1:8000

## 5. Chạy với MySQL + dữ liệu mẫu từ file SQL

Nếu muốn dùng schema/dữ liệu mẫu có sẵn trong dự án (`ecommerce_schema_mysql.sql`, `ecommerce_seed_data.sql`):

### Bước 1: Tạo database (ví dụ)

```sql
CREATE DATABASE tmdt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Bước 2: Cập nhật `.env`

Sửa các biến sau trong file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tmdt
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Bước 3: Import schema và seed data

```bash
mysql -u root -p tmdt < ecommerce_schema_mysql.sql
mysql -u root -p tmdt < ecommerce_seed_data.sql
```

### Bước 4: Chạy server

```bash
php artisan serve
```

## 6. Chạy frontend assets (nếu cần)

Chế độ development (Vite watch):

```bash
npm run dev
```

Build production assets:

```bash
npm run build
```

## 7. Chạy tất cả dịch vụ bằng 1 lệnh

Dự án có script Composer `dev` để chạy đồng thời server, queue, logs và Vite:

```bash
composer run dev
```

## 8. Chạy test

```bash
composer test
```

Hoặc:

```bash
php artisan test
```