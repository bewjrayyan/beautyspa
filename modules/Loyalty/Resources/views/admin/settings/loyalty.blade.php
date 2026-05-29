@php
    use Modules\Loyalty\Support\LoyaltySettingsDefaults;

    $loyaltyVal = fn (string $key) => old(
        $key,
        LoyaltySettingsDefaults::forForm($key, $settings ?? [])
    );
@endphp

<div class="st-tab st-tab--loyalty">
    <p class="st-tab__lead">{{ trans('loyalty::settings.help') }}</p>

    <div class="loyalty-settings">
        <div class="loyalty-settings__grid">
            <div class="loyalty-settings__col">
                @component('setting::admin.settings.partials.section', [
                    'icon' => 'fa-star',
                    'title' => trans('loyalty::settings.section_core'),
                    'description' => trans('loyalty::settings.section_core_help'),
                    'class' => 'st-section--compact',
                ])
                    {{ Form::number('loyalty_earn_rate_per_rm', trans('loyalty::settings.earn_rate_per_rm'), $errors, $settings, [
                        'min' => 0,
                        'step' => '0.01',
                        'value' => $loyaltyVal('loyalty_earn_rate_per_rm'),
                    ]) }}
                    {{ Form::number('loyalty_point_value_rm', trans('loyalty::settings.point_value_rm'), $errors, $settings, [
                        'min' => 0.01,
                        'step' => '0.01',
                        'value' => $loyaltyVal('loyalty_point_value_rm'),
                    ]) }}
                    {{ Form::number('loyalty_max_redeem_percent', trans('loyalty::settings.max_redeem_percent'), $errors, $settings, [
                        'min' => 1,
                        'max' => 100,
                        'value' => $loyaltyVal('loyalty_max_redeem_percent'),
                    ]) }}
                    {{ Form::number('loyalty_points_expire_months', trans('loyalty::settings.points_expire_months'), $errors, $settings, [
                        'min' => 1,
                        'max' => 60,
                        'value' => $loyaltyVal('loyalty_points_expire_months'),
                    ]) }}
                    {{ Form::number('loyalty_hold_minutes', trans('loyalty::settings.hold_minutes'), $errors, $settings, [
                        'min' => 5,
                        'max' => 120,
                        'value' => $loyaltyVal('loyalty_hold_minutes'),
                    ]) }}
                    {{ Form::checkbox('loyalty_allow_with_coupon', trans('loyalty::settings.allow_with_coupon'), trans('loyalty::settings.enable'), $errors, $settings) }}
                    <p class="help-block text-muted">{{ trans('loyalty::settings.allow_with_coupon_help') }}</p>
                @endcomponent
            </div>

            <div class="loyalty-settings__col">
                @component('setting::admin.settings.partials.section', [
                    'icon' => 'fa-birthday-cake',
                    'title' => trans('loyalty::settings.section_engagement'),
                    'description' => trans('loyalty::settings.section_engagement_help'),
                    'class' => 'st-section--compact',
                ])
                    {{ Form::checkbox('loyalty_birthday_bonus_enabled', trans('loyalty::settings.birthday_bonus_enabled'), trans('loyalty::settings.enable'), $errors, $settings) }}
                    {{ Form::number('loyalty_birthday_bonus_points', trans('loyalty::settings.birthday_bonus_points'), $errors, $settings, [
                        'min' => 0,
                        'value' => $loyaltyVal('loyalty_birthday_bonus_points'),
                    ]) }}
                    {{ Form::checkbox('loyalty_referral_enabled', trans('loyalty::settings.referral_enabled'), trans('loyalty::settings.enable'), $errors, $settings) }}
                    {{ Form::number('loyalty_referral_bonus_referrer', trans('loyalty::settings.referral_bonus_referrer'), $errors, $settings, [
                        'min' => 0,
                        'value' => $loyaltyVal('loyalty_referral_bonus_referrer'),
                    ]) }}
                    {{ Form::number('loyalty_referral_bonus_referee', trans('loyalty::settings.referral_bonus_referee'), $errors, $settings, [
                        'min' => 0,
                        'value' => $loyaltyVal('loyalty_referral_bonus_referee'),
                    ]) }}
                    {{ Form::number('loyalty_expiring_notify_days', trans('loyalty::settings.expiring_notify_days'), $errors, $settings, [
                        'min' => 1,
                        'max' => 90,
                        'value' => $loyaltyVal('loyalty_expiring_notify_days'),
                    ]) }}
                @endcomponent
            </div>
        </div>

        @component('setting::admin.settings.partials.section', [
            'icon' => 'fa-whatsapp',
            'title' => trans('loyalty::settings.section_whatsapp'),
            'description' => trans('loyalty::settings.whatsapp_help'),
            'class' => 'st-section--compact loyalty-settings__whatsapp',
        ])
            <div class="loyalty-settings__wa-grid">
                {{ Form::checkbox('loyalty_whatsapp_tier_upgrade', trans('loyalty::settings.whatsapp_tier_upgrade'), trans('loyalty::settings.enable'), $errors, $settings) }}
                {{ Form::checkbox('loyalty_whatsapp_points_earned', trans('loyalty::settings.whatsapp_points_earned'), trans('loyalty::settings.enable'), $errors, $settings) }}
                {{ Form::checkbox('loyalty_whatsapp_points_expiring', trans('loyalty::settings.whatsapp_points_expiring'), trans('loyalty::settings.enable'), $errors, $settings) }}
                {{ Form::checkbox('loyalty_whatsapp_birthday_bonus', trans('loyalty::settings.whatsapp_birthday_bonus'), trans('loyalty::settings.enable'), $errors, $settings) }}
                {{ Form::checkbox('loyalty_whatsapp_referral_bonus', trans('loyalty::settings.whatsapp_referral_bonus'), trans('loyalty::settings.enable'), $errors, $settings) }}
            </div>
        @endcomponent
    </div>
</div>
