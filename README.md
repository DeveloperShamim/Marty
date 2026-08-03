# SoleBd — Premium E-Commerce Storefront (Laravel)

A full-featured Laravel e-commerce application for a fashion & lifestyle store (shoes, luxury watches, genuine leather goods, bags & eyewear). Includes a high-converting storefront and a comprehensive admin panel.

---

## ✨ Feature Highlights

| Area | Details |
|---|---|
| 💬 **Live Customer Support Chat** | Floating widget with home & thread views, voice notes 🎙️, photo attachments 📷, product cards 🛍️, coupon sharing 🎟️, order tracking 🧾, status dropdown, 1-click storage cleanup 🧹, and Asia/Dhaka local timezone |
| 🏪 **Storefront** | Modern responsive homepage, shop catalog, single product, contact, cart, order confirmation |
| 🎨 **3-Color Theme Engine** | Pick 3 theme colors in admin → site colors update live across storefront & chat widget |
| 🏷️ **Brands** | 9 Official brand pages (Nike, Adidas, Apex, Casio, Seiko, Picard, Fossil, Ray-Ban, Woodland) with logos and product counts |
| 📦 **Products** | SKUs, color/size variants, flash sales, specs, customer reviews, square product media |
| 🗂️ **Categories** | 6 Categories (Sneakers & Running Shoes, Formal & Dress Footwear, Luxury & Sport Watches, Leather Belts & Wallets, Bags & Backpacks, Eyewear & Accessories) |
| 🎡 **Hero Banners** | Widescreen hero slider banners with custom background fit and call-to-action buttons |
| 🛒 **Cart & Checkout** | Session cart, coupon codes, mobile payment (bKash, Nagad, Rocket, COD), auto contact sync |
| 📋 **Orders** | Order management, status tracking, invoice PDF printing, UTM source tracking |
| 🛡️ **Fraud Detection** | Zero-API local risk scoring, TrxID validation, blacklist management |
| 👥 **Staff & Role Control** | Role-based access control (RBAC), 4 staff roles, account suspension |
| 📜 **Staff Audit Logs** | Automated activity log timeline for all staff actions + 1-click Clear Audit Logs feature |
| 👤 **Admin Profile** | Super Admin profile management to update email address & password credentials (`/admin/profile`) |
| 👥 **Visitor Analytics** | Live real-time visitor monitor, 14-day Chart.js line chart, device tracking |
| 🚚 **Courier Sync** | 1-click dispatch to Steadfast, Pathao, RedX via API |
| 🛒 **Abandoned Carts** | Track uncompleted checkouts, 1-click WhatsApp/SMS recovery |
| ⚡ **Admin Cache Clear** | 1-click topbar cache optimization (`optimize:clear`) |
| ⚙️ **Admin Dashboard** | Settings, Banner, Brand, Category, Product, Order, Coupon, User management |
| 🔐 **Auth** | Login, Register, Google OAuth 1-click login, OTP email verification |
| 📨 **Mail & OTP** | SMTP config, customer OTP toggle |
| 📈 **SEO & Tracking** | Meta tags, Facebook Pixel, Google Analytics / GTM |

---

## 🚀 Quick Setup (Fresh Install)

### Prerequisites
- PHP >= 8.2
- Composer
- MySQL or any supported DB
- Node.js and npm

### 1. Clone the repository

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
APP_NAME=SoleBd
DB_DATABASE=solebd
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. Run migrations and seed the database

```bash
php artisan migrate:fresh --seed
```

> This runs **all** migrations and seeds:
> - Super Admin and staff accounts (see credentials below)
> - Default site settings for **SoleBd**
> - 9 Brands, 6 Categories, 72 Products, SKUs, variants, banners, coupons, reviews, orders, support chats, visitor traffic, and audit logs

### 6. Link storage (for image uploads)

#### Standard Local / VPS Setup:
```bash
php artisan storage:link
```

#### Shared Hosting / cPanel Setup (`public_html`):
If your web host uses `public_html` instead of `public`:

**Option A (SSH Command Line):**
```bash
# 1. Symlink public folder to public_html
ln -s public public_html

# 2. Run Artisan storage link (supports both public and public_html automatically)
php artisan storage:link
```

**Option B (Without SSH / PHP Helper Script):**
Create a `link.php` script inside your `public_html` folder with the following code:
```php
<?php
// Symlink storage/app/public to public_html/storage
symlink(__DIR__.'/../storage/app/public', __DIR__.'/storage');
echo "Storage link created successfully!";
```
Visit `http://yourdomain.com/link.php` once in your browser, then delete `link.php`.

### 7. Start the dev server

```bash
php artisan serve
```

Visit: **http://127.0.0.1:8000**

---

## 🧹 Chat Storage Maintenance

To clean up old voice notes and attachment media files from your server storage:
```bash
# Clean chat media files older than 90 days
php artisan chat:prune --days=90

# Clean ONLY attachment files (keeping text history)
php artisan chat:prune --days=60 --attachments-only
```

---

## 🔑 Default Login Credentials

### Admin & Staff Panel — `/admin`

| Role | Email Login | Password | Access Level |
| :--- | :--- | :--- | :--- |
| 👑 **Super Admin** | `admin@solebd.com` | `password` | 100% Full Access |
| 👔 **Store Manager** | `store.manager@solebd.com` | `password` | Both Orders & Catalog Access |
| 📦 **Order Manager** | `order.manager@solebd.com` | `password` | Orders, Support & Customer Access |
| 🏭 **Inventory Manager** | `inventory.manager@solebd.com` | `password` | Products, Stock & Catalog Access |

### Customer Account

| Field    | Value               |
|----------|---------------------|
| Email    | `customer@solebd.com` |
| Password | `password`          |

---

## 🌱 Seeding Details

### What `php artisan migrate:fresh --seed` seeds

| Seeder                  | Contents                                                                 |
|-------------------------|--------------------------------------------------------------------------|
| `seedUsers()`           | 4 Staff accounts (Super Admin, Store Manager, Order Manager, Inventory Manager) + 1 Customer |
| `seedSettings()`        | All SoleBd site settings (name, theme colors, contact, payment, SEO)     |
| `seedCategories()`      | 6 categories: Sneakers & Running Shoes, Formal Footwear, Luxury Watches, Leather Belts & Wallets, Bags & Backpacks, Eyewear |
| `seedBrands()`          | 9 brands: Nike, Adidas, Apex, Casio, Seiko, Picard, Fossil, Ray-Ban, Woodland |
| `seedFeatures()`        | Homepage trust/feature badges                                            |
| `seedCoupons()`         | Sample discount coupons                                                  |
| `BannerSeeder`          | Homepage hero banner slides                                              |
| `seedProducts()`        | 72 product catalog items linked to categories and brands                |
| `seedReviews()`         | Customer product reviews                                                 |
| `seedOrders()`          | Sample orders with fraud risk scores and UTM source tracking             |
| `seedConversations()`   | Sample customer live support chat threads & messages                     |
| `seedVisitorLogs()`     | 14 days of realistic storefront traffic for Visitor Analytics            |
| `seedStaffActivityLogs()`| Sample audit timeline entries for employee action logs                   |

---

## 📄 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
