# AestheticCart Security Guide

This document summarizes security controls in this project and the checklist for production deployments.

## Implemented controls

### Payments

- Checkout completion verifies payments with gateway APIs (Stripe, Paystack, PayFast, Flutterwave, Instamojo, SSLCommerz, MercadoPago, Paytm, Authorize.Net, CHIP, bKash, Nagad, Iyzico).
- Orders must be pending and use the same `payment_method` as the callback.
- Offline methods (COD, bank transfer, check) require a valid checkout session.
- CHIP webhook can require `chip_webhook_secret` (see Settings → CHIP).

### Admin

- Role permissions (`can:`) on cache clear, sitemaps, and search-term deletion.
- Order updates use `SaveOrderRequest` and explicit `$fillable` fields.
- User `permissions` cannot be mass-assigned via the user form.

### Content (XSS)

- `clean_html()` sanitizes rich HTML on storefront output.
- Page, product, and blog content is sanitized on save.

### HTTP headers

`Modules\Support\Http\Middleware\SecurityHeaders` sets:

- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `X-Frame-Options: DENY` (admin) / `SAMEORIGIN` (storefront)
- `Permissions-Policy` (restricted camera/mic/geo)
- `Strict-Transport-Security` on HTTPS in production
- Optional CSP (see below)

### Media

- Uploads limited by MIME type and 10 MB max.

### Authentication

- WhatsApp OTP routes are rate-limited.
- CSRF on web forms (payment callbacks exempt where required).

## Production checklist

### Environment (`.env`)

```env
APP_ENV=production
APP_DEBUG=false
SECURITY_HEADERS_ENABLED=true
SECURITY_CSP_ENABLED=false
SECURITY_CSP_REPORT_ONLY=true
SECURITY_HSTS_ENABLED=true
```

1. Set **`APP_DEBUG=false`**.
2. Use HTTPS everywhere; HSTS is sent automatically in production over HTTPS.
3. Never commit `.env` or expose `storage/dotenv-editor/backups/`.
4. Rotate `APP_KEY` only with a planned re-encryption strategy.

### Content Security Policy (CSP)

CSP is **off by default** because checkout uses inline scripts and many third-party payment widgets.

Recommended rollout:

1. Set `SECURITY_CSP_ENABLED=true` and `SECURITY_CSP_REPORT_ONLY=true`.
2. Monitor browser console / `SECURITY_CSP_REPORT_URI` for violations.
3. When clean, set `SECURITY_CSP_REPORT_ONLY=false` to enforce.

### Server

- Block web access to `/storage/` except public-linked files.
- Keep PHP, Composer packages, and OS packages updated.
- Restrict admin URL (`/admin`) by IP or VPN if possible.

### Accounts

- Use strong passwords; limit admin users.
- Review roles in **Users → Roles** regularly.
- Disable unused payment gateways in settings.

### Backups

- Backup database and `storage/app` regularly.
- Store backups encrypted, not in a public directory.

## Reporting issues

If you discover a vulnerability, contact the site owner privately. Do not post exploit details publicly before a fix is deployed.

## Related files

| Area | Location |
|------|----------|
| Payment verification | `modules/Payment/Services/GatewayPaymentVerifier.php` |
| Checkout guard | `modules/Checkout/Services/CheckoutCompletionGuard.php` |
| HTML sanitization | `modules/Support/Services/HtmlSanitizer.php`, `clean_html()` |
| Security headers | `modules/Support/Http/Middleware/SecurityHeaders.php` |
| Header config | `config/security.php` |
