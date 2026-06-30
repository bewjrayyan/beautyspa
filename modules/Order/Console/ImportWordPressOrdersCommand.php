<?php

namespace Modules\Order\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Order\Entities\Order;
use Modules\Support\Country;
use Modules\Support\State;

class ImportWordPressOrdersCommand extends Command
{
    private const PLACEHOLDER_SKU = 'WP-IMPORT-LINE';

    private const WP_TRACKING_PREFIX = 'WP-';

    private ?string $trackingColumn = null;

    protected $signature = 'order:import-wordpress-orders
                            {file : Path to WordPress WooCommerce orders SQL dump}
                            {--dry-run : Preview without writing}
                            {--limit= : Maximum number of orders to import}
                            {--spa-branch-id= : Default spa branch for imported appointments}
                            {--repair-product-names : Fix product names on already-imported WordPress orders}';

    protected $description = 'Import WordPress / WooCommerce HPOS orders into FleetCart and link customers by email.';

    /** @var array<string, int> */
    private array $emailToUserId = [];

    /** @var array<int, true> */
    private array $existingWpOrderIds = [];

    private ?int $placeholderProductId = null;

    public function handle(): int
    {
        $file = $this->resolveFilePath((string) $this->argument('file'));
        $dryRun = (bool) $this->option('dry-run');
        $limit = $this->option('limit') !== null ? max(1, (int) $this->option('limit')) : null;

        if ($file === null) {
            $this->error('SQL dump file not found or not readable.');
            $this->line('');
            $this->line('Upload the WordPress orders .sql file first, then pass the real path. Example:');
            $this->line('  php artisan order:import-wordpress-orders storage/app/immaserilaris_web24.sql --dry-run');
            $this->line('  php artisan order:import-wordpress-orders /home/immaserilaris/public_html/v2/storage/app/immaserilaris_web24.sql');

            return self::FAILURE;
        }

        $sql = file_get_contents($file);

        if ($sql === false || trim($sql) === '') {
            $this->error('Could not read SQL dump file.');

            return self::FAILURE;
        }

        $tableMap = $this->buildTableMap($sql);

        if (! isset($tableMap['orders'])) {
            $this->error('No WooCommerce orders table (wc_orders) found in SQL dump.');

            return self::FAILURE;
        }

        if ((bool) $this->option('repair-product-names')) {
            $this->info('Reading product names from SQL dump (file parse only, no temp tables)…');

            return $this->repairProductNames($sql, $dryRun, $limit);
        }

        $this->info('Loading WordPress order tables from SQL dump…');

        if (! $dryRun) {
            $this->loadImportTables($sql, $tableMap);
        }

        $this->emailToUserId = DB::table('users')
            ->select(['id', 'email'])
            ->get()
            ->mapWithKeys(fn ($row) => [strtolower(trim((string) $row->email)) => (int) $row->id])
            ->all();

        $this->trackingColumn = $this->resolveTrackingColumn();

        if ($this->trackingColumn !== null) {
            $this->existingWpOrderIds = DB::table('orders')
                ->where($this->trackingColumn, 'like', self::WP_TRACKING_PREFIX . '%')
                ->pluck($this->trackingColumn)
                ->mapWithKeys(function ($tracking) {
                    $id = (int) str_replace(self::WP_TRACKING_PREFIX, '', (string) $tracking);

                    return [$id => true];
                })
                ->all();
        }

        if (! $dryRun) {
            $this->placeholderProductId = $this->ensurePlaceholderProduct();
        }

        $orders = $this->fetchShopOrders($sql, $tableMap['orders'], $dryRun);

        if ($orders === []) {
            $this->error('No shop_order rows found in SQL dump.');

            return self::FAILURE;
        }

        if ($limit !== null) {
            $orders = array_slice($orders, 0, $limit);
        }

        $this->info(sprintf('Processing %d WordPress orders…', count($orders)));

        $addresses = $this->fetchAddresses($sql, $tableMap['addresses'] ?? null, $dryRun);
        $operational = $this->fetchOperationalData($sql, $tableMap['operational'] ?? null, $dryRun);
        $lineItems = $this->fetchLineItems($sql, $tableMap['products'] ?? null, $dryRun);
        $meta = $this->fetchOrderMeta($sql, $tableMap['meta'] ?? null, $dryRun);
        $chipProducts = $this->fetchChipProductNames($sql, $tableMap['meta'] ?? null, $dryRun);

        $spaBranchId = $this->resolveSpaBranchId();

        $imported = 0;
        $skippedExisting = 0;
        $skippedInvalid = 0;
        $guestOrders = 0;
        $linkedCustomers = 0;
        $missingLineItems = 0;

        $bar = $this->output->createProgressBar(count($orders));
        $bar->start();

        foreach ($orders as $orderRow) {
            $bar->advance();

            $wpOrderId = (int) ($orderRow['id'] ?? 0);

            if ($wpOrderId <= 0) {
                $skippedInvalid++;

                continue;
            }

            if (isset($this->existingWpOrderIds[$wpOrderId])) {
                $skippedExisting++;

                continue;
            }

            $email = strtolower(trim((string) ($orderRow['billing_email'] ?? '')));

            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skippedInvalid++;

                continue;
            }

            $billing = $addresses[$wpOrderId]['billing'] ?? [];
            $shipping = $addresses[$wpOrderId]['shipping'] ?? $billing;
            $ops = $operational[$wpOrderId] ?? [];
            $orderMeta = $meta[$wpOrderId] ?? [];
            $items = $lineItems[$wpOrderId] ?? [];

            if ($items === []) {
                $missingLineItems++;
            }

            $customerId = $this->emailToUserId[$email] ?? null;

            if ($customerId === null) {
                $guestOrders++;
            } else {
                $linkedCustomers++;
            }

            if ($dryRun) {
                $imported++;

                continue;
            }

            $this->importOrder(
                $wpOrderId,
                $orderRow,
                $billing,
                $shipping,
                $ops,
                $orderMeta,
                $items,
                $chipProducts[$wpOrderId] ?? [],
                $email,
                $customerId,
                $spaBranchId,
            );

            $imported++;
            $this->existingWpOrderIds[$wpOrderId] = true;
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Imported', $imported],
                ['Linked to FleetCart customer', $linkedCustomers],
                ['Guest (email only, no account)', $guestOrders],
                ['Skipped (already imported)', $skippedExisting],
                ['Skipped (invalid)', $skippedInvalid],
                ['Orders without line items in dump', $missingLineItems],
            ]
        );

        if ($dryRun) {
            $this->warn('Dry run only — no records were written.');
        } else {
            $this->dropImportTables(array_values($tableMap));
            $this->info('Import complete. WordPress order IDs are stored in ' . ($this->trackingColumn ?? 'tracking_reference') . ' as WP-{id}.');
        }

        return self::SUCCESS;
    }

    /**
     * @param array<string, mixed> $orderRow
     * @param array<string, mixed> $billing
     * @param array<string, mixed> $shipping
     * @param array<string, mixed> $ops
     * @param array<string, string> $orderMeta
     * @param list<array<string, mixed>> $items
     * @param list<array{name: string, price: int, quantity: float}> $chipProducts
     */
    private function importOrder(
        int $wpOrderId,
        array $orderRow,
        array $billing,
        array $shipping,
        array $ops,
        array $orderMeta,
        array $items,
        array $chipProducts,
        string $email,
        ?int $customerId,
        ?int $spaBranchId,
    ): void {
        DB::transaction(function () use (
            $wpOrderId,
            $orderRow,
            $billing,
            $shipping,
            $ops,
            $orderMeta,
            $items,
            $chipProducts,
            $email,
            $customerId,
            $spaBranchId,
        ) {
            [$customerFirst, $customerLast] = $this->resolveCustomerName($billing, $orderMeta);
            [$billingFirst, $billingLast] = $this->splitPersonName((string) ($billing['first_name'] ?? $customerFirst));
            [$shippingFirst, $shippingLast] = $this->splitPersonName((string) ($shipping['first_name'] ?? $billingFirst));

            $discount = round((float) ($ops['discount_total_amount'] ?? 0), 4);
            $shippingCost = round((float) ($ops['shipping_total_amount'] ?? 0), 4);
            $total = round((float) ($orderRow['total_amount'] ?? 0), 4);
            $subTotal = $this->resolveSubTotal($items, $total, $discount, $shippingCost);
            $createdAt = $this->parseDateTime($orderRow['date_created_gmt'] ?? null) ?? now();
            $status = $this->mapOrderStatus((string) ($orderRow['status'] ?? ''));
            $paymentStatus = $this->mapPaymentStatus($status, $ops);
            $appointmentDate = $this->parseAppointmentDate($orderMeta['date_appointment'] ?? null);
            $appointmentTime = $this->parseAppointmentTime($orderMeta['time_appointment'] ?? null);

            $orderData = [
                'customer_id' => $customerId,
                'customer_email' => $email,
                'customer_phone' => $this->normalizePhone((string) ($billing['phone'] ?? '')),
                'customer_first_name' => Str::limit($customerFirst, 50, ''),
                'customer_last_name' => Str::limit($customerLast, 50, ''),
                'billing_first_name' => Str::limit($billingFirst, 50, ''),
                'billing_last_name' => Str::limit($billingLast, 50, ''),
                'billing_address_1' => Str::limit((string) ($billing['address_1'] ?? '-'), 255, ''),
                'billing_address_2' => $this->nullableString($billing['address_2'] ?? null),
                'billing_city' => Str::limit((string) ($billing['city'] ?? '-'), 50, ''),
                'billing_state' => $this->normalizeState((string) ($billing['state'] ?? ''), (string) ($billing['country'] ?? 'MY')),
                'billing_zip' => Str::limit((string) ($billing['postcode'] ?? ''), 20, ''),
                'billing_country' => $this->normalizeCountry((string) ($billing['country'] ?? 'MY')),
                'shipping_first_name' => Str::limit($shippingFirst, 50, ''),
                'shipping_last_name' => Str::limit($shippingLast, 50, ''),
                'shipping_address_1' => Str::limit((string) ($shipping['address_1'] ?? $billing['address_1'] ?? '-'), 255, ''),
                'shipping_address_2' => $this->nullableString($shipping['address_2'] ?? $billing['address_2'] ?? null),
                'shipping_city' => Str::limit((string) ($shipping['city'] ?? $billing['city'] ?? '-'), 50, ''),
                'shipping_state' => $this->normalizeState(
                    (string) ($shipping['state'] ?? $billing['state'] ?? ''),
                    (string) ($shipping['country'] ?? $billing['country'] ?? 'MY'),
                ),
                'shipping_zip' => Str::limit((string) ($shipping['postcode'] ?? $billing['postcode'] ?? ''), 20, ''),
                'shipping_country' => $this->normalizeCountry((string) ($shipping['country'] ?? $billing['country'] ?? 'MY')),
                'sub_total' => $subTotal,
                'shipping_method' => $shippingCost > 0 ? 'flat_rate' : 'free_shipping',
                'shipping_cost' => $shippingCost,
                'coupon_id' => null,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $this->mapPaymentMethod((string) ($orderRow['payment_method'] ?? '')),
                'payment_proof_file_id' => null,
                'currency' => strtoupper((string) ($orderRow['currency'] ?? 'MYR')),
                'currency_rate' => 1,
                'locale' => 'en',
                'status' => $status,
                'payment_status' => $paymentStatus,
                'note' => $this->buildOrderNote($wpOrderId, $orderRow, $orderMeta, $items),
                'beautician_id' => null,
                'appointment_date' => $appointmentDate,
                'appointment_time' => $appointmentTime,
                'spa_branch_id' => $appointmentDate || $appointmentTime ? $spaBranchId : null,
                'created_at' => $createdAt,
                'updated_at' => $this->parseDateTime($orderRow['date_updated_gmt'] ?? null) ?? $createdAt,
            ];

            if ($this->trackingColumn !== null) {
                $orderData[$this->trackingColumn] = self::WP_TRACKING_PREFIX . $wpOrderId;
            }

            $orderId = DB::table('orders')->insertGetId($orderData);

            $this->insertLineItems((int) $orderId, $items, $chipProducts, $subTotal, $total);
        });
    }

    /**
     * @param list<array<string, mixed>> $items
     * @param list<array{name: string, price: int, quantity: float}> $chipProducts
     */
    private function insertLineItems(int $orderId, array $items, array $chipProducts, float $subTotal, float $total): void
    {
        if ($items === []) {
            $name = $this->matchChipProductName($chipProducts, $total, 1, 0) ?? 'WordPress order total';
            $productId = $this->findOrCreateWpProduct(0, $name);

            DB::table('order_products')->insert([
                'order_id' => $orderId,
                'product_id' => $productId,
                'unit_price' => $total,
                'qty' => 1,
                'line_total' => $total,
            ]);

            return;
        }

        foreach ($items as $index => $item) {
            $qty = max(1, (int) ($item['product_qty'] ?? 1));
            $lineTotal = round((float) ($item['product_gross_revenue'] ?? 0), 4);
            $unitPrice = $qty > 0 ? round($lineTotal / $qty, 4) : $lineTotal;
            $wpProductId = (int) ($item['product_id'] ?? 0);
            $productId = $this->resolveLineItemProductId($wpProductId, $lineTotal, $qty, $index, $chipProducts);

            DB::table('order_products')->insert([
                'order_id' => $orderId,
                'product_id' => $productId,
                'unit_price' => $unitPrice,
                'qty' => $qty,
                'line_total' => $lineTotal,
            ]);
        }
    }

    /**
     * @param list<array{name: string, price: int, quantity: float}> $chipProducts
     */
    private function resolveLineItemProductId(
        int $wpProductId,
        float $lineTotal,
        int $qty,
        int $lineIndex,
        array $chipProducts,
    ): int {
        $catalogProductId = $this->resolveCatalogProductId($wpProductId);

        if ($catalogProductId !== null) {
            return $catalogProductId;
        }

        $name = $this->matchChipProductName($chipProducts, $lineTotal, $qty, $lineIndex)
            ?? ($wpProductId > 0 ? 'WordPress treatment #' . $wpProductId : 'WordPress treatment');

        return $this->findOrCreateWpProduct($wpProductId, $name);
    }

    /**
     * @param list<array{name: string, price: int, quantity: float}> $chipProducts
     */
    private function matchChipProductName(array $chipProducts, float $lineTotal, int $qty, int $lineIndex): ?string
    {
        if ($chipProducts === []) {
            return null;
        }

        $lineCents = (int) round($lineTotal * 100);

        foreach ($chipProducts as $chipProduct) {
            $price = (int) ($chipProduct['price'] ?? 0);
            $chipQty = max(1, (int) round((float) ($chipProduct['quantity'] ?? 1)));

            if ($price === $lineCents && $chipQty === $qty) {
                return trim((string) ($chipProduct['name'] ?? '')) ?: null;
            }
        }

        if (isset($chipProducts[$lineIndex]['name'])) {
            $name = trim((string) $chipProducts[$lineIndex]['name']);

            return $name !== '' ? $name : null;
        }

        if (count($chipProducts) === 1) {
            $name = trim((string) ($chipProducts[0]['name'] ?? ''));

            return $name !== '' ? $name : null;
        }

        return null;
    }

    private function findOrCreateWpProduct(int $wpProductId, string $name): int
    {
        $name = Str::limit(trim($name), 250, '');

        if ($name === '') {
            $name = $wpProductId > 0 ? 'WordPress treatment #' . $wpProductId : 'WordPress treatment';
        }

        $sku = $wpProductId > 0 ? 'WP-' . $wpProductId : self::PLACEHOLDER_SKU . '-' . md5($name);

        static $cache = [];

        if (isset($cache[$sku])) {
            return $cache[$sku];
        }

        $existingId = DB::table('products')->where('sku', $sku)->value('id');

        if ($existingId) {
            $this->ensureProductTranslationName((int) $existingId, $name);

            return $cache[$sku] = (int) $existingId;
        }

        $now = now();
        $slugBase = Str::slug($name);

        if ($slugBase === '') {
            $slugBase = 'wp-treatment-' . ($wpProductId > 0 ? $wpProductId : Str::random(6));
        }

        $slug = $this->uniqueProductSlug($slugBase);
        $productId = DB::table('products')->insertGetId([
            'slug' => $slug,
            'sku' => $sku,
            'price' => 0,
            'selling_price' => 0,
            'manage_stock' => 0,
            'in_stock' => 1,
            'is_active' => 0,
            'is_virtual' => 1,
            'viewed' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach (['en', 'ms'] as $locale) {
            DB::table('product_translations')->insert([
                'product_id' => $productId,
                'locale' => $locale,
                'name' => $name,
                'description' => 'Historical WordPress treatment imported from order data.',
                'short_description' => null,
            ]);
        }

        return $cache[$sku] = (int) $productId;
    }

    private function ensureProductTranslationName(int $productId, string $name): void
    {
        $name = Str::limit(trim($name), 250, '');

        if ($name === '') {
            return;
        }

        foreach (['en', 'ms'] as $locale) {
            $currentName = DB::table('product_translations')
                ->where('product_id', $productId)
                ->where('locale', $locale)
                ->value('name');

            if ($currentName === null) {
                DB::table('product_translations')->insert([
                    'product_id' => $productId,
                    'locale' => $locale,
                    'name' => $name,
                    'description' => 'Historical WordPress treatment imported from order data.',
                    'short_description' => null,
                ]);

                continue;
            }

            if ($currentName === 'Imported treatment (WordPress)' || str_starts_with((string) $currentName, 'WordPress treatment #')) {
                DB::table('product_translations')
                    ->where('product_id', $productId)
                    ->where('locale', $locale)
                    ->update(['name' => $name]);
            }
        }
    }

    private function uniqueProductSlug(string $base): string
    {
        $slug = Str::limit($base, 180, '');
        $candidate = $slug;
        $suffix = 1;

        while (DB::table('products')->where('slug', $candidate)->exists()) {
            $candidate = Str::limit($slug . '-' . $suffix, 190, '');
            $suffix++;
        }

        return $candidate;
    }

    private function resolveCatalogProductId(int $wpProductId): ?int
    {
        if ($wpProductId <= 0) {
            return null;
        }

        static $cache = [];

        if (array_key_exists($wpProductId, $cache)) {
            return $cache[$wpProductId];
        }

        $candidates = [
            (string) $wpProductId,
            'WP-' . $wpProductId,
            'wp-' . $wpProductId,
        ];

        $productId = DB::table('products')
            ->whereIn('sku', $candidates)
            ->where('sku', '!=', self::PLACEHOLDER_SKU)
            ->value('id');

        $cache[$wpProductId] = $productId !== null ? (int) $productId : null;

        return $cache[$wpProductId];
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    private function resolveSubTotal(array $items, float $total, float $discount, float $shippingCost): float
    {
        if ($items !== []) {
            $sum = 0.0;

            foreach ($items as $item) {
                $sum += (float) ($item['product_gross_revenue'] ?? 0);
            }

            if ($sum > 0) {
                return round($sum, 4);
            }
        }

        $derived = $total + $discount - $shippingCost;

        return round(max(0, $derived), 4);
    }

    /**
     * @param array<string, mixed> $billing
     * @param array<string, string> $orderMeta
     * @return array{0: string, 1: string}
     */
    private function resolveCustomerName(array $billing, array $orderMeta): array
    {
        $fullName = trim((string) ($orderMeta['_billing_full_name'] ?? ''));

        if ($fullName !== '') {
            return $this->splitPersonName($fullName);
        }

        return $this->splitPersonName((string) ($billing['first_name'] ?? 'Customer'));
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitPersonName(string $name): array
    {
        $name = trim($name);

        if ($name === '') {
            return ['Customer', ''];
        }

        $parts = preg_split('/\s+/', $name, 2) ?: [];

        $first = trim((string) ($parts[0] ?? 'Customer'));
        $last = trim((string) ($parts[1] ?? ''));

        return [Str::limit($first, 50, ''), Str::limit($last, 50, '')];
    }

    /**
     * @param array<string, mixed> $orderRow
     * @param array<string, string> $orderMeta
     * @param list<array<string, mixed>> $items
     */
    private function buildOrderNote(int $wpOrderId, array $orderRow, array $orderMeta, array $items): ?string
    {
        $lines = [
            'Imported from WordPress order #' . $wpOrderId,
        ];

        $customerNote = trim((string) ($orderRow['customer_note'] ?? ''));

        if ($customerNote !== '') {
            $lines[] = 'Customer note: ' . $customerNote;
        }

        if (! empty($orderMeta['beautician_imma'])) {
            $lines[] = 'Beautician (WP): ' . $orderMeta['beautician_imma'];
        }

        if (! empty($orderMeta['ada_penyakit'])) {
            $lines[] = 'Medical note (WP): ' . $orderMeta['ada_penyakit'];
        }

        $transactionId = trim((string) ($orderRow['transaction_id'] ?? ''));

        if ($transactionId !== '') {
            $lines[] = 'CHIP transaction: ' . $transactionId;
        }

        $paymentTitle = trim((string) ($orderRow['payment_method_title'] ?? ''));

        if ($paymentTitle !== '') {
            $lines[] = 'Payment (WP): ' . $paymentTitle;
        }

        if ($items !== []) {
            $itemLines = [];

            foreach ($items as $item) {
                $itemLines[] = sprintf(
                    'WP product #%d × %d (RM %s)',
                    (int) ($item['product_id'] ?? 0),
                    max(1, (int) ($item['product_qty'] ?? 1)),
                    number_format((float) ($item['product_gross_revenue'] ?? 0), 2, '.', ''),
                );
            }

            $lines[] = 'Line items: ' . implode('; ', $itemLines);
        }

        $note = implode("\n", $lines);

        return $note === '' ? null : Str::limit($note, 65000, '');
    }

    private function mapOrderStatus(string $status): string
    {
        return match ($status) {
            'wc-completed' => Order::COMPLETED,
            'wc-processing' => Order::PROCESSING,
            'wc-on-hold' => Order::ON_HOLD,
            'wc-pending' => Order::PENDING_PAYMENT,
            'wc-refunded' => Order::REFUNDED,
            'wc-cancelled', 'wc-canceled', 'wc-failed' => Order::CANCELED,
            default => Order::PENDING,
        };
    }

    /**
     * @param array<string, mixed> $ops
     */
    private function mapPaymentStatus(string $orderStatus, array $ops): string
    {
        if ($orderStatus === Order::REFUNDED) {
            return Order::PAYMENT_CANCELED;
        }

        if (in_array($orderStatus, [Order::COMPLETED, Order::PROCESSING], true)) {
            return ! empty($ops['date_paid_gmt']) ? Order::PAYMENT_PAID : Order::PAYMENT_PENDING;
        }

        if ($orderStatus === Order::CANCELED) {
            return Order::PAYMENT_CANCELED;
        }

        return Order::PAYMENT_PENDING;
    }

    private function mapPaymentMethod(string $method): string
    {
        return match ($method) {
            'wc_gateway_chip' => 'chip_fpx',
            'wc_gateway_chip_3' => 'chip_card',
            'wc_gateway_chip_4' => 'chip_ewallet',
            'wc_gateway_chip_5' => 'chip_atome',
            'wc_gateway_chip_6' => 'chip_duitnow',
            'bacs' => 'bank_transfer',
            'cod' => 'cash_on_delivery',
            default => $method !== '' ? $method : 'chip_fpx',
        };
    }

    private function parseAppointmentDate(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        foreach (['d/M/Y', 'd-m-Y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim($value))->toDateString();
            } catch (\Throwable) {
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseAppointmentTime(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        foreach (['h:i A', 'H:i', 'g:i A'] as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('H:i');
            } catch (\Throwable) {
            }
        }

        return null;
    }

    private function parseDateTime(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '' || str_starts_with($value, '0000')) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizePhone(string $phone): ?string
    {
        $phone = trim($phone);

        return $phone === '' ? null : Str::limit($phone, 30, '');
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : Str::limit($value, 255, '');
    }

    private function normalizeCountry(string $country): string
    {
        $country = strtoupper(trim($country));

        if ($country === '' || ! in_array($country, Country::supportedCodes(), true)) {
            return 'MY';
        }

        return $country;
    }

    private function normalizeState(string $state, string $country): string
    {
        $state = strtoupper(trim($state));
        $country = $this->normalizeCountry($country);
        $states = State::get($country);

        if ($state !== '' && isset($states[$state])) {
            return $state;
        }

        return $state !== '' ? Str::limit($state, 50, '') : (array_key_first($states) ?: 'SGR');
    }

    private function resolveTrackingColumn(): ?string
    {
        if (Schema::hasColumn('orders', 'tracking_reference')) {
            return 'tracking_reference';
        }

        if (Schema::hasColumn('orders', 'tracking_number')) {
            return 'tracking_number';
        }

        return null;
    }

    private function resolveSpaBranchId(): ?int
    {
        if (! app('modules')->isEnabled('SpaBranch')) {
            return null;
        }

        $option = $this->option('spa-branch-id');

        if ($option !== null && (int) $option > 0) {
            return (int) $option;
        }

        $branchId = DB::table('spa_branches')->where('is_active', true)->orderBy('id')->value('id');

        return $branchId !== null ? (int) $branchId : null;
    }

    private function ensurePlaceholderProduct(): int
    {
        $existing = DB::table('products')->where('sku', self::PLACEHOLDER_SKU)->value('id');

        if ($existing) {
            return (int) $existing;
        }

        $now = now();
        $productId = DB::table('products')->insertGetId([
            'slug' => 'wordpress-import-line-item',
            'sku' => self::PLACEHOLDER_SKU,
            'price' => 0,
            'selling_price' => 0,
            'manage_stock' => 0,
            'in_stock' => 1,
            'is_active' => 0,
            'is_virtual' => 1,
            'viewed' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach (['en', 'ms'] as $locale) {
            DB::table('product_translations')->insert([
                'product_id' => $productId,
                'locale' => $locale,
                'name' => 'Imported treatment (WordPress)',
                'description' => 'Placeholder for historical WordPress order line items.',
                'short_description' => null,
            ]);
        }

        return (int) $productId;
    }

    /**
     * @return array<string, string>
     */
    private function buildTableMap(string $sql): array
    {
        $suffixes = [
            'orders' => '_wc_orders`',
            'addresses' => '_wc_order_addresses`',
            'operational' => '_wc_order_operational_data`',
            'products' => '_wc_order_product_lookup`',
            'meta' => '_wc_orders_meta`',
        ];

        $map = [];

        foreach ($suffixes as $key => $suffix) {
            $pattern = '/CREATE TABLE `([^`]+' . preg_quote(rtrim($suffix, '`'), '/') . ')`/';

            if (preg_match($pattern, $sql, $matches)) {
                $map[$key] = 'wp_imp_' . $key;
            }
        }

        return $map;
    }

    /**
     * @param array<string, string> $tableMap
     */
    private function loadImportTables(string $sql, array $tableMap): void
    {
        $suffixToSource = [
            'orders' => '_wc_orders',
            'addresses' => '_wc_order_addresses',
            'operational' => '_wc_order_operational_data',
            'products' => '_wc_order_product_lookup',
            'meta' => '_wc_orders_meta',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tableMap as $key => $importTable) {
            $suffix = $suffixToSource[$key] ?? null;

            if ($suffix === null) {
                continue;
            }

            if (! preg_match('/CREATE TABLE `([^`]+' . preg_quote($suffix, '/') . ')`/s', $sql, $matches)) {
                continue;
            }

            $sourceTable = $matches[1];
            $createPattern = '/CREATE TABLE `' . preg_quote($sourceTable, '/') . '`.+?;\s*/s';

            if (! preg_match($createPattern, $sql, $createMatch)) {
                continue;
            }

            $createSql = str_replace('`' . $sourceTable . '`', '`' . $importTable . '`', $createMatch[0]);
            $createSql = $this->sanitizeCreateTableSql($createSql);
            $insertStatements = $this->extractInsertStatements(
                str_replace('`' . $sourceTable . '`', '`' . $importTable . '`', $sql),
                $importTable,
            );

            Schema::dropIfExists($importTable);
            DB::unprepared($createSql);

            foreach ($insertStatements as $statement) {
                DB::unprepared($statement);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @param list<string> $tables
     */
    private function dropImportTables(array $tables): void
    {
        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchShopOrders(string $sql, string $table, bool $dryRun): array
    {
        if ($dryRun) {
            return $this->parseOrdersFromSql($sql, $this->detectSourceTable($sql, '_wc_orders') ?? '');
        }

        return DB::table($table)
            ->where('type', 'shop_order')
            ->orderBy('id')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    /**
     * @return array<int, array{billing?: array<string, mixed>, shipping?: array<string, mixed>}>
     */
    private function fetchAddresses(string $sql, ?string $table, bool $dryRun): array
    {
        $rows = $dryRun
            ? $this->parseAddressRowsFromSql($sql)
            : ($table ? DB::table($table)->get()->map(fn ($row) => (array) $row)->all() : []);

        $grouped = [];

        foreach ($rows as $row) {
            $orderId = (int) ($row['order_id'] ?? 0);
            $type = (string) ($row['address_type'] ?? '');

            if ($orderId <= 0 || ! in_array($type, ['billing', 'shipping'], true)) {
                continue;
            }

            $grouped[$orderId][$type] = $row;
        }

        return $grouped;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fetchOperationalData(string $sql, ?string $table, bool $dryRun): array
    {
        $rows = $dryRun
            ? $this->parseOperationalRowsFromSql($sql)
            : ($table ? DB::table($table)->get()->map(fn ($row) => (array) $row)->all() : []);

        $grouped = [];

        foreach ($rows as $row) {
            $orderId = (int) ($row['order_id'] ?? 0);

            if ($orderId > 0) {
                $grouped[$orderId] = $row;
            }
        }

        return $grouped;
    }

    /**
     * @return array<int, list<array<string, mixed>>>
     */
    private function fetchLineItems(string $sql, ?string $table, bool $dryRun): array
    {
        $rows = $dryRun
            ? $this->parseLineItemRowsFromSql($sql)
            : ($table ? DB::table($table)->get()->map(fn ($row) => (array) $row)->all() : []);

        $grouped = [];

        foreach ($rows as $row) {
            $orderId = (int) ($row['order_id'] ?? 0);

            if ($orderId > 0) {
                $grouped[$orderId][] = $row;
            }
        }

        return $grouped;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function fetchOrderMeta(string $sql, ?string $table, bool $dryRun): array
    {
        $keys = [
            'date_appointment',
            'time_appointment',
            'beautician_imma',
            'ada_penyakit',
            '_billing_full_name',
        ];

        if ($dryRun) {
            return $this->parseMetaRowsFromSql($sql, $keys);
        }

        if (! $table) {
            return [];
        }

        $rows = DB::table($table)
            ->whereIn('meta_key', $keys)
            ->get(['order_id', 'meta_key', 'meta_value']);

        $grouped = [];

        foreach ($rows as $row) {
            $orderId = (int) $row->order_id;
            $grouped[$orderId][(string) $row->meta_key] = (string) $row->meta_value;
        }

        return $grouped;
    }

    /**
     * @return array<int, list<array{name: string, price: int, quantity: float}>>
     */
    private function fetchChipProductNames(string $sql, ?string $table, bool $dryRun): array
    {
        if ($dryRun) {
            return $this->parseChipProductNamesFromSql($sql);
        }

        if (! $table) {
            return [];
        }

        $rows = DB::table($table)->get(['order_id', 'meta_key', 'meta_value']);

        $grouped = [];

        foreach ($rows as $row) {
            $metaKey = (string) $row->meta_key;

            if (! str_contains($metaKey, '_wc_gateway_chip') || ! str_ends_with($metaKey, '_purchase') || str_ends_with($metaKey, '_purchase_ids')) {
                continue;
            }

            $products = $this->parseChipProductsFromMetaValue((string) $row->meta_value);

            if ($products !== []) {
                $grouped[(int) $row->order_id] = $products;
            }
        }

        return $grouped;
    }

    /**
     * @param array<int, true> $orderIdFilter
     * @return array<int, list<array{name: string, price: int, quantity: float}>>
     */
    private function parseChipProductNamesFromSql(string $sql, array $orderIdFilter = []): array
    {
        $sourceTable = $this->detectSourceTable($sql, '_wc_orders_meta');

        if ($sourceTable === null) {
            return [];
        }

        $filter = $orderIdFilter !== [] ? $orderIdFilter : null;
        $importTable = 'wp_parse_chip_meta';
        $replacedSql = str_replace('`' . $sourceTable . '`', '`' . $importTable . '`', $sql);
        $grouped = [];

        foreach ($this->extractInsertStatements($replacedSql, $importTable) as $statement) {
            foreach ($this->parseInsertTuples($statement) as $tuple) {
                if (count($tuple) < 4) {
                    continue;
                }

                $orderId = (int) $tuple[1];

                if ($filter !== null && ! isset($filter[$orderId])) {
                    continue;
                }

                $metaKey = (string) $tuple[2];

                if (! str_contains($metaKey, '_wc_gateway_chip') || ! str_ends_with($metaKey, '_purchase') || str_ends_with($metaKey, '_purchase_ids')) {
                    continue;
                }

                $products = $this->parseChipProductsFromMetaValue((string) $tuple[3]);

                if ($products === []) {
                    continue;
                }

                $grouped[$orderId] = $products;
            }
        }

        return $grouped;
    }

    /**
     * @return list<array{name: string, price: int, quantity: float}>
     */
    private function parseChipProductsFromMetaValue(string $metaValue): array
    {
        $metaValue = trim($metaValue);

        if ($metaValue === '' || ! str_starts_with($metaValue, 'a:')) {
            return [];
        }

        $parsed = @unserialize(stripslashes($metaValue), ['allowed_classes' => false]);

        if (! is_array($parsed)) {
            return [];
        }

        $purchase = $parsed['purchase'] ?? null;

        if (! is_array($purchase) || ! isset($purchase['products']) || ! is_array($purchase['products'])) {
            return [];
        }

        $products = [];

        foreach ($purchase['products'] as $product) {
            if (! is_array($product)) {
                continue;
            }

            $name = trim((string) ($product['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $products[] = [
                'name' => $name,
                'price' => (int) ($product['price'] ?? 0),
                'quantity' => (float) ($product['quantity'] ?? 1),
            ];
        }

        return $products;
    }

    private function repairProductNames(string $sql, bool $dryRun, ?int $limit): int
    {
        $this->trackingColumn = $this->resolveTrackingColumn();

        if ($this->trackingColumn === null) {
            $this->error('Orders table has no tracking_reference column.');

            return self::FAILURE;
        }

        $wpOrders = DB::table('orders')
            ->where($this->trackingColumn, 'like', self::WP_TRACKING_PREFIX . '%')
            ->orderBy('id')
            ->get(['id', $this->trackingColumn]);

        if ($limit !== null) {
            $wpOrders = $wpOrders->take($limit);
        }

        if ($wpOrders->isEmpty()) {
            $this->warn('No imported WordPress orders found (tracking_reference like WP-%).');

            return self::SUCCESS;
        }

        $wpOrderIds = [];

        foreach ($wpOrders as $order) {
            $wpOrderId = (int) str_replace(self::WP_TRACKING_PREFIX, '', (string) $order->{$this->trackingColumn});

            if ($wpOrderId > 0) {
                $wpOrderIds[$wpOrderId] = true;
            }
        }

        $this->placeholderProductId = (int) (DB::table('products')->where('sku', self::PLACEHOLDER_SKU)->value('id') ?? 0);

        $this->info(sprintf('Parsing SQL dump for %d WordPress order IDs…', count($wpOrderIds)));

        $lineItems = $this->groupLineItemsByOrderId($this->parseLineItemRowsFromSql($sql), $wpOrderIds);
        $chipProducts = $this->parseChipProductNamesFromSql($sql, $wpOrderIds);

        $this->info(sprintf('Repairing product names on %d imported WordPress orders…', $wpOrders->count()));

        $repairedLines = 0;
        $skippedLines = 0;

        $bar = $this->output->createProgressBar($wpOrders->count());
        $bar->start();

        foreach ($wpOrders as $order) {
            $bar->advance();

            $wpOrderId = (int) str_replace(self::WP_TRACKING_PREFIX, '', (string) $order->{$this->trackingColumn});
            $items = $lineItems[$wpOrderId] ?? [];
            $chip = $chipProducts[$wpOrderId] ?? [];
            $orderProducts = DB::table('order_products')->where('order_id', $order->id)->orderBy('id')->get();

            foreach ($orderProducts as $index => $orderProduct) {
                $item = $items[$index] ?? null;
                $wpProductId = (int) ($item['product_id'] ?? 0);
                $lineTotal = round((float) ($orderProduct->line_total ?? $item['product_gross_revenue'] ?? 0), 4);
                $qty = max(1, (int) ($orderProduct->qty ?? $item['product_qty'] ?? 1));
                $currentSku = (string) DB::table('products')->where('id', $orderProduct->product_id)->value('sku');
                $needsRepair = $this->placeholderProductId > 0
                    && (int) $orderProduct->product_id === $this->placeholderProductId;

                if (! $needsRepair && $currentSku !== self::PLACEHOLDER_SKU && ! str_starts_with($currentSku, 'WP-')) {
                    $skippedLines++;

                    continue;
                }

                $productId = $this->resolveLineItemProductId($wpProductId, $lineTotal, $qty, $index, $chip);
                $resolvedName = $this->matchChipProductName($chip, $lineTotal, $qty, $index);

                if ($resolvedName !== null && ! $dryRun) {
                    $this->ensureProductTranslationName($productId, $resolvedName);
                }

                if ((int) $orderProduct->product_id === $productId) {
                    $skippedLines++;

                    continue;
                }

                if (! $dryRun) {
                    DB::table('order_products')
                        ->where('id', $orderProduct->id)
                        ->update(['product_id' => $productId]);
                }

                $repairedLines++;
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Line items repaired', $repairedLines],
                ['Line items skipped', $skippedLines],
            ]
        );

        if ($dryRun) {
            $this->warn('Dry run only — no line items were updated.');
        } else {
            $this->info('Product name repair complete.');
        }

        return self::SUCCESS;
    }

    /**
     * @param list<array<string, mixed>> $rows
     * @param array<int, true> $orderIdFilter
     * @return array<int, list<array<string, mixed>>>
     */
    private function groupLineItemsByOrderId(array $rows, array $orderIdFilter): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $orderId = (int) ($row['order_id'] ?? 0);

            if ($orderId <= 0 || ! isset($orderIdFilter[$orderId])) {
                continue;
            }

            $grouped[$orderId][] = $row;
        }

        return $grouped;
    }

    private function detectSourceTable(string $sql, string $suffix): ?string
    {
        if (preg_match('/CREATE TABLE `([^`]+' . preg_quote($suffix, '/') . ')`/', $sql, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function sanitizeCreateTableSql(string $sql): string
    {
        $sql = str_replace("DEFAULT '0000-00-00 00:00:00'", 'DEFAULT NULL', $sql);
        $sql = preg_replace('/`date_created` datetime NOT NULL DEFAULT NULL/', '`date_created` datetime NULL DEFAULT NULL', $sql);

        return trim($sql);
    }

    /**
     * @return list<string>
     */
    private function extractInsertStatements(string $sql, string $table): array
    {
        $statements = [];
        $needle = 'INSERT INTO `' . $table . '`';
        $offset = 0;
        $length = strlen($sql);

        while (($position = strpos($sql, $needle, $offset)) !== false) {
            $end = $this->findSqlStatementEnd($sql, $position, $length);

            if ($end === null) {
                break;
            }

            $statements[] = substr($sql, $position, $end - $position + 1);
            $offset = $end + 1;
        }

        return $statements;
    }

    private function findSqlStatementEnd(string $sql, int $start, int $length): ?int
    {
        $inString = false;

        for ($i = $start; $i < $length; $i++) {
            $char = $sql[$i];

            if ($inString) {
                if ($char === '\\' && $i + 1 < $length) {
                    $i++;

                    continue;
                }

                if ($char === "'") {
                    if ($i + 1 < $length && $sql[$i + 1] === "'") {
                        $i++;

                        continue;
                    }

                    $inString = false;
                }

                continue;
            }

            if ($char === "'") {
                $inString = true;

                continue;
            }

            if ($char === ';') {
                return $i;
            }
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseOrdersFromSql(string $sql, string $sourceTable): array
    {
        if ($sourceTable === '') {
            return [];
        }

        $importTable = 'wp_parse_orders';
        $replacedSql = str_replace('`' . $sourceTable . '`', '`' . $importTable . '`', $sql);
        $rows = [];

        foreach ($this->extractInsertStatements($replacedSql, $importTable) as $statement) {
            foreach ($this->parseInsertTuples($statement) as $tuple) {
                if (count($tuple) < 10) {
                    continue;
                }

                if (($tuple[3] ?? '') !== 'shop_order') {
                    continue;
                }

                $rows[] = [
                    'id' => (int) $tuple[0],
                    'status' => $tuple[1],
                    'currency' => $tuple[2],
                    'type' => $tuple[3],
                    'tax_amount' => $tuple[4],
                    'total_amount' => $tuple[5],
                    'customer_id' => $tuple[6],
                    'billing_email' => $tuple[7],
                    'date_created_gmt' => $tuple[8],
                    'date_updated_gmt' => $tuple[9],
                    'payment_method' => $tuple[11] ?? '',
                    'payment_method_title' => $tuple[12] ?? '',
                    'transaction_id' => $tuple[13] ?? '',
                    'customer_note' => $tuple[16] ?? '',
                ];
            }
        }

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseAddressRowsFromSql(string $sql): array
    {
        $sourceTable = $this->detectSourceTable($sql, '_wc_order_addresses');

        if ($sourceTable === null) {
            return [];
        }

        $importTable = 'wp_parse_addresses';
        $replacedSql = str_replace('`' . $sourceTable . '`', '`' . $importTable . '`', $sql);
        $rows = [];

        foreach ($this->extractInsertStatements($replacedSql, $importTable) as $statement) {
            foreach ($this->parseInsertTuples($statement) as $tuple) {
                if (count($tuple) < 14) {
                    continue;
                }

                $rows[] = [
                    'order_id' => (int) $tuple[1],
                    'address_type' => $tuple[2],
                    'first_name' => $tuple[3],
                    'last_name' => $tuple[4],
                    'address_1' => $tuple[6],
                    'address_2' => $tuple[7],
                    'city' => $tuple[8],
                    'state' => $tuple[9],
                    'postcode' => $tuple[10],
                    'country' => $tuple[11],
                    'email' => $tuple[12],
                    'phone' => $tuple[13],
                ];
            }
        }

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseOperationalRowsFromSql(string $sql): array
    {
        $sourceTable = $this->detectSourceTable($sql, '_wc_order_operational_data');

        if ($sourceTable === null) {
            return [];
        }

        $importTable = 'wp_parse_operational';
        $replacedSql = str_replace('`' . $sourceTable . '`', '`' . $importTable . '`', $sql);
        $rows = [];

        foreach ($this->extractInsertStatements($replacedSql, $importTable) as $statement) {
            foreach ($this->parseInsertTuples($statement) as $tuple) {
                if (count($tuple) < 18) {
                    continue;
                }

                $rows[] = [
                    'order_id' => (int) $tuple[1],
                    'date_paid_gmt' => $tuple[11],
                    'date_completed_gmt' => $tuple[12],
                    'shipping_total_amount' => $tuple[14],
                    'discount_total_amount' => $tuple[16],
                ];
            }
        }

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseLineItemRowsFromSql(string $sql): array
    {
        $sourceTable = $this->detectSourceTable($sql, '_wc_order_product_lookup');

        if ($sourceTable === null) {
            return [];
        }

        $importTable = 'wp_parse_products';
        $replacedSql = str_replace('`' . $sourceTable . '`', '`' . $importTable . '`', $sql);
        $rows = [];

        foreach ($this->extractInsertStatements($replacedSql, $importTable) as $statement) {
            foreach ($this->parseInsertTuples($statement) as $tuple) {
                if (count($tuple) < 8) {
                    continue;
                }

                $rows[] = [
                    'order_id' => (int) $tuple[1],
                    'product_id' => (int) $tuple[2],
                    'product_qty' => (int) $tuple[6],
                    'product_gross_revenue' => (float) $tuple[8],
                ];
            }
        }

        return $rows;
    }

    /**
     * @param list<string> $keys
     * @return array<int, array<string, string>>
     */
    private function parseMetaRowsFromSql(string $sql, array $keys): array
    {
        $sourceTable = $this->detectSourceTable($sql, '_wc_orders_meta');

        if ($sourceTable === null) {
            return [];
        }

        $importTable = 'wp_parse_meta';
        $replacedSql = str_replace('`' . $sourceTable . '`', '`' . $importTable . '`', $sql);
        $grouped = [];

        foreach ($this->extractInsertStatements($replacedSql, $importTable) as $statement) {
            foreach ($this->parseInsertTuples($statement) as $tuple) {
                if (count($tuple) < 4) {
                    continue;
                }

                $metaKey = (string) $tuple[2];

                if (! in_array($metaKey, $keys, true)) {
                    continue;
                }

                $orderId = (int) $tuple[1];
                $grouped[$orderId][$metaKey] = (string) $tuple[3];
            }
        }

        return $grouped;
    }

    /**
     * @return list<list<string>>
     */
    private function parseInsertTuples(string $insertSql): array
    {
        if (! preg_match('/VALUES\s*(.+);\s*$/s', $insertSql, $valueMatch)) {
            return [];
        }

        return $this->parseSqlTuples($valueMatch[1]);
    }

    /**
     * @return list<list<string>>
     */
    private function parseSqlTuples(string $valuesBlob): array
    {
        $tuples = [];
        $length = strlen($valuesBlob);
        $i = 0;

        while ($i < $length) {
            while ($i < $length && $valuesBlob[$i] !== '(') {
                $i++;
            }

            if ($i >= $length) {
                break;
            }

            $i++;
            $tuple = [];
            $current = '';
            $inString = false;

            while ($i < $length) {
                $char = $valuesBlob[$i];

                if ($inString) {
                    if ($char === '\\' && $i + 1 < $length) {
                        $current .= $valuesBlob[$i + 1];
                        $i += 2;

                        continue;
                    }

                    if ($char === "'") {
                        if ($i + 1 < $length && $valuesBlob[$i + 1] === "'") {
                            $current .= "'";
                            $i += 2;

                            continue;
                        }

                        $inString = false;
                        $i++;

                        continue;
                    }

                    $current .= $char;
                    $i++;

                    continue;
                }

                if ($char === "'") {
                    $inString = true;
                    $i++;

                    continue;
                }

                if ($char === ',') {
                    $tuple[] = $this->normalizeSqlValue(trim($current));
                    $current = '';
                    $i++;

                    continue;
                }

                if ($char === ')') {
                    $tuple[] = $this->normalizeSqlValue(trim($current));
                    $tuples[] = $tuple;
                    $i++;

                    break;
                }

                $current .= $char;
                $i++;
            }
        }

        return $tuples;
    }

    private function normalizeSqlValue(string $value): string
    {
        if ($value === 'NULL') {
            return '';
        }

        return $value;
    }

    private function resolveFilePath(string $file): ?string
    {
        $file = trim($file);

        if ($file === '' || $file === '/path/to/file.sql') {
            return null;
        }

        $candidates = [$file];

        if (! str_starts_with($file, '/')) {
            $candidates[] = base_path($file);
            $candidates[] = storage_path('app/' . ltrim($file, '/'));
        }

        foreach ($candidates as $candidate) {
            if (is_readable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
