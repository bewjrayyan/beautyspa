# BeautySpa — AestheticCart

Private e-commerce and booking platform for a beauty / spa business, built on **AestheticCart** (Laravel 11, modular architecture) by **MediaCreative Solution** (Bewj Rayyan), with admin workflows for orders, reports, treatments, loyalty, and CHIP payments.

> **Repository:** [github.com/bewjrayyan/beautyspa](https://github.com/bewjrayyan/beautyspa) (private)

---

## Tech stack

| Layer | Technology |
|--------|------------|
| Backend | PHP 8.2+, Laravel 11 |
| Database | MySQL / MariaDB |
| Frontend (admin & storefront) | Vite 7, Vue 3, Alpine.js, Bootstrap 5, Sass |
| Modules | `nwidart/laravel-modules` |
| Payments | CHIP Collect (+ COD, bank transfer, etc.) |
| PDF | Dompdf |

---

## Main features

### Storefront & checkout
- Product catalog, cart, checkout, customer accounts  
- Treatment reservations & beautician booking (see `docs/TREATMENT_RESERVATION.md`)  
- Loyalty & membership (see `docs/LOYALTY_MEMBERSHIP.md`)  
- CHIP online payments (see `docs/PAYMENT_CHIP.md`)  
- Special gift / voucher flows (see `docs/SPECIALGIFT.md`)

### Admin — orders (custom)
- View **archived** (soft-deleted) orders from the orders index  
- **Permanent delete** for archived orders only  
- Order detail: customer avatar, payment method label (incl. legacy `chip` key), archived banner  
- Header meta: email & phone with icons, date of birth, current age  
- **New customer** / **Returning customer** badge  
- Row actions menu: fixed positioning (no fly-away dropdown), no slide animation glitch  

### Admin — reports (custom)
- Per-order rows: Products Purchase, Sales, Customers Order reports  
- **Grand total** column uses order `total` (not line subtotal)  
- Contact, payment status, beautician & appointment columns  
- CSV / Excel / PDF export aligned with on-screen columns  
- Includes soft-deleted orders where relevant (`withTrashed`)  

### Locales
- **English (`en`)** and **Bahasa Malaysia (`ms`)** for admin UI  
- Translation sync commands (see below)

---

## Requirements

- PHP **8.2+** with extensions: `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `zip`, `curl`  
- Composer 2.x  
- Node.js **18+** and npm  
- MySQL 5.7+ / MariaDB 10.3+  
- Apache (e.g. XAMPP) with `mod_rewrite`, or nginx + PHP-FPM  

---

## Local setup (XAMPP example)

### 1. Clone

```bash
git clone https://github.com/bewjrayyan/beautyspa.git
cd beautyspa
```

### 2. Dependencies

```bash
composer install
npm install
npm run build
```

> `public/build/` is not in git — you must run `npm run build` after clone.

### 3. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
APP_URL=http://localhost/beautyspa/public
# or your vhost document root pointing to /public

DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

### 4. Database & install

Create an empty MySQL database, then open in the browser:

```
http://localhost/beautyspa/public/install
```

Complete the web installer (or import an existing dump if you use one).

### 5. Storage link & permissions

```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

### 6. Translations (after lang file changes)

```bash
php artisan order:sync-translations
php artisan report:sync-translations
# or all modules:
php artisan translation:refresh-cache --sync
php artisan view:clear
```

---

## Useful Artisan commands

| Command | Purpose |
|---------|---------|
| `php artisan order:sync-translations` | Sync Order module `en`/`ms` to DB + refresh cache |
| `php artisan report:sync-translations` | Sync Report (+ Order) translations |
| `php artisan translation:refresh-cache --sync` | Sync all module lang files |
| `php artisan view:clear` | Clear compiled Blade views |

---

## Admin access

Default path (adjust for your `APP_URL`):

| Area | URL |
|------|-----|
| Admin login | `/admin/login` |
| Orders | `/admin/orders` |
| Reports | `/admin/reports` |

Archived orders: **Orders** → **Show archived** → open order or use row actions.

---

## Project structure (high level)

```
app/                 # AestheticCart core (install, license, middleware)
modules/             # Feature modules (Order, Report, Product, Loyalty, …)
public/              # Web root (index.php, built assets after npm run build)
resources/           # Global views / lang
storage/             # Logs, cache, uploads (not in git for user content)
docs/                # Internal feature notes (CHIP, loyalty, reservations, …)
```

Custom work in this repo is mainly under:

- `modules/Order/` — admin orders UI, archived list, force delete, customer badges  
- `modules/Report/` — report queries, exports, table UI  
- `modules/Payment/Services/PaymentMethodLabel.php` — payment labels for orders  

---

## Development

```bash
# Frontend watch
npm run dev

# Production assets
npm run build
```

Enable debug locally only:

```env
APP_DEBUG=true
APP_ENV=local
```

See also: `docs/SECURITY.md`, `docs/PERFORMANCE.md`.

---

## Git workflow

```bash
git checkout main
git pull origin main

# after changes
git add .
git commit -m "Describe your change"
git push origin main
```

Use a feature branch for larger work:

```bash
git checkout -b feature/your-feature-name
git push -u origin feature/your-feature-name
```

---

## Security notes

- **Never commit** `.env`, API keys, or GitHub tokens  
- Keep this repository **private**  
- Rotate credentials if they were ever shared in chat or logs  
- Production: `APP_DEBUG=false`, HTTPS, strong DB passwords  

---

## Documentation

| File | Topic |
|------|--------|
| [docs/PAYMENT_CHIP.md](docs/PAYMENT_CHIP.md) | CHIP payment channels & settings |
| [docs/TREATMENT_RESERVATION.md](docs/TREATMENT_RESERVATION.md) | Beautician bookings |
| [docs/LOYALTY_MEMBERSHIP.md](docs/LOYALTY_MEMBERSHIP.md) | Loyalty tiers & points |
| [docs/SPECIALGIFT.md](docs/SPECIALGIFT.md) | Gift vouchers |
| [docs/SECURITY.md](docs/SECURITY.md) | Security headers |
| [docs/PERFORMANCE.md](docs/PERFORMANCE.md) | Caching & CDN hints |

---

## License & ownership

**BeautySpa** and the customizations in this repository are developed and maintained by:

| | |
|---|---|
| **Author** | Bewj Rayyan ([@bewjrayyan](https://github.com/bewjrayyan)) |
| **Company** | MediaCreative Solution |

This is a **private** codebase for the BeautySpa business. Unauthorized copying, distribution, or use outside MediaCreative Solution / Bewj Rayyan is not permitted unless agreed in writing.

For support or changes, contact MediaCreative Solution or open an issue on this private repository.
