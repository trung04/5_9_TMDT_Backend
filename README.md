# 5_9_TMDT Backend

Laravel ecommerce backend with a standalone Blade website. The customer
storefront, admin web pages, and API all run from this folder; the separate
`../5_9_TMDT_Frontend` project is not required to open the website.

## Requirements

- PHP >= 8.2
- Composer
- Node.js + npm
- MySQL 8+

## Install

Run these commands from `5_9_TMDT_Backend`:

```bash
composer install
npm install
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
mysql -u root -p ecommerce_db < database/ecommerce_schema_mysql.sql
mysql -u root -p ecommerce_db < database/ecommerce_seed_data.sql
```

You can also use Laravel migrations when that fits your local workflow:

```bash
php artisan migrate --seed
```

## Run the Website

For local development with Vite hot reload, run one command from this backend
folder:

```bash
npm run web
```

Or use the Composer shortcut:

```bash
composer run dev
```

Then open:

```text
Customer website: http://127.0.0.1:8000/
Admin website:    http://127.0.0.1:8000/admin-web/login
```

For a single Laravel server without the Vite dev server, build the assets first:

```bash
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Backend health check:

```text
GET http://127.0.0.1:8000/backend-status
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
