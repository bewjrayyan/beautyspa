<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\User\Support\PhoneNumber;

return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $phoneColumns = [
        'orders' => 'customer_phone',
        'treatment_bookings' => 'customer_phone',
        'users' => 'phone',
        'beauticians' => 'phone',
    ];


    public function up(): void
    {
        foreach ($this->phoneColumns as $table => $column) {
            if (! $this->tableHasColumn($table, $column)) {
                continue;
            }

            DB::table($table)
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($table, $column) {
                    foreach ($rows as $row) {
                        $current = (string) $row->{$column};
                        $normalized = PhoneNumber::normalize($current);

                        if ($normalized === '' || $normalized === $current) {
                            continue;
                        }

                        DB::table($table)
                            ->where('id', $row->id)
                            ->update([$column => $normalized]);
                    }
                });
        }
    }


    public function down(): void
    {
        // Non-reversible data cleanup.
    }


    private function tableHasColumn(string $table, string $column): bool
    {
        return DB::getSchemaBuilder()->hasTable($table)
            && DB::getSchemaBuilder()->hasColumn($table, $column);
    }
};
