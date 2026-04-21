# Cart API Docs

This document describes the customer cart APIs.

Base URL:

```text
http://127.0.0.1:8000/api
```

## Overview

- Auth: Laravel Sanctum Bearer token
- Allowed role: `CUSTOMER`
- One active cart per customer
- `GET /cart` creates the active cart if it does not exist yet

## Endpoints

### 1. Get Active Cart

- Method: `GET`
- URL: `/cart`

Successful response:

```json
{
  "message": "Cart retrieved successfully.",
  "data": {
    "id": 1,
    "status": "ACTIVE",
    "item_count": 2,
    "total_quantity": 3,
    "subtotal": "320.00",
    "items": []
  }
}
```

### 2. Add Item To Cart

- Method: `POST`
- URL: `/cart/items`

Request body:

```json
{
  "product_id": 1,
  "quantity": 2
}
```

Rules:

- Adds the product to the active cart
- If the product already exists in the cart, quantity is incremented
- Rejects inactive products or quantities above available stock

### 3. Update Cart Item Quantity

- Method: `PATCH`
- URL: `/cart/items/{cartItem}`

Request body:

```json
{
  "quantity": 4
}
```

### 4. Remove Cart Item

- Method: `DELETE`
- URL: `/cart/items/{cartItem}`

Rules:

- Removes only items that belong to the authenticated customer's active cart
- Returns `404` if the item does not belong to that cart

## Error Cases

- `401`: missing or invalid token
- `403`: authenticated user is not a `CUSTOMER` or cannot authenticate
- `404`: cart item not found for the current customer
- `422`: validation failure, inactive product, or insufficient stock
