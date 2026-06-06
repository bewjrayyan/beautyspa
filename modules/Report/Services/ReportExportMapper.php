<?php

namespace Modules\Report\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Modules\Report\Report;
use Modules\Report\Support\ReportFormatters;

class ReportExportMapper
{
    public static function build(Report $report, Request $request, string $type): array
    {
        $rows = $report->report($request)->limit(5000)->get();

        return [
            'title' => trans('report::admin.filters.report_types.' . $type),
            'headings' => static::headings($type),
            'rows' => static::mapRows($type, $rows),
        ];
    }


    private static function headings(string $type): array
    {
        $spaBranchEnabled = is_module_enabled('SpaBranch');

        return match ($type) {
            'sales_report' => static::t(static::withSpaBranch([
                'order', 'order_date', 'customer_name', 'contact', 'beautician_appointment', 'order_status', 'payment_status',
                'products', 'subtotal', 'shipping', 'discount', 'tax', 'grand_total',
            ], $spaBranchEnabled, 'contact')),
            'coupons_report' => static::t([
                'date', 'coupon_name', 'coupon_code', 'orders', 'unique_customers', 'discount', 'orders_total',
            ]),
            'customers_order_report' => static::t(static::withSpaBranch([
                'order', 'order_date', 'customer_name', 'customer_email', 'customer_phone', 'customer_group',
                'beautician_appointment', 'order_status', 'payment_status', 'products', 'grand_total',
            ], $spaBranchEnabled, 'customer_group')),
            'products_purchase_report' => static::t(static::withSpaBranch([
                'order', 'order_date', 'product', 'qty', 'grand_total',
                'customer_name', 'contact', 'beautician_appointment', 'order_status', 'payment_status',
            ], $spaBranchEnabled, 'contact')),
            'products_view_report' => static::t([
                'product', 'views',
            ]),
            'products_stock_report' => static::t([
                'product', 'qty', 'stock_availability',
            ]),
            'branded_products_report' => static::t([
                'brand', 'products_count',
            ]),
            'categorized_products_report' => static::t([
                'category', 'products_count',
            ]),
            'taxed_products_report' => static::t([
                'tax_class', 'products_count',
            ]),
            'tagged_products_report' => static::t([
                'tag', 'products_count',
            ]),
            'search_report' => static::t([
                'keyword', 'results', 'hits',
            ]),
            'shipping_report' => static::t([
                'date', 'shipping_method', 'orders', 'unique_customers', 'avg_shipping', 'total',
            ]),
            'tax_report' => static::t([
                'date', 'tax_name', 'orders', 'total',
            ]),
            'beautician_bookings_report' => static::t(static::withSpaBranch([
                'appointment', 'customer', 'product', 'beautician', 'contact', 'order_status', 'payment_status', 'total',
            ], $spaBranchEnabled, 'beautician')),
            'loyalty_report' => [
                trans('loyalty::reports.date'),
                trans('loyalty::reports.customer'),
                trans('loyalty::reports.type'),
                trans('loyalty::reports.points'),
                trans('loyalty::reports.balance'),
                trans('loyalty::reports.description'),
            ],
            default => [],
        };
    }


