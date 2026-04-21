# Supplier API Docs

Tài liệu này mô tả bộ API nhà cung cấp của dự án.

Base URL mặc định:

```text
http://127.0.0.1:8000/api
```

## Tổng quan

- **Public endpoints**: Lấy danh sách, xem chi tiết nhà cung cấp, lấy sản phẩm theo nhà cung cấp
- **Admin endpoints**: Tạo, cập nhật, xóa nhà cung cấp (yêu cầu authentication token)
- **Xác thực**: Laravel Sanctum Bearer Token (ADMIN endpoints)
- **Unique fields**: supplier_code, phone, email phải unique
- **Soft delete**: Xóa nhà cung cấp không xóa dữ liệu, chỉ đánh dấu `is_active = false`
- **Validation**: Không thể xóa nhà cung cấp nếu đang có sản phẩm liên kết

## Supplier payload

Mọi response trả về `supplier` hoặc `data` có chứa supplier đều có dạng:

```json
{
  "id": 1,
  "supplier_code": "SUP001",
  "name": "Công ty TNHH ABC",
  "contact_name": "Nguyễn Văn A",
  "phone": "0123456789",
  "email": "contact@abc.com",
  "address": "123 Đường ABC, Quận 1, TP.HCM",
  "is_active": true,
  "created_at": "2026-04-18T12:00:00.000000Z",
  "updated_at": "2026-04-18T12:00:00.000000Z",
  "products_count": 5
}
```

## 1. Get List Suppliers (Public)

Lấy danh sách tất cả nhà cung cấp đang hoạt động.

- Method: `GET`
- URL: `/suppliers`
- Auth: không cần

Query parameters (all optional):

- `per_page`: số item trên trang (mặc định: 15)
- `page`: số trang (mặc định: 1)

Response thành công: `200 OK`

```json
{
  "message": "Suppliers retrieved successfully.",
  "data": [
    {
      "id": 1,
      "supplier_code": "SUP001",
      "name": "Công ty TNHH ABC",
      "contact_name": "Nguyễn Văn A",
      "phone": "0123456789",
      "email": "contact@abc.com",
      "address": "123 Đường ABC, Quận 1, TP.HCM",
      "is_active": true,
      "created_at": "2026-04-18T12:00:00.000000Z",
      "updated_at": "2026-04-18T12:00:00.000000Z",
      "products_count": 5
    }
  ],
  "pagination": {
    "total": 1,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
  }
}
```

## 2. Get Supplier Details (Public)

Lấy thông tin chi tiết nhà cung cấp theo ID.

- Method: `GET`
- URL: `/suppliers/{id}`
- Auth: không cần

Response thành công: `200 OK`

```json
{
  "message": "Supplier retrieved successfully.",
  "data": {
    "id": 1,
    "supplier_code": "SUP001",
    "name": "Công ty TNHH ABC",
    "contact_name": "Nguyễn Văn A",
    "phone": "0123456789",
    "email": "contact@abc.com",
    "address": "123 Đường ABC, Quận 1, TP.HCM",
    "is_active": true,
    "created_at": "2026-04-18T12:00:00.000000Z",
    "updated_at": "2026-04-18T12:00:00.000000Z",
    "products_count": 5
  }
}
```

Response lỗi: `404 Not Found`

```json
{
  "message": "Supplier not found."
}
```

## 3. Get Products by Supplier (Public)

Lấy danh sách sản phẩm của nhà cung cấp.

- Method: `GET`
- URL: `/suppliers/{id}/products`
- Auth: không cần

Query parameters:

- `per_page`: số item trên trang (mặc định: 15)

Response thành công: `200 OK`

```json
{
  "message": "Supplier products retrieved successfully.",
  "data": [
    {
      "id": 1,
      "sku": "PROD001",
      "name": "iPhone 15",
      "description": "Điện thoại thông minh",
      "sale_price": 25000000.00,
      "stock_quantity": 10,
      "is_active": true,
      "created_at": "2026-04-18T12:00:00.000000Z",
      "updated_at": "2026-04-18T12:00:00.000000Z"
    }
  ],
  "pagination": {
    "total": 1,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
  }
}
```

## 4. Create Supplier (Admin)

Tạo nhà cung cấp mới.

- Method: `POST`
- URL: `/admin/suppliers`
- Auth: Bearer Token (ADMIN role)

Request body:

```json
{
  "supplier_code": "SUP002",
  "name": "Công ty XYZ",
  "contact_name": "Trần Thị B",
  "phone": "0987654321",
  "email": "info@xyz.com",
  "address": "456 Đường XYZ, Quận 2, TP.HCM",
  "is_active": true
}
```

Validation rules:

- `supplier_code`: required, string, max:50, unique
- `name`: required, string, max:150
- `contact_name`: nullable, string, max:120
- `phone`: required, string, max:20, regex phone pattern, unique
- `email`: nullable, email, max:120, unique
- `address`: nullable, string, max:255
- `is_active`: nullable, boolean

Response thành công: `201 Created`

```json
{
  "message": "Supplier created successfully.",
  "data": {
    "id": 2,
    "supplier_code": "SUP002",
    "name": "Công ty XYZ",
    "contact_name": "Trần Thị B",
    "phone": "0987654321",
    "email": "info@xyz.com",
    "address": "456 Đường XYZ, Quận 2, TP.HCM",
    "is_active": true,
    "created_at": "2026-04-18T12:00:00.000000Z",
    "updated_at": "2026-04-18T12:00:00.000000Z"
  }
}
```

Response lỗi validation: `422 Unprocessable Entity`

```json
{
  "message": "The supplier_code field is required.",
  "errors": {
    "supplier_code": [
      "The supplier_code field is required."
    ]
  }
}
```

## 5. Update Supplier (Admin)

Cập nhật thông tin nhà cung cấp.

- Method: `PUT`
- URL: `/admin/suppliers/{id}`
- Auth: Bearer Token (ADMIN role)

Request body: (chỉ cần fields cần update)

```json
{
  "name": "Công ty XYZ Updated",
  "phone": "0987654322"
}
```

Response thành công: `200 OK`

```json
{
  "message": "Supplier updated successfully.",
  "data": {
    "id": 2,
    "supplier_code": "SUP002",
    "name": "Công ty XYZ Updated",
    "contact_name": "Trần Thị B",
    "phone": "0987654322",
    "email": "info@xyz.com",
    "address": "456 Đường XYZ, Quận 2, TP.HCM",
    "is_active": true,
    "created_at": "2026-04-18T12:00:00.000000Z",
    "updated_at": "2026-04-18T12:00:00.000000Z"
  }
}
```

## 6. Delete Supplier (Admin)

Xóa nhà cung cấp (soft delete).

- Method: `DELETE`
- URL: `/admin/suppliers/{id}`
- Auth: Bearer Token (ADMIN role)

Response thành công: `200 OK`

```json
{
  "message": "Supplier deleted successfully."
}
```

Response lỗi (nếu có sản phẩm liên kết): `400 Bad Request`

```json
{
  "message": "Cannot delete supplier because it is associated with products."
}
```

## Error Codes

- `400 Bad Request`: Dữ liệu không hợp lệ hoặc không thể xóa
- `401 Unauthorized`: Chưa đăng nhập hoặc token không hợp lệ
- `403 Forbidden`: Không có quyền admin
- `404 Not Found`: Nhà cung cấp không tồn tại
- `422 Unprocessable Entity`: Validation lỗi
- `500 Internal Server Error`: Lỗi server