@extends('admin::layout')

@section('title', trans('specialgift::admin.tab_settings'))

@section('content_header')
    <h3>{{ trans('specialgift::admin.tab_settings') }}</h3>

    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li>
        <li><a href="{{ route('admin.gift_voucher_submissions.index') }}">{{ trans('specialgift::admin.submissions') }}</a></li>
        <li class="active">{{ trans('specialgift::admin.tab_settings') }}</li>
    </ol>
@endsection

@section('content')
    @include('specialgift::admin.partials.hub-nav', [
        'activeTab' => 'settings',
        'sendGiftUrl' => $sendGiftUrl,
    ])

    <form
        method="POST"
        action="{{ route('admin.gift_voucher_submissions.settings.update') }}"
        class="gv-hub-form"
        id="gift-voucher-settings-form"
    >
        @csrf
        @method('PUT')
        <input type="hidden" name="section" value="settings">

        <div class="box box-primary gv-hub-panel">
            <div class="box-body gv-hub-panel__body">
                @include('specialgift::admin.settings.partials.operational', [
                    'settings' => $settings,
                    'specialGiftSettings' => $specialGiftSettings,
                    'voucherBackground' => $voucherBackground,
                    'specialGiftConfig' => $specialGiftConfig,
                    'sendGiftUrl' => $sendGiftUrl,
                ])
            </div>

            <div class="box-footer gv-hub-panel__footer">
                @include('specialgift::admin.partials.hub-sticky-footer', [
                    'sendGiftUrl' => $sendGiftUrl,
                    'hint' => trans('specialgift::admin.settings_save_hint'),
                ])
            </div>
        </div>
    </form>
@endsection

@push('globals')
    @vite([
        'modules/SpecialGift/Resources/assets/admin/sass/main.scss',
        'modules/Setting/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/js/main.js',
        'modules/SpecialGift/Resources/assets/admin/js/main.js',
    ])
@endpush
