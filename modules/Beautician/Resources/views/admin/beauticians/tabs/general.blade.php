@php
    use Modules\Beautician\Support\JobTitleOptions;

    $profileColor = old('profile_color', $beautician->profile_color ?? '#6366f1');
    $displayFirstName = old('first_name', $beautician->first_name ?? '');
    $displayLastName = old('last_name', $beautician->last_name ?? '');
    $displayName = trim($displayFirstName . ' ' . $displayLastName);
    $displayTitle = old('job_title', $beautician->job_title ?? '');
    $isActive = (bool) old('is_active', $beautician->is_active ?? true);
    $hasPhoto = $beautician->profile_image->exists;
    $displayPhone = old('phone', $beautician->phone ?? '');
    $displayPosition = old('position', $beautician->position ?? 0);
    $jobTitleOptions = JobTitleOptions::forSelect($displayTitle ?: null);
    $portalUserId = old('user_id', $beautician->user_id);
    $portalUserLabel = trans('beautician::beauticians.form.no_portal_user');

    if ($portalUserId) {
        if ($beautician->user && (int) $beautician->user->id === (int) $portalUserId) {
            $portalUserLabel = trim($beautician->user->first_name.' '.$beautician->user->last_name) ?: $beautician->user->email;
        } else {
            foreach ($adminUsers ?? [] as $adminUser) {
                if ((int) $adminUser['id'] === (int) $portalUserId) {
                    $portalUserLabel = preg_replace('/\s*\([^)]+\)$/', '', $adminUser['label']);
                    break;
                }
            }
        }
    }

    $memberSince = $beautician->exists && $beautician->created_at
        ? $beautician->created_at->timezone(config('app.timezone'))->format('d M Y')
        : null;

    $portalErrorFields = ['user_id', 'portal_email', 'portal_password', 'portal_password_confirmation'];
    $portalHasErrors = collect($portalErrorFields)->contains(fn ($field) => $errors->has($field));
    $passwordDisclosureOpen = $errors->has('portal_password') || $errors->has('portal_password_confirmation');
    $portalDisclosureOpen = $portalHasErrors || (bool) $portalUserId;
@endphp

