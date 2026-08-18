# Laporan Audit Performa — AestheticCart (FleetCart)

**Tanggal:** 1 Agustus 2026
**Target:** `http://localhost/fleetcart` (dev environment)
**Stack terukur:** Laravel 12.64.0 · PHP 8.4.24 (php-fpm) · MariaDB 10.4.28 · Apache 2.4.56 · 47 modul · 521 route

---

## 0. Ringkasan Eksekutif

Aplikasi ini **tidak lambat karena database besar** — datanya kecil (1.303 order,
1.796 user, 299 produk). Bottleneck-nya ada di **kode yang mengulang query yang sama
belasan kali per request**, dan di **rendering Blade**.

Temuan terbesar: satu query pengecekan role dieksekusi **13–15× per halaman admin**.
Perbaikannya ±10 baris kode dan sudah saya ukur hasilnya: **request handling 34–46%
lebih cepat, jumlah query turun 40–58%**.

Beberapa area ternyata **sudah sehat** dan tidak perlu disentuh: aset statis sudah
diminifikasi + gzip + cache 1 tahun, OPcache hit rate 99,76%, dan `.env.production.example`
sudah dikonfigurasi dengan benar.

| # | Masalah | Dampak | Effort | Status bukti |
|---|---------|--------|--------|--------------|
| 1 | N+1 `hasRoleName()` 13–15×/halaman | **Tinggi** | Rendah | Terukur, fix terverifikasi |
| 2 | Tabel `orders` tanpa index pada kolom filter | **Tinggi** (tumbuh) | Rendah | EXPLAIN: full scan |
| 3 | `require_once` mematikan route caching | Sedang | Sangat rendah | Terbukti eksperimen |
| 4 | Rendering Blade 130–144 ms di storefront | Sedang | Sedang | Terukur |
| 5 | 4 provider dev aktif tiap request | Rendah–Sedang | Rendah | Terverifikasi |
| 6 | `env()` di runtime memblokir `config:cache` | Rendah | Sedang | 11 lokasi |
| 7 | Redis dikonfigurasi tapi tidak aktif | Rendah (dev) | Rendah | Port 6379 closed |
| 8 | `innodb_buffer_pool_size` = 16 MB | Rendah (dev) | Rendah | Terverifikasi |

---

## 1. Metodologi

Semua angka di laporan ini hasil pengukuran, bukan estimasi teoretis.

1. **Profiler request kustom** (`public/_reqprofile.php`, sudah dihapus) — boot aplikasi,
   autentikasi sebagai admin, dispatch request nyata lewat php-fpm, mencatat wall time
   boot vs handle, waktu SQL, dan seluruh query log.
2. **Harness PHPUnit** (`tests/Feature/AdminPerformanceProfileTest.php`, sudah dihapus) —
   `actingAs($admin)` ke 10 route admin, mengelompokkan query berdasarkan *shape*
   untuk memunculkan pola N+1.
3. **Boot-phase profiler** — memisahkan biaya tiap tahap bootstrap Laravel.
4. **EXPLAIN** langsung ke MariaDB + `information_schema` untuk index & ukuran tabel.
5. **curl** n=8–12 per endpoint untuk avg/min/p50.

> **Catatan:** Laravel Debugbar dan Query Detector sudah terpasang di proyek, tapi
> keduanya dimatikan (`DEBUGBAR_ENABLED=false`, `QUERY_DETECTOR_ENABLED=false`).
> Saya memakai profiler kustom agar bisa mengukur **request admin terautentikasi**
> secara headless tanpa menyalakan overlay UI. Telescope tidak terpasang di proyek ini.

**Semua instrumentasi sudah dibersihkan** — `git diff` kosong, `bootstrap/cache/`
kembali ke isi semula.

---

## 2. Baseline Terukur

Waktu respons end-to-end (curl, n=12, cache hangat):

