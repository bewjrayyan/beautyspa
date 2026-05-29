# AestheticCart — Modul Membership & Loyalty Point (Imma Serilaris)

**Versi dokumen:** 1.0  
**Tarikh:** 2026-05-21  
**Status implementasi:** Fasa 1–4 lengkap — earn/redeem, tier, checkout, WhatsApp, birthday, referral, laporan, BM, kupon.

---

## 1. Ringkasan

Program ini menggabungkan **tier membership** (Silver / Gold / Platinum) dengan **wallet mata ganjaran** yang boleh ditebus semasa checkout. Reka bentuk mengikut best practice program spa & retail (Sephora Beauty Insider, Starbucks Rewards) dan diselaraskan dengan stack AestheticCart sedia ada: `User`, `Order`, `Coupon`, `Checkout`, `Setting`, WhatsApp OTP.

| Keputusan produk | Nilai lalai (MVP) |
|------------------|-------------------|
| Mata per RM belanja (base) | 1 mata / RM1 (selepas diskaun kupon) |
| Nilai tebusan | 1 mata = RM 0.10 |
| Had tebusan checkout | Maks. 30% nilai cart (belum wire penuh checkout — Fasa 2) |
| Earn trigger | Order status = `completed` sahaja |
| Mata per produk | Bonus tetap per unit + pengganda earn per produk (admin → Produk) |
| Clawback | Order `canceled` / `refunded` selepas earn |
| Tamat tempoh mata | 12 bulan dari tarikh earn (job `loyalty:expire-points`) |
| Tier rolling window | 365 hari (lifetime spend pada wallet) |

---

## 2. Prinsip reka bentuk

1. **Tier ≠ baki mata** — tier berdasarkan spend/eligibility; mata disimpan dalam wallet berasingan.
2. **Ledger immutable** — setiap perubahan baki direkod dalam `loyalty_transactions`; baki wallet = cache terkini.
3. **Idempotensi** — setiap earn/clawback/redeem guna `reference_type` + `reference_id` unik (contoh: `order:123:earn`).
4. **Earn selepas bayar** — elak fraud dan kerumitan refund sebelum selesai.
5. **Modul berasingan** — `modules/Loyalty`, tidak membebankan `User` atau `Order` dengan logik bisnes berat.

---

## 3. Seni bina modul

```
modules/Loyalty/
├── Config/config.php          # Lalai perniagaan
├── Config/permissions.php
├── Database/Migrations/
├── Database/Seeders/LoyaltyDatabaseSeeder.php
├── Entities/
│   ├── LoyaltyTier.php
│   ├── LoyaltyWallet.php
│   ├── LoyaltyTransaction.php
│   ├── LoyaltyTierHistory.php
│   └── LoyaltyRedemptionHold.php
├── Services/
│   ├── LoyaltyConfig.php
│   ├── LoyaltyWalletService.php
│   ├── LoyaltyEarnService.php
│   ├── LoyaltyTierService.php
│   └── LoyaltyRedemptionService.php
├── Listeners/
│   ├── ProcessLoyaltyOnOrderStatusChanged.php
│   └── CreateWalletOnCustomerRegistered.php
├── Http/Controllers/
│   ├── Admin/TierController.php
│   ├── Admin/MemberController.php
│   └── AccountLoyaltyController.php
└── Providers/LoyaltyServiceProvider.php
```

### Integrasi AestheticCart

| Modul | Integrasi |
|-------|-----------|
| **Order** | `OrderStatusChanged` → earn / clawback |
| **User** | `CustomerRegistered` → cipta wallet + tier Silver |
| **Coupon** | Redeem loyalty tidak boleh campur kupon tertentu (Fasa 2) |
| **Checkout** | Quote/hold/capture mata (Fasa 2) |
| **Setting** | Tab admin untuk override config (Fasa 2) |
| **WhatsApp** | Notifikasi tier naik / mata hampir luput (Fasa 3) |

---

## 4. Skema pangkalan data

### 4.1 `loyalty_tiers`

| Kolum | Jenis | Keterangan |
|-------|-------|------------|
| id | increments | PK |
| slug | string unique | `silver`, `gold`, `platinum` |
| name | string | Paparan |
| min_lifetime_spend | decimal | Ambang naik tier (RM) |
| earn_multiplier | decimal | Pengganda earn (1.0, 1.25, 1.5) |
| benefits | json nullable | Senarai faedah untuk UI |
| sort_order | int | Susunan paparan |
| is_active | boolean | |

