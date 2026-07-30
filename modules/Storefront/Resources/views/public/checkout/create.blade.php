@extends('storefront::public.layout')

@section('title', trans('storefront::checkout.checkout'))

@section('content')
    <section
        x-data="Checkout(AestheticCart.data.checkout)"
        class="checkout-wrap checkout-wrap--modern"
    >
        <div class="container">
            @include('storefront::public.checkout.create.steps')

            <form class="checkout-form checkout-form--modern" @input="errors.clear($event.target.name)">
                <div class="checkout-inner">
                    <div class="checkout-main">
                        @include('storefront::public.checkout.create.form.account_details')

                        <div class="checkout-card checkout-card-billing">
                            @include('storefront::public.checkout.create.form.billing_details')
                            @include('storefront::public.checkout.create.form.shipping_details')
                        </div>

                        @include('storefront::public.checkout.create.form.spa_branch')

                        @include('storefront::public.checkout.create.form.treatment_booking')

                        <div class="checkout-card checkout-card-payment">
                            @include('storefront::public.checkout.create.payment')
                            @include('storefront::public.checkout.create.shipping')
                        </div>
                    </div>

                    <div class="checkout-sidebar">
                        @include('storefront::public.checkout.create.order_summary')
                    </div>
                </div>
            </form>

        </div>
    </section>
@endsection

@push('pre-scripts')
    @include('storefront::public.partials.google_recaptcha_script')
@endpush

@push('globals')
    <script>
        AestheticCart.data.checkout = @json($checkoutConfig);

        AestheticCart.langs['storefront::checkout.payment_for_order'] = '{{ trans("storefront::checkout.payment_for_order") }}';
        AestheticCart.langs['storefront::checkout.remember_about_your_order'] = '{{ trans("storefront::checkout.remember_about_your_order") }}';
    </script>

    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/checkout/create/main.scss',
        'modules/Storefront/Resources/assets/public/js/pages/checkout/create/main.js',
        'modules/Storefront/Resources/assets/public/js/vendors/flatpickr.js',
    ])
@endpush