| Halaman | avg | min | p50 |
|---|---|---|---|
| `/admin/login` | 92 ms | 87 ms | 92 ms |
| `/` (storefront) | 88 ms | 85 ms | 86 ms |
| `/admin` (redirect chain) | 199 ms | — | — |

Pemecahan internal (profiler, admin terautentikasi, n=8):

| Halaman | handle | SQL | queries |
|---|---|---|---|
| `/admin` (dashboard) | 102,4 ms | 52,7 ms | 34 |
| `/admin/users` | 105,7 ms | 57,3 ms | 28 |
| `/admin/orders` | 58,5 ms | 28,9 ms | 23 |
| `/admin/products` | 42,7 ms | 16,8 ms | 19 |
| `/en` (storefront home) | 142,9 ms | 12,9 ms | 9 |
| `/en/products` | 187,2 ms | 43,1 ms | 13 |

Biaya boot framework: **28 ms** (106 service provider, 1.053 file, 4 MB peak).

---

## 3. Temuan Berdasarkan Dampak

### 🔴 Temuan #1 — N+1: `hasRoleName()` dieksekusi 13–15× per halaman admin

**Bukti terukur.** Satu *query shape* mendominasi tiap halaman admin:

```
x15  21,63 ms  select count(*) as aggregate from `roles`
                 inner join `user_roles` on `roles`.`id` = `user_roles`.`role_id`
                 where `user_roles`.`user_id` = ? and exists (
                   select * from `role_translations` ... )
```

Itu **15 dari 49 query** dashboard (31%) dan 19% dari total waktu SQL — hanya untuk
menjawab pertanyaan yang sama berulang kali: *"apakah user ini admin?"*

**Root cause.** [`modules/User/Entities/User.php:222`](modules/User/Entities/User.php:222):

```php
public function hasRoleName($name)
{
    return $this->roles()->whereTranslation('name', $name)->count() !== 0;
}
```

Tidak ada memoization. Setiap pemanggilan = 1 query JOIN + subquery `EXISTS`.
Method ini dipanggil secara tidak langsung dari banyak tempat dalam satu render:

| Pemanggil | File |
|---|---|
| `isBeauticianOnly()` → 2× `hasRoleName` | `User.php:150` |
| `isCustomer()` → 1× `hasRoleName` | `User.php:137` |
| `hasPendingBeauticianProfile()` | `User.php:164` |
| Sidebar extender (Admin) | `modules/Admin/Sidebar/SidebarExtender.php:21,33` |
| Sidebar extender (Translation) | `modules/Translation/Sidebar/SidebarExtender.php:18` |
| Sidebar extender (Support) ×2 | `modules/Support/Sidebar/SidebarExtender.php:18,30` |
| Layout body class | `modules/Admin/Resources/views/layout.blade.php:43` |
| Top nav ×2 | `modules/Admin/Resources/views/partials/top_nav.blade.php:3,43` |
| Sidebar logo | `modules/Admin/Resources/views/partials/sidebar.blade.php:2` |
| Middleware | `RestrictBeauticianPortalMiddleware.php:30,38` |
| Middleware | `AdminMiddleware.php:34` |

Setiap sidebar extender memanggil `isBeauticianOnly()` **per item menu** — itulah
sumber pengalinya.

**Solusi.** Memoize per request. Role user tidak berubah di tengah request.

```php
// modules/User/Entities/User.php

/** Memoisasi per-request untuk hasRoleName(). */
protected array $roleNameCache = [];

public function hasRoleName($name)
{
    if (array_key_exists($name, $this->roleNameCache)) {
        return $this->roleNameCache[$name];
    }

    return $this->roleNameCache[$name] =
        $this->roles()->whereTranslation('name', $name)->count() !== 0;
}
```

**Hasil terukur (saya terapkan, ukur n=8, lalu revert):**

