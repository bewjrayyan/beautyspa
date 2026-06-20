# AestheticCart — Modul Tempahan Rawatan (Treatment Reservation)

**Versi dokumen:** 1.1  
**Tarikh:** 2026-05-21  
**Status implementasi:** Fasa 1–6B lengkap — production notes, storefront My Booking links.

---

## 1. Ringkasan

Modul `TreatmentReservation` mengurus tempahan rawatan maya (virtual products) dari checkout → `treatment_bookings` → admin kalendar/kanban, portal beautician, dan self-service pelanggan.

| Komponen | URL / Akses |
|----------|-------------|
| Admin tempahan | `/en/admin/treatment-reservations` |
| Portal beautician | `/en/admin/my/job-sheet` |
| Akaun portal | `/en/admin/my/account` |
| Log masuk beautician | `/en/beautician/login` → WhatsApp OTP |
| Self-service pelanggan | `/en/my-appointments` |
| iCal feed beautician | `/en/calendar/beautician/{id}/{token}.ics` |

---

## 2. Aliran data

```
Checkout (virtual product + beautician + slot)
    → Order (beautician_id, appointment_date, appointment_time)
        → BookingSyncService → treatment_bookings
            → Admin UI / Portal / Reminders / Reports
```

**Status tempahan:** `pending` → `in_progress` → `completed` | `canceled`

Sinkron dengan status pesanan melalui `OrderTreatmentBookingObserver` dan `SyncTreatmentBookingFromOrder`.

---

## 3. Perintah Artisan & Scheduler

| Perintah | Kekerapan | Fungsi |
|----------|-----------|--------|
| `treatment-reservation:sync-orders` | Manual | Sync semua pesanan → tempahan |
| `treatment-reservations:send-appointment-reminders` | Setiap 15 min | Peringatan beautician |
| `treatment-reservations:send-customer-appointment-reminders` | Setiap 15 min | Peringatan pelanggan |
| `treatment-reservations:send-customer-followups` | Harian 10:00 | Susulan selepas rawatan selesai |

**Cron server:** pastikan `* * * * * php artisan schedule:run` aktif.

---

## 4. Tetapan WhatsApp (Settings → General → WhatsApp Notifications)

| Kunci | Fungsi |
|-------|--------|
| `onesender_*` | API OneSender |
| `whatsapp_customer_reminder_*` | Peringatan pra-temujanji (pelanggan) |
| `whatsapp_beautician_*` | Tempahan baharu + peringatan (beautician) |
| `whatsapp_customer_completed_*` | Terima kasih bila status = completed |
| `whatsapp_customer_followup_*` | Susulan N hari selepas temujanji |
| `whatsapp_order_tracking_url` | Pautan jejak pesanan dalam mesej |

---

## 5. Fasa 5 — Ciri baharu

### 5A — Laporan admin
- Tab **Reports** di Treatment Reservations
- Penapis tarikh + beautician, ringkasan statistik, **Export CSV**

### 5B — Self-service pelanggan
- `/my-appointments` — OTP WhatsApp, lihat temujanji akan datang
- Batalkan / jadual semula (kemas kini tempahan + pesanan)

### 5C — iCal beautician
- URL langganan di **My Account** portal beautician
- Token HMAC — jangan kongsi URL secara terbuka

### 5D — Notifikasi lanjutan
- WA terima kasih bila rawatan ditandakan **completed**
- WA susulan harian (lalai 7 hari selepas temujanji)

### 5E — QA checklist

#### Checkout → tempahan
- [ ] Checkout rawatan maya dengan beautician + slot
- [ ] Pesanan muncul di admin kalendar/kanban
- [ ] Tempahan sync ke `treatment_bookings`

#### Portal beautician
- [ ] Log masuk WhatsApp OTP di `/beautician/login`
- [ ] Job sheet: drag status, nota, klik temujanji hari ini
- [ ] My Account: tukar kata laluan, salin URL iCal

#### WhatsApp
- [ ] OneSender dikonfigurasi
- [ ] Peringatan beautician + pelanggan (uji command manual)
- [ ] Terima kasih completed + susulan (toggle dalam settings)

#### Self-service pelanggan
- [ ] `/my-appointments` — OTP, lihat tempahan
- [ ] Batalkan / jadual semula

#### Laporan
- [ ] Tab Reports — penapis + export CSV

#### Locale
- [ ] `/ms/admin/my/job-sheet` — teks BM
- [ ] Mobile portal ≤768px

---

## 6. Migrasi penting

| Fail | Kolum |
|------|-------|
| `2026_05_21_000002` | `beautician_notes`, `reminder_sent_at` |
| `2026_05_21_000003` | `treatment_booking_activities` |
| `2026_05_21_000004` | `customer_reminder_sent_at` |
| `2026_05_21_000005` | `completed_notification_sent_at`, `followup_sent_at` |

