@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('order::orders.order') . ' #' . $order->id)

    <li><a href="{{ route('admin.orders.index') }}">{{ trans('order::orders.orders') }}</a></li>
    <li class="active">#{{ $order->id }}</li>
@endcomponent

@section('content')
    @php
        $canSendOrderWhatsApp = \Modules\User\Services\OneSenderWhatsAppService::isConfigured()
            && filled($order->customer_phone);
    @endphp

    <div class="order-show box">
        @if ($order->trashed())
            <div class="order-show__archived-alert alert alert-warning" role="status">
                <i class="fa fa-archive" aria-hidden="true"></i>
                <span>{{ trans('order::orders.archived_banner') }}</span>
            </div>
        @endif

        @include('order::admin.orders.partials.order_header', ['canSendOrderWhatsApp' => $canSendOrderWhatsApp])

        <div class="order-show__body">
            <div class="row">
                <div class="col-lg-8">
                    @include('order::admin.orders.partials.order_and_account_information')

                    @include('order::admin.orders.partials.address_information')
                    @include('order::admin.orders.partials.items_ordered')

                    @if (app('modules')->isEnabled('Loyalty') && ! empty($orderStampData))
                        @include('loyalty::admin.orders.partials.stamp_information', $orderStampData)
                    @endif

                    @if (! empty($treatmentBooking?->activities) && $treatmentBooking->activities->isNotEmpty())
                        <div class="order-show__section">
                            @include('treatmentreservation::admin.partials.booking_activity_log', [
                                'activities' => $treatmentBooking->activities,
                            ])
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <div class="order-show__sidebar">
                        @include('order::admin.orders.partials.order_totals')

                        @if (app('modules')->isEnabled('GoogleIntegration'))
                            @include('googleintegration::admin.orders.partials.google_sheets_sync', ['order' => $order])
                        @endif

                        @include('order::admin.orders.partials.order_customer_note')
                        @include('order::admin.orders.partials.order_tracking')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    @vite(['modules/Order/Resources/assets/admin/sass/main.scss'])
@endpush

@push('scripts')
    @vite(['modules/Order/Resources/assets/admin/js/main.js'])
@endpush
