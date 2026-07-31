@extends('storefront::public.auth.layout')

@section('title', trans('beautician::beauticians.self_registration.pending_title'))

@push('globals')
    <style>
        .beautician-pending { min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        .beautician-pending__card { width: min(560px, 100%); padding: 40px 32px; border-radius: 18px; background: #fff; text-align: center; box-shadow: 0 20px 60px rgba(14, 30, 62, .12); }
        .beautician-pending__icon { width: 72px; height: 72px; margin: 0 auto 20px; border-radius: 50%; display: grid; place-items: center; color: #9a3412; background: #ffedd5; font-size: 30px; }
        .beautician-pending__card h2 { color: #0e1e3e; margin-bottom: 12px; }
        .beautician-pending__card p { color: #64748b; line-height: 1.7; }
        .beautician-pending__actions { margin-top: 24px; display: flex; justify-content: center; gap: 14px; flex-wrap: wrap; }
    </style>
@endpush

@section('content')
    <div class="beautician-pending">
        <div class="beautician-pending__card">
            <div class="beautician-pending__icon" aria-hidden="true">✓</div>
            @include('storefront::public.auth.partials.notification')
            <h2>{{ trans('beautician::beauticians.self_registration.pending_title') }}</h2>
            <p>{{ trans('beautician::beauticians.self_registration.pending_message') }}</p>
            <p>{{ trans('beautician::beauticians.self_registration.pending_access') }}</p>

            <div class="beautician-pending__actions">
                @auth
                    <a class="btn btn-primary" href="{{ route('admin.logout') }}">{{ trans('beautician::beauticians.self_registration.sign_out') }}</a>
                @else
                    <a class="btn btn-primary" href="{{ route('admin.login') }}">{{ trans('beautician::beauticians.self_registration.portal_login') }}</a>
                @endauth
                <a class="btn btn-default" href="{{ route('home') }}">{{ trans('beautician::beauticians.self_registration.back_home') }}</a>
            </div>
        </div>
    </div>
@endsection
