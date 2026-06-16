@extends('admin::layout')

@section('title', trans('storefront::storefront.storefront'))

@section('content_header')
    <div class="settings-page-header">
        <div class="settings-page-header__text">
            <h3>{{ trans('storefront::storefront.storefront') }}</h3>
            <p class="settings-page-header__subtitle">{{ trans('storefront::storefront.form.page_subtitle') }}</p>
        </div>
    </div>

    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li>
        <li class="active">{{ trans('storefront::storefront.storefront') }}</li>
    </ol>
@endsection

@section('content')
    <div class="settings-shell">
        @if ($errors->any())
            <div class="settings-alert settings-alert--danger" role="alert">
                <div class="settings-alert__icon" aria-hidden="true">
                    <i class="fa fa-exclamation-circle"></i>
                </div>
                <div class="settings-alert__body">
                    <p class="settings-alert__title">{{ trans('core::messages.the_given_data_was_invalid') }}</p>
                    <ul class="settings-alert__list">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {!! $tabs->render(compact('settings')) !!}
    </div>
@endsection

@push('globals')
    @vite([
        'modules/Setting/Resources/assets/admin/sass/main.scss',
        'modules/Setting/Resources/assets/admin/js/main.js',
        'modules/Storefront/Resources/assets/admin/sass/main.scss',
        'modules/Storefront/Resources/assets/admin/js/main.js',
        'modules/Media/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/js/main.js',
    ])
@endpush
