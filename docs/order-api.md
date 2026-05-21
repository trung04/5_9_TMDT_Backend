# Order API Docs

This document describes the customer checkout and order APIs.

Base URL:

```text
http://127.0.0.1:8000/api
```

## Overview

- Auth: Laravel Sanctum Bearer token
- Allowed role: `CUSTOMER`
- Checkout source: current active cart only
- Payment method: server-assigned `COD`

## Endpoints

### 1. Checkout Current Cart

- Method: `POST`
- URL: `/orders/checkout`

Request body:

```json
{
  "recipient_name": "Nguyen Van A",
  "recipient_phone": "0912345678",
  "shipping_address": "123 Nguyen Trai, Ha Noi",
  "note": "Call before delivery"
}
```

Checkout behavior:

- Rejects an empty cart
- Re-prices every cart item from the current `products.sale_price`
- Rejects inactive products or insufficient stock
- Creates `orders`, `order_items`, `payments`, and `order_status_history`
- Decrements `products.stock_quantity`
- Marks the cart as `CHECKED_OUT`

Successful response:

```json
{
  "message": "Order created successfully.",
  "data": {
    "id": 1,
    "order_no": "ORD-20260001",
    "payment_method": "COD",
    "status": "PENDING",
    "subtotal": "320.00",
    "shipping_fee": "0.00",
    "discount_amount": "0.00",
    "total_amount": "320.00"
  }
}
```

### 2. Get My Orders

- Method: `GET`
- URL: `/orders`

Query parameters:

- `per_page` optional, default `15`
- `page` optional, default Laravel pagination page

Response includes:

- order summary
- payment summary
- pagination metadata

### 3. Get My Order Detail

- Method: `GET`
- URL: `/orders/{order}`

Response includes:

- order summary
- recipient information
- order items
- payment summary
- status history

## Error Cases

- `401`: missing or invalid token
- `403`: authenticated user is not a `CUSTOMER` or cannot authenticate
- `404`: order does not belong to the authenticated customer
- `422`: empty cart, inactive cart item product, or insufficient stock
