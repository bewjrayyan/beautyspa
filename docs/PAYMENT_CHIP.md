# CHIP Collect payment methods

FleetCart integrates [CHIP Collect](https://github.com/CHIPAsia/chip-php-sdk) with optional **per-channel** checkout methods.

## Admin setup

**Settings → CHIP** (`/admin/settings?tab=chip`):

1. Enable **CHIP** and enter Brand ID + API Key.
2. Under **CHIP Collect payment methods**, enable one or more:
   - **FPX** (`chip_fpx`) — online banking
   - **Cards** (`chip_card`) — credit/debit (whitelist from API or custom codes)
   - **Atome** (`chip_atome`) — Atome BNPL via CHIP
3. **Surcharge (sen)** — added to the CHIP checkout total (MYR: `100` = RM1.00).
4. **CHIP codes** — optional comma-separated values for `payment_method_whitelist` (see CHIP `GET /payment_methods/`). Defaults: `fpx`, `card`, `atome`.

**Generic “all methods”** — enable *Show generic CHIP — all methods* to keep the original single `chip` gateway (no whitelist).

## Checkout

Each enabled method appears as its own radio option. The customer is redirected to CHIP with only that channel available.

## Technical notes

- Purchase payload uses `payment_method_whitelist` ([SDK `PurchaseBuilder::paymentMethodWhitelist`](https://github.com/CHIPAsia/chip-php-sdk)).
- Surcharges are sent as an extra line item + `purchase.total_override` when needed.
- Webhooks accept any order whose `payment_method` starts with `chip`.

## Verify CHIP codes

Use your brand credentials and list methods:

```bash
curl -s "https://gate.chip-in.asia/api/v1/payment_methods/?brand_id=YOUR_BRAND&currency=MYR" \
  -H "Authorization: Bearer YOUR_API_KEY"
```

Copy the exact codes into the whitelist field if defaults do not match your CHIP account.
