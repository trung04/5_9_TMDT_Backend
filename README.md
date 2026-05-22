# 5_9_TMDT Backend

Laravel API backend for the 5_9_TMDT ecommerce project.

The React admin/customer frontend is a separate project in `../5_9_TMDT_Frontend`.
This backend does not build or serve frontend assets.

## Requirements

- PHP >= 8.2
- Composer
- MySQL 8+

## Install

Run these commands from `5_9_TMDT_Backend`:

```bash
composer install
copy .env.example .env
php artisan key:generate
```

Update database settings in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

## Database

Create the database:

```sql
CREATE DATABASE ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Import the schema and seed data:

```bash
mysql -u root -p ecommerce_db < ecommerce_schema_mysql.sql
mysql -u root -p ecommerce_db < ecommerce_seed_data.sql
```

You can also use Laravel migrations when that fits your local workflow:

```bash
php artisan migrate --seed
```

## Run

Start the API server:

```bash
php artisan serve
```

Or use the Composer shortcut:

```bash
composer run dev
```

Backend health check:

```text
GET http://127.0.0.1:8000/
```

API routes are mounted under:

```text
http://127.0.0.1:8000/api
```

## Test

```bash
composer test
```

or:

```bash
php artisan test
```
