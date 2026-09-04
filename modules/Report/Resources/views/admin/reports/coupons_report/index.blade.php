@extends('report::admin.reports.layout')

@section('filter_zones')
    @component('report::admin.reports.partials.filter_zone', [
        'title' => trans('report::admin.filters.zone_filters'),
        'icon' => 'fa-filter',
        'hint' => trans('report::admin.coupons.filter_hint'),
        'class' => 'report-filter-zone--sales',
    ])
        @include('report::admin.reports.filters.from')
        @include('report::admin.reports.filters.to')
        @include('report::admin.reports.filters.status')
        @include('report::admin.reports.filters.group')

        <div class="form-group report-field">
            <label class="report-field__label" for="coupon-code">{{ trans('report::admin.filters.coupon_code') }}</label>
            <input type="text" name="coupon_code" class="form-control" id="coupon-code" value="{{ $request->coupon_code }}">
        </div>
    @endcomponent
@endsection

@section('report_result')
    @php
        $couponRows = collect($report->items());
        $moneyAmount = static fn ($value) => $value instanceof \Modules\Support\Money
            ? (float) $value->amount()
            : (float) ($value ?? 0);
        $couponCount = $couponRows->count();
        $totalOrders = (int) $couponRows->sum('total_orders');
        $totalCustomers = (int) $couponRows->sum('unique_customers');
        $totalDiscount = $couponRows->sum(fn ($item) => $moneyAmount($item->total));
        $influencedRevenue = $couponRows->sum(fn ($item) => $moneyAmount($item->orders_total));
        $averageOrderValue = $totalOrders > 0 ? $influencedRevenue / $totalOrders : 0;
        $peakOrders = (int) ($couponRows->max('total_orders') ?: 0);
    @endphp

    <section class="sales-workspace">
        <header class="sales-workspace__header">
            <div class="sales-workspace__heading">
                <span class="sales-workspace__eyebrow">
                    <i class="fa fa-ticket" aria-hidden="true"></i>
                    {{ trans('report::admin.coupons.promotion_intelligence') }}
                </span>
                <h5>{{ trans('report::admin.coupons.title') }}</h5>
                <p>{{ trans('report::admin.coupons.subtitle') }}</p>
            </div>

            <div class="sales-workspace__headline">
                <span>{{ trans('report::admin.coupons.filtered_snapshot') }}</span>
                <strong>{{ number_format($couponCount) }}</strong>
                <small>{{ trans('report::admin.coupons.campaigns_on_page') }}</small>
            </div>
        </header>

        <div class="sales-workspace__metrics">
            @foreach ([
                ['class' => 'blue', 'icon' => 'fa-line-chart', 'label' => trans('report::admin.coupons.revenue_influenced'), 'value' => \Modules\Support\Money::inDefaultCurrency($influencedRevenue)->format()],
                ['class' => 'purple', 'icon' => 'fa-ticket', 'label' => trans('report::admin.coupons.campaigns'), 'value' => number_format($couponCount)],
                ['class' => 'green', 'icon' => 'fa-shopping-bag', 'label' => trans('report::admin.coupons.coupon_orders'), 'value' => number_format($totalOrders)],
                ['class' => 'cyan', 'icon' => 'fa-users', 'label' => trans('report::admin.coupons.customers_reached'), 'value' => number_format($totalCustomers)],
                ['class' => 'amber', 'icon' => 'fa-tags', 'label' => trans('report::admin.coupons.discount_granted'), 'value' => \Modules\Support\Money::inDefaultCurrency($totalDiscount)->format()],
                ['class' => 'mint', 'icon' => 'fa-calculator', 'label' => trans('report::admin.coupons.average_order_value'), 'value' => \Modules\Support\Money::inDefaultCurrency($averageOrderValue)->format()],
            ] as $metric)
                <article class="sales-workspace-metric sales-workspace-metric--{{ $metric['class'] }}">
                    <span class="sales-workspace-metric__icon"><i class="fa {{ $metric['icon'] }}" aria-hidden="true"></i></span>
                    <span>{{ $metric['label'] }}</span>
                    <strong>{{ $metric['value'] }}</strong>
                </article>
            @endforeach
        </div>

        <section class="sales-transactions">
            <header class="sales-transactions__header">
                <div>
                    <span class="sales-transactions__kicker">{{ trans('report::admin.coupons.filtered_snapshot') }}</span>
                    <h6>{{ trans('report::admin.coupons.performance_title') }}</h6>
                    <p>{{ trans('report::admin.coupons.performance_help') }}</p>
                </div>
                <span class="sales-transactions__count">
                    {{ trans_choice('report::admin.coupons.campaign_count', $couponCount, ['count' => number_format($couponCount)]) }}
                </span>
            </header>

            <div class="sales-transactions__table-wrap">
                <table class="sales-transactions__table" id="sales-transaction-table">
                    <thead>
                        <tr>
                            @foreach ([
                                ['label' => trans('report::admin.table.date'), 'sort' => 'number'],
                                ['label' => trans('report::admin.coupons.campaign'), 'sort' => 'text'],
                                ['label' => trans('report::admin.table.orders'), 'sort' => 'number'],
                                ['label' => trans('report::admin.table.unique_customers'), 'sort' => 'number'],
                                ['label' => trans('report::admin.coupons.average_order_value'), 'sort' => 'number'],
                                ['label' => trans('report::admin.table.discount'), 'sort' => 'number'],
                                ['label' => trans('report::admin.coupons.revenue'), 'sort' => 'number'],
                            ] as $heading)
                                <th aria-sort="none">
                                    <button type="button" class="sales-sort" data-sales-sort="{{ $heading['sort'] }}">
                                        {{ $heading['label'] }} <i class="fa fa-sort" aria-hidden="true"></i>
                                    </button>
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($report as $data)
                            @php
                                $rowDiscount = $moneyAmount($data->total);
                                $rowRevenue = $moneyAmount($data->orders_total);
                                $rowAverage = $data->total_orders > 0 ? $rowRevenue / $data->total_orders : 0;
                                $usageShare = $peakOrders > 0 ? (int) round(($data->total_orders / $peakOrders) * 100) : 0;
                            @endphp
                            <tr>
                                <td data-label="{{ trans('report::admin.table.date') }}" data-sales-value="{{ $data->start_date->timestamp }}">
                                    <div class="coupon-period">
                                        <i class="fa fa-calendar-o" aria-hidden="true"></i>
                                        <span>
                                            <strong>{{ $data->start_date->format('d M Y') }}</strong>
                                            <small>{{ trans('report::admin.coupons.to_date', ['date' => $data->end_date->format('d M Y')]) }}</small>
                                        </span>
                                    </div>
                                </td>
                                <td data-label="{{ trans('report::admin.coupons.campaign') }}" data-sales-value="{{ mb_strtolower($data->name ?: $data->code) }}">
                                    <div class="coupon-campaign">
                                        <span class="coupon-campaign__mark"><i class="fa fa-ticket" aria-hidden="true"></i></span>
                                        <span>
                                            <strong>{{ $data->name ?: trans('report::admin.coupons.untitled_campaign') }}</strong>
                                            <code>{{ $data->code }}</code>
                                        </span>
                                    </div>
                                </td>
                                <td data-label="{{ trans('report::admin.table.orders') }}" data-sales-value="{{ $data->total_orders }}">
                                    <div class="coupon-usage">
                                        <strong>{{ number_format($data->total_orders) }}</strong>
                                        <span aria-hidden="true"><i style="width: {{ $usageShare }}%"></i></span>
                                        <small>{{ trans('report::admin.coupons.relative_usage', ['percent' => $usageShare]) }}</small>
                                    </div>
                                </td>
                                <td data-label="{{ trans('report::admin.table.unique_customers') }}" data-sales-value="{{ $data->unique_customers }}">
                                    <span class="coupon-customer-count"><i class="fa fa-user" aria-hidden="true"></i>{{ number_format($data->unique_customers) }}</span>
                                </td>
                                <td data-label="{{ trans('report::admin.coupons.average_order_value') }}" data-sales-value="{{ $rowAverage }}">
                                    {{ \Modules\Support\Money::inDefaultCurrency($rowAverage)->format() }}
                                </td>
                                <td data-label="{{ trans('report::admin.table.discount') }}" data-sales-value="{{ $rowDiscount }}">
                                    <span class="coupon-discount">−{{ \Modules\Support\Money::inDefaultCurrency($rowDiscount)->format() }}</span>
                                </td>
                                <td data-label="{{ trans('report::admin.coupons.revenue') }}" data-sales-value="{{ $rowRevenue }}">
                                    <strong class="coupon-revenue">{{ \Modules\Support\Money::inDefaultCurrency($rowRevenue)->format() }}</strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="coupon-empty" colspan="7">
                                    <span><i class="fa fa-ticket" aria-hidden="true"></i></span>
                                    <strong>{{ trans('report::admin.coupons.empty_title') }}</strong>
                                    <p>{{ trans('report::admin.coupons.empty_help') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($report->hasPages())
                <div class="sales-transactions__pagination">
                    {!! $report->links() !!}
                </div>
            @endif
        </section>
    </section>
@endsection
