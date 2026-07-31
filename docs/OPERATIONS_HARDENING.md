# Production operations hardening

## Privacy retention and legal hold

The retention command is a dry-run unless `--execute` is supplied. Execution also requires `PRIVACY_RETENTION_ENABLED=true`; leave it false until the business owner or legal adviser approves the periods.

```bash
php artisan privacy:apply-retention --json
php artisan privacy:consultation-legal-hold 123 --reason="Active complaint" --actor=1
php artisan privacy:consultation-legal-hold 123 --release
php artisan privacy:apply-retention --execute --limit=100 --json
```

Completed consultations have no default deletion period. `PRIVACY_COMPLETED_CONSULTATION_DAYS` must be explicitly configured. Expired, unsubmitted requests default to 90 days after expiry; access logs default to 730 days. Legal hold blocks both consultation and related access-log deletion. Executed runs are audited in `privacy_retention_runs`.

Rollback: disable `PRIVACY_RETENTION_ENABLED`, remove the scheduler entry through deployment rollback, and restore deleted records/files from the protected backup. Database rollback cannot reconstruct retained data.

## Queue health

Use a database/Redis queue in production and keep a supervised worker running. The health check is read-only and exits non-zero when a threshold is exceeded:

```bash
php artisan queue:health --json
php artisan queue:failed
```

Set `QUEUE_HEALTH_MONITOR_ENABLED=true` only after log alert delivery is configured. The scheduler checks every ten minutes. Investigate failures before running `queue:retry`; retries can repeat external effects if the underlying operation is not idempotent.

## CSP report-only rollout

Start with:

```dotenv
SECURITY_CSP_ENABLED=true
SECURITY_CSP_REPORT_ONLY=true
```

Reports are accepted at `/_security/csp-report`, rate-limited, and written to the 30-day `security` log. URL query strings are removed and client IPs are HMAC-hashed. Review and tune reports before setting `SECURITY_CSP_REPORT_ONLY=false`. The existing policy still allows inline/eval scripts for legacy compatibility; eliminating those requires nonce/hash work before CSP can be considered strict.

## Slow-query monitoring and benchmarks

Enable slow-query logging at a conservative threshold. Bindings are intentionally omitted from logs to avoid leaking PII and tokens.

```dotenv
SLOW_QUERY_LOG_ENABLED=true
SLOW_QUERY_LOG_MS=500
```

Run the bounded read-only production benchmark before and after traffic-sensitive releases:

```bash
php artisan performance:benchmark --iterations=5 --explain --json
```

Record median/P95 and access paths for recent orders, product category/detail, customer order history, and booking collision checks. Enable CDN, response caching, WebP conversion, or critical CSS only after real traffic metrics identify a bottleneck; do not cache authenticated or sensitive responses.