### 4.2 `loyalty_wallets`

| Kolum | Jenis | Keterangan |
|-------|-------|------------|
| id | increments | PK |
| user_id | int unique | FK → users |
| tier_id | int | Tier semasa |
| balance | int | Baki mata |
| lifetime_spend | decimal | Jumlah perbelanjaan terkumpul (pesanan selesai) |
| tier_assigned_at | timestamp | |

### 4.3 `loyalty_transactions` (ledger)

| Kolum | Jenis | Keterangan |
|-------|-------|------------|
| id | bigIncrements | PK |
| wallet_id | int | |
| type | string | `earn`, `redeem`, `adjust`, `expire`, `clawback`, `bonus` |
| points | int | Positif = masuk, negatif = keluar |
| balance_after | int | Baki selepas transaksi |
| reference_type | string nullable | `order`, `admin`, `hold`, dll. |
| reference_id | string nullable | Idempotency key |
| description | string nullable | |
| meta | json nullable | Order id, admin user, dll. |
| expires_at | timestamp nullable | Tamat mata (untuk earn) |
| created_at | timestamp | |

**Indeks unik:** `(wallet_id, reference_type, reference_id, type)` — elak double earn.

### 4.4 `loyalty_tier_history`

Log setiap perubahan tier: `user_id`, `from_tier_id`, `to_tier_id`, `reason`, `created_at`.

### 4.5 `loyalty_redemption_holds`

Hold sementara semasa checkout: `user_id`, `cart_id`/`session_id`, `points`, `discount_amount`, `expires_at`.

### 4.6 Kolum tambahan `orders`

| Kolum | Keterangan |
|-------|------------|
| loyalty_points_earned | Mata diperoleh order ini |
| loyalty_points_redeemed | Mata ditebus |
| loyalty_discount_amount | RM diskaun dari mata |

---

## 5. Logik perniagaan

### 5.1 Earn (order completed)

```
eligible_rm = max(0, order.sub_total - order.discount - order.loyalty_discount_amount)
base_points = floor(eligible_rm * earn_rate_per_rm)
points = floor(base_points * tier.earn_multiplier)
```

- Hanya jika `customer_id` wujud (pelanggan log masuk / akaun).
- Guest checkout: tiada earn (boleh ditambah “claim later” Fasa 3).
- Kemas kini `lifetime_spend` pada wallet.
- Panggil `LoyaltyTierService::evaluate()` untuk naik/turun tier.

### 5.2 Clawback (canceled / refunded)

Jika transaksi `order:{id}:earn` wujud dan belum di-clawback:

- Debit mata sama (atau baki tersedia jika kurang).
- Kurangkan `lifetime_spend` mengikut eligible asal.
- Re-evaluate tier.

### 5.3 Redeem (checkout — Fasa 2)

1. **Quote** — kira maks mata dari % cart & baki wallet.
2. **Hold** — kunci mata 15 minit (`loyalty_redemption_holds`).
3. **Capture** — pada order placed: debit wallet, set `loyalty_discount_amount`.
4. **Release** — jika checkout dibatalkan atau hold luput.

### 5.4 Tier

| Tier | Min lifetime spend (RM) | Pengganda earn |
|------|---------------------------|----------------|
| Silver | 0 | 1.0× |
| Gold | 2,000 | 1.25× |
| Platinum | 5,000 | 1.5× |

Naik tier automatik apabila `lifetime_spend >= min_lifetime_spend` tier tertinggi yang layak. Turun tier (grace) — Fasa 2: semak rolling 365 hari.

### 5.5 Tamat tempoh mata

Command: `php artisan loyalty:expire-points`

- Cari transaksi `earn` dengan `expires_at < now()` yang belum diproses expire.
- Cipta transaksi `expire` negatif sehingga baki mencukupi.

---

## 6. API & routes

### 6.1 Storefront (auth)

| Method | Route | Nama | Fungsi |
|--------|-------|------|--------|
| GET | `/account/loyalty` | `account.loyalty.index` | Tier, baki, nilai RM |
| GET | `/account/loyalty/transactions` | `account.loyalty.transactions` | Sejarah ledger |

### 6.2 Admin

