<?php

namespace Modules\User\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\User\Entities\Role;
use Modules\User\Entities\User;
use Modules\User\Sentinel\WordPressCompatibleHasher;

class ImportWordPressCustomersCommand extends Command
{
    protected $signature = 'user:import-wordpress-customers
                            {file : Path to WordPress users SQL dump}
                            {--dry-run : Preview without writing}
                            {--table=wp_import_users : Temporary import table name}';

    protected $description = 'Import WordPress / WooCommerce users into FleetCart as customers.';

    public function handle(): int
    {
        $file = $this->argument('file');
        $dryRun = (bool) $this->option('dry-run');
        $table = (string) $this->option('table');

        if (! is_readable($file)) {
            $this->error("File not readable: {$file}");

            return self::FAILURE;
        }

        $customerRoleId = (int) setting('customer_role');

        if ($customerRoleId <= 0) {
            $this->error('Customer role is not configured (setting customer_role).');

            return self::FAILURE;
        }

        $customerRole = Role::find($customerRoleId);

        if (! $customerRole) {
            $this->error("Customer role id {$customerRoleId} not found.");

            return self::FAILURE;
        }

        $this->info('Loading WordPress users from SQL dump…');

        $rows = $this->loadRowsFromSqlDump($file, $table);

        if ($rows === []) {
            $this->error('No users found in SQL dump.');

            return self::FAILURE;
        }

        $this->info(sprintf('Found %d WordPress user rows.', count($rows)));

        $existingEmails = User::query()
            ->pluck('email')
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->flip()
            ->all();

        $hasher = new WordPressCompatibleHasher();
        $now = Carbon::now();
        $imported = 0;
        $skippedExisting = 0;
        $skippedInvalid = 0;
        $skippedDuplicate = 0;
        $seenEmails = [];

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        $pending = [];

        foreach ($rows as $row) {
            $bar->advance();

            $email = strtolower(trim((string) ($row['user_email'] ?? '')));

            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skippedInvalid++;

                continue;
            }

            if ((int) ($row['user_status'] ?? 0) !== 0) {
                $skippedInvalid++;

                continue;
            }

            if (isset($seenEmails[$email])) {
                $skippedDuplicate++;

                continue;
            }

            $seenEmails[$email] = true;

            if (isset($existingEmails[$email])) {
                $skippedExisting++;

                continue;
            }

            [$firstName, $lastName] = $this->splitName(
                (string) ($row['display_name'] ?? ''),
                (string) ($row['user_login'] ?? ''),
            );

            $password = $hasher->normalizeStoredHash((string) ($row['user_pass'] ?? ''));

            if ($password === '') {
                $skippedInvalid++;

                continue;
            }

            if ($dryRun) {
                $imported++;

                continue;
            }

            $pending[] = [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'password' => $password,
                'created_at' => $this->parseRegisteredAt($row['user_registered'] ?? null) ?? $now,
            ];

            if (count($pending) >= 100) {
                $imported += $this->insertCustomerBatch($pending, $customerRoleId, $now);
                $pending = [];
            }
        }

