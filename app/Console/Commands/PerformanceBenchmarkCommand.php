<?php

namespace AestheticCart\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PerformanceBenchmarkCommand extends Command
{
    protected $signature = 'performance:benchmark
        {--connection= : Laravel database connection}
        {--iterations=3 : Repetitions per read-only query (1-10)}
        {--explain : Include EXPLAIN FORMAT=JSON access-path summaries}
        {--json : Emit machine-readable JSON}';

    protected $description = 'Run bounded read-only benchmarks for critical production query paths';

    public function handle(): int
    {
        $connection = DB::connection($this->option('connection') ?: null);
        $iterations = max(1, min(10, (int) $this->option('iterations')));

        if ($connection->getDriverName() !== 'mysql') {
            $this->error('The production benchmark currently supports MySQL/MariaDB only.');

            return self::FAILURE;
        }

        $report = [
            'environment' => app()->environment(),
            'connection' => $connection->getName(),
            'driver' => $connection->getDriverName(),
            'server_version' => (string) $connection->selectOne('SELECT VERSION() AS version')->version,
            'iterations' => $iterations,
            'mode' => 'read-only',
            'benchmarks' => [],
        ];

        foreach ($this->queries($connection) as $name => [$sql, $bindings]) {
            $times = [];
            $rowCount = 0;

            for ($iteration = 0; $iteration < $iterations; $iteration++) {
                $startedAt = hrtime(true);
                $rows = $connection->select($sql, $bindings);
                $times[] = (hrtime(true) - $startedAt) / 1_000_000;
                $rowCount = count($rows);
            }

            sort($times);
            $result = [
                'rows_returned' => $rowCount,
                'min_ms' => number_format($times[0], 3, '.', ''),
                'median_ms' => number_format($times[(int) floor((count($times) - 1) / 2)], 3, '.', ''),
                'p95_ms' => number_format($times[(int) floor((count($times) - 1) * 0.95)], 3, '.', ''),
            ];

            if ($this->option('explain')) {
                $result['plan'] = $this->explain($connection, $sql, $bindings);
            }

            $report['benchmarks'][$name] = $result;
        }

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Environment: %s | Connection: %s | Server: %s | Mode: read-only',
            $report['environment'],
            $report['connection'],
            $report['server_version']
        ));

        $rows = [];
        foreach ($report['benchmarks'] as $name => $result) {
            $rows[] = [
                $name,
                $result['rows_returned'],
                $result['min_ms'],
                $result['median_ms'],
                $result['p95_ms'],
                $this->formatPlan($result['plan'] ?? []),
            ];
        }

        $this->table(['Query path', 'Rows', 'Min ms', 'Median ms', 'P95 ms', 'Plan'], $rows);

        return self::SUCCESS;
    }

    /**
     * @return array<string, array{0: string, 1: array<int, mixed>}>
     */
    private function queries(Connection $connection): array
    {
        $queries = [
            'orders_recent_report' => [
                <<<'SQL'
                    SELECT id, status, payment_status, total, currency, created_at
                    FROM orders
                    WHERE created_at >= ?
                      AND deleted_at IS NULL
                    ORDER BY created_at DESC
                    LIMIT 25
                SQL,
                [now()->subDays(30)->startOfDay()->toDateTimeString()],
            ],
            'product_category_lookup' => [
                <<<'SQL'
                    SELECT product_id
                    FROM product_categories
                    WHERE category_id = ?
                    ORDER BY product_id
                    LIMIT 25
                SQL,
                [(int) ($connection->table('product_categories')->min('category_id') ?? 0)],
            ],
        ];

        $orderSlot = $connection->selectOne(<<<'SQL'
            SELECT beautician_id, appointment_date, appointment_time
            FROM orders
            WHERE beautician_id IS NOT NULL
              AND appointment_date IS NOT NULL
              AND appointment_time IS NOT NULL
            ORDER BY id DESC
            LIMIT 1
        SQL);

        if ($orderSlot !== null) {
            $queries['order_slot_collision_check'] = [
                <<<'SQL'
                    SELECT id
                    FROM orders
                    WHERE beautician_id = ?
                      AND appointment_date = ?
                      AND appointment_time = ?
                      AND status IN ('pending_payment','pending','processing','on_hold','completed')
                      AND deleted_at IS NULL
                    LIMIT 1
                SQL,
                [$orderSlot->beautician_id, $orderSlot->appointment_date, $orderSlot->appointment_time],
            ];
        }

        $bookingSlot = $connection->selectOne(<<<'SQL'
            SELECT beautician_id, appointment_date, appointment_time
            FROM treatment_bookings
            WHERE beautician_id IS NOT NULL
              AND appointment_date IS NOT NULL
              AND appointment_time IS NOT NULL
            ORDER BY id DESC
            LIMIT 1
        SQL);

        if ($bookingSlot !== null) {
            $queries['booking_slot_collision_check'] = [
                <<<'SQL'
                    SELECT id
                    FROM treatment_bookings
                    WHERE beautician_id = ?
                      AND appointment_date = ?
                      AND appointment_time = ?
                      AND status IN ('pending','in_progress')
                      AND deleted_at IS NULL
                    LIMIT 1
                SQL,
                [$bookingSlot->beautician_id, $bookingSlot->appointment_date, $bookingSlot->appointment_time],
            ];
        }

        return $queries;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function explain(Connection $connection, string $sql, array $bindings): array
    {
        $row = $connection->selectOne('EXPLAIN FORMAT=JSON ' . $sql, $bindings);
        $json = json_decode((string) ($row->EXPLAIN ?? '{}'), true);
        $plans = [];

        $walk = function (mixed $node) use (&$walk, &$plans): void {
            if (! is_array($node)) {
                return;
            }

            if (isset($node['table_name'])) {
                $plans[] = Arr::only($node, [
                    'table_name',
                    'access_type',
                    'key',
                    'rows_examined_per_scan',
                    'rows_produced_per_join',
                ]);
            }

            foreach ($node as $value) {
                $walk($value);
            }
        };

        $walk($json);

        return $plans;
    }

    private function formatPlan(array $plans): string
    {
        if ($plans === []) {
            return 'not requested';
        }

        return collect($plans)->map(function (array $plan): string {
            return sprintf(
                '%s:%s:%s',
                $plan['table_name'] ?? '?',
                $plan['access_type'] ?? '?',
                $plan['key'] ?? 'no-index'
            );
        })->implode(', ');
    }
}
