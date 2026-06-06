<?php

namespace Modules\Report\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Order\Entities\OrderProduct;
use Modules\Support\Money;

class ReportFormatters
{
    public static function customerName(object $row): string
    {
        return trim(($row->customer_first_name ?? '') . ' ' . ($row->customer_last_name ?? ''));
    }

    public static function contact(object $row): string
    {
        return trim(implode(' / ', array_filter([
            $row->customer_phone ?? null,
            $row->customer_email ?? null,
        ]))) ?: '—';
    }

    public static function spaBranchName(object $row): string
    {
        $name = trim((string) ($row->spa_branch_name ?? ''));

        return $name !== '' ? $name : '—';
    }

    public static function beauticianAppointment(object $row): string
    {
        $beautician = trim((string) ($row->beautician_name ?? ''));
        $hasAppointment = ! empty($row->appointment_date);
        $appointment = $hasAppointment ? static::appointment($row) : null;

        $lines = array_values(array_filter([
            $beautician !== '' ? $beautician : null,
            $appointment,
        ]));

        return $lines !== [] ? implode("\n", $lines) : '—';
    }

    public static function appointment(object $row): string
    {
        if (empty($row->appointment_date)) {
            return '—';
        }

        $date = $row->appointment_date instanceof Carbon
            ? $row->appointment_date->format('d M Y')
            : Carbon::parse($row->appointment_date)->format('d M Y');

        $time = trim((string) ($row->appointment_time ?? ''));

        return $time !== '' ? "{$date} {$time}" : $date;
    }

    public static function orderStatus(?string $status): string
    {
        if ($status === null || $status === '') {
            return '—';
        }

        $label = trans('order::statuses.' . $status);

        return $label !== 'order::statuses.' . $status ? $label : $status;
    }

    public static function paymentStatus(?string $status): string
    {
        if ($status === null || $status === '') {
            return trans('order::payment_statuses.pending');
        }

        $label = trans('order::payment_statuses.' . $status);

        return $label !== 'order::payment_statuses.' . $status
            ? $label
            : ucfirst(str_replace('_', ' ', $status));
    }

    public static function orderProductOptionLines(OrderProduct $line): array
    {
        $parts = [];

        foreach ($line->options as $option) {
            $values = $option->values->pluck('label')->filter()->implode(', ');

            if ($values !== '') {
                $parts[] = $option->name . ': ' . $values;
            } elseif (! empty($option->value)) {
                $parts[] = $option->name . ': ' . $option->value;
            }
        }

        foreach ($line->variations as $variation) {
            $label = trim((string) ($variation->value ?? ''));

            if ($label !== '') {
                $parts[] = $label;
            }
        }

        return $parts;
    }

    public static function orderProductOptions(OrderProduct $line): string
    {
        $parts = static::orderProductOptionLines($line);

        return $parts !== [] ? implode(' · ', $parts) : '—';
    }

    public static function orderDate($value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return ($value instanceof Carbon ? $value : Carbon::parse($value))->format('d M Y H:i');
    }

    public static function orderLinesQty(Collection $lines): int
    {
        return (int) $lines->sum('qty');
    }

    public static function orderLinesTotal(Collection $lines): Money
    {
        return Money::inDefaultCurrency($lines->sum(fn (OrderProduct $line) => $line->line_total->amount()));
    }

    /**
     * @param  Collection<int, OrderProduct>  $lines
     */
    public static function orderProductsSummary(Collection $lines): array
    {
        return $lines->map(function (OrderProduct $line) {
            $options = static::orderProductOptions($line);

            return [
                'name' => $line->name,
                'product_id' => $line->product_id,
                'qty' => $line->qty,
                'unit_price' => $line->unit_price->format(),
                'line_total' => $line->line_total->format(),
                'options' => $options !== '—' ? $options : null,
                'trashed' => $line->trashed(),
            ];
        })->all();
    }

    public static function orderProductOptionValues(OrderProduct $line): string
    {
        $parts = [];

        foreach ($line->options as $option) {
            $values = $option->values->pluck('label')->filter()->implode(', ');

            if ($values !== '') {
                $parts[] = $values;
            } elseif (! empty($option->value)) {
                $parts[] = $option->value;
            }
        }

        foreach ($line->variations as $variation) {
            $label = trim((string) ($variation->value ?? ''));

            if ($label !== '') {
                $parts[] = $label;
            }
        }

        return implode(', ', $parts);
    }

    public static function orderProductLineMeta(OrderProduct $line): string
    {
        $text = '× ' . $line->qty;
        $options = static::orderProductOptionValues($line);

        if ($options !== '') {
            $text .= ' - ' . $options;
        }

        return $text . ' - ' . $line->line_total->format();
    }

    public static function orderProductLineLabel(OrderProduct $line): string
    {
        return $line->name . ' ' . static::orderProductLineMeta($line);
    }

    public static function orderProductMultilineLabel(OrderProduct $line): string
    {
        return $line->name . "\n" . static::orderProductLineMeta($line);
    }

    public static function orderProductsText(Collection $lines): string
    {
        return $lines
            ->map(fn (OrderProduct $line) => static::orderProductLineLabel($line))
            ->implode('; ');
    }

    public static function orderProductsMultilineText(Collection $lines): string
    {
        return $lines
            ->map(fn (OrderProduct $line) => static::orderProductMultilineLabel($line))
            ->implode("\n\n");
    }

    public static function orderOptionsText(Collection $lines): string
    {
        $parts = $lines
            ->map(fn (OrderProduct $line) => static::orderProductOptions($line))
            ->filter(fn (string $value) => $value !== '—')
            ->unique()
            ->values();

        return $parts->isNotEmpty() ? $parts->implode(' · ') : '—';
    }
}
