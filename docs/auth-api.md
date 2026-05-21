# Auth API Docs

Tài liệu này mô tả bộ API xác thực hiện tại của dự án.

Base URL mặc định:

```text
http://127.0.0.1:8000/api
```

## Tổng quan

- Cơ chế xác thực: Laravel Sanctum Bearer Token
- Định danh đăng nhập: `email`
- Đăng ký tự tạo tài khoản `CUSTOMER`
- `register` sẽ tạo user và trả token ngay
- `login` sẽ tạo token mới mỗi lần đăng nhập
- `logout` chỉ thu hồi token đang dùng cho request hiện tại

## User payload

Mọi response trả về `user` đều có dạng:

```json
{
  "id": 1,
  "full_name": "Nguyen Van A",
  "email": "user@example.com",
  "phone": "0912345678",
  "role": "CUSTOMER",
  "status": "ACTIVE",
  "is_active": true,
  "created_at": "2026-04-18T09:15:49.000000Z",
  "updated_at": "2026-04-18T09:15:49.000000Z"
}
```

## 1. Register

Tạo tài khoản mới và trả access token.

- Method: `POST`
- URL: `/register`
- Auth: không cần

Request body:

```json
{
  "full_name": "Nguyen Van Test",
  "email": "testapi1@example.com",
  "phone": "0912345678",
  "password": "password123",
  "password_confirmation": "password123"
}
```

Quy tắc validate:

- `full_name`: bắt buộc, chuỗi, tối đa 120 ký tự
- `email`: bắt buộc, đúng định dạng email, tối đa 120 ký tự, unique
- `phone`: bắt buộc, chuỗi, tối đa 20 ký tự, unique
- `password`: bắt buộc, tối thiểu 8 ký tự, phải có `password_confirmation`

Response thành công: `201 Created`

```json
{
  "message": "User registered successfully.",
  "access_token": "1|plain-text-token",
  "token_type": "Bearer",
  "user": {
    "id": 10,
    "full_name": "Nguyen Van Test",
    "email": "testapi1@example.com",
    "phone": "0912345678",
    "role": "CUSTOMER",
    "status": "ACTIVE",
    "is_active": true,
    "created_at": "2026-04-18T09:15:49.000000Z",
    "updated_at": "2026-04-18T09:15:49.000000Z"
  }
}
```

Response lỗi phổ biến:

- `422 Unprocessable Entity`: sai validate hoặc trùng `email` / `phone`

Ví dụ `curl`:

```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Nguyen Van Test",
    "email": "testapi1@example.com",
    "phone": "0912345678",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

## 2. Login

Đăng nhập bằng email và mật khẩu.

- Method: `POST`
- URL: `/login`
- Auth: không cần

Request body:

```json
{
  "email": "testapi1@example.com",
  "password": "password123"
}
```

Ghi chú:

- Email sẽ được trim và chuyển về lowercase trước khi validate / truy vấn
- Chỉ user có `status = ACTIVE` và `is_active = true` mới đăng nhập được

Response thành công: `200 OK`

```json
{
  "message": "Logged in successfully.",
  "access_token": "2|plain-text-token",
  "token_type": "Bearer",
  "user": {
    "id": 10,
    "full_name": "Nguyen Van Test",
    "email": "testapi1@example.com",
    "phone": "0912345678",
    "role": "CUSTOMER",
    "status": "ACTIVE",
    "is_active": true,
    "created_at": "2026-04-18T09:15:49.000000Z",
    "updated_at": "2026-04-18T09:15:49.000000Z"
  }
}
```

Response lỗi phổ biến:

- `401 Unauthorized`: sai `email` hoặc `password`
- `403 Forbidden`: tài khoản `INACTIVE`, `BLOCKED`, hoặc `is_active = false`
- `422 Unprocessable Entity`: thiếu field hoặc sai định dạng email

Ví dụ `curl`:

```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "testapi1@example.com",
    "password": "password123"
  }'
