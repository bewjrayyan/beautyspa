@php
    $meta = $meta ?? [];
    $tabName = $meta['name'] ?? '';
    $eyebrow = $tabName === 'sms'
        ? trans('setting::settings.sms.hero_eyebrow')
        : ($meta['group'] ?? '');
    $title = $meta['label'] ?? '';
    $lead = $meta['lead'] ?? null;
    $icon = $meta['icon'] ?? 'fa-cog';
    $onesenderEnabled = $tabName === 'sms'
        ? filter_var(setting('onesender_enabled'), FILTER_VALIDATE_BOOLEAN)
        : false;
@endphp

<div @class(['st-settings__hero', 'st-settings__hero--whatsapp' => $tabName === 'sms'])>
    <div class="st-settings__hero-icon" aria-hidden="true">
        <i class="fa {{ $icon }}"></i>
    </div>

    <div class="st-settings__hero-copy">
        @if ($eyebrow !== '')
            <span class="st-settings__hero-eyebrow">{{ $eyebrow }}</span>
        @endif
        @if ($title !== '')
            <h2 class="st-settings__hero-title">{{ $title }}</h2>
        @endif
        @if (! empty($lead))
            <p class="st-settings__hero-lead">{{ $lead }}</p>
        @endif
    </div>

    <div class="st-settings__hero-meta">
        <span class="settings-unsaved-badge is-hidden" role="status">
            <span class="settings-unsaved-badge__dot" aria-hidden="true"></span>
            {{ trans('setting::settings.form.unsaved_changes') }}
        </span>

        @if ($tabName === 'sms')
            <span class="st-settings__hero-badge st-settings__hero-badge--{{ $onesenderEnabled ? 'on' : 'off' }}">
                <i class="fa fa-{{ $onesenderEnabled ? 'check-circle' : 'exclamation-circle' }}" aria-hidden="true"></i>
                {{ trans('setting::settings.sms.hero_status_' . ($onesenderEnabled ? 'on' : 'off')) }}
            </span>
            <span class="st-settings__hero-note">
                <i class="fa fa-eye" aria-hidden="true"></i>
                {{ trans('setting::settings.sms.hero_live_preview') }}
            </span>
        @endif
    </div>
</div>
