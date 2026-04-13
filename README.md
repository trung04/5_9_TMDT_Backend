# 5_9_TMDT Backend

Backend Laravel cho dự án thương mại điện tử nông sản.

## 1. Yêu cầu môi trường

- PHP >= 8.2
- Composer
- Node.js >= 18 và npm
- MySQL 8+

## 2. Cài đặt dự án

Mở terminal trong thư mục gốc dự án, sau đó chạy:

```bash
composer install
npm install
```

## 3. Cấu hình biến môi trường

Tạo file `.env` từ file mẫu:

```bash
copy .env.example .env
```

Sau đó cập nhật cấu hình database trong `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tmdt
DB_USERNAME=root
DB_PASSWORD=your_password
```

Tiếp theo tạo application key:

```bash
php artisan key:generate
```

## 4. Tạo database MySQL

Tạo database mới trong MySQL, ví dụ:

```sql
CREATE DATABASE tmdt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 5. Import schema và dữ liệu mẫu

Dự án đã có sẵn 2 file SQL:

- `ecommerce_schema_mysql.sql`: cấu trúc database
- `ecommerce_seed_data.sql`: dữ liệu mẫu

Chạy lần lượt các lệnh sau:

```bash
mysql -u root -p tmdt < ecommerce_schema_mysql.sql
mysql -u root -p tmdt < ecommerce_seed_data.sql
```

Nếu bạn dùng user hoặc tên database khác, thay lại trong câu lệnh cho đúng.

## 6. Chạy dự án

### Chạy Laravel server

```bash
php artisan serve
```

Truy cập: http://127.0.0.1:8000

### Chạy Vite ở môi trường phát triển

```bash
npm run dev
```

### Hoặc chạy đồng thời các dịch vụ của dự án

```bash
composer run dev
```

Lệnh trên sẽ chạy đồng thời:

- Laravel development server
- Queue listener
- Laravel logs
- Vite development server

## 7. Chạy test

```bash
composer test
```

Hoặc:

```bash
php artisan test
```