| Halaman | handle sebelum | sesudah | Δ | query sebelum | sesudah | Δ |
|---|---|---|---|---|---|---|
| `/admin` | 102,4 ms | **66,4 ms** | **−35%** | 34 | 21,8 | −36% |
| `/admin/orders` | 58,5 ms | **36,8 ms** | **−37%** | 23 | 12 | −48% |
| `/admin/products` | 42,7 ms | **22,9 ms** | **−46%** | 19 | 8 | −58% |
| `/admin/users` | 105,7 ms | **70,2 ms** | **−34%** | 28 | 17 | −39% |

> Ini bukan estimasi — angka "sesudah" adalah hasil pengukuran nyata dengan patch
> terpasang. Patch sudah saya revert; repo bersih.

**Perbaikan lanjutan (opsional, dampak lebih besar lagi).** `hasRoleId()`
([`User.php:246`](modules/User/Entities/User.php:246)) punya pola identik. Lebih baik lagi:
eager-load relasi `roles` sekali saat autentikasi lalu evaluasi di memori, sehingga
`hasRoleName` + `hasRoleId` sama-sama jadi **0 query tambahan**.

---

### 🔴 Temuan #2 — `orders` tidak punya index pada kolom yang difilter

**Bukti terukur.** `EXPLAIN` untuk query dashboard:

```
type: ALL          possible_keys: NULL      key: NULL
rows: 1303         Extra: Using where; Using filesort
```

Full table scan + filesort, setiap kali dashboard dibuka.

**Root cause.** Index yang ada di `orders` hanya `customer_id`, `coupon_id`,
`spa_branch_id`. Padahal query dashboard memfilter/mengurutkan pada:

| Kolom | Dipakai untuk | Punya index? |
|---|---|---|
| `appointment_date` | `whereNotNull` + `whereDate` + `orderBy` | ❌ |
| `payment_status` | `whereIn(PENDING, PROCESSING)` | ❌ |
| `status` | `withoutCanceledOrders()` | ❌ |
| `created_at` | `latest()` | ❌ |
| `deleted_at` | soft delete | ❌ |

Query paling lambat di dashboard (7,27 ms) tepat yang memfilter `appointment_date`.

**Kenapa ini prioritas tinggi padahal cuma 7 ms?** Karena biayanya **linear terhadap
jumlah order**. Di 1.303 baris masih 7 ms; di 50.000 baris jadi ~270 ms untuk satu
query saja, dan dashboard menjalankan 4 query semacam itu.

**Solusi.** Migration baru:

```php
Schema::table('orders', function (Blueprint $table) {
    $table->index(['appointment_date', 'status'], 'orders_appointment_status_index');
    $table->index(['payment_status', 'status'], 'orders_payment_status_index');
    $table->index(['created_at'], 'orders_created_at_index');
    $table->index(['deleted_at'], 'orders_deleted_at_index');
});
```

**Estimasi.** Query dashboard terkait order dari full-scan → index range scan.
Pada data sekarang ±5–7 ms hemat; pada 50k order menghindari degradasi ~10–20×.

> ⚠️ `whereDate('appointment_date', today())` membungkus kolom dalam fungsi `DATE()`,
> sehingga **index tidak akan terpakai**. Ubah jadi range agar index efektif:
> ```php
> ->whereBetween('appointment_date', [today()->startOfDay(), today()->endOfDay()])
> ```
> Tanpa perubahan ini, index `appointment_date` hanya membantu sebagian.

---

### 🟠 Temuan #3 — `require_once` membuat route caching mustahil

**Bukti terbukti eksperimental.** Saya boot aplikasi 2× dalam satu proses PHP
(persis yang dilakukan `RouteCacheCommand::getFreshApplication()`):

```
boot #1 -> total routes=521   admin routes=372   'home' route=YES
boot #2 -> total routes=24    admin routes=0     'home' route=MISSING
```

Boot kedua kehilangan **497 dari 521 route**.

**Root cause.** [`modules/Core/Providers/RouteServiceProvider.php:99`](modules/Core/Providers/RouteServiceProvider.php:99)
(juga baris 134 dan 150):

```php
require_once $path;   // ← boot ke-2 mengembalikan false, route tidak terdaftar
```

