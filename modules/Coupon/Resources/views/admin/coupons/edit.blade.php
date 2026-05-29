@extends('admin::layout')

@section('title', trans('admin::resource.edit', ['resource' => trans('coupon::coupons.coupon')]) . ' - ' . $coupon->name)

@section('content_header')
    <ol class="breadcrumb">
        <li>
            <a href="{{ route('admin.dashboard.index') }}" class="breadcrumb-home-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M12 18V15" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M10.07 2.81997L3.13999 8.36997C2.35999 8.98997 1.85999 10.3 2.02999 11.28L3.35999 19.24C3.59999 20.66 4.95999 21.81 6.39999 21.81H17.6C19.03 21.81 20.4 20.65 20.64 19.24L21.97 11.28C22.13 10.3 21.63 8.98997 20.86 8.36997L13.93 2.82997C12.86 1.96997 11.13 1.96997 10.07 2.81997Z" stroke="#292D32" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </li>
        <li><a href="{{ route('admin.coupons.index') }}">{{ trans('coupon::coupons.coupons') }}</a></li>
        <li class="active">{{ $coupon->name }}</li>
    </ol>
@endsection

@section('content')
    <div class="coupon-admin coupon-form-page coupon-form-page--compact">
        <div class="coupon-form-page__shell">
            <div class="coupon-form-page__main">
                <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}" class="form-horizontal coupon-form" id="coupon-edit-form" novalidate>
                    {{ csrf_field() }}
                    {{ method_field('put') }}

                    {!! $tabs->render(compact('coupon')) !!}
                </form>
            </div>

            @include('coupon::admin.coupons.partials.preview_sidebar', ['coupon' => $coupon])
        </div>
    </div>
@endsection

@push('styles')
    @vite(['modules/Coupon/Resources/assets/admin/sass/main.scss'])
@endpush

@include('coupon::admin.coupons.partials.scripts')