        if ($pending !== [] && ! $dryRun) {
            $imported += $this->insertCustomerBatch($pending, $customerRoleId, $now);
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Imported', $imported],
                ['Skipped (already in FleetCart)', $skippedExisting],
                ['Skipped (duplicate in dump)', $skippedDuplicate],
                ['Skipped (invalid)', $skippedInvalid],
            ]
        );

        if ($dryRun) {
            $this->warn('Dry run only — no records were written.');
        } else {
            Schema::dropIfExists($table);
            $this->info('Import complete. WordPress password hashes are preserved for customer login.');
        }

        return self::SUCCESS;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function loadRowsFromSqlDump(string $file, string $table): array
    {
        $sql = file_get_contents($file);

        if ($sql === false) {
            return [];
        }

        $sourceTable = $this->detectSourceTableName($sql);

        if ($sourceTable === null) {
            return [];
        }

        $sql = str_replace($sourceTable, $table, $sql);
        $insertSql = $this->extractInsertStatements($sql, $table);

        if ($insertSql === '') {
            return [];
        }

        if (! $this->option('dry-run')) {
            $this->prepareImportTable($table);
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table($table)->truncate();
            DB::unprepared($insertSql);
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        if ($this->option('dry-run')) {
            return $this->parseInsertRowsFromSql($insertSql, $table);
        }

        return DB::table($table)
            ->select([
                'user_email',
                'user_pass',
                'display_name',
                'user_login',
                'user_registered',
                'user_status',
            ])
            ->orderBy('ID')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    private function detectSourceTableName(string $sql): ?string
    {
        if (preg_match('/CREATE TABLE `([^`]+)`/', $sql, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function prepareImportTable(string $table): void
    {
        Schema::dropIfExists($table);
        $this->createImportTable($table);
    }

    private function extractInsertStatements(string $sql, string $table): string
    {
        $pattern = '/INSERT INTO `' . preg_quote($table, '/') . '`[^;]+;/s';

        if (! preg_match_all($pattern, $sql, $matches)) {
            return '';
        }

        return implode("\n", $matches[0]);
    }

    private function createImportTable(string $table): void
    {
        Schema::create($table, function ($blueprint) {
            $blueprint->unsignedBigInteger('ID')->primary();
            $blueprint->string('user_login', 60)->default('');
            $blueprint->string('user_pass', 255)->default('');
            $blueprint->string('user_nicename', 50)->default('');
            $blueprint->string('user_email', 100)->default('');
            $blueprint->string('user_url', 100)->default('');
            $blueprint->dateTime('user_registered')->nullable();
            $blueprint->string('user_activation_key', 255)->default('');
            $blueprint->integer('user_status')->default(0);
            $blueprint->string('display_name', 250)->default('');
        });
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function parseInsertRowsFromSql(string $sql, string $table): array
    {
        $pattern = '/INSERT INTO `' . preg_quote($table, '/') . '`[^;]+;/s';

        if (! preg_match_all($pattern, $sql, $matches)) {
            return [];
        }

        $rows = [];

        foreach ($matches[0] as $statement) {
            if (! preg_match('/VALUES\s*(.+);\s*$/s', $statement, $valueMatch)) {
                continue;
            }

            foreach ($this->parseSqlTuples($valueMatch[1]) as $tuple) {
                if (count($tuple) < 10) {
                    continue;
                }

                $rows[] = [
                    'user_email' => $tuple[4],
                    'user_pass' => $tuple[2],
                    'display_name' => $tuple[9],
                    'user_login' => $tuple[1],
                    'user_registered' => $tuple[6],
                    'user_status' => (int) $tuple[8],
                ];
            }
        }

        return $rows;
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
                    $tuple[] = trim($current);
                    $current = '';
                    $i++;

                    continue;
                }

                if ($char === ')') {
                    $tuple[] = trim($current);
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

    /**
     * @return array{0: string, 1: string}
     */
    private function splitName(string $displayName, string $login): array
    {
        $displayName = trim($displayName);
        $login = trim($login);

        if ($displayName !== '') {
            $parts = preg_split('/\s+/', $displayName, 2) ?: [];

            $first = trim((string) ($parts[0] ?? ''));
            $last = trim((string) ($parts[1] ?? ''));

            if ($first !== '' && $last !== '') {
                return [Str::limit($first, 50, ''), Str::limit($last, 50, '')];
            }

            if ($first !== '') {
                return [Str::limit($first, 50, ''), 'Customer'];
            }
        }

        $fallback = $login !== '' ? $login : 'Customer';

        return [Str::limit($fallback, 50, ''), 'Customer'];
    }

    private function parseRegisteredAt(mixed $value): ?Carbon
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

    /**
     * @param list<array<string, mixed>> $batch
     */
    private function insertCustomerBatch(array $batch, int $customerRoleId, Carbon $now): int
    {
        return (int) DB::transaction(function () use ($batch, $customerRoleId, $now) {
            $count = 0;

            foreach ($batch as $item) {
                $userId = DB::table('users')->insertGetId([
                    'first_name' => $item['first_name'],
                    'last_name' => $item['last_name'],
                    'email' => $item['email'],
                    'phone' => '',
                    'password' => $item['password'],
                    'permissions' => null,
                    'last_login' => null,
                    'created_at' => $item['created_at'],
                    'updated_at' => $now,
                ]);

                DB::table('user_roles')->insert([
                    'user_id' => $userId,
                    'role_id' => $customerRoleId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('activations')->insert([
                    'user_id' => $userId,
                    'code' => Str::random(32),
                    'completed' => true,
                    'completed_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $count++;
            }

            return $count;
        });
    }
}
