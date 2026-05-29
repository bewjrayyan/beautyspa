# AestheticCart Performance Guide

Panduan ringkas untuk mempercepat storefront dan mengurangkan beban database.

## Perubahan terbaru dalam projek

| Item | Kesan |
|------|--------|
| `APP_CACHE=true` | Cache tetap untuk settings, menu, slider, kategori, banner (sebelum ini cache hilang setiap request) |
| Wishlist count | `count()` SQL, bukan `get()->count()` |
| Blog home | Eager load `files` (elak N+1 imej) |
| Slider | Eager load `slides.file` |
| `cdn_url()` + `MEDIA_CDN_URL` | Gambar/media boleh dihantar melalui CDN |
| Hero LCP | `fetchpriority="high"` pada slide pertama |
| Lazy load | Sudah digunakan pada kebanyakan `<img>` produk/banner |
| WebP + responsive widths | Upload media → WebP + variant 480w/960w (`ImageOptimizationService`) |
| Product `srcset` | `File::srcset` + product cards Alpine |
| CMS page HTML cache | `CacheStaticResponse` middleware (opt-in `RESPONSE_CACHE_ENABLED`) |
| `CACHE_DRIVER` | `CoreServiceProvider` menghormati Redis/file dari `.env` |
| Queue tables | Migration `jobs` / `failed_jobs` untuk `QUEUE_DRIVER=database` |

## 0. XAMPP / macOS: storage permissions & `Unable to set visibility for file cache`

AestheticCart uses `swayok/alternative-laravel-cache` (Flysystem). Tagged cache files live under `storage/framework/cache/local-data/cache/` (pool folder name is literally `cache`).

Flysystem calls `chmod` on that folder when it already exists. On macOS, **only the file owner** can chmod — Apache fails on folders created by your CLI user even when mode is `777`. **In code (local):** `XamppPermissiveLocalFilesystemAdapter` ignores failed visibility updates; `TranslationLoader` falls back to uncached translations if cache still fails.

If **Apache** (`daemon`) created those files and you run **`php artisan`** as your Mac user, you may see:

`League\Flysystem\UnableToSetVisibility: Unable to set visibility for file cache`

**Already in code:** Artisan uses a separate path: `storage/framework/cache/cli-data` (see `CoreServiceProvider::setupAppCacheDriver`).

**Automatic (local):** `bootstrap/xampp-compat.php` runs from `public/index.php` before Laravel. If Apache cannot write under `storage/`, it falls back to `/tmp`:

| Purpose | Preferred path | Fallback |
|---------|----------------|----------|
| Logs | `storage/logs/laravel.log` | `/tmp/fleetcart-logs/laravel.log` |
| File cache | `storage/framework/cache/local-data` | `/tmp/fleetcart-cache` |
| Sessions | `storage/framework/sessions` | `/tmp/fleetcart-sessions` |
| Compiled views | `storage/framework/views` | `/tmp/fleetcart-views` |

`AppServiceProvider` applies `FLEETCART_*_PATH` env vars to logging, cache, `session.files`, and `view.compiled`.

**One-time fix (recommended):**

```bash
chmod 777 storage/logs storage/framework/sessions storage/framework/views
chmod 666 storage/logs/laravel.log
chmod -R 777 storage/framework/cache/local-data
```

Or shared ownership:

```bash
sudo chown -R "$(whoami):staff" /Applications/XAMPP/xamppfiles/htdocs/fleetcart/storage
chmod -R 775 storage
```

## 1. Environment (wajib production)

```env
APP_ENV=production
APP_DEBUG=false
APP_CACHE=true
CACHE_DRIVER=file
```

