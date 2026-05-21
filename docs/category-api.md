# Category API Docs

Tài liệu này mô tả bộ API danh mục sản phẩm của dự án.

Base URL mặc định:

```text
http://127.0.0.1:8000/api
```

## Tổng quan

- **Public endpoints**: Lấy danh sách, xem chi tiết danh mục, lấy sản phẩm theo danh mục
- **Admin endpoints**: Tạo, cập nhật, xóa danh mục (yêu cầu authentication token)
- **Xác thực**: Laravel Sanctum Bearer Token (ADMIN endpoints)
- **Unique name**: Tên danh mục phải unique
- **Soft delete**: Xóa danh mục không xóa dữ liệu, chỉ đánh dấu `is_active = false`

## Category payload

Mọi response trả về `category` hoặc `data` có chứa category đều có dạng:

```json
{
  "id": 1,
  "name": "Điện thoại",
  "description": "Các loại điện thoại di động",
  "is_active": true,
  "created_at": "2026-04-18T12:00:00.000000Z",
  "updated_at": "2026-04-18T12:00:00.000000Z"
}
```

## 1. Get List Categories (Public)

Lấy danh sách tất cả danh mục đang hoạt động.

- Method: `GET`
- URL: `/categories`
- Auth: không cần

Query parameters (all optional):

- `per_page`: số item trên trang (mặc định: 15)
- `page`: số trang (mặc định: 1)

Response thành công: `200 OK`

```json
{
  "message": "Categories retrieved successfully.",
  "data": [
    {
      "id": 1,
      "name": "Điện thoại",
      "description": "Các loại điện thoại di động",
      "is_active": true,
      "created_at": "2026-04-18T12:00:00.000000Z",
      "updated_at": "2026-04-18T12:00:00.000000Z"
    },
    {
      "id": 2,
      "name": "Laptop",
      "description": "Máy tính xách tay",
      "is_active": true,
      "created_at": "2026-04-18T12:05:00.000000Z",
      "updated_at": "2026-04-18T12:05:00.000000Z"
    }
  ],
  "pagination": {
    "total": 10,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
  }
}
```

Ví dụ `curl`:

```bash
# Lấy danh sách danh mục mặc định
curl http://127.0.0.1:8000/api/categories

# Lấy danh mục trang 2, 10 item trên trang
curl "http://127.0.0.1:8000/api/categories?page=2&per_page=10"
```

## 2. Get Category Detail (Public)

Lấy chi tiết một danh mục theo ID, bao gồm số lượng sản phẩm.

- Method: `GET`
- URL: `/categories/{id}`
- Auth: không cần

URL parameters:

- `id` (required): ID của danh mục

Response thành công: `200 OK`

```json
{
  "message": "Category retrieved successfully.",
  "data": {
    "id": 1,
    "name": "Điện thoại",
    "description": "Các loại điện thoại di động",
    "is_active": true,
    "products_count": 25,
    "created_at": "2026-04-18T12:00:00.000000Z",
    "updated_at": "2026-04-18T12:00:00.000000Z"
  }
}
```

Response lỗi:

- `404 Not Found`: danh mục không tồn tại hoặc không hoạt động

Ví dụ `curl`:

```bash
curl http://127.0.0.1:8000/api/categories/1
```

## 3. Create Category (Admin only)

Tạo danh mục mới.

- Method: `POST`
- URL: `/admin/categories`
- Auth: Bearer token (required)

Header:

```http
Authorization: Bearer <access_token>
```

Request body:

```json
{
  "name": "Tablet",
  "description": "Máy tính bảng",
  "is_active": true
}
```

Quy tắc validate:

- `name`: bắt buộc, chuỗi, tối đa 120 ký tự, **unique**
- `description`: tùy chọn, chuỗi, tối đa 1000 ký tự
- `is_active`: tùy chọn (mặc định: true), boolean

Response thành công: `201 Created`

```json
{
  "message": "Category created successfully.",
  "data": {
    "id": 5,
    "name": "Tablet",
    "description": "Máy tính bảng",
    "is_active": true,
    "created_at": "2026-04-18T12:15:00.000000Z",
    "updated_at": "2026-04-18T12:15:00.000000Z"
  }
}
```

Response lỗi phổ biến:

- `422 Unprocessable Entity`: sai validate, trùng tên, hoặc thiếu field bắt buộc
- `401 Unauthorized`: thiếu token hoặc token không hợp lệ

Ví dụ `curl`:

```bash
curl -X POST http://127.0.0.1:8000/api/admin/categories \
  -H "Authorization: Bearer <access_token>" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Tablet",
    "description": "Máy tính bảng",
    "is_active": true
  }'
```

## 4. Update Category (Admin only)

Cập nhật thông tin danh mục.

- Method: `PUT`
- URL: `/admin/categories/{id}`
- Auth: Bearer token (required)

Header:

```http
Authorization: Bearer <access_token>
```

URL parameters:

- `id` (required): ID của danh mục

Request body (tất cả tùy chọn - chỉ gửi các field cần thay đổi):

```json
{
  "name": "Tablet Pro",
  "description": "Máy tính bảng cao cấp",
  "is_active": true
}
```

Response thành công: `200 OK`

```json
{
  "message": "Category updated successfully.",
  "data": {
    "id": 5,
    "name": "Tablet Pro",
    "description": "Máy tính bảng cao cấp",
    "is_active": true,
    "created_at": "2026-04-18T12:15:00.000000Z",
    "updated_at": "2026-04-18T12:20:00.000000Z"
  }
}
```

Response lỗi:

