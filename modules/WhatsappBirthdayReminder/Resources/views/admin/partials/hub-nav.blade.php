@php
    $activeTab = $activeTab ?? 'logs';
    $settingsUrl = route('admin.settings.edit', ['tab' => 'sms']);
@endphp

<div class="clearfix" style="margin-bottom: 15px;">
    <p class="text-muted">{{ trans('whatsappbirthday::admin.hub_lead') }}</p>
    <ul class="nav nav-tabs">
        <li @class(['active' => $activeTab === 'logs'])>
            <a href="{{ route('admin.whatsapp_birthday.index') }}">{{ trans('whatsappbirthday::admin.logs') }}</a>
        </li>
        @if (auth()->user()?->hasAccess('admin.settings.edit') || auth()->user()?->hasAccess('admin.whatsapp_birthday.settings'))
            <li>
                <a href="{{ $settingsUrl }}">{{ trans('whatsappbirthday::admin.settings') }}</a>
            </li>
        @endif
    </ul>
</div>
