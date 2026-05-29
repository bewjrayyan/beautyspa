# Send Gift Voucher (SpecialGift)

Standalone **send gift** flow like [immaserilaris.com/send-gift](https://immaserilaris.com/send-gift/) and the WordPress *Gift Voucher Sender* plugin: no gift products, no shop redirect, no checkout fields.

## Flow

1. Customer opens **`/send-gift`**.
2. Fills in: **Recipient name**, **Order number**, **WhatsApp number**, optional **Your name**.
3. On submit the app:
   - Validates the order exists in FleetCart (`orders.id`, with or without `#` prefix).
   - Generates a **personalised voucher JPEG** (name + order on admin-uploaded background).
   - Sends **WhatsApp image + caption** via **OneSender** (same API as order notifications).

Submissions are logged in **`gift_voucher_submissions`** (admin → Gift voucher submissions).

## Setup

1. Enable module **SpecialGift** in `modules.json` (if not already).
2. Run migrations: `php artisan module:migrate SpecialGift`
3. **Settings → Special gift**:
   - Enable send gift page
   - Upload **voucher background** image
   - Set **message template** (placeholders below)
4. **Settings → WhatsApp Notifications**: configure OneSender API URL + key.
5. Ensure `public/storage` is linked: `php artisan storage:link`
6. Grant **`admin.gift_voucher_submissions.index`** to admin roles.

Generated vouchers are stored under `storage/app/public/specialgift/generated/` and must be reachable by OneSender (public HTTPS URL).

## Message placeholders

| Placeholder | Meaning |
|-------------|---------|
| `{recipient_name}` | Recipient name from form |
| `{order_number}` | Order number entered |
| `{sender_name}` | Optional sender name |
| `{voucher_value}` | Reserved (empty) |

Caption is sent with the voucher image.

## Public URL

- Form: `/send-gift`
- Route names: `specialgift.send.create`, `specialgift.send.store`