`require_once` mencatat file yang sudah di-`require`. Saat aplikasi di-boot ulang
di proses yang sama, file route **tidak dieksekusi lagi** → router kosong.

Ini persis yang membuat tim menonaktifkan route caching di
[`app/Console/Commands/RouteCacheCommand.php:14`](app/Console/Commands/RouteCacheCommand.php:14) —
komentarnya sudah menggambarkan gejalanya dengan tepat, hanya penyebabnya yang belum
teridentifikasi.

**Solusi.** Ganti `require_once` → `require` di 3 lokasi. **Terverifikasi:**

```
boot #1 -> total routes=521   admin routes=372   'home' route=YES
boot #2 -> total routes=521   admin routes=372   'home' route=YES   ✅
```

**Estimasi — jujur: kecil.** Saya generate route cache secara manual lalu ukur:

| Kondisi | login avg |
|---|---|
| tanpa route cache | 92 ms |
| dengan route cache | 114 ms (**lebih lambat**) |

File cache-nya 770 KB untuk 521 route, dan biaya unserialize-nya melebihi
penghematan registrasi route. **Rekomendasi: tetap jangan pakai route caching.**

Nilai perbaikan `require_once` bukan pada kecepatan, tapi pada **membuka jalan**:
Laravel Octane, `php artisan optimize`, dan test yang mem-boot ulang aplikasi
semuanya saat ini rusak diam-diam karena ini. Perbaiki karena ini bug, bukan demi ms.

---

### 🟠 Temuan #4 — Rendering Blade adalah bottleneck storefront

**Bukti terukur.**

| Halaman | handle | SQL | **PHP** | ukuran HTML |
|---|---|---|---|---|
| `/en` | 142,9 ms | 12,9 ms (9 q) | **130,1 ms (91%)** | 236 KB |
| `/en/products` | 187,2 ms | 43,1 ms (13 q) | **144,1 ms (77%)** | 280 KB |

Database **bukan** masalahnya di sini — eager loading storefront sudah rapi
(hanya 9–13 query). Yang mahal adalah menyusun HTML 236–280 KB.

**Root cause.** Halaman menghasilkan HTML sangat besar. 236 KB untuk satu homepage
itu ±3–5× ukuran wajar; artinya banyak markup dirender di server yang mungkin tidak
langsung terlihat user (mega-menu penuh, semua varian produk, modal tersembunyi).

Ada juga N+1 ringan di `/en`: `select * from users where id = ?` **×5**.

**Solusi.**
1. **Aktifkan response cache yang sudah ada.** Proyek sudah punya infrastrukturnya
   ([`config/performance.php`](config/performance.php), `ClearPageResponseCache`,
   `ClearSettingCache`) tapi di `.env` masih dikomentari:
   ```dotenv
   RESPONSE_CACHE_ENABLED=true
   RESPONSE_CACHE_HOME_ENABLED=true
   RESPONSE_CACHE_TTL_MINUTES=60
   ```
   Untuk halaman anonim, ini memangkas ~130 ms PHP menjadi ~5 ms.
2. Cache fragment mega-menu/footer dengan `Cache::remember` — konten jarang berubah.
3. Telusuri N+1 `users` ×5 di homepage (kemungkinan review/testimonial) dan tambahkan
   `with('user')`.

**Estimasi.** Response cache: **−85–90%** untuk pengunjung anonim (142 ms → ~15 ms).
Cache fragment tanpa response cache: **−20–30%**.

---

### 🟡 Temuan #5 — 4 service provider dev aktif di setiap request

**Bukti terukur.** Provider yang ter-load saat runtime:

```
Barryvdh\Debugbar\ServiceProvider
BeyondCode\DumpServer\DumpServerServiceProvider
BeyondCode\QueryDetector\QueryDetectorServiceProvider
Spatie\LaravelIgnition\IgnitionServiceProvider
```

