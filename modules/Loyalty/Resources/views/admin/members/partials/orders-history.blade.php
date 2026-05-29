@php
    $canViewOrders = auth()->user()?->hasAccess('admin.orders.show');
@endphp

<div class="loyalty-member-card loyalty-member-orders">
    <div class="loyalty-member-card__head">
        <h3>
            <i class="fa fa-shopping-bag" aria-hidden="true"></i>
            {{ trans('loyalty::members.show.orders_title') }}
        </h3>
        <p>{{ trans('loyalty::members.show.orders_lead') }}</p>
    </div>
    <div class="loyalty-member-card__body loyalty-member-orders__table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ trans('loyalty::members.show.orders_col_appointment') }}</th>
                    <th>{{ trans('loyalty::members.show.orders_col_type') }}</th>
                    <th>{{ trans('loyalty::members.show.orders_col_items') }}</th>
                    <th>{{ trans('loyalty::members.show.orders_col_status') }}</th>
                    <th class="text-right">{{ trans('loyalty::members.show.orders_col_total') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($memberOrders as $order)
                    @php
                        $isBooking = $order->beautician_id || $order->appointment_date;

                        $appointmentBeautician = null;
                        $appointmentDateLine = null;
                        $appointmentTimeLine = null;

                        if ($isBooking) {
                            $appointmentBeautician = $order->beautician?->name;

                            if ($order->appointment_date) {
                                $appointmentDateLine = $order->appointment_date
                                    ->timezone(config('app.timezone'))
                                    ->format('d M Y');
                            }

                            if (filled($order->appointment_time)) {
                                $appointmentTimeLine = $order->appointment_time;
                            }
                        }

                        $beauticianScheduleUrl = null;

                        if ($appointmentBeautician && $order->beautician_id) {
                            if (auth()->user()?->hasAccess('admin.treatment_reservations.index')) {
                                $beauticianScheduleUrl = route('admin.treatment_reservations.index', array_filter([
                                    'view' => 'calendar',
                                    'beautician_id' => $order->beautician_id,
                                    'month' => $order->appointment_date?->format('Y-m'),
                                ]));
                            } elseif (auth()->user()?->hasAccess('admin.beauticians.edit')) {
                                $beauticianScheduleUrl = route('admin.beauticians.edit', $order->beautician_id)
                                    . '#tr-beautician-schedule-app';
                            }
                        }

                        $hasAppointmentCell = $appointmentBeautician
                            || $appointmentDateLine
                            || $appointmentTimeLine;
                    @endphp
                    <tr>
                        <td class="loyalty-member-orders__date">
                            <div class="loyalty-member-orders__datetime">
                                @if ($hasAppointmentCell)
                                    @if ($appointmentBeautician)
                                        @if ($beauticianScheduleUrl)
                                            <a
                                                href="{{ $beauticianScheduleUrl }}"
                                                class="loyalty-member-orders__datetime-beautician"
                                            >{{ $appointmentBeautician }}</a>
                                        @else
                                            <span class="loyalty-member-orders__datetime-beautician">{{ $appointmentBeautician }}</span>
                                        @endif
                                    @endif
                                    @if ($appointmentDateLine || $appointmentTimeLine)
                                        @if ($canViewOrders)
                                            <a href="{{ route('admin.orders.show', $order) }}" class="loyalty-member-orders__datetime-link">
                                        @endif
                                        @if ($appointmentDateLine)
                                            <span class="loyalty-member-orders__datetime-date">{{ $appointmentDateLine }}</span>
                                        @endif
                                        @if ($appointmentTimeLine)
                                            <span class="loyalty-member-orders__datetime-time">{{ $appointmentTimeLine }}</span>
                                        @endif
                                        @if ($canViewOrders)
                                            </a>
                                        @endif
                                    @endif
                                @else
                                    <span class="loyalty-member-orders__datetime-empty">—</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="loyalty-member-orders__type loyalty-member-orders__type--{{ $isBooking ? 'booking' : 'purchase' }}">
                                <i class="fa {{ $isBooking ? 'fa-calendar' : 'fa-shopping-cart' }}" aria-hidden="true"></i>
                                {{ $isBooking
                                    ? trans('loyalty::members.show.orders_type_booking')
                                    : trans('loyalty::members.show.orders_type_purchase') }}
                            </span>
                        </td>
                        <td class="loyalty-member-orders__items">
                            @if ($order->products->isEmpty())
                                <span class="loyalty-member-orders__items-empty">—</span>
                            @else
                                <div class="loyalty-member-orders__items-stack">
                                    @foreach ($order->products as $line)
                                        <div class="loyalty-member-orders__item-row">
                                            <div class="loyalty-member-orders__item-name">
                                                @if ($canViewOrders)
                                                    <a href="{{ route('admin.orders.show', $order) }}">{{ $line->name }}</a>
                                                @else
                                                    {{ $line->name }}
                                                @endif
                                            </div>
                                            @php
                                                $hasSelectionMeta = $line->hasAnyVariation() || $line->hasAnyOption();
                                                $variantLabel = $line->product_variant?->name;
                                                $showVariantFallback = ! $hasSelectionMeta
                                                    && filled($variantLabel);
                                            @endphp
                                            @if ($hasSelectionMeta || $showVariantFallback)
                                                <div class="loyalty-member-orders__item-meta">
                                                    @if ($line->hasAnyVariation())
                                                        @foreach ($line->variations as $variation)
                                                            <span>
                                                                {{ $variation->name }}:
                                                                {{ $variation->values->first()?->label ?? $variation->value }}
                                                            </span>
                                                        @endforeach
                                                    @endif
                                                    @if ($line->hasAnyOption())
                                                        @foreach ($line->options as $option)
                                                            <span>
                                                                {{ $option->name }}:
                                                                @if ($option->option->isFieldType())
                                                                    {{ $option->value }}
                                                                @else
                                                                    {{ $option->values->implode('label', ', ') }}
                                                                @endif
                                                            </span>
                                                        @endforeach
                                                    @endif
                                                    @if ($showVariantFallback)
                                                        <span>{{ $variantLabel }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                <div class="loyalty-member-orders__item-order">
                                    @if ($canViewOrders)
                                        <a href="{{ route('admin.orders.show', $order) }}">#{{ $order->id }}</a>
                                    @else
                                        #{{ $order->id }}
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ order_status_badge_class($order->status) }}">
                                {{ $order->status() }}
                            </span>
                        </td>
                        <td class="text-right loyalty-member-orders__total">
                            @if ($canViewOrders)
                                <a href="{{ route('admin.orders.show', $order) }}">
                                    {{ $order->total->format() }}
                                </a>
                            @else
                                {{ $order->total->format() }}
                            @endif
                            @if ((int) $order->loyalty_points_redeemed > 0)
                                <small>
                                    {{ trans('loyalty::members.show.orders_points_used', [
                                        'points' => number_format($order->loyalty_points_redeemed),
                                    ]) }}
                                </small>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="loyalty-member-orders__empty">
                            <i class="fa fa-inbox" aria-hidden="true"></i>
                            {{ trans('loyalty::members.show.no_orders') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
