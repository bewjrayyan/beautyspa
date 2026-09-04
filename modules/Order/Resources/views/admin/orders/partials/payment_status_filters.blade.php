@php
    use Modules\Order\Entities\Order;

    $activePaymentStatus = (string) request('payment_status', '');
    $activePaymentChannel = (string) request('payment_channel', '');
    if (! in_array($activePaymentChannel, ['offline', 'online'], true)) {
        $activePaymentChannel = '';
    }

    $activeMonth = (string) request('month', '');
    if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $activeMonth)) {
        $activeMonth = '';
    }

    $activeDateFrom = (string) request('date_from', '');
    $activeDateTo = (string) request('date_to', '');
    $activeSearch = (string) request('search', '');

    $monthOptions = collect(range(0, 17))->map(function (int $offset) {
        $date = now()->startOfMonth()->subMonths($offset);

        return [
            'value' => $date->format('Y-m'),
            'label' => $date->translatedFormat('F Y'),
        ];
    });

    $activeFilterCount = collect([
        $activePaymentStatus,
        $activePaymentChannel,
        $activeMonth,
        ($activeDateFrom !== '' || $activeDateTo !== '') ? 'date-range' : '',
        $activeSearch,
    ])->filter()->count();

@endphp

<div class="orders-index__toolbar" aria-label="{{ trans('order::orders.filters_title') }}">
    <div class="orders-index__filter-head">
        <div class="orders-index__filter-heading">
            <span class="orders-index__filter-icon" aria-hidden="true"><i class="fa fa-sliders"></i></span>
            <span>
                <strong>{{ trans('order::orders.filters_title') }}</strong>
                <small>{{ trans('order::orders.filters_description') }}</small>
            </span>
        </div>
        <span class="orders-index__filter-summary {{ $activeFilterCount > 0 ? 'is-active' : '' }}">
            <i class="fa {{ $activeFilterCount > 0 ? 'fa-filter' : 'fa-list-ul' }}" aria-hidden="true"></i>
            {{ $activeFilterCount > 0 ? trans('order::orders.filters_active', ['count' => $activeFilterCount]) : trans('order::orders.filters_all_orders') }}
        </span>
    </div>

    <div class="orders-index__filter-body">
    <div class="orders-index__filter-row">
        <span class="orders-index__filter-label" id="orders-channel-label" title="{{ trans('order::orders.filter_payment_channel_help') }}" data-step="01">
            <strong>{{ trans('order::orders.filter_by_payment_channel') }}</strong>
            <small>{{ trans('order::orders.filter_channel_hint') }}</small>
        </span>
        <div
            class="orders-index__segment"
            id="orders-payment-channel-filters"
            role="group"
            aria-labelledby="orders-channel-label"
        >
            <button
                type="button"
                class="orders-index__chip {{ $activePaymentChannel === '' ? 'is-active' : '' }}"
                data-payment-channel=""
                aria-pressed="{{ $activePaymentChannel === '' ? 'true' : 'false' }}"
            >
                <span class="orders-index__chip-text">{{ trans('order::orders.filter_payment_channel_all') }}</span>
                <span class="orders-index__chip-count">{{ number_format($totalOrdersCount) }}</span>
            </button>
            <button
                type="button"
                class="orders-index__chip orders-index__chip--offline {{ $activePaymentChannel === 'offline' ? 'is-active' : '' }}"
                data-payment-channel="offline"
                aria-pressed="{{ $activePaymentChannel === 'offline' ? 'true' : 'false' }}"
                title="{{ trans('order::orders.filter_payment_channel_help') }}"
            >
                <span class="orders-index__chip-swatch" aria-hidden="true"></span>
                <span class="orders-index__chip-text">{{ trans('order::orders.filter_payment_channel_offline') }}</span>
                <span class="orders-index__chip-count">{{ number_format($offlineOrdersCount) }}</span>
            </button>
            <button
                type="button"
                class="orders-index__chip orders-index__chip--online {{ $activePaymentChannel === 'online' ? 'is-active' : '' }}"
                data-payment-channel="online"
                aria-pressed="{{ $activePaymentChannel === 'online' ? 'true' : 'false' }}"
                title="{{ trans('order::orders.filter_payment_channel_help') }}"
            >
                <span class="orders-index__chip-swatch" aria-hidden="true"></span>
                <span class="orders-index__chip-text">{{ trans('order::orders.filter_payment_channel_online') }}</span>
                <span class="orders-index__chip-count">{{ number_format($onlineOrdersCount) }}</span>
            </button>
        </div>
    </div>

    <div class="orders-index__filter-row">
        <span class="orders-index__filter-label" id="orders-status-label" title="{{ trans('order::orders.filter_payment_status_help') }}" data-step="02">
            <strong>{{ trans('order::orders.filter_by_payment_status') }}</strong>
            <small>{{ trans('order::orders.filter_status_hint') }}</small>
        </span>
        <div
            class="orders-index__segment orders-index__segment--wrap"
            id="orders-payment-filters"
            role="group"
            aria-labelledby="orders-status-label"
        >
            <button
                type="button"
                class="orders-index__chip {{ $activePaymentStatus === '' ? 'is-active' : '' }}"
                data-payment-status=""
                aria-pressed="{{ $activePaymentStatus === '' ? 'true' : 'false' }}"
            >
                <span class="orders-index__chip-text">{{ trans('order::orders.filter_payment_status_all') }}</span>
                <span class="orders-index__chip-count">{{ number_format($totalOrdersCount) }}</span>
            </button>

            @foreach (Order::paymentStatuses() as $status)
                @php
                    $count = (int) ($paymentStatusCounts[$status] ?? 0);
                    $isActive = $activePaymentStatus === $status;
                    $short = trans('order::orders.filter_payment_status_' . $status);
                    $full = trans('order::payment_statuses.' . $status);
                @endphp
                <button
                    type="button"
                    class="orders-index__chip orders-index__chip--{{ $status }} {{ $isActive ? 'is-active' : '' }}"
                    data-payment-status="{{ $status }}"
                    aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                    title="{{ $full }}"
                >
                    <span class="orders-index__chip-swatch" aria-hidden="true"></span>
                    <span class="orders-index__chip-text">{{ $short }}</span>
                    <span class="orders-index__chip-count">{{ number_format($count) }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <div class="orders-index__filter-row orders-index__filter-row--meta">
        <span class="orders-index__filter-label" id="orders-period-label" title="{{ trans('order::orders.filter_date_help') }}" data-step="03">
            <strong>{{ trans('order::orders.filter_by_period') }}</strong>
            <small>{{ trans('order::orders.filter_period_hint') }}</small>
        </span>
        <div class="orders-index__meta" role="group" aria-labelledby="orders-period-label">
            <label class="orders-index__field">
                <span class="sr-only">{{ trans('order::orders.filter_month') }}</span>
                <select
                    id="orders-filter-month"
                    class="orders-index__control orders-index__control--month"
                    title="{{ trans('order::orders.filter_month_help') }}"
                >
                    <option value="">{{ trans('order::orders.filter_month_all') }}</option>
                    @foreach ($monthOptions as $option)
                        <option value="{{ $option['value'] }}" @selected($activeMonth === $option['value'])>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
            </label>

            @php
                $dateRangeDefault = '';
                if ($activeDateFrom !== '' && $activeDateTo !== '') {
                    $dateRangeDefault = $activeDateFrom . ' to ' . $activeDateTo;
                } elseif ($activeDateFrom !== '') {
                    $dateRangeDefault = $activeDateFrom;
                }
            @endphp
            <label class="orders-index__field orders-index__field--range">
                <span class="orders-index__field-hint">{{ trans('order::orders.filter_date_range') }}</span>
                <input
                    type="text"
                    id="orders-filter-date-range"
                    class="orders-index__control orders-index__control--range datetime-picker"
                    data-range
                    data-default-date="{{ $dateRangeDefault }}"
                    value="{{ $dateRangeDefault }}"
                    placeholder="{{ trans('order::orders.filter_date_range_placeholder') }}"
                    title="{{ trans('order::orders.filter_date_help') }}"
                    autocomplete="off"
                    readonly
                >
            </label>

            <label class="orders-index__field orders-index__field--search">
                <span class="sr-only">{{ trans('order::orders.filter_search') }}</span>
                <input
                    type="search"
                    id="orders-filter-search"
                    class="orders-index__control orders-index__control--search"
                    value="{{ $activeSearch }}"
                    placeholder="{{ trans('order::orders.filter_search_placeholder') }}"
                    title="{{ trans('order::orders.filter_search_help') }}"
                    maxlength="100"
                    autocomplete="off"
                >
            </label>

            <div class="orders-index__meta-actions">
                <button type="button" id="orders-filter-apply" class="btn btn-primary btn-sm orders-index__meta-btn">
                    <i class="fa fa-filter" aria-hidden="true"></i>
                    {{ trans('order::orders.filter_apply') }}
                </button>
                <button type="button" id="orders-filter-clear" class="btn btn-default btn-sm orders-index__meta-btn">
                    <i class="fa fa-undo" aria-hidden="true"></i>
                    {{ trans('order::orders.filter_clear') }}
                </button>
            </div>
        </div>
    </div>
    </div>

</div>