Untuk trafik tinggi, guna **Redis**:

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
```

Selepas ubah `.env`:

```bash
php artisan config:clear
php artisan cache:clear
php artisan optimize
```

## 2. CDN (Cloudflare, Bunny, AWS CloudFront)

1. Letakkan domain CDN di hadapan `APP_URL` untuk folder `/storage` (dan optional `/build`).
2. Set dalam `.env`:

```env
MEDIA_CDN_URL=https://cdn.yourdomain.com
ASSET_CDN_URL=https://cdn.yourdomain.com
```

3. Semua `File::path` dan URL media akan ditulis semula automatik melalui `cdn_url()`.

**Cloudflare tip:** aktifkan Brotli, HTTP/3, cache static assets, polish images.

## 3. Database

### Sudah ada dalam AestheticCart

- Settings: `Setting::allCached()` (perlu `APP_CACHE=true`)
- Menu / Mega menu: `Cache::tags` + `rememberForever`
- Kategori searchable, slider, banner, top brands: cached
- Search terms: cache 1 jam

### Best practice

1. **Eager loading** — sentiasa `with()` untuk relation yang dipaparkan dalam loop.
2. **Elak query dalam Blade** — pindah ke View Composer / controller.
3. **Index** — pastikan index pada `orders.customer_id`, `products.slug`, `order_products.order_id`.
4. **Production** — `php artisan optimize` + `config:cache` + `route:cache` (selepas deploy).
5. **Debug query** — local sahaja: `QUERY_DETECTOR_ENABLED=true` dalam `.env`.

### Contoh N+1 untuk elak

```php
// Buruk
foreach ($posts as $post) {
    $post->featured_image; // query setiap iterasi
}

// Baik
BlogPost::with('files')->get();
```

## 4. Frontend / storefront

| Teknik | Status projek |
|--------|----------------|
| Lazy load images | ✅ kebanyakan view |
| Vite build production | Jalankan `npm run build` sebelum deploy |
| Font preload | ✅ Google Fonts preconnect |
| Hero LCP | ✅ fetchpriority pada slide pertama |
| Defer JS | Alpine/Vite modules (default module) |

**Jangan** lazy-load logo header atau hero slide pertama (LCP).

### WebP & srcset (upload baharu)

Imej JPEG/PNG yang dimuat naik melalui admin media akan:

1. Dijana WebP (optional ganti fail asal)
2. Dijana variant lebar `480` dan `960` (lalai)
3. Disimpan dalam `files.responsive_paths` dan diekspos sebagai `srcset` dalam API/Alpine

```env
IMAGE_WEBP_ENABLED=true
IMAGE_REPLACE_WITH_WEBP=true
IMAGE_RESPONSIVE_WIDTHS=480,960
```

Imej lama tidak dijana semula automatik — muat naik semula atau jalankan skrip batch jika perlu.

### CMS full-page cache (guest)

```env
RESPONSE_CACHE_ENABLED=true
RESPONSE_CACHE_TTL_MINUTES=60
```

Cache dikosongkan apabila halaman disimpan/dipadam di admin. Header `X-Response-Cache: HIT|MISS` untuk debug.

### Queue (e-mel pesanan, dll.)

```bash
php artisan migrate
php artisan queue:work --tries=3
```

```env
QUEUE_DRIVER=database
```

### Cadangan seterusnya

- Critical CSS inline untuk above-the-fold (advanced)
- Batch re-process media sedia ada ke WebP

## 5. PHP / Laravel

```bash
# Production deploy
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize
php artisan view:cache
```

Enable **OPcache** dalam `php.ini` (production):

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
```

## 6. Admin panel

- `APP_CACHE=true` juga cache terjemahan bila tidak debug.
- Clear cache selepas ubah settings: **Tools → Clear Cache** (perlu permission settings).

## 7. Monitoring

- **Laravel Telescope** / **Clockwork** — local profiling
- **MySQL slow query log** — query > 1s
- **Google PageSpeed Insights** — LCP, CLS, TTFB
- **Cloudflare analytics** — jika guna CDN

## 8. Checklist deploy pantas

- [ ] `APP_CACHE=true`
- [ ] `APP_DEBUG=false`
- [ ] `npm run build` + assets di CDN atau cache browser
- [ ] Redis (optional, `CACHE_DRIVER=redis`)
- [ ] `RESPONSE_CACHE_ENABLED=true` untuk FAQ/legal (production)
- [ ] `php artisan queue:work` jika `QUEUE_DRIVER=database`
- [ ] OPcache enabled
- [ ] `php artisan optimize`
- [ ] CDN untuk `/storage` media
- [ ] MySQL backup + index review

## Related files

- `config/performance.php` — CDN config
- `config/app.php` — `APP_CACHE`
- `modules/Support/helpers.php` — `cdn_url()`
- `modules/Storefront/Http/ViewComposers/LayoutComposer.php` — layout data