- `404 Not Found`: danh mục không tồn tại
- `422 Unprocessable Entity`: sai validate hoặc trùng tên
- `401 Unauthorized`: thiếu token

Ví dụ `curl`:

```bash
curl -X PUT http://127.0.0.1:8000/api/admin/categories/5 \
  -H "Authorization: Bearer <access_token>" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Tablet Pro",
    "description": "Máy tính bảng cao cấp"
  }'
```

## 5. Delete Category (Admin only - Soft Delete)

Xóa danh mục (đánh dấu `is_active = false`, không xóa dữ liệu).

- Method: `DELETE`
- URL: `/admin/categories/{id}`
- Auth: Bearer token (required)

Header:

```http
Authorization: Bearer <access_token>
```

URL parameters:

- `id` (required): ID của danh mục

Response thành công: `200 OK`

```json
{
  "message": "Category deleted successfully."
}
```

Response lỗi:

- `404 Not Found`: danh mục không tồn tại
- `401 Unauthorized`: thiếu token

Ví dụ `curl`:

```bash
curl -X DELETE http://127.0.0.1:8000/api/admin/categories/5 \
  -H "Authorization: Bearer <access_token>"
```

## 6. Get Products by Category (Public)

Lấy danh sách sản phẩm trong một danh mục, hỗ trợ filter và tìm kiếm.

- Method: `GET`
- URL: `/categories/{id}/products`
- Auth: không cần

URL parameters:

- `id` (required): ID của danh mục

Query parameters (all optional):

- `per_page`: số item trên trang (mặc định: 15)
- `page`: số trang (mặc định: 1)
- `min_price`: giá tối thiểu. Ví dụ: `100000`
- `max_price`: giá tối đa. Ví dụ: `5000000`
- `supplier_id`: ID nhà cung cấp. Ví dụ: `1`
- `search`: tìm kiếm theo tên hoặc mô tả sản phẩm. Ví dụ: `iphone`

Response thành công: `200 OK`

```json
{
  "message": "Products in category retrieved successfully.",
  "category": {
    "id": 1,
    "name": "Điện thoại"
  },
  "data": [
    {
      "id": 1,
      "category_id": 1,
      "supplier_id": 2,
      "sku": "PHONE-001",
      "name": "iPhone 15 Pro",
      "description": "Điện thoại Apple flagship 2024",
      "sale_price": "29999999.99",
      "stock_quantity": 50,
      "is_active": true,
      "created_at": "2026-04-18T12:00:00.000000Z",
      "updated_at": "2026-04-18T12:00:00.000000Z"
    }
  ],
  "pagination": {
    "total": 25,
    "per_page": 15,
    "current_page": 1,
    "last_page": 2
  }
}
```

Response lỗi:

- `404 Not Found`: danh mục không tồn tại

Ví dụ `curl`:

```bash
# Lấy tất cả sản phẩm trong danh mục 1
curl http://127.0.0.1:8000/api/categories/1/products

# Lấy sản phẩm trong danh mục 1, giá từ 1 tỷ đến 5 tỷ
curl "http://127.0.0.1:8000/api/categories/1/products?min_price=1000000000&max_price=5000000000"

# Tìm sản phẩm iPhone trong danh mục 1
curl "http://127.0.0.1:8000/api/categories/1/products?search=iphone"

# Tìm sản phẩm từ nhà cung cấp 2 trong danh mục 1
curl "http://127.0.0.1:8000/api/categories/1/products?supplier_id=2"

# Kết hợp nhiều filter
curl "http://127.0.0.1:8000/api/categories/1/products?min_price=1000000&max_price=5000000&supplier_id=2&search=iphone&per_page=10"
```

## Error format

### 401 không được phép truy cập (admin endpoints)

```json
{
  "message": "Unauthenticated."
}
```

### 404 không tìm thấy danh mục

```json
{
  "message": "Category not found."
}
```

### 422 lỗi validate

```json
{
  "message": "The name field is required. (and 1 more error)",
  "errors": {
    "name": [
      "The name field is required."
    ],
    "description": [
      "The description field may not be greater than 1000 characters."
    ]
  }
}
```

Ví dụ lỗi trùng tên:

```json
{
  "message": "The name has already been taken.",
  "errors": {
    "name": [
      "Tên danh mục này đã tồn tại."
    ]
  }
}
```

## Luồng test nhanh

1. **Test Public API**:
   - Gọi `GET /categories` để lấy danh sách danh mục
   - Gọi `GET /categories/1` để lấy chi tiết danh mục
   - Gọi `GET /categories/1/products` để lấy sản phẩm

2. **Test Admin API** (cần token):
   - Dùng token từ `POST /api/login` hoặc `POST /api/register`
   - Gọi `POST /admin/categories` để tạo danh mục mới
   - Gọi `PUT /admin/categories/1` để cập nhật danh mục
   - Gọi `DELETE /admin/categories/1` để xóa danh mục

## Ghi chú quan trọng

- **Tên danh mục unique**: System sẽ kiểm tra tên không trùng với danh mục khác
- **Soft delete**: Xóa danh mục chỉ đánh dấu `is_active = false`, không xóa khỏi DB. Điều này bảo vệ FK với bảng `products`
- **Danh mục inactive**: Không xuất hiện trong endpoint `/categories` nhưng vẫn có thể truy cập trực tiếp nếu biết ID
- **Products count**: Khi xem chi tiết danh mục, API trả về `products_count` = số sản phẩm đang hoạt động (`is_active = true`) trong danh mục
- **Filter sản phẩm**: Endpoint `/categories/{id}/products` hỗ trợ filter theo giá, nhà cung cấp, và tìm kiếm theo tên
