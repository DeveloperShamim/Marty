# Marty — Laravel E-Commerce Storefront

A full-featured Laravel e-commerce application for a fashion/lifestyle store (shoes, watches, leather goods, accessories). Includes a beautiful storefront and a complete admin dashboard.

---

## ✨ Feature Highlights

| Area | Details |
|---|---|
| 🏪 Storefront | Homepage, Shop, Single Product, Contact, Cart |
| 🎨 3-Color Theme Engine | Pick 3 colors in admin → site colors update instantly |
| 🏷️ Brands | Full brand catalog with logos, filtering, and featured display |
| 📦 Products | SKUs, variants, flash sales, specifications, reviews |
| 🗂️ Categories | Hierarchical categories with icons and featured flags |
| 🎡 Hero Banners | Slideshow banners with background images and text overlays |
| 🛒 Cart & Checkout | Session cart, coupon codes, mobile payment (bKash, Nagad, Rocket, COD) |
| 📋 Orders | Order management, status tracking, invoice PDF |
| ⚙️ Admin Dashboard | Settings, Banner, Brand, Category, Product, Order, Coupon, User management |
| 🔐 Auth | Login, Register, Google OAuth, OTP email verification |
| 📨 Mail & OTP | SMTP config, customer OTP toggle |
| 📈 SEO & Tracking | Meta tags, Facebook Pixel, Google Analytics / GTM |

---

## 🚀 Quick Setup (Fresh Install)

### Prerequisites
- PHP >= 8.2
- Composer
- MySQL or any supported DB
- Node.js and npm

### 1. Clone the repo

```bash
git clone https://github.com/DeveloperShamim/Marty.git
cd Marty
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install Node dependencies and build assets

```bash
npm install
npm run build
```

### 4. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and set your database credentials:

```env
DB_DATABASE=marty
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Run migrations and seed the database

```bash
php artisan migrate --seed
```

> This runs **all** migrations and seeds:
> - Admin user (see credentials below)
> - Default site settings (including theme colors)
> - Sample categories, brands, products, banners, coupons, reviews, and orders

### 6. Link storage (for image uploads)

```bash
php artisan storage:link
```

### 7. Start the dev server

```bash
php artisan serve
```

Visit: **http://localhost:8000**

---

## 🔑 Default Login Credentials

### Admin Panel — `/admin`

| Field    | Value                    |
|----------|--------------------------|
| Email    | `admin@freshkart.test`   |
| Password | `password`               |

### Customer Account

| Field    | Value                      |
|----------|----------------------------|
| Email    | `customer@freshkart.test`  |
| Password | `password`                 |

---

## 🌱 Seeding Details

### What `php artisan migrate --seed` seeds

| Seeder            | Contents                                                                 |
|-------------------|--------------------------------------------------------------------------|
| `seedUsers()`     | 1 admin + 1 sample customer                                              |
| `seedSettings()`  | All site settings (name, theme colors, contact, payment, SEO, etc.)      |
| `seedCategories()`| 5 categories: Shoes, Watches, Leather Belts, Leather Bags, Accessories   |
| `seedBrands()`    | 11 brands: Nike, Adidas, Apex, Casio, Seiko, Fossil, Ray-Ban, etc.       |
| `seedFeatures()`  | Homepage trust/feature badges                                            |
| `seedCoupons()`   | Sample discount coupons                                                  |
| `BannerSeeder`    | Homepage hero banner slides                                              |
| `seedProducts()`  | Sample product catalog linked to categories and brands                   |
| `seedReviews()`   | Customer product reviews                                                 |
| `seedOrders()`    | Sample orders with order items                                           |

### Re-seed only (without dropping tables)

```bash
php artisan db:seed
```

### Fresh migration + reseed — WARNING: wipes all data

```bash
php artisan migrate:fresh --seed
```

---

## 🎨 Theme Colors

The site uses a **3-color engine**. You can change all colors from the admin panel:

**Admin → Settings → Brand & Theme → Theme Colors**

| Setting                            | Key                    | Default   |
|------------------------------------|------------------------|-----------|
| Primary Accent (buttons, links)    | `theme_primary_color`  | `#E8751B` |
| Dark Heading (text, navbar)        | `theme_dark_color`     | `#1C1917` |
| Soft Surface (page backgrounds)    | `theme_surface_color`  | `#FFF8F3` |

Click **"Reset to Defaults"** in the admin panel to restore these values, or choose a **preset theme** (Warm Orange, Royal Sapphire, Emerald Luxe, Ruby Crimson).

---

## 🏷️ Brands

Brands are seeded automatically. To manage brands:

**Admin → Brands**

- Add brand logo, website, and description
- Toggle **Featured** to show brand on the homepage brands strip
- Assign brands to products in the product edit form

---

## 📁 Project Structure (Key Folders)

```
app/
  Http/Controllers/
    Admin/          ← Admin controllers (Products, Orders, Brands, Settings…)
    Auth/           ← Login, Register, OTP
  Models/           ← Eloquent models (Product, Brand, Category, Order…)
  helpers.php       ← Global helpers: setting(), generate_3_color_matching_theme()

database/
  migrations/       ← All table migrations (run in date order)
  seeders/
    DatabaseSeeder.php   ← Main seeder (users, settings, products, brands…)
    BannerSeeder.php     ← Hero banner slides

resources/
  views/
    admin/          ← Admin panel Blade views
    storefront/     ← Public storefront views
      partials/     ← header.blade.php, footer.blade.php, cart-drawer.blade.php
      home.blade.php
      shop.blade.php
      product.blade.php

public/storage/     ← Symlinked uploaded files (run php artisan storage:link)
```

---

## 🔧 Environment Variables Reference

Key variables in `.env`:

```env
APP_NAME=Marty
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_DATABASE=marty
DB_USERNAME=root
DB_PASSWORD=

# Google OAuth (optional)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# Mail (optional — defaults to log driver for local dev)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailpit.test
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 🧪 Running Tests

```bash
php artisan test
```

---

## 📜 License

MIT
