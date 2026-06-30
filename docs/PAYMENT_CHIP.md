# CHIP Collect payment methods

AestheticCart integrates [CHIP Collect](https://github.com/CHIPAsia/chip-php-sdk) with optional **per-channel** checkout methods.

## Admin setup

**Settings → CHIP** (`/admin/settings?tab=chip`):

1. Enable **CHIP** and enter Brand ID + API Key (use **Test Mode** key while developing).
2. **Webhook URL** — public HTTPS URL, e.g. `https://your-domain.com/payment/chip/webhook` (used as `success_callback`).
3. **CHIP public key** — PEM public key for `X-Signature` verification (from CHIP portal, or leave empty to auto-fetch via API).
4. Under **CHIP Collect payment methods**, enable one or more:
   - **FPX** (`chip_fpx`) — online banking
   - **Cards** (`chip_card`) — credit/debit (whitelist from API or custom codes)
   - **E-wallets** (`chip_ewallet`) — Touch 'n Go, GrabPay, ShopeePay (default codes: `razer_tng`, `razer_grabpay`, `razer_shopeepay`)
   - **DuitNow QR** (`chip_duitnow`) — scan & pay (default code: `duitnow_qr`)
   - **Atome** (`chip_atome`) — Atome BNPL via CHIP (default code: `razer_atome`)
5. **Surcharge (sen)** — added to the CHIP checkout total (MYR: `100` = RM1.00).
6. **CHIP codes** — optional comma-separated values for `payment_method_whitelist` (see CHIP `GET /payment_methods/`). Defaults: `fpx`, card networks, `razer_tng`, `duitnow_qr`, `razer_atome`.

**Generic “all methods”** — enable *Show generic CHIP — all methods* to keep the original single `chip` gateway (no whitelist). When disabled and at least one per-method option is enabled, only those methods appear at checkout.

## Checkout

Each enabled method appears as its own radio option. The customer is redirected to CHIP with only that channel available.

## Technical notes

- Purchase payload uses `payment_method_whitelist` ([SDK `PurchaseBuilder::paymentMethodWhitelist`](https://github.com/CHIPAsia/chip-php-sdk)).
- Surcharges are sent as an extra line item + `purchase.total_override` when needed.
- `success_callback` / webhook POSTs are verified with RSA `X-Signature` (SHA-256) against the CHIP public key.
- Invalid signatures return HTTP 200 but are not processed (CHIP retry-safe).
- Webhooks accept any order whose `payment_method` is a CHIP gateway key (`chip`, `chip_fpx`, etc.).

## Verify CHIP codes

Use your brand credentials and list methods:

```bash
curl -s "https://gate.chip-in.asia/api/v1/payment_methods/?brand_id=YOUR_BRAND&currency=MYR" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

Copy the exact codes into the whitelist field if defaults do not match your CHIP account.