Total 106 provider (60 dari modul), `BootProviders` = 19,5 ms dari 28 ms boot.

**Root cause.** Paket-paket ini ada di `require-dev`, tapi auto-discovery Laravel tetap
mendaftarkannya selama tersedia di `vendor/`. Debugbar tetap mem-boot collector-nya
walau `DEBUGBAR_ENABLED=false`.

**Solusi.** Untuk **produksi** (di dev sebaiknya tetap dibiarkan — memang berguna):

```bash
composer install --no-dev --optimize-autoloader --classmap-authoritative
```

Atau blokir auto-discovery lewat `composer.json` → `extra.laravel.dont-discover`
(bagian ini sudah ada di proyek, tinggal ditambah).

**Estimasi.** −8–12 ms per request di produksi. Di dev: jangan diubah.

---

### 🟡 Temuan #6 — `env()` di kode runtime memblokir `config:cache`

**Bukti.** 11 pemanggilan `env()` di luar file config:

| File | Baris |
|---|---|
| `modules/Core/Providers/CoreServiceProvider.php` | 247, 331, 333, 334, 344, 345, 346, 347 |
| `modules/Core/Support/WritableStorageBootstrap.php` | 183, 184 |
| `modules/Support/Cache/CacheHealth.php` | 73, 74 |

Saat `config:cache` aktif, `.env` **tidak dibaca lagi** — `env()` mengembalikan `null`.
Akibatnya konfigurasi mail (host, username, password, encryption) dan level log akan
diam-diam jatuh ke default. Ini bug fungsional yang menunggu terjadi, bukan sekadar isu
performa.

**Solusi.** Pindahkan ke file config lalu baca dengan `config()`:

```php
// config/mail.php
'host' => env('MAIL_HOST', ''),

// CoreServiceProvider.php:331
$host = trim((string) (setting('mail_host') ?: config('mail.host', '')));
```

**Estimasi performa — jujur: nol.** Saya ukur `config:cache` aktif vs tidak:
**92 ms vs 92 ms**, tidak ada bedanya. Perbaiki ini demi **keamanan konfigurasi**,
bukan kecepatan.

---

### 🟡 Temuan #7 — Redis dikonfigurasi tapi tidak berjalan

**Bukti.**

```
REDIS_HOST=127.0.0.1  REDIS_PORT=6379   (di .env)
→ port 6379 CLOSED
→ ekstensi php redis: tidak terpasang
→ CACHE_DRIVER=file, SESSION_DRIVER=file  (yang benar-benar dipakai)
```

`predis/predis` **sudah terpasang** di vendor, jadi Redis bisa dipakai lewat TCP
tanpa ekstensi PHP — tinggal jalankan server-nya.

Cache, session, dan compiled view semuanya di `/tmp`
(`/tmp/fleetcart-cache`, `/tmp/fleetcart-sessions`, `/tmp/fleetcart-views` — 5,1 MB,
812 view). Di macOS `/tmp` dibersihkan saat reboot, jadi cache hilang tiap restart.

**Solusi (produksi).** `.env.production.example` **sudah benar**
(`CACHE_DRIVER=redis`, `SESSION_DRIVER=redis`, `QUEUE_DRIVER=database`) — pastikan
saja server produksi memakainya dan Redis benar-benar hidup.

**Untuk dev:** file cache sudah memadai. Yang perlu diubah hanya memindahkan path
keluar dari `/tmp` supaya cache tidak hilang tiap reboot.

**Estimasi.** Di dev: dapat diabaikan (dataset kecil, file cache cukup cepat).
Di produksi multi-worker: signifikan, tapi templatenya sudah benar.

---

### 🟡 Temuan #8 — Tuning MariaDB & PHP

**Bukti terukur:**

