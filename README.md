# Vant Bangladesh — Laravel E-Commerce Store

A ready-to-launch Laravel online shop with a full admin panel, built for Bangladeshi stores that sell on Facebook and
ship cash on delivery. The demo data is set up as **Vant Bangladesh**, a leather goods shop, and every part of the
look (name, logo, colours, fonts, homepage, menus, pages) can be changed from the admin panel.

---

## Features

### Storefront

| Area | What it does |
|---|---|
| **Shop** | Mobile-first home, shop, category, brand and product pages, phone tab bar, sliding product rows, full-screen photo viewer with swipe and zoom |
| **Ordering** | Order now on every card, option picker with colour swatches, side cart, one-page checkout with delivery zone picked from the typed address, optional delivery note |
| **Payments** | Cash on delivery, bKash, Nagad and Rocket with TrxID check; safe COD orders can confirm themselves |
| **Accounts** | Customer account, order tracking, Google login and email OTP |
| **Pages** | About us, FAQ, Shipping, Returns or any page at `/page/{slug}`, shown in the footer and sitemap |
| **SEO** | Meta tags, sitemap, robots.txt, one store address (www and http redirect to `APP_URL`), shop-styled 404 page |

### Admin panel (`/admin`)

| Area | What it does |
|---|---|
| **Dashboard** | Today and this month, profit, orders to ship, needs-action list, top products, store performance, and a "Finish setting up your store" checklist |
| **Orders** | Status tabs, bulk confirm / send to courier / cancel, invoices and parcel labels, staff call notes, new-order alerts that close by themselves |
| **Couriers** | One-click booking with Steadfast, Pathao and RedX, automatic delivery updates, courier scan for hand-over and returns |
| **Fraud check** | Risk score per order, blacklist, customer delivery history across couriers (BD Courier) |
| **POS** | Counter sales with barcode scanning, receipts and barcode printing; online and shop stock stay in sync |
| **Catalog** | Products with colour/size variants, one-level sub-categories, brands, inventory with low-stock alerts, size guide, reviews, CSV import/export |
| **Media** | Media library with bulk image compression |
| **Marketing** | Flash sale with countdown, coupons, free-delivery rules, header ticker, promo cards with dates, abandoned-cart recovery by WhatsApp/SMS |
| **Store builder** | Logo, 3 theme colours (or colours from the logo), 6 font pairs, corner style, 5 one-click style packs, live preview |
| **Homepage & menus** | Show, hide and reorder homepage sections; hero slider; build the top menu bar with dropdowns (light or dark); choose which categories show in the slider |
| **Profit** | Expenses, net profit, sales by channel, visitor analytics, CSV exports |
| **Tracking** | Meta Pixel + Conversions API, GA4 (browser and server), TikTok Pixel, Google Ads, GTM, with setup guides in English and Bangla and a duplicate-tag warning |
| **Staff** | Super Admin, Store Manager, Order Manager and Inventory Manager roles, each with its own phone menu bar; suspension and audit log |
| **Backups** | Daily database backup on a schedule you set, back up now, download, upload and restore |

---

## Requirements

- PHP 8.3+ with the usual Laravel extensions (pdo_mysql, mbstring, openssl, curl, gd, intl, zip, fileinfo)
- MySQL / MariaDB (SQLite works for local testing)
- Composer
- Node.js is only needed to rebuild the CSS. The compiled CSS is committed in `public/theme/css/`.

## Install

```bash
git clone https://github.com/DeveloperShamim/Marty.git
cd Marty
git checkout VantBangladesh
composer install
cp .env.example .env
php artisan key:generate
```

Set `APP_NAME`, `APP_URL` and the database in `.env`, then:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

- With `APP_ENV=local` the seeder fills a **demo shop**: settings, categories, brands, banners, coupons, starter pages
  and staff accounts. Add demo products and orders with
  `php artisan db:seed --class=ProductSeeder` and `php artisan db:seed --class=OrderSeeder`.
- With `APP_ENV=production` it only creates an admin account (`ADMIN_EMAIL` / `ADMIN_PASSWORD` from `.env`, or a random
  password printed once), the default settings and categories. No demo data or `password` accounts, unless you set
  `SEED_DEMO=true`.

### New client store

On a fresh copy for a new client, after `php artisan migrate --seed`, run:

```bash
php artisan store:new --name="Glow Mart" --type=beauty --phone=01811-223344 \
  --email=hello@glowmart.com --admin-email=owner@glowmart.com
```

This replaces the demo shop's name, wording, logo, payment numbers, categories, coupons, banners and trust strip with a
starter pack for that business type, and picks a matching style pack. Types: `fashion`, `beauty`, `electronics`,
`grocery`, `home`, `general`. With `--admin-email`, it creates an admin and prints the password once. It refuses to run
on a store that already has products or orders. Then log in to `/admin`: the Dashboard checklist shows what is left.

### Cron job

Courier status sync, daily backups and cleanup need the scheduler. Add this cron job to run every minute:

```
* * * * * cd /path/to/site && php artisan schedule:run >> /dev/null 2>&1
```

Run `php artisan app:launch-check` before going live. It checks the cron job, backups and other settings.

### Shared hosting (cPanel)

Point the domain's document root at `public/`. If the host only allows `public_html`, symlink it with
`ln -s public public_html`, then run `php artisan storage:link`.

## Demo logins (local demo data only)

| Role | Email | Password |
|---|---|---|
| Super Admin | `admin@vantbd.com` | `password` |
| Store Manager | `manager@vantbd.com` | `password` |
| Order Manager | `orders@vantbd.com` | `password` |
| Inventory Manager | `inventory@vantbd.com` | `password` |
| Customer | `customer@vantbd.com` | `password` |

Change these passwords, or don't seed demo data, on a live store.

## Useful commands

| Command | What it does |
|---|---|
| `php artisan store:new` | Sets up a fresh copy as a new client's blank store (see Install) |
| `php artisan app:launch-check` | Checks the store is ready to go live |
| `php artisan app:backup-db` | Saves a gzipped SQL copy in `storage/app/backups` (newest 14 kept); also runs daily |
| `php artisan couriers:sync` | Pulls the latest delivery status from the couriers; also runs on the schedule |
| `php artisan test` | Runs the test suite |

## Styles (Tailwind)

The store and admin use built stylesheets in `public/theme/css/tw-store.css` and `tw-admin.css`, made by Tailwind 3
from the classes in the views (configs in `tailwind/`). Theme colours from Admin → Settings reach the CSS as variables
printed by `tailwind_css()` in `app/helpers.php`, so colour changes need no rebuild. After adding or changing classes in
a view, rebuild and commit the CSS:

```bash
npm install
npm run build:css
```

`LongRunTest::test_built_stylesheets_are_up_to_date` fails when they are stale. Swiper, Chart.js, JsBarcode and
SortableJS are served from `public/theme/vendor/` at pinned versions.

## Updating the live site

On the server, from the project folder:

```bash
bash deploy.sh
```

It puts the site in maintenance mode, pulls `VantBangladesh` (or `$BRANCH`), installs packages, runs migrations,
refreshes caches and opens the site again. If a step fails, the site is opened again and the error is shown.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
