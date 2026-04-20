# API Overview - Tổng hợp

Tài liệu này cung cấp tổng quan lại tất cả APIs của dự án.

## Base URL

```
http://127.0.0.1:8000/api
```

## 📚 Documentation Files

- **Auth APIs**: Xem [docs/auth-api.md](auth-api.md)
- **Category APIs**: Xem [docs/category-api.md](category-api.md)

---

## 🔐 Authentication Overview

### Cơ chế
- **Framework**: Laravel Sanctum
- **Token type**: Bearer Token
- **Header**: `Authorization: Bearer <access_token>`

### Token Lifecycle
1. Gọi `/register` → nhận token ngay
2. Gọi `/login` → nhận token mới
3. Sử dụng token trong header `Authorization: Bearer <token>`
4. Gọi `/logout` → thu hồi token hiện tại

---

## 📊 API Routes Summary

### Public Routes (không cần authentication)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/register` | Đăng ký tài khoản mới (CUSTOMER) |
| `POST` | `/login` | Đăng nhập bằng email/password |
| `GET` | `/categories` | Danh sách danh mục |
| `GET` | `/categories/{id}` | Chi tiết danh mục |
| `GET` | `/categories/{id}/products` | Sản phẩm trong danh mục |

### Protected Routes (cần Bearer token)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/me` | Lấy thông tin user hiện tại |
| `POST` | `/logout` | Đăng xuất (thu hồi token) |
| `POST` | `/admin/categories` | Tạo danh mục (ADMIN) |
| `PUT` | `/admin/categories/{id}` | Cập nhật danh mục (ADMIN) |
| `DELETE` | `/admin/categories/{id}` | Xóa danh mục (ADMIN) |

---

## 🔄 Common Workflows

### 1. Luồng Đăng ký & Đăng nhập

```bash
# Bước 1: Đăng ký tài khoản
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Nguyen Van A",
    "email": "user@example.com",
    "phone": "0912345678",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Response: token và user info

# Bước 2: Dùng token để gọi API protected
TOKEN="1|plain-text-token"
curl http://127.0.0.1:8000/api/me \
  -H "Authorization: Bearer $TOKEN"
```

### 2. Luồng Category Management

```bash
# Bước 1: Lấy danh sách danh mục (public)
curl http://127.0.0.1:8000/api/categories

# Bước 2: Đăng nhập để lấy token admin
TOKEN=$(curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password123"}' \
  | jq -r '.access_token')

# Bước 3: Tạo danh mục baru
curl -X POST http://127.0.0.1:8000/api/admin/categories \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Laptop","description":"Máy tính xách tay"}'

# Bước 4: Lấy sản phẩm trong danh mục (public)
curl http://127.0.0.1:8000/api/categories/1/products

# Bước 5: Cập nhật danh mục (admin)
curl -X PUT http://127.0.0.1:8000/api/admin/categories/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Laptop Gaming"}'
```

### 3. Luồng Filter Sản phẩm theo Danh mục

```bash
# Filter theo giá
curl "http://127.0.0.1:8000/api/categories/1/products?min_price=10000000&max_price=50000000"

# Filter theo nhà cung cấp
curl "http://127.0.0.1:8000/api/categories/1/products?supplier_id=1"

# Tìm kiếm
curl "http://127.0.0.1:8000/api/categories/1/products?search=iphone"

# Kết hợp nhiều filter
curl "http://127.0.0.1:8000/api/categories/1/products?min_price=5000000&max_price=30000000&supplier_id=1&search=apple"
```

---

## 📝 Response Patterns

### Successful Response (2xx)

```json
{
  "message": "Operation successful.",
  "data": {
    "id": 1,
    "name": "Example",
    ...
  },
  "pagination": {
    "total": 10,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
  }
}
```

### Error Response