| Setting | Nilai sekarang | Rekomendasi | Catatan |
|---|---|---|---|
| `innodb_buffer_pool_size` | **16 MB** | 256 MB+ | Default XAMPP; DB ~6 MB jadi masih muat, tapi terlalu kecil untuk tumbuh |
| `slow_query_log` | **OFF** | ON | Tanpa ini regresi tidak terdeteksi |
| `long_query_time` | 10 s | 0.5 s | 10 s praktis tidak pernah kena |
| `query_cache_type` | OFF | biarkan OFF | Benar — deprecated & jadi bottleneck di MariaDB |
| `opcache.interned_strings_buffer` | **8 MB** | 32 MB | Rendah untuk app 47 modul |
| `opcache.jit` | `disable` | biarkan | Tapi `jit_buffer_size=64MB` teralokasi sia-sia → set 0 |
| `opcache.memory_consumption` | 128 MB | cukup | 77,8 MB terpakai, 49,2 MB bebas |
| `opcache.validate_timestamps` | On, freq 2 | Off di **produksi** | Benar untuk dev |
| `memory_limit` | 128 MB | cukup | Peak terukur hanya 12 MB |
| `max_execution_time` | 30 s | cukup | — |

**OPcache sehat:** hit rate **99,76%**, 3.347 script ter-cache, 0 restart OOM.
Ini tidak perlu disentuh.

---

## 4. Area yang Sudah Sehat — Jangan Diubah

Audit ini juga mengkonfirmasi beberapa hal sudah benar. Penting untuk tidak
"memperbaiki" yang tidak rusak:

| Area | Temuan |
|---|---|
| **Minifikasi aset** | ✅ Semua CSS/JS ter-build 1 baris (terminifikasi) |
| **Kompresi** | ✅ `Content-Encoding: gzip` aktif — CSS 427 KB → **132 KB** |
| **Cache header aset** | ✅ `Cache-Control: max-age=31536000, public` + ETag |
| **Cache busting** | ✅ Hash + versi di nama file (`main-5oUj5Ry2-v4.7.71.css`) |
| **OPcache** | ✅ hit rate 99,76%, tanpa restart OOM |
| **Blade compiled cache** | ✅ 812 view terkompilasi, otomatis |
| **DashboardController** | ✅ Sudah rapi — `Cache::remember` 5 menit untuk statistik, `select()` kolom spesifik, `with(['user','tier'])` |
| **Eager loading storefront** | ✅ Hanya 9–13 query untuk halaman kompleks |
| **Cache settings** | ✅ `Setting::allCached()` |
| **Autoloader** | ✅ `optimize-autoloader: true`, 19.325 entri classmap |
| **Template produksi** | ✅ `.env.production.example` sudah benar |
| **Index tabel** | ✅ Hanya `migrations` yang tanpa index sekunder (tidak masalah) |

**Catatan tentang bundel CSS.** `main-5oUj5Ry2.css` 427 KB → 132 KB gzip, rasio hanya
3,2× (bandingkan `app-BSyUPsky.css`: 319 KB → 42 KB, 7,6×). Rasio rendah biasanya
menandakan **aset base64 yang di-embed** di dalam CSS. Layak dicek, tapi prioritas
rendah karena cache 1 tahun sudah menutupi biayanya untuk repeat visitor.

---

## 5. Rencana Implementasi Berdasarkan Prioritas

### Fase 1 — Quick win (±30 menit, dampak terbesar)

1. **Memoize `hasRoleName()`** — Temuan #1
   *Terukur: −34–46% waktu handle admin, −40–58% query.*
2. **Tambah index `orders`** — Temuan #2
   *Ganti juga `whereDate()` → `whereBetween()` agar index terpakai.*
3. **`require_once` → `require`** di `RouteServiceProvider` (3 baris) — Temuan #3
   *Memperbaiki bug boot ulang; jangan aktifkan route caching.*

### Fase 2 — Storefront (±2–3 jam)

4. Aktifkan `RESPONSE_CACHE_ENABLED=true` — Temuan #4
   *Estimasi −85–90% untuk halaman anonim.*
5. Perbaiki N+1 `users` ×5 di homepage.
6. Cache fragment mega-menu & footer.

