# API Overview

This document gives a quick overview of the main API groups in the project.

Base URL:

```text
http://127.0.0.1:8000/api
```

## Documentation Files

- Auth APIs: `docs/auth-api.md`
- Category APIs: `docs/category-api.md`
- Supplier APIs: `docs/supplier-api.md`
- Cart APIs: `docs/cart-api.md`
- Order APIs: `docs/order-api.md`

## Authentication

- Framework: Laravel Sanctum
- Header: `Authorization: Bearer <access_token>`
- Public auth endpoints:
  - `POST /register`
  - `POST /login`
- Protected auth endpoints:
  - `GET /me`
  - `POST /logout`

## Public Routes

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/register` | Register a new customer account |
| `POST` | `/login` | Login with email and password |
| `GET` | `/products` | List active products |
| `GET` | `/products/{id}` | Get product detail |
| `GET` | `/categories` | List active categories |
| `GET` | `/categories/{id}` | Get category detail |
| `GET` | `/categories/{id}/products` | List products by category |
| `GET` | `/suppliers` | List active suppliers |
| `GET` | `/suppliers/{id}` | Get supplier detail |
| `GET` | `/suppliers/{id}/products` | List products by supplier |

## Protected Customer Routes

These endpoints require a valid token and a user with role `CUSTOMER`.

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/me` | Get current authenticated user |
| `POST` | `/logout` | Revoke current token |
| `GET` | `/cart` | Get or create the active cart |
| `POST` | `/cart/items` | Add or increment a cart item |
| `PATCH` | `/cart/items/{id}` | Update a cart item quantity |
| `DELETE` | `/cart/items/{id}` | Remove a cart item |
| `POST` | `/orders/checkout` | Create an order from the active cart |
| `GET` | `/orders` | List the current user's orders |
| `GET` | `/orders/{id}` | Get current user's order detail |

## Protected Admin Routes

These endpoints require a valid token. Current controllers apply role checks in code where needed.

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/admin/products` | List products for admin management |
| `GET` | `/admin/products/{id}` | Get product detail for admin |
| `POST` | `/admin/products` | Create a product |
| `PUT` | `/admin/products/{id}` | Update a product |
| `PATCH` | `/admin/products/{id}/status` | Update product status |
| `DELETE` | `/admin/products/{id}` | Delete a product |
| `POST` | `/admin/categories` | Create a category |
| `PUT` | `/admin/categories/{id}` | Update a category |
| `DELETE` | `/admin/categories/{id}` | Delete a category |
| `POST` | `/admin/suppliers` | Create a supplier |
| `PUT` | `/admin/suppliers/{id}` | Update a supplier |
| `DELETE` | `/admin/suppliers/{id}` | Delete a supplier |

## Common Response Shape

Successful responses use:

```json
{
  "message": "Operation successful.",
  "data": {}
}
```

List responses may also include:

```json
{
  "pagination": {
    "total": 10,
    "per_page": 15,
    "current_page": 1,
    "last_page": 1
  }
}
```

Validation failures return HTTP `422` with an `errors` object.
