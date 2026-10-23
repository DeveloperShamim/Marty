# Vantor — Laravel E-Commerce for Bangladeshi Online Shops

A ready-to-launch Laravel online shop with a full admin panel, built for stores that sell on Facebook and ship cash on delivery.

## Features

| Area | What it does |
|---|---|
| **Storefront** | Mobile-first home, shop, product, cart, one-page checkout, order tracking, customer account, Google login and email OTP |
| **Payments** | Cash on delivery, bKash, Nagad and Rocket with TrxID check; safe COD orders can confirm themselves |
| **Couriers** | One-click booking with Steadfast, Pathao and RedX, parcel labels, automatic delivery updates, courier scan for hand-over and returns |
| **Fraud check** | Risk score per order, blacklist, customer delivery history across couriers (BD Courier) |
| **Orders** | Status tabs, bulk actions, invoices, staff call notes, new-order alerts |
| **POS** | Counter sales with barcode scanning, receipts and barcode printing; online and shop stock stay in sync |
| **Catalog** | Products with colour/size variants, inventory, size guide, reviews, media library with image compression |
| **Marketing** | Flash sale with countdown, coupons, free-delivery rules, header ticker, abandoned-cart recovery by WhatsApp/SMS |
| **Profit** | Expenses, net profit, sales by channel, visitor analytics, CSV exports |
| **Staff** | Store, order and inventory manager roles, suspension, audit log |
| **Tracking** | Meta Pixel + Conversions API, GA4 (browser and server), TikTok Pixel, Google Ads, GTM |
| **Theme** | Pick 3 colours in Store Settings and the whole shop and admin follow |

## Requirements

- PHP 8.3+ with the usual Laravel extensions (pdo_mysql, mbstring, openssl, curl, gd, intl, zip, fileinfo)
- MySQL/MariaDB (SQLite works for local testing)
- Composer
- Node.js is **not** needed to run the shop. The compiled CSS is committed in `public/theme/css/`.

## Install

```bash
git clone https://github.com/DeveloperShamim/Marty.git vantor
cd vantor
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Set the database in `.env`, then:

```bash
php artisan migrate --seed
php artisan storage:link
```

- With `APP_ENV=local` the seeder fills a **demo shop**: settings, categories, a house brand, banners, coupons and staff accounts. Add demo products and orders with
  `php artisan db:seed --class=ProductSeeder` and `php artisan db:seed --class=OrderSeeder`.
- With `APP_ENV=production` it only creates an admin account (`ADMIN_EMAIL` / `ADMIN_PASSWORD` from `.env`, or a printed random password), the default settings and categories. No demo products, orders or `password` accounts.

Add a cron job that runs every minute (courier status sync, cleanup and other scheduled tasks need it):

```
* * * * * cd /path/to/vantor && php artisan schedule:run >> /dev/null 2>&1
```

Run `php artisan app:launch-check` before going live.

### Shared hosting (cPanel)

Point the domain's document root at `public/`. If the host only allows `public_html`, symlink it: `ln -s public public_html`, then run `php artisan storage:link`.

## Demo logins (local demo data only)

| Role | Email | Password |
|---|---|---|
| Super Admin | `admin@example.com` | `password` |
| Store Manager | `manager@example.com` | `password` |
| Order Manager | `orders@example.com` | `password` |
| Inventory Manager | `inventory@example.com` | `password` |
| Customer | `customer@example.com` | `password` |

Admin panel: `/admin`.

## Styles

Pages use Tailwind CSS compiled ahead of time, not the Tailwind play CDN:

- `public/theme/css/tw-store.css` (storefront) and `public/theme/css/tw-admin.css` (admin), built from `tailwind.store.config.js` and `tailwind.admin.config.js`.
- Theme colours are CSS variables set by the layouts from Store Settings, so colour changes in admin need no rebuild.
- After adding or changing Tailwind classes in a view, rebuild and commit the CSS:

```bash
npm install
npm run build        # or npm run watch:store / npm run watch:admin while editing
```

Swiper, Chart.js, JsBarcode and SortableJS are served from `public/theme/vendor/` at pinned versions.

## Updating a live site

`bash deploy.sh` on the server pulls the current branch, installs packages, runs migrations and refreshes caches.