### Fase 3 — Higienis produksi (±1–2 jam)

7. Pindahkan 11 `env()` runtime ke `config()` — Temuan #6 *(keamanan config, bukan kecepatan)*
8. `composer install --no-dev --classmap-authoritative` untuk produksi — Temuan #5
9. Pastikan Redis hidup di produksi — Temuan #7
10. Tuning MariaDB/OPcache: `innodb_buffer_pool_size=256M`,
    `slow_query_log=ON`, `long_query_time=0.5`,
    `opcache.interned_strings_buffer=32`, `opcache.jit_buffer_size=0` — Temuan #8

### Fase 4 — Monitoring berkelanjutan

11. Aktifkan `QUERY_DETECTOR_ENABLED=true` di dev (sudah terpasang) agar N+1 baru
    ketahuan saat development, bukan saat audit.
12. Aktifkan slow query log dan review berkala.

---

## 6. Estimasi Gabungan

**Panel admin (Fase 1):**

| | sekarang | setelah Fase 1 |
|---|---|---|
| `/admin` handle | 102 ms | **~66 ms** (terukur) |
| `/admin/products` handle | 43 ms | **~23 ms** (terukur) |
| query per halaman | 19–34 | **8–22** (terukur) |

**Storefront (Fase 2):** 142 ms → **~15 ms** untuk pengunjung anonim (estimasi, belum diukur).

**Produksi (Fase 3):** tambahan −8–12 ms per request dari eliminasi provider dev.

---

## 7. Catatan Kejujuran & Batasan

Beberapa hal yang perlu disampaikan apa adanya:

- **Route caching dan config caching tidak membantu.** Saya mengukur keduanya secara
  langsung: route cache justru **memperlambat** (92 → 114 ms), config cache **tidak
  berpengaruh** (92 → 92 ms). Saran umum "selalu jalankan `php artisan optimize`"
  tidak berlaku untuk proyek ini. Rekomendasi saya untuk kedua item tersebut
  dilandasi alasan **kebenaran/keamanan**, bukan performa.

- **Ukuran data masih kecil.** 1.303 order dan 299 produk. Temuan #2 (index) dampaknya
  hari ini kecil (±5–7 ms), tapi saya prioritaskan tinggi karena biayanya tumbuh linear.

- **Angka storefront Fase 2 belum diukur.** Berbeda dengan Fase 1 (yang saya patch,
  ukur, lalu revert), estimasi response cache belum diverifikasi eksperimental.

- **Variasi pengukuran.** Sampel tunggal sempat menunjukkan perbaikan −71% untuk
  Temuan #1; setelah n=8 angka sebenarnya **−35%**. Semua angka di laporan ini
  memakai n=8–12, bukan sampel tunggal.

- **Ini environment dev.** `APP_DEBUG=true`, `APP_ENV=local`, `QUEUE_DRIVER=sync`
  semuanya **wajar untuk dev** dan sengaja tidak saya laporkan sebagai masalah —
  `.env.production.example` sudah menanganinya dengan benar.

- **Halaman `/admin/settings`** menghasilkan HTML 648 KB (terbesar di aplikasi) dan
  memakan ~380 ms, hampir seluruhnya PHP. Belum saya telusuri sampai akar karena
  profiler mengembalikan 500 pada rute tersebut (kemungkinan bergantung state session).
  Layak diaudit terpisah.

- **PHP CLI mismatch.** CLI default (`/Applications/XAMPP/.../php` = 8.2.4) di bawah
  syarat `composer.json` (>= 8.3.1), sehingga `php artisan` gagal. Web server memakai
  8.4.24. Semua perintah artisan di audit ini memakai
  `/opt/homebrew/opt/php@8.4/bin/php`. Menyamakan keduanya akan menghemat kebingungan.

**Semua instrumentasi sudah dihapus dan seluruh patch eksperimental sudah di-revert.**
`git diff` kosong; hanya untracked file yang memang sudah ada sebelum audit.
