<?php

namespace Modules\Order\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Applies Orders index list filters (month, date range, search).
 * Invalid input is ignored so DataTables AJAX stays resilient.
 */
final class OrderIndexQueryFilter
{
    public function apply(Builder $query, Request $request): void
    {
        $payload = Validator::make($request->only([
            'month',
            'date_from',
            'date_to',
            'search',
            'date',
        ]), [
            'month' => ['nullable', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
            'search' => ['nullable', 'string', 'max:100'],
            'date' => ['nullable', 'in:today'],
        ])->valid();

        $this->applyCreatedAtFilters(
            $query,
            isset($payload['month']) ? (string) $payload['month'] : null,
            isset($payload['date_from']) ? (string) $payload['date_from'] : null,
            isset($payload['date_to']) ? (string) $payload['date_to'] : null,
            isset($payload['date']) ? (string) $payload['date'] : null,
        );

        if (! empty($payload['search'])) {
            $this->applySearch($query, trim((string) $payload['search']));
        }
    }

    private function applyCreatedAtFilters(
        Builder $query,
        ?string $month,
        ?string $dateFrom,
        ?string $dateTo,
        ?string $legacyDate,
    ): void {
        $from = $this->parseDate($dateFrom);
        $to = $this->parseDate($dateTo);

        if ($from !== null && $to !== null && $from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        // Explicit range wins over month / legacy "today".
        if ($from !== null || $to !== null) {
            if ($from !== null) {
                $query->where('created_at', '>=', $from->copy()->startOfDay());
            }

            if ($to !== null) {
                $query->where('created_at', '<=', $to->copy()->endOfDay());
            }

            return;
        }

        if ($month !== null && $month !== '') {
            try {
                $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $end = $start->copy()->endOfMonth();
                $query->whereBetween('created_at', [$start, $end]);
            } catch (\Throwable) {
                // ignore malformed month
            }

            return;
        }

        if ($legacyDate === 'today') {
            $query->whereDate('created_at', today());
        }
    }

    private function applySearch(Builder $query, string $term): void
    {
        if ($term === '') {
            return;
        }

        $like = '%' . addcslashes($term, '%_\\') . '%';

        $query->where(function (Builder $builder) use ($term, $like): void {
            if (ctype_digit($term)) {
                $builder->orWhere('id', (int) $term);
            }

            $builder
                ->orWhere('customer_first_name', 'like', $like)
                ->orWhere('customer_last_name', 'like', $like)
                ->orWhere('customer_email', 'like', $like)
                ->orWhere('customer_phone', 'like', $like)
                ->orWhereRaw(
                    "CONCAT(COALESCE(customer_first_name, ''), ' ', COALESCE(customer_last_name, '')) LIKE ?",
                    [$like]
                )
                ->orWhereHas('transaction', function (Builder $transactionQuery) use ($like): void {
                    $transactionQuery->where('transaction_id', 'like', $like);
                });
        });
    }

    private function parseDate(?string $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