    private static function mapRows(string $type, Collection $rows): array
    {
        $spaBranchEnabled = is_module_enabled('SpaBranch');

        return match ($type) {
            'sales_report' => $rows->map(fn ($row) => static::withSpaBranchRow([
                '#' . $row->order_id,
                ReportFormatters::orderDate($row->order_date),
                ReportFormatters::customerName($row),
                static::contact($row),
                ReportFormatters::beauticianAppointment($row),
                ReportFormatters::orderStatus($row->order_status),
                ReportFormatters::paymentStatus($row->payment_status),
                $row->total_products,
                static::money($row->sub_total),
                static::money($row->shipping_cost),
                static::money($row->discount),
                static::money($row->tax),
                static::money($row->total),
            ], $spaBranchEnabled, $row, 4))->all(),
            'coupons_report' => $rows->map(fn ($row) => [
                static::dateRange($row->start_date, $row->end_date),
                $row->name,
                $row->code,
                $row->total_orders,
                $row->unique_customers,
                static::money($row->total),
                static::money($row->orders_total),
            ])->all(),
            'customers_order_report' => $rows->map(fn ($row) => static::withSpaBranchRow([
                '#' . $row->order_id,
                ReportFormatters::orderDate($row->order_date),
                ReportFormatters::customerName($row),
                $row->customer_email,
                $row->customer_phone ?: '—',
                is_null($row->customer_id)
                    ? trans('report::admin.table.guest')
                    : trans('report::admin.table.registered'),
                ReportFormatters::beauticianAppointment($row),
                ReportFormatters::orderStatus($row->order_status),
                ReportFormatters::paymentStatus($row->payment_status),
                $row->total_products,
                static::money($row->total),
            ], $spaBranchEnabled, $row, 6))->all(),
            'products_purchase_report' => $rows->map(function ($row) use ($spaBranchEnabled) {
                $lines = $row->products;

                return static::withSpaBranchRow([
                    '#' . $row->order_id,
                    ReportFormatters::orderDate($row->order_date),
                    ReportFormatters::orderProductsMultilineText($lines),
                    ReportFormatters::orderLinesQty($lines),
                    static::money($row->total),
                    ReportFormatters::customerName($row),
                    static::contact($row),
                    ReportFormatters::beauticianAppointment($row),
                    ReportFormatters::orderStatus($row->order_status),
                    ReportFormatters::paymentStatus($row->payment_status),
                ], $spaBranchEnabled, $row, 6);
            })->all(),
            'products_view_report' => $rows->map(fn ($row) => [
                $row->name,
                $row->viewed,
            ])->all(),
            'products_stock_report' => $rows->map(fn ($row) => [
                $row->name,
                $row->qty ?: '—',
                $row->isInStock()
                    ? trans('report::admin.filters.stock_availability_states.in_stock')
                    : trans('report::admin.filters.stock_availability_states.out_of_stock'),
            ])->all(),
            'branded_products_report',
            'categorized_products_report',
            'taxed_products_report',
            'tagged_products_report' => $rows->map(fn ($row) => [
                $row->name,
                $row->products_count,
            ])->all(),
            'search_report' => $rows->map(fn ($row) => [
                $row->term,
                $row->results,
                $row->hits,
            ])->all(),
            'shipping_report' => $rows->map(fn ($row) => [
                static::dateRange($row->start_date, $row->end_date),
                $row->shipping_method,
                $row->total_orders,
                $row->unique_customers,
                number_format((float) $row->avg_shipping, 2),
                static::money($row->total),
            ])->all(),
            'tax_report' => $rows->map(fn ($row) => [
                static::dateRange($row->start_date, $row->end_date),
                $row->name,
                $row->total_orders,
                static::money($row->total),
            ])->all(),
            'beautician_bookings_report' => $rows->map(fn ($row) => static::withSpaBranchRow([
                static::appointment($row),
                trim(($row->customer_first_name ?? '') . ' ' . ($row->customer_last_name ?? '')),
                $row->products?->pluck('name')->filter()->unique()->implode(', ') ?: '—',
                $row->beautician_name,
                static::contact($row),
                ReportFormatters::orderStatus($row->status),
                ReportFormatters::paymentStatus($row->payment_status),
                static::money($row->total),
            ], $spaBranchEnabled, $row, 4))->all(),
            'loyalty_report' => $rows->map(function ($row) {
                $types = [
                    'earn' => trans('loyalty::reports.types.earn'),
                    'redeem' => trans('loyalty::reports.types.redeem'),
                    'adjust' => trans('loyalty::reports.types.adjust'),
                    'expire' => trans('loyalty::reports.types.expire'),
                    'clawback' => trans('loyalty::reports.types.clawback'),
                    'bonus' => trans('loyalty::reports.types.bonus'),
                ];

                return [
                    $row->created_at?->format('Y-m-d H:i'),
                    trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? '')) . ' (' . ($row->customer_email ?? '') . ')',
                    $types[$row->type] ?? $row->type,
                    ($row->points > 0 ? '+' : '') . $row->points,
                    number_format((float) $row->balance_after),
                    $row->description,
                ];
            })->all(),
            default => [],
        };
    }


    private static function contact(object $row): string
    {
        return ReportFormatters::contact($row);
    }


    private static function withSpaBranch(array $keys, bool $enabled, string $after): array
    {
        if (! $enabled) {
            return $keys;
        }

        $index = array_search($after, $keys, true);

        if ($index === false) {
            $keys[] = 'spa_branch';

            return $keys;
        }

        array_splice($keys, $index + 1, 0, ['spa_branch']);

        return $keys;
    }

    private static function withSpaBranchRow(array $row, bool $enabled, object $source, int $insertAt): array
    {
        if (! $enabled) {
            return $row;
        }

        array_splice($row, $insertAt, 0, [ReportFormatters::spaBranchName($source)]);

        return $row;
    }

    private static function t(array $keys): array
    {
        return array_map(
            fn (string $key) => trans('report::admin.table.' . $key),
            $keys
        );
    }


    private static function dateRange($start, $end): string
    {
        if (! $start || ! $end) {
            return '';
        }

        $startDate = $start instanceof Carbon ? $start : Carbon::parse($start);
        $endDate = $end instanceof Carbon ? $end : Carbon::parse($end);

        return $startDate->toFormattedDateString() . ' - ' . $endDate->toFormattedDateString();
    }


    private static function appointment($row): string
    {
        return ReportFormatters::appointment($row);
    }


    private static function money($value): string
    {
        if (is_object($value) && method_exists($value, 'format')) {
            return $value->format();
        }

        if ($value === null || $value === '') {
            return '—';
        }

        return (string) $value;
    }
}