<div class="beautician-profile-page">
    <header class="bp-hero" id="bp-hero" style="--bp-profile-color: {{ $profileColor }};">
        <div class="bp-hero-main">
            <div class="bp-hero-avatar-block">
                <div class="bp-hero-avatar-wrap">
                    <div class="bp-hero-avatar" id="bp-hero-avatar">
                        @if ($hasPhoto)
                            <img src="{{ $beautician->profile_image->path }}" alt="" id="bp-hero-avatar-img">
                        @else
                            <span
                                class="bp-hero-initial"
                                id="bp-hero-avatar-initial"
                                style="background-color: {{ $profileColor }};"
                            >{{ strtoupper(mb_substr($displayFirstName ?: 'B', 0, 1)) }}</span>
                        @endif
                    </div>

                    @hasAccess('admin.media.index')
                        <div class="bp-hero-avatar-media" id="bp-avatar-media">
                            <button
                                type="button"
                                class="image-picker"
                                id="bp-avatar-picker-trigger"
                                data-input-name="files[profile]"
                                tabindex="-1"
                                aria-hidden="true"
                            ></button>

                            <div class="single-image image-holder-wrapper">
                                @if ($hasPhoto)
                                    <div class="image-holder">
                                        <img src="{{ $beautician->profile_image->path }}" alt="">
                                        <button type="button" class="btn remove-image" data-input-name="files[profile]">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none">
                                                <path d="M6.00098 17.9995L17.9999 6.00053" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M17.9999 17.9995L6.00098 6.00055" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                        <input type="hidden" name="files[profile]" value="{{ $beautician->profile_image->id }}">
                                    </div>
                                @else
                                    <div class="image-holder placeholder">
                                        <input type="hidden" name="files[profile]" value="">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <button
                            type="button"
                            class="bp-hero-avatar-edit"
                            id="bp-avatar-edit-btn"
                            title="{{ trans('beautician::beauticians.form.change_photo') }}"
                        >
                            <span class="sr-only">{{ trans('beautician::beauticians.form.change_photo') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 8h2l1.5-2h7L16 8h4a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <circle cx="12" cy="13" r="3.2" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                        </button>

                        <button
                            type="button"
                            class="bp-hero-avatar-remove"
                            id="bp-avatar-remove-btn"
                            title="{{ trans('beautician::beauticians.form.remove_photo') }}"
                            @if (! $hasPhoto) hidden @endif
                        >
                            <span class="sr-only">{{ trans('beautician::beauticians.form.remove_photo') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    @endHasAccess
                </div>
            </div>

            <div class="bp-hero-identity">
                <div class="bp-hero-name-row">
                    <h2 class="bp-hero-name" id="bp-hero-name">{{ $displayName ?: trans('beautician::beauticians.form.new_profile') }}</h2>
                    <span class="bp-hero-status-badge {{ $isActive ? 'is-active' : 'is-inactive' }}" id="bp-hero-status-badge">
                        {{ $isActive ? trans('beautician::beauticians.active') : trans('beautician::beauticians.inactive') }}
                    </span>
                </div>

                <p class="bp-hero-meta">
                    <i class="fa fa-briefcase"></i>
                    <span id="bp-hero-title">{{ $displayTitle ?: trans('beautician::beauticians.form.no_job_title') }}</span>
                </p>

                <p class="bp-hero-meta">
                    <i class="fa fa-phone"></i>
                    <span id="bp-hero-phone">{{ $displayPhone ?: '—' }}</span>
                </p>
            </div>
        </div>

        @if ($beautician->exists)
            <div class="bp-hero-insights">
                <article class="bp-hero-insight">
                    <div class="bp-hero-insight-icon">
                        <span class="bp-hero-insight-swatch" id="bp-hero-insight-swatch" style="background-color: {{ $profileColor }};"></span>
                    </div>
                    <div class="bp-hero-insight-body">
                        <span class="bp-hero-insight-label">{{ trans('beautician::beauticians.form.hero_accent_color') }}</span>
                        <span class="bp-hero-insight-value" id="bp-hero-insight-color">{{ strtoupper($profileColor) }}</span>
                    </div>
                </article>

                <article class="bp-hero-insight">
                    <div class="bp-hero-insight-icon">
                        <i class="fa fa-user"></i>
                    </div>
                    <div class="bp-hero-insight-body">
                        <span class="bp-hero-insight-label">{{ trans('beautician::beauticians.form.hero_portal_account') }}</span>
                        <span class="bp-hero-insight-value" id="bp-hero-insight-portal">{{ $portalUserLabel }}</span>
                    </div>
                </article>

                <article class="bp-hero-insight">
                    <div class="bp-hero-insight-icon">
                        <i class="fa fa-shopping-cart"></i>
                    </div>
                    <div class="bp-hero-insight-body">
                        <span class="bp-hero-insight-label">{{ trans('beautician::beauticians.form.hero_checkout') }}</span>
                        <span class="bp-hero-insight-value bp-hero-insight-checkout {{ $isActive ? 'is-active' : 'is-inactive' }}" id="bp-hero-insight-checkout">
                            {{ $isActive ? trans('beautician::beauticians.form.hero_visible_at_checkout') : trans('beautician::beauticians.form.hero_hidden_at_checkout') }}
                        </span>
                    </div>
                </article>

                <article class="bp-hero-insight">
                    <div class="bp-hero-insight-icon">
                        <i class="fa fa-calendar"></i>
                    </div>
                    <div class="bp-hero-insight-body">
                        <span class="bp-hero-insight-label">{{ trans('beautician::beauticians.form.hero_profile_created') }}</span>
                        <span class="bp-hero-insight-value" id="bp-hero-insight-since">
                            {{ $memberSince ?: trans('beautician::beauticians.form.hero_not_saved_yet') }}
                        </span>
                    </div>
                </article>
            </div>

            <ul class="bp-hero-stats">
                <li>
                    <span class="bp-hero-stat-label">{{ trans('beautician::beauticians.table.status') }}</span>
                    <span class="bp-hero-stat-value bp-hero-stat-status {{ $isActive ? 'is-active' : 'is-inactive' }}" id="bp-hero-stat-status">
                        {{ $isActive ? trans('beautician::beauticians.active') : trans('beautician::beauticians.inactive') }}
                    </span>
                </li>
                <li>
                    <span class="bp-hero-stat-label">{{ trans('beautician::beauticians.beautician') }}</span>
                    <span class="bp-hero-stat-value" id="bp-hero-stat-id">
                        {{ $beautician->id ? '#'.$beautician->id : '—' }}
                    </span>
                </li>
                <li>
                    <span class="bp-hero-stat-label">{{ trans('beautician::attributes.sort_order') }}</span>
                    <span class="bp-hero-stat-value" id="bp-hero-stat-position">{{ $displayPosition }}</span>
                </li>
            </ul>
        @else
            <p class="bp-hero-new-hint">{{ trans('beautician::beauticians.form.hero_new_hint') }}</p>
        @endif
    </header>

    <div class="bp-content-layout">
        <main class="bp-content-main">
            <div class="bp-card bp-card--basic">
            <div class="bp-card-header">
                <h3>{{ trans('beautician::beauticians.form.sections.basic') }}</h3>
                <p>{{ trans('beautician::beauticians.form.sections.basic_help') }}</p>
            </div>
            <div class="bp-card-body">
                <div class="bp-basic-layout">
                    <div class="bp-basic-fields">
                        <div class="bp-basic-field">
                            {{ Form::text('first_name', trans('beautician::attributes.first_name'), $errors, $beautician, [
                                'required' => true,
                                'class' => 'form-control bp-input',
                            ]) }}
                        </div>

                        <div class="bp-basic-field">
                            {{ Form::text('last_name', trans('beautician::attributes.last_name'), $errors, $beautician, [
                                'required' => true,
                                'class' => 'form-control bp-input',
                            ]) }}
                        </div>

                        <div class="bp-basic-field">
                            {{ Form::phone('phone', trans('beautician::attributes.phone'), $errors, $beautician, [
                                'required' => true,
                                'class' => 'form-control bp-input',
                                'placeholder' => '60123456789',
                            ]) }}
                            <p class="bp-field-hint">{{ trans('beautician::beauticians.form.phone_help') }}</p>
                        </div>

                        <div class="bp-basic-field">
                            @include('beautician::admin.partials.job_title_field', [
                                'beautician' => $beautician,
                                'jobTitleOptions' => $jobTitleOptions,
                            ])
                        </div>
                    </div>

                    <div class="bp-appearance-panel">
                        <span class="bp-appearance-panel__icon" aria-hidden="true">
                            <i class="fa fa-tint"></i>
                        </span>

                        <div class="bp-color-field bp-color-field--inline">
                            {{ Form::color('profile_color', trans('beautician::attributes.profile_color'), $errors, $beautician, [
                                'id' => 'profile-color',
                                'class' => 'bp-color-input',
                            ]) }}
                            <span class="bp-color-hint">{{ trans('beautician::beauticians.form.profile_color_help') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            @include('beautician::admin.beauticians.partials.schedule')
        </main>

        <aside class="bp-content-sidebar">
            <div class="bp-card bp-card--booking">
            <div class="bp-card-header">
                <h3>{{ trans('beautician::beauticians.form.sections.booking') }}</h3>
                <p>{{ trans('beautician::beauticians.form.sections.booking_help') }}</p>
            </div>
            <div class="bp-card-body">
                @if (is_module_enabled('SpaBranch'))
                    @if (($spaBranches ?? collect())->isNotEmpty())
                        @php
                            $selectedSpaBranchIds = array_map('intval', (array) (
                                request()->session()->hasOldInput('spa_branches_present')
                                    ? old('spa_branches', [])
                                    : ($selectedSpaBranchIds ?? [])
                            ));
                        @endphp

                        <div class="bp-spa-branches-field">
                            <div class="form-group">
                                <label class="col-md-3 control-label text-left">
                                    {{ trans('beautician::attributes.spa_branches') }}
                                </label>
                                <div class="col-md-9">
                                    <div class="bp-spa-branch-checkboxes">
                                        @foreach ($spaBranches as $branchId => $branchName)
                                            <label class="bp-spa-branch-checkbox">
                                                <input
                                                    type="checkbox"
                                                    name="spa_branches[]"
                                                    value="{{ $branchId }}"
                                                    {{ in_array((int) $branchId, $selectedSpaBranchIds, true) ? 'checked' : '' }}
                                                >
                                                <span>{{ $branchName }}</span>
                                            </label>
                                        @endforeach
                                    </div>

                                    @if ($errors->has('spa_branches'))
                                        <span class="help-block text-red">{{ $errors->first('spa_branches') }}</span>
                                    @endif
                                </div>
                            </div>

                            <p class="bp-field-hint">{{ trans('beautician::beauticians.form.spa_branches_help') }}</p>
                        </div>
                    @else
                        <p class="bp-field-hint">{{ trans('beautician::beauticians.form.no_spa_branches_yet') }}</p>
                        @hasAccess('admin.spa_branches.create')
                            <a href="{{ route('admin.spa_branches.create') }}" class="btn btn-default btn-sm">
                                {{ trans('beautician::beauticians.form.create_spa_branch') }}
                            </a>
                        @endHasAccess
                    @endif

                    <div class="bp-group-divider"></div>
                @endif

                <div class="bp-toggle-row">
                    <div class="bp-toggle-copy">
                        <strong>{{ trans('beautician::beauticians.form.enable_beautician') }}</strong>
                        <span>{{ trans('beautician::beauticians.form.enable_beautician_help') }}</span>
                    </div>
                    <label class="bp-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            {{ $isActive ? 'checked' : '' }}
                            id="beautician-is-active"
                        >
                        <span class="bp-switch-slider"></span>
                    </label>
                </div>

                <div class="bp-group-divider"></div>

                {{ Form::number('position', trans('beautician::beauticians.form.position_field_label'), $errors, $beautician, [
                    'min' => 0,
                    'class' => 'form-control bp-input',
                ]) }}
                <p class="bp-field-hint">{{ trans('beautician::beauticians.form.sort_order_help') }}</p>
            </div>
            </div>

            <div class="bp-card bp-card--portal">
            <div class="bp-card-header">
                <h3>{{ trans('beautician::beauticians.form.sections.portal') }}</h3>
                <p>{{ trans('beautician::beauticians.form.sections.portal_help') }}</p>
            </div>
            <div class="bp-card-body">
                @if ($beautician->exists)
                    <div class="bp-portal-actions">
                        <a
                            href="{{ route('admin.beauticians.portal.dashboard', $beautician) }}"
                            class="btn btn-primary btn-sm"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <i class="fa fa-external-link"></i>
                            {{ trans('beautician::beauticians.form.open_beautician_portal') }}
                        </a>
                        <p class="bp-field-hint">{{ trans('beautician::beauticians.form.open_beautician_portal_help') }}</p>
                    </div>
                @endif

                <p class="bp-portal-auto-note">
                    <i class="fa fa-info-circle"></i>
                    {{ trans('beautician::beauticians.form.portal_auto_note') }}
                </p>

                <details class="bp-disclosure bp-disclosure--portal" {{ $portalDisclosureOpen ? 'open' : '' }}>
                    <summary>
                        {{ trans('beautician::beauticians.form.customize_login') }}
                        <span class="bp-disclosure-hint">{{ trans('beautician::beauticians.form.customize_login_help') }}</span>
                    </summary>

                    <div class="bp-disclosure-body">
                        <div class="bp-portal-fields">
                            <div class="bp-portal-field">
                                <label for="beautician-user-id">{{ trans('beautician::attributes.user_id') }}</label>
                                <select
                                    name="user_id"
                                    id="beautician-user-id"
                                    class="form-control selectize prevent-creation"
                                >
                                    <option value="">{{ trans('beautician::beauticians.form.no_portal_user') }}</option>
                                    @foreach ($adminUsers ?? [] as $adminUser)
                                        <option
                                            value="{{ $adminUser['id'] }}"
                                            {{ (int) old('user_id', $beautician->user_id) === $adminUser['id'] ? 'selected' : '' }}
                                        >
                                            {{ $adminUser['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="bp-field-hint">{{ trans('beautician::beauticians.form.portal_user_help') }}</p>
                            </div>

                            @if (! $portalUserId)
                                {{ Form::email('portal_email', trans('beautician::attributes.portal_email'), $errors, $beautician, [
                                    'value' => old('portal_email'),
                                    'placeholder' => trans('beautician::beauticians.form.portal_email_placeholder'),
                                ]) }}
                                <p class="bp-field-hint">{{ trans('beautician::beauticians.form.portal_email_help') }}</p>
                            @elseif ($beautician->user)
                                <div class="bp-portal-field bp-portal-login-email">
                                    <label>{{ trans('beautician::beauticians.form.portal_login_email') }}</label>
                                    <p class="form-control-static">
                                        {{ $beautician->user->email }}
                                        <a href="{{ route('admin.users.edit', $beautician->user) }}" class="btn btn-default btn-xs">
                                            {{ trans('beautician::beauticians.form.open_user_account') }}
                                        </a>
                                    </p>
                                </div>
                            @endif

                            <details class="bp-disclosure bp-disclosure--password" {{ $passwordDisclosureOpen ? 'open' : '' }}>
                                <summary>{{ trans('beautician::beauticians.form.set_specific_password') }}</summary>

                                <div class="bp-disclosure-body">
                                    <div
                                        class="bp-password-panel"
                                        id="bp-password-panel"
                                        data-bp-password-panel
                                        data-strength-empty="{{ trans('user::users.create_page.password_strength_empty') }}"
                                        data-strength-weak="{{ trans('user::users.create_page.password_strength_weak') }}"
                                        data-strength-fair="{{ trans('user::users.create_page.password_strength_fair') }}"
                                        data-strength-good="{{ trans('user::users.create_page.password_strength_good') }}"
                                        data-strength-strong="{{ trans('user::users.create_page.password_strength_strong') }}"
                                        data-match-empty="{{ trans('user::users.create_page.password_match_empty') }}"
                                        data-match-match="{{ trans('user::users.create_page.password_match_match') }}"
                                        data-match-mismatch="{{ trans('user::users.create_page.password_match_mismatch') }}"
                                        data-show-password="{{ trans('user::auth.show_password') }}"
                                        data-hide-password="{{ trans('user::auth.hide_password') }}"
                                    >
                                        <div class="bp-portal-field bp-password-field">
                                            <div class="bp-password-field-head">
                                                <label for="portal_password">{{ trans('beautician::attributes.portal_password') }}</label>
                                                <button type="button" class="bp-password-generate" data-bp-password-generate>
                                                    <i class="fa fa-magic"></i>
                                                    {{ trans('user::users.create_page.password_generate') }}
                                                </button>
                                            </div>

                                            <div class="bp-password-input-wrap @error('portal_password') has-error @enderror">
                                                <input
                                                    type="password"
                                                    name="portal_password"
                                                    id="portal_password"
                                                    class="form-control"
                                                    value=""
                                                    placeholder="{{ trans('beautician::beauticians.form.portal_password_placeholder') }}"
                                                    autocomplete="new-password"
                                                    data-bp-password-input
                                                >
                                                <button
                                                    type="button"
                                                    class="bp-password-toggle"
                                                    data-bp-password-toggle
                                                    data-target="portal_password"
                                                    aria-label="{{ trans('user::auth.show_password') }}"
                                                    aria-pressed="false"
                                                >
                                                    <i class="fa fa-eye" data-icon-show></i>
                                                    <i class="fa fa-eye-slash hide" data-icon-hide></i>
                                                </button>
                                            </div>

                                            @error('portal_password')
                                                <span class="help-block text-red">{{ $message }}</span>
                                            @enderror

                                            <div class="bp-password-strength" data-bp-password-strength hidden>
                                                <div class="bp-password-strength-track" aria-hidden="true">
                                                    <span class="bp-password-strength-fill" data-bp-password-strength-fill></span>
                                                </div>
                                                <p class="bp-password-strength-label" data-bp-password-strength-label></p>
                                            </div>

                                            <ul class="bp-password-checks" aria-live="polite">
                                                <li data-bp-password-check="length">
                                                    <i class="fa fa-circle-o" data-icon-pending></i>
                                                    <i class="fa fa-check-circle hide" data-icon-done></i>
                                                    {{ trans('user::users.create_page.password_check_length') }}
                                                </li>
                                                <li data-bp-password-check="length8">
                                                    <i class="fa fa-circle-o" data-icon-pending></i>
                                                    <i class="fa fa-check-circle hide" data-icon-done></i>
                                                    {{ trans('user::users.create_page.password_check_length8') }}
                                                </li>
                                                <li data-bp-password-check="letter">
                                                    <i class="fa fa-circle-o" data-icon-pending></i>
                                                    <i class="fa fa-check-circle hide" data-icon-done></i>
                                                    {{ trans('user::users.create_page.password_check_letter') }}
                                                </li>
                                                <li data-bp-password-check="number">
                                                    <i class="fa fa-circle-o" data-icon-pending></i>
                                                    <i class="fa fa-check-circle hide" data-icon-done></i>
                                                    {{ trans('user::users.create_page.password_check_number') }}
                                                </li>
                                                <li data-bp-password-check="mixed">
                                                    <i class="fa fa-circle-o" data-icon-pending></i>
                                                    <i class="fa fa-check-circle hide" data-icon-done></i>
                                                    {{ trans('user::users.create_page.password_check_mixed') }}
                                                </li>
                                            </ul>

                                            <p class="bp-password-generated-hint hide" data-bp-password-generated-hint>
                                                <i class="fa fa-info-circle"></i>
                                                <span>{{ trans('user::users.create_page.password_generated_hint') }}</span>
                                            </p>
                                        </div>

                                        <div class="bp-portal-field bp-password-field">
                                            <label for="portal_password_confirmation">{{ trans('beautician::attributes.portal_password_confirmation') }}</label>

                                            <div class="bp-password-input-wrap @error('portal_password_confirmation') has-error @enderror">
                                                <input
                                                    type="password"
                                                    name="portal_password_confirmation"
                                                    id="portal_password_confirmation"
                                                    class="form-control"
                                                    value=""
                                                    autocomplete="new-password"
                                                    data-bp-password-confirm
                                                >
                                                <button
                                                    type="button"
                                                    class="bp-password-toggle"
                                                    data-bp-password-toggle
                                                    data-target="portal_password_confirmation"
                                                    aria-label="{{ trans('user::auth.show_password') }}"
                                                    aria-pressed="false"
                                                >
                                                    <i class="fa fa-eye" data-icon-show></i>
                                                    <i class="fa fa-eye-slash hide" data-icon-hide></i>
                                                </button>
                                            </div>

                                            @error('portal_password_confirmation')
                                                <span class="help-block text-red">{{ $message }}</span>
                                            @enderror

                                            <p class="bp-password-match" data-bp-password-match></p>
                                        </div>
                                    </div>
                                    <p class="bp-field-hint">{{ trans('beautician::beauticians.form.portal_password_help') }}</p>
                                </div>
                            </details>

                            @if ($beautician->user_id)
                                <button
                                    type="submit"
                                    form="beautician-reset-portal-form"
                                    class="bp-portal-reset-link"
                                    id="beautician-reset-portal-btn"
                                >
                                    <i class="fa fa-refresh"></i>
                                    {{ trans('beautician::beauticians.form.portal_reset_password') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </details>
            </div>
            </div>

            <div class="bp-form-actions bp-form-actions--sidebar">
                <span class="bp-form-actions__hint">{{ trans('beautician::beauticians.form.save_all_changes_help') }}</span>
                <button type="submit" class="btn btn-primary" data-loading>
                    {{ $beautician->exists ? trans('beautician::beauticians.form.save_all_changes') : trans('beautician::beauticians.form.create_beautician') }}
                </button>
            </div>
        </aside>
    </div>
</div>

@push('styles')
    <style>
        .tab-pane#general {
            background: #f3f4f6;
            margin: 0 -15px;
            padding: 20px;
            border-radius: 0 0 12px 12px;
            overflow: visible;
        }

        .beautician-form-layout .accordion-box-content {
            padding: 0;
            border: none;
            box-shadow: none;
            background: transparent;
        }

        .tab-content.clearfix:has(.beautician-profile-page) > .form-group {
            display: none;
        }

        .tab-pane#general .accordion-box-content,
        .tab-pane#general .tab-content {
            overflow: visible;
        }

        .beautician-profile-page {
            --bp-primary: #4f46e5;
            --bp-primary-soft: #eef2ff;
            --bp-border: #e5e7eb;
            --bp-text: #111827;
            --bp-muted: #6b7280;
            --bp-card-radius: 16px;
            margin: -10px 0 10px;
        }

        .beautician-profile-page .bp-content-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(300px, 360px);
            gap: 24px;
            width: 100%;
            max-width: none;
            align-items: start;
        }

        .beautician-profile-page .bp-content-main,
        .beautician-profile-page .bp-content-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
            min-width: 0;
        }

        .beautician-profile-page .bp-content-layout .bp-card {
            min-width: 0;
            margin-bottom: 0;
        }

        .beautician-profile-page .bp-card--booking,
        .beautician-profile-page .bp-card--portal {
            height: auto;
        }

        .beautician-profile-page .bp-basic-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(200px, 240px);
            gap: 24px;
            align-items: stretch;
        }

        .beautician-profile-page .bp-basic-fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px 24px;
            min-width: 0;
        }

        .beautician-profile-page .bp-basic-field {
            min-width: 0;
        }

        .beautician-profile-page .bp-basic-field .form-group {
            margin-bottom: 0;
        }

        .beautician-profile-page .bp-basic-field .bp-field-hint {
            margin-bottom: 0;
        }

        .beautician-profile-page .bp-appearance-panel {
            position: relative;
            display: flex;
            align-items: center;
            min-width: 0;
            padding: 20px;
            border: 1px solid var(--bp-border);
            border-radius: 14px;
            background: linear-gradient(145deg, #fafafa 0%, #f8fafc 100%);
        }

        .beautician-profile-page .bp-appearance-panel__icon {
            position: absolute;
            top: 14px;
            right: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 9px;
            color: var(--bp-primary);
            background: var(--bp-primary-soft);
        }

        .beautician-profile-page .bp-appearance-panel .bp-color-field {
            width: 100%;
            padding-top: 8px;
        }

        .beautician-profile-page .bp-hero-new-hint {
            margin: 0;
            font-size: 13.5px;
            color: var(--bp-muted);
        }

        .beautician-profile-page .form-group {
            margin-bottom: 18px;
        }

        .beautician-profile-page .bp-card-body .form-group::before,
        .beautician-profile-page .bp-card-body .form-group::after {
            display: none;
        }

        .beautician-profile-page .bp-card-body .form-group > label,
        .beautician-profile-page .bp-card-body .form-group > .col-md-3,
        .beautician-profile-page .bp-card-body .form-group > .col-md-9 {
            width: 100%;
            max-width: 100%;
            padding-left: 0;
            padding-right: 0;
            float: none;
        }

        .beautician-profile-page .bp-card-body .form-group > label {
            padding-top: 0;
            margin-bottom: 8px;
        }

        .beautician-profile-page .bp-card-body .form-group {
            margin-left: 0;
            margin-right: 0;
        }

        .beautician-profile-page .bp-content-layout .bp-card-header {
            padding: 18px 20px 0;
        }

        .beautician-profile-page .bp-content-layout .bp-card-body {
            padding: 16px 20px 20px;
        }

        .beautician-profile-page .bp-identity-row {
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--bp-border);
        }

        .beautician-profile-page .bp-identity-row .bp-color-field--inline {
            max-width: 260px;
        }

        .beautician-profile-page .bp-appearance-panel .bp-color-field--inline {
            max-width: none;
        }

        .beautician-profile-page .bp-group-divider {
            height: 1px;
            background: var(--bp-border);
            margin: 18px 0;
        }

        .beautician-profile-page .bp-portal-auto-note {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin: 0 0 4px;
            padding: 10px 12px;
            font-size: 13px;
            color: var(--bp-muted);
            background: #f9fafb;
            border-radius: 8px;
        }

        .beautician-profile-page .bp-portal-auto-note i {
            margin-top: 2px;
            color: var(--bp-primary);
        }

        .beautician-profile-page .bp-disclosure {
            margin-top: 14px;
            border-top: 1px solid var(--bp-border);
            padding-top: 14px;
        }

        .beautician-profile-page .bp-disclosure summary {
            cursor: pointer;
            list-style: none;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--bp-primary);
            display: flex;
            align-items: baseline;
            gap: 8px;
            flex-wrap: wrap;
        }

        .beautician-profile-page .bp-disclosure summary::-webkit-details-marker {
            display: none;
        }

        .beautician-profile-page .bp-disclosure summary::before {
            content: "\25B8";
            display: inline-block;
            font-size: 11px;
            color: var(--bp-muted);
            transition: transform 0.15s ease;
        }

        .beautician-profile-page .bp-disclosure[open] > summary::before {
            transform: rotate(90deg);
        }

        .beautician-profile-page .bp-disclosure summary .bp-disclosure-hint {
            font-size: 12px;
            font-weight: 400;
            color: var(--bp-muted);
        }

        .beautician-profile-page .bp-disclosure-body {
            margin-top: 14px;
        }

        .beautician-profile-page .bp-disclosure .bp-disclosure {
            margin-top: 12px;
            padding-top: 12px;
        }

        .beautician-profile-page .bp-portal-reset-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 4px;
            padding: 0;
            background: none;
            border: none;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--bp-muted);
            cursor: pointer;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .beautician-profile-page .bp-portal-reset-link:hover {
            color: var(--bp-text);
        }

        .beautician-profile-page .bp-portal-fields > .form-group {
            margin-bottom: 16px;
        }

        .beautician-profile-page .bp-portal-fields > .form-group:last-of-type {
            margin-bottom: 0;
        }

        .beautician-profile-page .bp-portal-field {
            margin-bottom: 16px;
        }

        .beautician-profile-page .bp-portal-field:last-child {
            margin-bottom: 0;
        }

        .beautician-profile-page .bp-portal-field > label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--bp-text);
            margin-bottom: 8px;
        }

        .beautician-profile-page .bp-portal-field .form-control {
            height: 44px;
            border-radius: 10px;
            border-color: var(--bp-border);
            box-shadow: none;
        }

        .beautician-profile-page .bp-portal-login-email .form-control-static {
            margin: 0;
            padding: 0;
            min-height: 0;
            font-size: 13px;
            line-height: 1.5;
            word-break: break-word;
        }

        .beautician-profile-page .bp-portal-login-email .btn {
            margin-top: 8px;
        }

        .beautician-profile-page .bp-portal-fields .bp-field-hint {
            margin-top: 8px;
            margin-bottom: 16px;
        }

        .beautician-profile-page .bp-portal-fields .bp-field-hint:last-child {
            margin-bottom: 0;
        }

        .bp-password-panel {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .bp-password-field-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 8px;
        }

        .bp-password-field-head label {
            margin-bottom: 0 !important;
        }

        .bp-password-generate {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-shrink: 0;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
            color: var(--bp-primary);
            background: var(--bp-primary-soft);
            border: 1px solid transparent;
            border-radius: 999px;
            cursor: pointer;
            transition: background 0.15s ease;
        }

        .bp-password-generate:hover {
            background: color-mix(in srgb, var(--bp-primary) 18%, #ffffff);
        }

        .bp-password-input-wrap {
            position: relative;
        }

        .bp-password-input-wrap .form-control {
            height: 44px;
            padding-right: 40px;
            border-radius: 10px;
            border-color: var(--bp-border);
            box-shadow: none;
        }

        .bp-password-input-wrap.has-error .form-control {
            border-color: #dc2626;
        }

        .bp-password-toggle {
            position: absolute;
            top: 0;
            right: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            padding: 0;
            color: var(--bp-muted);
            background: transparent;
            border: none;
            cursor: pointer;
        }

        .bp-password-toggle:hover {
            color: var(--bp-text);
        }

        .bp-password-toggle .hide {
            display: none;
        }

        .bp-password-strength {
            margin-top: 10px;
        }

        .bp-password-strength-track {
            height: 4px;
            border-radius: 999px;
            background: #e5e7eb;
            overflow: hidden;
        }

        .bp-password-strength-fill {
            display: block;
            height: 100%;
            width: 0;
            border-radius: 999px;
            background: #dc2626;
            transition: width 0.2s ease, background 0.2s ease;
        }

        .bp-password-strength[data-level="fair"] .bp-password-strength-fill {
            background: #f59e0b;
        }

        .bp-password-strength[data-level="good"] .bp-password-strength-fill {
            background: #3b82f6;
        }

        .bp-password-strength[data-level="strong"] .bp-password-strength-fill {
            background: #16a34a;
        }

        .bp-password-strength-label {
            margin: 6px 0 0;
            font-size: 12px;
            color: var(--bp-muted);
        }

        .bp-password-checks {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 4px 12px;
            margin: 10px 0 0;
            padding: 0;
            list-style: none;
        }

        .bp-password-checks li {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--bp-muted);
        }

        .bp-password-checks li i {
            font-size: 13px;
        }

        .bp-password-checks li[data-icon-done] {
            color: #16a34a;
        }

        .bp-password-checks li.is-met {
            color: var(--bp-text);
        }

        .bp-password-checks li.is-met [data-icon-pending] {
            display: none;
        }

        .bp-password-checks li:not(.is-met) [data-icon-done] {
            display: none;
        }

        .bp-password-checks .fa-check-circle {
            color: #16a34a;
        }

        .bp-password-generated-hint {
            display: flex;
            align-items: flex-start;
            gap: 6px;
            margin: 10px 0 0;
            padding: 8px 10px;
            font-size: 12px;
            line-height: 1.4;
            color: var(--bp-primary);
            background: var(--bp-primary-soft);
            border-radius: 8px;
        }

        .bp-password-generated-hint.hide {
            display: none;
        }

        .bp-password-match {
            margin: 8px 0 0;
            font-size: 12px;
            min-height: 16px;
        }

        .bp-password-match.is-match {
            color: #16a34a;
        }

        .bp-password-match.is-mismatch {
            color: #dc2626;
        }

        .bp-password-match.is-empty {
            color: var(--bp-muted);
        }

        .beautician-profile-page .bp-portal-actions {
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e8edf3;

            .btn {
                width: 100%;
                font-weight: 600;
            }
        }

        .beautician-profile-page .bp-portal-links {
            margin-bottom: 12px;
        }

        .beautician-profile-page .bp-portal-reset {
            margin-top: 4px;
        }

        .beautician-profile-page .bp-field-block {
            margin-bottom: 0;
        }

        .beautician-profile-page .bp-field-block > label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--bp-text);
            margin-bottom: 8px;
        }

        .beautician-profile-page .bp-field-block .form-control {
            height: 44px;
            border-radius: 10px;
            border-color: var(--bp-border);
            box-shadow: none;
        }

        .bp-form-split {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0 48px;
            align-items: start;
            padding: 4px 8px 0;
        }

        .bp-form-split__col {
            min-width: 0;
            padding: 0 8px;
        }

        .bp-form-split__col + .bp-form-split__col {
            padding-left: 32px;
            border-left: 1px solid var(--bp-border);
        }

        .bp-form-split__col .form-group {
            margin-bottom: 18px;
        }

        .bp-form-split__col .form-group:last-of-type {
            margin-bottom: 0;
        }

        .bp-card-schedule {
            margin-top: 4px;
        }

        .bp-card-schedule .bp-schedule-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }

        .bp-card-schedule .bp-schedule-stats .fc-saas-stat {
            align-items: center;
            gap: 14px;
            padding: 18px 16px;
            border-radius: 12px;
            min-width: 0;
            border-style: solid;
        }

        .bp-card-schedule .bp-schedule-stats .fc-saas-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            font-size: 18px;
        }

        .bp-card-schedule .bp-schedule-stats .fc-saas-stat-body {
            padding-top: 0;
            min-width: 0;
        }

        .bp-card-schedule .bp-schedule-stats .fc-saas-stat-label {
            margin-bottom: 6px;
            font-size: 11px;
            line-height: 1.3;
        }

        .bp-card-schedule .bp-schedule-stats .fc-saas-stat-value {
            margin-bottom: 4px;
            font-size: 24px;
            line-height: 1.15;
        }

        .bp-card-schedule .bp-schedule-stats .fc-saas-stat-hint {
            font-size: 11px;
            line-height: 1.4;
        }

        @@media (max-width: 991px) {
            .bp-card-schedule .bp-schedule-stats {
                grid-template-columns: 1fr;
            }
        }

        .bp-schedule-tabs {
            margin-bottom: 16px;
            border-bottom: 1px solid var(--bp-border);
        }

        .bp-schedule-tabs > li > a {
            font-weight: 600;
            color: var(--bp-muted);
        }

        .bp-schedule-tabs > li.active > a,
        .bp-schedule-tabs > li.active > a:hover,
        .bp-schedule-tabs > li.active > a:focus {
            color: var(--bp-primary);
            border-color: var(--bp-border) var(--bp-border) transparent;
        }

        .bp-schedule-tabs__link {
            float: right;
        }

        .bp-schedule-tabs__link > a {
            color: var(--bp-muted);
        }

        .bp-schedule-panels .tr-calendar.box,
        .bp-schedule-panels .tr-kanban--embedded .tr-kanban-board {
            box-shadow: none;
            border: 1px solid var(--bp-border);
            border-radius: 12px;
        }

        .bp-schedule-panels .tr-calendar.box {
            padding: 16px;
        }

        .bp-schedule-app .tr-kanban-card-beautician {
            display: none;
        }

        .beautician-profile-page .form-group > label {
            font-size: 13px;
            font-weight: 600;
            color: var(--bp-text);
            margin-bottom: 8px;
        }

        .beautician-profile-page .bp-input {
            height: 44px;
            border-radius: 10px;
            border-color: var(--bp-border);
            box-shadow: none;
        }

        .beautician-profile-page .bp-input:focus {
            border-color: var(--bp-primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
        }

        .bp-hero {
            display: grid;
            grid-template-columns: auto minmax(280px, 1fr) auto;
            align-items: center;
            gap: 28px 32px;
            margin-bottom: 28px;
            padding: 28px 32px;
            border-radius: var(--bp-card-radius);
            border: 1px solid var(--bp-border);
            background: linear-gradient(
                135deg,
                color-mix(in srgb, var(--bp-profile-color, #6366f1) 18%, #ffffff) 0%,
                #ffffff 55%
            );
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
        }

        .bp-hero-main {
            display: flex;
            align-items: center;
            gap: 20px;
            min-width: 0;
        }

        .bp-hero-avatar-block {
            flex-shrink: 0;
        }

        .bp-form-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-top: 4px;
            padding: 20px 0 4px;
            border-top: 1px solid var(--bp-border);
        }

        .bp-form-actions--sidebar {
            align-items: stretch;
            flex-direction: column;
            margin-top: 0;
            padding: 18px;
            border: 1px solid var(--bp-border);
            border-radius: var(--bp-card-radius);
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .bp-form-actions--sidebar .bp-form-actions__hint {
            max-width: none;
        }

        .bp-form-actions--sidebar .btn-primary {
            width: 100%;
        }

        .bp-form-actions__hint {
            margin: 0;
            max-width: 720px;
            font-size: 13px;
            line-height: 1.45;
            color: #64748b;
        }

        .bp-form-actions .btn-primary {
            min-width: 120px;
            height: 42px;
            border-radius: 10px;
            font-weight: 600;
        }

        .bp-hero-avatar-wrap {
            position: relative;
            flex-shrink: 0;
            --bp-avatar-size: 120px;
        }

        .bp-hero-avatar-media {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .bp-hero-avatar-edit,
        .bp-hero-avatar-remove {
            position: absolute;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 0;
            color: #fff;
            background: var(--bp-profile-color, #6366f1);
            border: 3px solid #fff;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.18);
            cursor: pointer;
            transition: transform 0.15s ease, background 0.15s ease;
        }

        .bp-hero-avatar-edit:hover,
        .bp-hero-avatar-remove:hover {
            transform: scale(1.05);
        }

        .bp-hero-avatar-edit {
            right: 0;
            bottom: 0;
            width: 36px;
            height: 36px;
        }

        .bp-hero-avatar-remove {
            top: 0;
            right: 0;
            width: 30px;
            height: 30px;
            background: #dc2626;
            border-width: 2px;
        }

        .bp-hero-avatar-remove:hover {
            background: #b91c1c;
        }

        .bp-hero-avatar {
            width: var(--bp-avatar-size, 120px);
            height: var(--bp-avatar-size, 120px);
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.12);
            overflow: hidden;
            background: var(--bp-primary-soft);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bp-hero-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .bp-hero-initial {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.04em;
        }

        .bp-hero-identity {
            min-width: 0;
        }

        .bp-hero-name-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .bp-hero-name {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: var(--bp-text);
            line-height: 1.2;
            word-break: break-word;
        }

        .bp-hero-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .bp-hero-status-badge.is-active {
            color: #166534;
            background: #dcfce7;
        }

        .bp-hero-status-badge.is-inactive {
            color: #991b1b;
            background: #fee2e2;
        }

        .bp-hero-insights {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            width: 100%;
            max-width: 560px;
            margin: 0 auto;
        }

        .bp-hero-insight {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            min-width: 0;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.85);
            background: rgba(255, 255, 255, 0.78);
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
        }

        .bp-hero-insight-icon {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            color: var(--bp-profile-color, #6366f1);
            background: color-mix(in srgb, var(--bp-profile-color, #6366f1) 12%, #ffffff);
            font-size: 15px;
        }

        .bp-hero-insight-swatch {
            display: block;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 0 1px rgba(15, 23, 42, 0.08);
        }

        .bp-hero-insight-body {
            min-width: 0;
        }

        .bp-hero-insight-label {
            display: block;
            margin-bottom: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--bp-muted);
        }

        .bp-hero-insight-value {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--bp-text);
            line-height: 1.35;
            word-break: break-word;
        }

        .bp-hero-insight-checkout.is-active {
            color: #166534;
        }

        .bp-hero-insight-checkout.is-inactive {
            color: #991b1b;
        }

        .bp-hero-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 6px;
            font-size: 14px;
            color: var(--bp-muted);
            word-break: break-word;
        }

        .bp-hero-meta i {
            flex-shrink: 0;
            font-size: 16px;
            color: var(--bp-profile-color, #6366f1);
        }

        .bp-hero-meta:last-child {
            margin-bottom: 0;
        }

        .bp-hero-stats {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin: 0;
            padding: 0;
            list-style: none;
            min-width: 200px;
        }

        .bp-hero-stat-label {
            display: block;
            margin-bottom: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--bp-muted);
        }

        .bp-hero-stat-value {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--bp-text);
        }

        .bp-hero-stat-status.is-active {
            color: #166534;
        }

        .bp-hero-stat-status.is-inactive {
            color: #991b1b;
        }

        .bp-card {
            background: #fff;
            border: 1px solid var(--bp-border);
            border-radius: var(--bp-card-radius);
            margin-bottom: 20px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .bp-card-header {
            padding: 20px 32px 0;
        }

        .bp-card-header h3 {
            margin: 0 0 6px;
            font-size: 16px;
            font-weight: 700;
            color: var(--bp-text);
        }

        .bp-card-header p {
            margin: 0;
            font-size: 13px;
            color: var(--bp-muted);
            line-height: 1.5;
        }

        .bp-card-body {
            padding: 24px 32px 28px;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .bp-color-field .form-group {
            margin-bottom: 0;
        }

        .bp-color-field .form-group > label {
            font-size: 13px;
            font-weight: 600;
            color: var(--bp-text);
        }

        .bp-color-input {
            width: 100%;
            height: 44px;
            padding: 4px;
            border-radius: 10px;
            border: 1px solid var(--bp-border);
            cursor: pointer;
        }

        .bp-color-hint,
        .bp-field-hint {
            display: block;
            margin-top: 8px;
            font-size: 12px;
            color: var(--bp-muted);
            line-height: 1.45;
        }

        .bp-form-grid .bp-field-hint {
            margin-top: -8px;
            margin-bottom: 4px;
        }

        .bp-form-grid .bp-field-stack .bp-field-hint {
            margin-top: 8px;
        }

        .bp-spa-branches-field .form-group {
            margin-bottom: 0;
        }

        .bp-spa-branch-checkboxes {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 14px 16px;
            background: #f9fafb;
            border: 1px solid var(--bp-border);
            border-radius: 12px;
        }

        .bp-spa-branch-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            font-weight: 400;
            cursor: pointer;
        }

        .bp-spa-branch-checkbox input {
            margin: 0;
            flex-shrink: 0;
        }

        .bp-spa-branch-checkbox span {
            font-size: 14px;
            color: var(--bp-text);
        }

        .bp-toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 18px;
            background: #f9fafb;
            border-radius: 12px;
            border: 1px solid var(--bp-border);
        }

        .bp-toggle-copy {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .bp-toggle-copy strong {
            font-size: 14px;
            color: var(--bp-text);
        }

        .bp-toggle-copy span {
            font-size: 13px;
            color: var(--bp-muted);
        }

        .bp-switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 28px;
            flex-shrink: 0;
            margin: 0;
        }

        .bp-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .bp-switch-slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background: #d1d5db;
            border-radius: 999px;
            transition: 0.2s ease;
        }

        .bp-switch-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 3px;
            bottom: 3px;
            background: #fff;
            border-radius: 50%;
            transition: 0.2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
        }

        .bp-switch input:checked + .bp-switch-slider {
            background: var(--bp-primary);
        }

        .bp-switch input:checked + .bp-switch-slider:before {
            transform: translateX(20px);
        }

        @@media (max-width: 1199px) {
            .beautician-profile-page .bp-content-layout {
                grid-template-columns: 1fr;
            }

            .beautician-profile-page .bp-content-sidebar {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                align-items: start;
            }

            .beautician-profile-page .bp-form-actions--sidebar {
                grid-column: 1 / -1;
            }
        }

        @@media (max-width: 991px) {
            .bp-hero {
                grid-template-columns: 1fr;
                padding: 22px 20px;
            }

            .bp-hero-main {
                flex-direction: row;
                align-items: center;
                gap: 16px;
            }

            .bp-hero-insights {
                max-width: none;
            }

            .bp-hero-stats {
                width: 100%;
                flex-direction: row;
                flex-wrap: wrap;
                gap: 20px;
            }

            .bp-hero-stats li {
                min-width: 120px;
            }

            .bp-hero-name {
                font-size: 22px;
            }

            .beautician-profile-page .bp-basic-layout {
                grid-template-columns: 1fr;
            }

            .beautician-profile-page .bp-appearance-panel {
                min-height: 146px;
            }

            .bp-form-split {
                grid-template-columns: 1fr;
                gap: 0;
                padding: 0;
            }

            .bp-form-split__col {
                padding: 0;
            }

            .bp-form-split__col + .bp-form-split__col {
                margin-top: 24px;
                padding-top: 24px;
                padding-left: 0;
                border-left: none;
                border-top: 1px solid var(--bp-border);
            }
        }

        @@media (max-width: 767px) {
            .beautician-profile-page .bp-basic-fields {
                grid-template-columns: 1fr;
            }

            .beautician-profile-page .bp-content-sidebar {
                grid-template-columns: 1fr;
            }

            .beautician-profile-page .bp-form-actions--sidebar {
                grid-column: auto;
            }

            .beautician-profile-page .bp-card--booking,
            .beautician-profile-page .bp-card--portal {
                height: auto;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const firstNameInput = document.querySelector('[name="first_name"]');
            const lastNameInput = document.querySelector('[name="last_name"]');
            const titleSelect = document.querySelector('[name="job_title"]');
            const colorInput = document.getElementById('profile-color') || document.querySelector('[name="profile_color"]');
            const phoneInput = document.querySelector('[name="phone"]');
            const positionInput = document.querySelector('[name="position"]');
            const activeInput = document.getElementById('beautician-is-active');
            const userSelect = document.getElementById('beautician-user-id');
            const heroName = document.getElementById('bp-hero-name');
            const heroTitle = document.getElementById('bp-hero-title');
            const heroPhone = document.getElementById('bp-hero-phone');
            const heroStatusBadge = document.getElementById('bp-hero-status-badge');
            const heroStatStatus = document.getElementById('bp-hero-stat-status');
            const heroStatPosition = document.getElementById('bp-hero-stat-position');
            const heroInsightColor = document.getElementById('bp-hero-insight-color');
            const heroInsightSwatch = document.getElementById('bp-hero-insight-swatch');
            const heroInsightPortal = document.getElementById('bp-hero-insight-portal');
            const heroInsightCheckout = document.getElementById('bp-hero-insight-checkout');
            const heroAvatar = document.getElementById('bp-hero-avatar');
            const hero = document.getElementById('bp-hero');
            const pickerWrapper = document.querySelector('#bp-avatar-media .single-image');
            const avatarEditBtn = document.getElementById('bp-avatar-edit-btn');
            const avatarRemoveBtn = document.getElementById('bp-avatar-remove-btn');
            const avatarPickerTrigger = document.getElementById('bp-avatar-picker-trigger');

            const defaultName = @json(trans('beautician::beauticians.form.new_profile'));
            const defaultTitle = @json(trans('beautician::beauticians.form.no_job_title'));
            const activeLabel = @json(trans('beautician::beauticians.active'));
            const inactiveLabel = @json(trans('beautician::beauticians.inactive'));
            const noPortalLabel = @json(trans('beautician::beauticians.form.no_portal_user'));
            const visibleCheckoutLabel = @json(trans('beautician::beauticians.form.hero_visible_at_checkout'));
            const hiddenCheckoutLabel = @json(trans('beautician::beauticians.form.hero_hidden_at_checkout'));

            const syncHeroText = () => {
                if (heroName) {
                    const fullName = [firstNameInput?.value.trim(), lastNameInput?.value.trim()]
                        .filter(Boolean)
                        .join(' ');
                    heroName.textContent = fullName || defaultName;
                }

                if (heroTitle) {
                    const titleValue = (titleSelect?.value || '').trim();

                    heroTitle.textContent = titleValue || defaultTitle;
                }

                if (heroStatStatus && activeInput) {
                    heroStatStatus.textContent = activeInput.checked ? activeLabel : inactiveLabel;
                    heroStatStatus.classList.toggle('is-active', activeInput.checked);
                    heroStatStatus.classList.toggle('is-inactive', ! activeInput.checked);
                }

                if (heroStatusBadge && activeInput) {
                    heroStatusBadge.textContent = activeInput.checked ? activeLabel : inactiveLabel;
                    heroStatusBadge.classList.toggle('is-active', activeInput.checked);
                    heroStatusBadge.classList.toggle('is-inactive', ! activeInput.checked);
                }

                if (heroInsightCheckout && activeInput) {
                    heroInsightCheckout.textContent = activeInput.checked ? visibleCheckoutLabel : hiddenCheckoutLabel;
                    heroInsightCheckout.classList.toggle('is-active', activeInput.checked);
                    heroInsightCheckout.classList.toggle('is-inactive', ! activeInput.checked);
                }

                if (heroPhone) {
                    heroPhone.textContent = phoneInput?.value.trim() || '—';
                }

                if (heroStatPosition) {
                    heroStatPosition.textContent = positionInput?.value !== '' ? positionInput.value : '0';
                }
            };

            const syncHeroColor = () => {
                const color = colorInput?.value || '#6366f1';

                if (hero) {
                    hero.style.setProperty('--bp-profile-color', color);
                }

                if (heroInsightSwatch) {
                    heroInsightSwatch.style.backgroundColor = color;
                }

                if (heroInsightColor) {
                    heroInsightColor.textContent = color.toUpperCase();
                }
            };

            const syncHeroPortal = () => {
                if (!heroInsightPortal || !userSelect) {
                    return;
                }

                if (!userSelect.value) {
                    heroInsightPortal.textContent = noPortalLabel;

                    return;
                }

                const selectedOption = userSelect.options[userSelect.selectedIndex];
                heroInsightPortal.textContent = selectedOption.text.replace(/\s*\([^)]+\)$/, '').trim();
            };

            const syncHeroAvatar = () => {
                if (!heroAvatar) {
                    return;
                }

                const pickerImg = pickerWrapper?.querySelector('.image-holder img');

                if (pickerImg?.src) {
                    heroAvatar.innerHTML = `<img src="${pickerImg.src}" alt="" id="bp-hero-avatar-img">`;

                    if (avatarRemoveBtn) {
                        avatarRemoveBtn.hidden = false;
                    }

                    return;
                }

                const color = colorInput?.value || '#6366f1';
                const initial = (firstNameInput?.value.trim() || 'B').charAt(0).toUpperCase();

                heroAvatar.innerHTML = `<span class="bp-hero-initial" id="bp-hero-avatar-initial" style="background-color:${color};">${initial}</span>`;

                if (avatarRemoveBtn) {
                    avatarRemoveBtn.hidden = true;
                }
            };

            avatarEditBtn?.addEventListener('click', () => {
                avatarPickerTrigger?.click();
            });

            avatarRemoveBtn?.addEventListener('click', () => {
                pickerWrapper?.querySelector('.remove-image')?.click();
            });

            firstNameInput?.addEventListener('input', () => {
                syncHeroText();
                syncHeroAvatar();
            });

            lastNameInput?.addEventListener('input', () => {
                syncHeroText();
            });

            phoneInput?.addEventListener('input', syncHeroText);
            positionInput?.addEventListener('input', syncHeroText);
            activeInput?.addEventListener('change', syncHeroText);
            userSelect?.addEventListener('change', syncHeroPortal);
            titleSelect?.addEventListener('change', syncHeroText);
            colorInput?.addEventListener('input', () => {
                syncHeroColor();
                syncHeroAvatar();
            });

            if (pickerWrapper) {
                const observer = new MutationObserver(syncHeroAvatar);
                observer.observe(pickerWrapper, { childList: true, subtree: true, attributes: true, attributeFilter: ['src'] });
            }

            syncHeroText();
            syncHeroColor();
            syncHeroAvatar();

            const resetPortalBtn = document.getElementById('beautician-reset-portal-btn');
            const resetPortalForm = document.getElementById('beautician-reset-portal-form');
            const portalPasswordInput = document.getElementById('portal_password');
            const portalPasswordConfirmInput = document.getElementById('portal_password_confirmation');
            const passwordMismatchMessage = @json(trans('validation.confirmed', ['attribute' => trans('beautician::attributes.portal_password')]));
            const resetApplyMessage = @json(trans('beautician::beauticians.form.portal_reset_password_apply_help'));
            const resetGenerateMessage = @json(trans('beautician::beauticians.form.portal_reset_password_help'));

            resetPortalBtn?.addEventListener('click', function (event) {
                if (!resetPortalForm) {
                    return;
                }

                const password = portalPasswordInput?.value || '';
                const confirmation = portalPasswordConfirmInput?.value || '';

                resetPortalForm.querySelector('[name="portal_password"]').value = password;
                resetPortalForm.querySelector('[name="portal_password_confirmation"]').value = confirmation;

                if (password && password !== confirmation) {
                    event.preventDefault();
                    window.alert(passwordMismatchMessage);

                    return;
                }

                if (!window.confirm(password ? resetApplyMessage : resetGenerateMessage)) {
                    event.preventDefault();
                }
            });

            initPasswordPanel(document.getElementById('bp-password-panel'));
        });

        function randomIndex(max) {
            const array = new Uint32Array(1);
            crypto.getRandomValues(array);

            return array[0] % max;
        }

        function shuffleString(value) {
            const chars = value.split('');

            for (let i = chars.length - 1; i > 0; i -= 1) {
                const j = randomIndex(i + 1);
                [chars[i], chars[j]] = [chars[j], chars[i]];
            }

            return chars.join('');
        }

        function generateSecurePassword(length = 14) {
            const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
            const lower = 'abcdefghijkmnopqrstuvwxyz';
            const numbers = '23456789';
            const special = '!@#$%&*';
            const all = upper + lower + numbers + special;

            let password = '';
            password += upper[randomIndex(upper.length)];
            password += lower[randomIndex(lower.length)];
            password += numbers[randomIndex(numbers.length)];
            password += special[randomIndex(special.length)];

            while (password.length < length) {
                password += all[randomIndex(all.length)];
            }

            return shuffleString(password);
        }

        function analyzePasswordStrength(password) {
            const checks = {
                length: password.length >= 6,
                length8: password.length >= 8,
                letter: /[a-zA-Z]/.test(password),
                number: /\d/.test(password),
                mixed: /[a-z]/.test(password) && /[A-Z]/.test(password),
            };

            let score = 0;

            if (checks.length) score += 20;
            if (checks.length8) score += 15;
            if (password.length >= 12) score += 10;
            if (checks.letter) score += 15;
            if (checks.number) score += 15;
            if (checks.mixed) score += 15;
            if (/[^a-zA-Z0-9]/.test(password)) score += 10;

            let level = 'empty';

            if (password.length > 0) {
                if (score < 35) {
                    level = 'weak';
                } else if (score < 55) {
                    level = 'fair';
                } else if (score < 80) {
                    level = 'good';
                } else {
                    level = 'strong';
                }
            }

            return { checks, score: Math.min(score, 100), level };
        }

        function initPasswordPanel(root) {
            if (!root) {
                return;
            }

            const passwordInput = root.querySelector('[data-bp-password-input]');
            const confirmInput = root.querySelector('[data-bp-password-confirm]');
            const generateBtn = root.querySelector('[data-bp-password-generate]');
            const strengthRoot = root.querySelector('[data-bp-password-strength]');
            const strengthFill = root.querySelector('[data-bp-password-strength-fill]');
            const strengthLabel = root.querySelector('[data-bp-password-strength-label]');
            const matchEl = root.querySelector('[data-bp-password-match]');
            const generatedHint = root.querySelector('[data-bp-password-generated-hint]');
            const checkItems = root.querySelectorAll('[data-bp-password-check]');
            const strengthLabels = {
                empty: root.dataset.strengthEmpty || '',
                weak: root.dataset.strengthWeak || '',
                fair: root.dataset.strengthFair || '',
                good: root.dataset.strengthGood || '',
                strong: root.dataset.strengthStrong || '',
            };

            const setCheckState = (key, passed) => {
                const item = root.querySelector(`[data-bp-password-check="${key}"]`);

                if (!item) {
                    return;
                }

                item.classList.toggle('is-met', passed);
            };

            const updateStrength = () => {
                const password = passwordInput?.value || '';
                const { checks, score, level } = analyzePasswordStrength(password);

                checkItems.forEach((item) => {
                    const key = item.dataset.bpPasswordCheck;

                    if (key && Object.prototype.hasOwnProperty.call(checks, key)) {
                        setCheckState(key, checks[key]);
                    }
                });

                if (!strengthRoot || !strengthFill || !strengthLabel) {
                    return;
                }

                if (!password) {
                    strengthRoot.hidden = true;
                    strengthFill.style.width = '0%';
                    strengthLabel.textContent = strengthLabels.empty;

                    return;
                }

                strengthRoot.hidden = false;
                strengthFill.style.width = `${score}%`;
                strengthLabel.textContent = strengthLabels[level] || strengthLabels.empty;
                strengthRoot.dataset.level = level;
            };

            const updateMatch = () => {
                if (!matchEl || !passwordInput || !confirmInput) {
                    return;
                }

                const password = passwordInput.value;
                const confirmation = confirmInput.value;

                matchEl.classList.remove('is-match', 'is-mismatch', 'is-empty');

                if (!confirmation) {
                    matchEl.textContent = root.dataset.matchEmpty || '';
                    matchEl.classList.add('is-empty');

                    return;
                }

                if (password === confirmation) {
                    matchEl.textContent = root.dataset.matchMatch || '';
                    matchEl.classList.add('is-match');

                    return;
                }

                matchEl.textContent = root.dataset.matchMismatch || '';
                matchEl.classList.add('is-mismatch');
            };

            const bindToggle = (button) => {
                const targetId = button.dataset.target;
                const input = root.querySelector(`#${CSS.escape(targetId)}`);

                if (!input) {
                    return;
                }

                button.addEventListener('click', () => {
                    const isVisible = input.type === 'text';

                    input.type = isVisible ? 'password' : 'text';
                    button.setAttribute('aria-pressed', String(!isVisible));
                    button.setAttribute(
                        'aria-label',
                        isVisible ? (root.dataset.showPassword || 'Show password') : (root.dataset.hidePassword || 'Hide password')
                    );

                    button.querySelector('[data-icon-show]')?.classList.toggle('hide', !isVisible);
                    button.querySelector('[data-icon-hide]')?.classList.toggle('hide', isVisible);
                });
            };

            root.querySelectorAll('[data-bp-password-toggle]').forEach(bindToggle);

            const refresh = () => {
                updateStrength();
                updateMatch();
            };

            passwordInput?.addEventListener('input', () => {
                generatedHint?.classList.add('hide');
                refresh();
            });

            confirmInput?.addEventListener('input', updateMatch);

            generateBtn?.addEventListener('click', () => {
                const password = generateSecurePassword();

                if (!passwordInput || !confirmInput) {
                    return;
                }

                passwordInput.value = password;
                confirmInput.value = password;
                passwordInput.type = 'text';
                confirmInput.type = 'text';

                root.querySelectorAll('[data-bp-password-toggle]').forEach((button) => {
                    button.setAttribute('aria-pressed', 'true');
                    button.setAttribute('aria-label', root.dataset.hidePassword || 'Hide password');
                    button.querySelector('[data-icon-show]')?.classList.add('hide');
                    button.querySelector('[data-icon-hide]')?.classList.remove('hide');
                });

                generatedHint?.classList.remove('hide');
                refresh();
                passwordInput.focus();
            });

            refresh();
        }
    </script>
@endpush