```

## 3. Me

Lấy thông tin user hiện tại theo access token.

- Method: `GET`
- URL: `/me`
- Auth: Bearer token

Header:

```http
Authorization: Bearer <access_token>
```

Response thành công: `200 OK`

```json
{
  "user": {
    "id": 10,
    "full_name": "Nguyen Van Test",
    "email": "testapi1@example.com",
    "phone": "0912345678",
    "role": "CUSTOMER",
    "status": "ACTIVE",
    "is_active": true,
    "created_at": "2026-04-18T09:15:49.000000Z",
    "updated_at": "2026-04-18T09:15:49.000000Z"
  }
}
```

Response lỗi:

- `401 Unauthorized`: thiếu token hoặc token không hợp lệ

Ví dụ `curl`:

```bash
curl http://127.0.0.1:8000/api/me \
  -H "Authorization: Bearer <access_token>"
```

## 4. Logout

Thu hồi token hiện tại.

- Method: `POST`
- URL: `/logout`
- Auth: Bearer token

Header:

```http
Authorization: Bearer <access_token>
```

Response thành công: `200 OK`

```json
{
  "message": "Logged out successfully."
}
```

Response lỗi:

- `401 Unauthorized`: thiếu token hoặc token không hợp lệ

Ví dụ `curl`:

```bash
curl -X POST http://127.0.0.1:8000/api/logout \
  -H "Authorization: Bearer <access_token>"
```

## Error format

### 401 sai thông tin đăng nhập

```json
{
  "message": "Invalid credentials."
}
```

### 403 tài khoản không được phép đăng nhập

```json
{
  "message": "Your account is not allowed to sign in."
}
```

### 422 lỗi validate

```json
{
  "message": "The email field is required. (and 1 more error)",
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password field is required."
    ]
  }
}
```

## Luồng test nhanh

1. Gọi `POST /register` để tạo tài khoản mới.
2. Lấy `access_token` từ response.
3. Gọi `GET /me` với Bearer token để kiểm tra token hoạt động.
4. Gọi `POST /logout` với chính token đó.
5. Gọi lại `GET /me` với token cũ, mong đợi `401`.

## Ghi chú dữ liệu seed

- Các user có sẵn trong `ecommerce_seed_data.sql` chủ yếu là dữ liệu demo.
- Không nên dùng các seed user đó để test đăng nhập nếu bạn chưa tự thay `password_hash` bằng hash thật.
- Cách đơn giản nhất để test là dùng `POST /register` tạo account mới rồi dùng chính account đó để `login`.

## Tài khoản SQL mẫu để test login / 403

Bạn có thể chèn nhanh các user mẫu sau:

```sql
INSERT INTO users
(full_name, email, phone, password_hash, role, status, is_active, created_at, updated_at)
VALUES
('Active User', 'active@example.com', '0901111111', '$2y$10$FhHMK0IzDB8ZxIYG5OkLG.vVrOHkZlhnAnL9xICDXh1/l.q.Egg0y', 'CUSTOMER', 'ACTIVE', 1, NOW(), NOW()),
('Blocked User', 'blocked@example.com', '0902222222', '$2y$10$V16cweZ4GbJErB4o5Ofpxe6l4XEcKLZObVq.c3HviLflPzjXHIdYC', 'CUSTOMER', 'BLOCKED', 1, NOW(), NOW()),
('Inactive User', 'inactive@example.com', '0903333333', '$2y$10$vfS7C5p/ImE1xzQi7Mok9Odn4UxJwgYb7qkXmD1jqPhSbipqg.dES', 'CUSTOMER', 'INACTIVE', 1, NOW(), NOW());
```

Thông tin đăng nhập:

- `active@example.com` / `password123` -> `200`
- `blocked@example.com` / `blocked123` -> `403`
- `inactive@example.com` / `inactive123` -> `403`
