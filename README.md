# Marty

A Laravel-based e-commerce storefront and admin application.

## Features
- Product catalog and shopping cart
- Admin management for products, orders, and settings
- Authentication and OTP support

## Getting started

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```
