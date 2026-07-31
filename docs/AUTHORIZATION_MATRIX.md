# Authorization regression matrix

This matrix identifies the security boundary that automated tests must preserve.

| Flow | Authentication | Ownership / integrity check | Rate limit | Expected denial |
|---|---|---|---|---|
| Customer consultation list/view/PDF/submit | `auth` middleware | `ConsultationCustomerAccess::claim` | Submit 5/min, PDF 20/min | 404 or validation redirect |
| Public consultation token lookup | Public high-entropy token | Hashed token, expiry, revocation, customer identifier | 10/min/IP | 404 or generic mismatch |
| Admin consultation PDF | Admin session + permission | Completed immutable submission | 20/min | 403/404 |
| Customer order/view/invoice/receipt | `auth` middleware | Query through authenticated user's `orders()` relation | Session controls | 404 |
| CHIP webhook | Public callback | RSA signature, purchase uniqueness, payment idempotency | Signature checked before mutation | 200 ignored, no mutation |
| CSP reporting | Public browser report | No state mutation; URL query strings discarded | 60/min/IP | 204 discard |

`tests/Unit/Support/OperationalSecurityContractTest.php` is a regression tripwire for these route, ownership, signature, and queue invariants. It complements feature tests and is not a penetration test.