Jalankan: `php artisan migrate`

---

## 7. Ujian manual pantas

```bash
php artisan treatment-reservations:send-appointment-reminders
php artisan treatment-reservations:send-customer-appointment-reminders
php artisan treatment-reservations:send-customer-followups
php artisan treatment-reservation:sync-orders
```

---

## 8. Fail utama

| Kawasan | Path |
|---------|------|
| Admin controller | `Http/Controllers/Admin/ReservationController.php` |
| Portal | `Http/Controllers/Admin/PortalController.php` |
| Self-service | `Http/Controllers/BookingSelfServiceController.php` |
| iCal | `Http/Controllers/CalendarFeedController.php` |
| Laporan | `Services/TreatmentBookingsReportService.php` |
| Reminder | `Services/CustomerAppointmentReminderService.php` |
| Completed / follow-up | `Services/CustomerCompletedNotificationService.php`, `CustomerFollowUpNotificationService.php` |
| Docs | `docs/TREATMENT_RESERVATION.md` |

---

## 9. Production launch (Fasa 6A)

### 9.1 `.env` production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
```

### 9.2 Cron (wajib)

```cron
* * * * * cd /path/to/fleetcart && php artisan schedule:run >> /dev/null 2>&1
```

Perintah berjadual: peringatan beautician + pelanggan (15 min), susulan pelanggan (10:00 harian).

### 9.3 Semak sebelum go-live

- [ ] OneSender API dikonfigurasi — uji OTP + reminder manual
- [ ] Settings WhatsApp (reminder, completed, follow-up)
- [ ] `php artisan migrate` + `php artisan treatment-reservation:sync-orders`
- [ ] Portal beautician + self-service `/my-appointments`
- [ ] Footer / top nav / checkout complete — pautan Temujanji Saya
- [ ] Backup DB + `.env`

### 9.4 Selepas deploy

```bash
php artisan config:cache
php artisan view:cache
npm run build
```

---

## 10. Storefront UX (Fasa 6B)

| Lokasi | Pautan |
|--------|--------|
| Footer → My Account | Temujanji Saya |
| Top navigation | Temujanji Saya |
| Sidebar → More | Temujanji Saya |
| Checkout complete (rawatan) | Urus temujanji saya |

---

## 11. Fasa 6C — Laporan PDF

- Tab **Reports** → butang **Print / Save PDF**
- Route: `GET /admin/treatment-reservations/export/pdf?from=&to=&beautician_id=`
- Paparan cetak dengan ringkasan, prestasi beautician (payroll), senarai tempahan
- Simpan sebagai PDF melalui **Print → Save as PDF** dalam browser

---

## 12. Fasa 6D — Slot & ketersediaan beautician

### Portal beautician
- **`/admin/my/availability`** — waktu kerja mingguan + sekatan masa
- Lalai: Isn–Sab 10:00–18:00 (slot 1 jam)

### Self-service pelanggan
- Jadual semula: pilih tarikh → dropdown slot available sahaja
- API: `GET /my-appointments/{id}/slots?date=YYYY-MM-DD` (selepas OTP)

### Jadual DB
- `beautician_working_hours` — waktu kerja mingguan
- `beautician_blocked_times` — tarikh/masa disekat

---

## 13. Fasa 6D+ — Slot checkout

### Checkout storefront
- Masa temujanji: dropdown slot available (gantikan flatpickr masa bebas)
- API awam: `GET /availability/beautician/{id}/slots?date=YYYY-MM-DD`
- Validasi server: `ValidBeauticianSlot` dalam `StoreOrderRequest` (slot mesti available)

### Ujian pantas
1. Checkout produk rawatan virtual → pilih beautician + tarikh
2. Dropdown masa hanya papar slot available mengikut waktu kerja / sekatan
3. Cuba tempah slot yang sudah penuh → validation error

---

## 14. Fasa 6E — Analytics dashboard

### Dashboard admin
- Tab **Dashboard** → statistik 30 hari: hasil, kadar selesai, pemenuhan, no-show
- Carta: trend hasil, breakdown status, hasil mengikut beautician (Chart.js)

### Metrik
- **Completion rate** — selesai / tempahan aktif dalam tempoh
- **Fulfillment rate** — temujanji lepas selesai / semua temujanji lepas aktif
- **No-show rate** — temujanji lepas masih pending/in progress

### Fail utama
- `Services/TreatmentReservationAnalyticsService.php`
- `Resources/assets/admin/js/analytics.js`
- `Resources/views/admin/reservations/partials/analytics.blade.php`