```json
{
  "message": "Error description.",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

---

## ⚠️ HTTP Status Codes

| Code | Meaning | Example |
|------|---------|---------|
| `200` | OK - Request thành công | GET /me, PUT /categories |
| `201` | Created - Resource được tạo | POST /register, POST /categories |
| `401` | Unauthorized - Thiếu/sai token | Missing token, expired token |
| `403` | Forbidden - Tài khoản INACTIVE/BLOCKED | Account disabled |
| `404` | Not Found - Resource không tồn tại | Category không tìm thấy |
| `422` | Unprocessable Entity - Validation failed | Invalid input |

---

## 🛡️ Authentication Status & Meanings

### User Status (status field)
- `ACTIVE` - Tài khoản hoạt động bình thường
- `INACTIVE` - Tài khoản tạm dừng (không đăng nhập được)
- `BLOCKED` - Tài khoản bị khóa (không đăng nhập được)

### User Active Flag (is_active field)
- `true` - Tài khoản kích hoạt (có thể đăng nhập)
- `false` - Tài khoản vô hiệu hóa (không đăng nhập được)

### Login Rules
- Để đăng nhập được: `status = "ACTIVE"` **AND** `is_active = true`
- Nếu vi phạm: Return `403 Forbidden` với message "Your account is not allowed to sign in."

---

## 🎯 Validation Rules

### Registration

| Field | Rules | Notes |
|-------|-------|-------|
| `full_name` | Required, string, max 120 | Trim whitespace |
| `email` | Required, email, max 120, unique | Lowercase |
| `phone` | Required, string, max 20, unique | Trim whitespace |
| `password` | Required, min 8, confirmed | Must match password_confirmation |

### Category

| Field | Rules | Notes |
|-------|-------|-------|
| `name` | Required, string, max 120, unique | Trim whitespace, case-sensitive |
| `description` | Optional, string, max 1000 | Trim whitespace |
| `is_active` | Optional, boolean | Default: true |

---

## 🧪 Quick Test Script

```bash
#!/bin/bash

API="http://127.0.0.1:8000/api"

echo "=== Test 1: Register ==="
REGISTER=$(curl -s -X POST $API/register \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Test User",
    "email": "test@example.com",
    "phone": "0912345678",
    "password": "password123",
    "password_confirmation": "password123"
  }')

TOKEN=$(echo $REGISTER | jq -r '.access_token')
echo "Token: $TOKEN"

echo "=== Test 2: Get Me ==="
curl -s $API/me \
  -H "Authorization: Bearer $TOKEN" | jq .

echo "=== Test 3: Get Categories ==="
curl -s $API/categories | jq .

echo "=== Test 4: Create Category ==="
curl -s -X POST $API/admin/categories \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test Category","description":"Test"}' | jq .

echo "=== Test 5: Logout ==="
curl -s -X POST $API/logout \
  -H "Authorization: Bearer $TOKEN" | jq .
```

---

## 📦 Data Models

### User
```json
{
  "id": 1,
  "full_name": "Nguyen Van A",
  "email": "user@example.com",
  "phone": "0912345678",
  "role": "CUSTOMER",
  "status": "ACTIVE",
  "is_active": true,
  "created_at": "2026-04-18T09:15:49Z",
  "updated_at": "2026-04-18T09:15:49Z"
}
```

### Category
```json
{
  "id": 1,
  "name": "Điện thoại",
  "description": "Các loại điện thoại di động",
  "is_active": true,
  "created_at": "2026-04-18T12:00:00Z",
  "updated_at": "2026-04-18T12:00:00Z"
}
```

### Product
```json
{
  "id": 1,
  "category_id": 1,
  "supplier_id": 2,
  "sku": "PHONE-001",
  "name": "iPhone 15 Pro",
  "description": "Flagship phone",
  "sale_price": "29999999.99",
  "stock_quantity": 50,
  "is_active": true,
  "created_at": "2026-04-18T12:00:00Z",
  "updated_at": "2026-04-18T12:00:00Z"
}
```

---

## 🚀 Next Steps

1. **Migration**: Chạy `php artisan migrate`
2. **Seeding**: Chạy `php artisan db:seed` (nếu cần data test)
3. **Testing**: Dùng script test hoặc Postman
4. **Documentation**: Tham khảo auth-api.md & category-api.md

---

## 📞 Support

Nếu gặp vấn đề:
1. Kiểm tra response error message
2. Xem HTTP status code (401, 403, 404, 422)
3. Tham khảo tài liệu chi tiết trong docs/

---

**Last updated**: 2026-04-18
