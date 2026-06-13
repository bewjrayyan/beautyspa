@php
    $passwordPanelId = $passwordPanelId ?? 'create_password';
    $passwordRequired = $passwordRequired ?? true;
    $passwordTitle = $passwordTitle ?? trans('user::users.create_page.password_title');
    $passwordLead = $passwordLead ?? trans('user::users.create_page.password_lead');
    $passwordTip = $passwordTip ?? null;
    $passwordLabel = $passwordLabel ?? trans('user::attributes.users.password');
    $passwordConfirmLabel = $passwordConfirmLabel ?? trans('user::attributes.users.password_confirmation');
    $passwordGeneratedHint = $passwordGeneratedHint ?? trans('user::users.create_page.password_generated_hint');
@endphp

<div
    class="admin-profile-card admin-profile-password-panel"
    id="{{ $passwordPanelId }}"
    data-admin-password-panel
    data-strength-empty="{{ trans('user::users.create_page.password_strength_empty') }}"
    data-strength-weak="{{ trans('user::users.create_page.password_strength_weak') }}"
    data-strength-fair="{{ trans('user::users.create_page.password_strength_fair') }}"
    data-strength-good="{{ trans('user::users.create_page.password_strength_good') }}"
    data-strength-strong="{{ trans('user::users.create_page.password_strength_strong') }}"
    data-match-empty="{{ trans('user::users.create_page.password_match_empty') }}"
    data-match-match="{{ trans('user::users.create_page.password_match_match') }}"
    data-match-mismatch="{{ trans('user::users.create_page.password_match_mismatch') }}"
    data-generated-hint="{{ $passwordGeneratedHint }}"
    data-show-password="{{ trans('user::auth.show_password') }}"
    data-hide-password="{{ trans('user::auth.hide_password') }}"
>
    <div class="admin-profile-password-panel__head">
        <div class="admin-profile-password-panel__intro">
            <h2 class="admin-profile-card__title">
                <i class="fa fa-lock" aria-hidden="true"></i>
                {{ $passwordTitle }}
            </h2>
            <p class="admin-profile-card__lead">{{ $passwordLead }}</p>
            @if ($passwordTip)
                <p class="admin-profile-card__tip">
                    <i class="fa fa-info-circle" aria-hidden="true"></i>
                    {{ $passwordTip }}
                </p>
            @endif
        </div>

        <button
            type="button"
            class="btn btn-default btn-sm admin-profile-password-panel__generate"
            data-admin-password-generate
        >
            <i class="fa fa-magic" aria-hidden="true"></i>
            {{ trans('user::users.create_page.password_generate') }}
        </button>
    </div>

    <div class="admin-profile-password-panel__body">
        <div class="admin-profile-password-panel__field">
            <label for="{{ $passwordPanelId }}_password" class="admin-profile-password-panel__label">
                {{ $passwordLabel }}
                @if ($passwordRequired)
                    <span class="admin-profile-password-panel__required" aria-hidden="true">*</span>
                @endif
            </label>

            <div class="admin-profile-password-panel__input-wrap @error('password') has-error @enderror">
                <input
                    type="password"
                    name="password"
                    id="{{ $passwordPanelId }}_password"
                    class="form-control admin-profile-password-panel__input"
                    @if ($passwordRequired) required @endif
                    autocomplete="new-password"
                    data-admin-password-input
                >
                <button
                    type="button"
                    class="admin-profile-password-panel__toggle"
                    data-admin-password-toggle
                    data-target="{{ $passwordPanelId }}_password"
                    aria-label="{{ trans('user::auth.show_password') }}"
                    aria-pressed="false"
                >
                    <i class="fa fa-eye" aria-hidden="true" data-icon-show></i>
                    <i class="fa fa-eye-slash hide" aria-hidden="true" data-icon-hide></i>
                </button>
            </div>

            @error('password')
                <p class="help-block text-red">{{ $message }}</p>
            @enderror

            <div class="admin-profile-password-panel__strength" data-admin-password-strength hidden>
                <div class="admin-profile-password-panel__strength-track" aria-hidden="true">
                    <span
                        class="admin-profile-password-panel__strength-fill"
                        data-admin-password-strength-fill
                    ></span>
                </div>
                <p class="admin-profile-password-panel__strength-label">
                    <span data-admin-password-strength-label></span>
                </p>
            </div>

            <ul class="admin-profile-password-panel__checks" aria-live="polite">
                <li data-admin-password-check="length">
                    <i class="fa fa-circle-o" aria-hidden="true" data-icon-pending></i>
                    <i class="fa fa-check-circle hide" aria-hidden="true" data-icon-done></i>
                    {{ trans('user::users.create_page.password_check_length') }}
                </li>
                <li data-admin-password-check="length8">
                    <i class="fa fa-circle-o" aria-hidden="true" data-icon-pending></i>
                    <i class="fa fa-check-circle hide" aria-hidden="true" data-icon-done></i>
                    {{ trans('user::users.create_page.password_check_length8') }}
                </li>
                <li data-admin-password-check="letter">
                    <i class="fa fa-circle-o" aria-hidden="true" data-icon-pending></i>
                    <i class="fa fa-check-circle hide" aria-hidden="true" data-icon-done></i>
                    {{ trans('user::users.create_page.password_check_letter') }}
                </li>
                <li data-admin-password-check="number">
                    <i class="fa fa-circle-o" aria-hidden="true" data-icon-pending></i>
                    <i class="fa fa-check-circle hide" aria-hidden="true" data-icon-done></i>
                    {{ trans('user::users.create_page.password_check_number') }}
                </li>
                <li data-admin-password-check="mixed">
                    <i class="fa fa-circle-o" aria-hidden="true" data-icon-pending></i>
                    <i class="fa fa-check-circle hide" aria-hidden="true" data-icon-done></i>
                    {{ trans('user::users.create_page.password_check_mixed') }}
                </li>
            </ul>

            <p class="admin-profile-password-panel__hint hide" data-admin-password-generated-hint>
                <i class="fa fa-info-circle" aria-hidden="true"></i>
                <span></span>
            </p>
        </div>

        <div class="admin-profile-password-panel__field">
            <label for="{{ $passwordPanelId }}_password_confirmation" class="admin-profile-password-panel__label">
                {{ $passwordConfirmLabel }}
                @if ($passwordRequired)
                    <span class="admin-profile-password-panel__required" aria-hidden="true">*</span>
                @endif
            </label>

            <div class="admin-profile-password-panel__input-wrap @error('password_confirmation') has-error @enderror">
                <input
                    type="password"
                    name="password_confirmation"
                    id="{{ $passwordPanelId }}_password_confirmation"
                    class="form-control admin-profile-password-panel__input"
                    @if ($passwordRequired) required @endif
                    autocomplete="new-password"
                    data-admin-password-confirm
                >
                <button
                    type="button"
                    class="admin-profile-password-panel__toggle"
                    data-admin-password-toggle
                    data-target="{{ $passwordPanelId }}_password_confirmation"
                    aria-label="{{ trans('user::auth.show_password') }}"
                    aria-pressed="false"
                >
                    <i class="fa fa-eye" aria-hidden="true" data-icon-show></i>
                    <i class="fa fa-eye-slash hide" aria-hidden="true" data-icon-hide></i>
                </button>
            </div>

            @error('password_confirmation')
                <p class="help-block text-red">{{ $message }}</p>
            @enderror

            <p class="admin-profile-password-panel__match" data-admin-password-match></p>
        </div>
    </div>
</div>