| Route prefix | Fungsi |
|--------------|--------|
| `admin/loyalty/tiers` | CRUD tier |
| `admin/loyalty/members` | Senarai wallet, adjust manual |
| `admin/loyalty/members/{id}` | Detail + transaksi |

### 6.3 Checkout API (Fasa 2 — dilaksanakan)

| Method | Route | Nama |
|--------|-------|------|
| GET | `/cart/loyalty/quote` | `cart.loyalty.quote` |
| POST | `/cart/loyalty` | `cart.loyalty.store` |
| DELETE | `/cart/loyalty` | `cart.loyalty.destroy` |

---

## 7. Permissions admin

```
admin.loyalty.tiers.index|create|edit|destroy
admin.loyalty.members.index|show|adjust
```

Tambah ke role Admin melalui **Settings → Roles** atau seeder.

---

## 8. Konfigurasi

Fail: `modules/Loyalty/Config/config.php`

```php
'earn_rate_per_rm' => 1,
'point_value_rm' => 0.10,
'max_redeem_percent' => 30,
'points_expire_months' => 12,
'hold_minutes' => 15,
```

Override masa depan melalui `setting('loyalty_*')` dalam tab Setting (Fasa 2).

---

## 9. Roadmap

| Fasa | Minggu | Skop |
|------|--------|------|
| **1 MVP** | 1–2 | Modul, migrate, seed tier, earn/clawback, wallet on register, admin tier/members, halaman akaun |
| **2 Checkout** | 2–3 | Redeem quote/hold/capture, kolum order, Cart/Checkout integration |
| **3 Engagement** | 3–4 | ✅ WhatsApp, birthday bonus, referral, admin order loyalty |
| **4 Analytics** | 4–6 | ✅ Dashboard liability, laporan transaksi, BM, tetapan kupon+mata |

---

## 10. Ujian manual (MVP)

1. `php artisan module:enable Loyalty`
2. `php artisan module:migrate Loyalty`
3. `php artisan module:seed Loyalty`
4. Daftar pelanggan baru → semak `loyalty_wallets` wujud, tier Silver.
5. Cipta order untuk pelanggan → tandakan **Completed** → semak `loyalty_transactions` type `earn`.
6. Tukar status ke **Refunded** → semak `clawback`.
7. Admin → Loyalty → Members → Adjust +100 mata.
8. `/account/loyalty` — paparan baki & tier.

---

## 11. Mata bonus per produk

Setiap produk boleh dikonfigurasi di **Admin → Produk** (seksyen *Ganjaran kesetiaan*):

| Medan | Lalai | Kesan |
|-------|-------|--------|
| `loyalty_bonus_points` | 0 | Mata tambahan × kuantiti baris apabila pesanan `completed` |
| `loyalty_earn_multiplier` | 1 | Pengganda pada mata daripada belanja baris tersebut (tier multiplier masih dikenakan) |

**Formula earn (per baris pesanan):**

1. Agihkan jumlah layak pesanan (`sub_total − kupon − tebusan mata`) secara pro-rata mengikut `line_total`.
2. `mata_belanja = floor(line_layak × earn_rate × tier_multiplier × product_multiplier)`
3. `mata_bonus = loyalty_bonus_points × qty`
4. Jumlah pesanan = jumlah semua baris.

Nilai disimpan pada `orders.loyalty_points_earned`; clawback menggunakan nilai tersebut (bukan kira semula).

---

## 12. Rujukan kod utama

| Fail | Tanggungjawab |
|------|----------------|
| `ProcessLoyaltyOnOrderStatusChanged` | Earn / clawback pada perubahan status |
| `CreateWalletOnCustomerRegistered` | Wallet + Silver |
| `LoyaltyEarnService` | Kira mata & panggil wallet |
| `LoyaltyWalletService` | Credit/debit idempotent |
| `LoyaltyTierService` | Naik tier & history |
| `LoyaltyRedemptionService` | Hold/quote (asas untuk Fasa 2) |

---

## 13. Nota keselamatan & audit

- Semua adjust manual admin wajib `description` + `meta.admin_user_id`.
- Jangan benarkan redeem melebihi baki atau had % tanpa validasi server-side.
- Log tier history untuk pertikaian pelanggan.
- Backup berkala jadual `loyalty_transactions` (liability kewangan).

---

*Dokumen ini menjadi sumber rujukan rasmi untuk pembangunan modul Loyalty AestheticCart / Imma Serilaris.*
