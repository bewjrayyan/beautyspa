@php
    use Modules\Beautician\Support\JobTitleOptions;

    $firstName = old('first_name', $beautician->first_name ?? '');
    $lastName = old('last_name', $beautician->last_name ?? '');
    $phone = old('phone', $beautician->phone ?? '');
    $jobTitle = old('job_title', $beautician->job_title ?? '');
    $profileColor = old('profile_color', $beautician->profile_color ?? '#6366f1');
    $isActive = (bool) old('is_active', $beautician->is_active ?? true);
    $jobTitleOptions = JobTitleOptions::forSelect($jobTitle ?: null);
    $selectedSpaBranchIds = array_map('intval', (array) ($selectedSpaBranchIds ?? []));
    $branchScope = old('branch_scope', empty($selectedSpaBranchIds) ? 'all' : 'specific');

    $hasAnyError = $errors->any();
@endphp

<div class="bc-create-page">
    <div class="bc-create-intro">
        <div>
            <span class="bc-eyebrow">{{ trans('beautician::beauticians.form.create_flow.eyebrow') }}</span>
            <h2>{{ trans('beautician::beauticians.form.create_flow.title') }}</h2>
            <p>{{ trans('beautician::beauticians.form.create_flow.intro') }}</p>
        </div>
        <span class="bc-required-note">{{ trans('beautician::beauticians.form.create_flow.required_note') }}</span>
    </div>

    @if ($hasAnyError)
        <div class="bc-error-summary" role="alert" aria-labelledby="bc-error-summary-title">
            <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
            <div>
                <strong id="bc-error-summary-title">{{ trans('beautician::beauticians.form.create_flow.error_title') }}</strong>
                <p>{{ trans('beautician::beauticians.form.create_flow.error_help') }}</p>
            </div>
        </div>
    @endif

    <div class="bc-create-layout">
        <main class="bc-create-main">
            <section
                class="bc-card"
                id="bc-section-profile"
            >
                <div class="bc-card__header">
                    <h3>{{ trans('beautician::beauticians.form.create_flow.profile_title') }}</h3>
                    <p>{{ trans('beautician::beauticians.form.create_flow.profile_help') }}</p>
                </div>

                <div class="bc-card__body">
                    @hasAccess('admin.media.index')
                        <div class="bc-photo-section">
                            <div class="bc-photo-copy">
                                <strong>{{ trans('beautician::beauticians.form.create_flow.photo_label') }}</strong>
                                <span>{{ trans('beautician::beauticians.form.create_flow.photo_help') }}</span>
                            </div>
                            <div class="bc-photo-picker">
                                @include('media::admin.image_picker.single', [
                                    'file' => $beautician->profile_image,
                                    'inputName' => 'files[profile]',
                                    'title' => '',
                                    'aspect' => 'square',
                                ])
                            </div>
                        </div>
                    @endHasAccess

                    <div class="bc-form-grid">
                        <div class="bc-field{{ $errors->has('first_name') ? ' has-error' : '' }}">
                            <label for="first_name">
                                {{ trans('beautician::beauticians.form.create_flow.first_name') }}
                                <span class="bc-required" aria-hidden="true">*</span>
                            </label>
                            <input
                                type="text"
                                name="first_name"
                                id="first_name"
                                value="{{ $firstName }}"
                                class="form-control"
                                placeholder="{{ trans('beautician::beauticians.form.create_flow.first_name_placeholder') }}"
                                autocomplete="given-name"
                                aria-describedby="first_name_help{{ $errors->has('first_name') ? ' first_name_error' : '' }}"
                                required
                            >
                            <span class="bc-field-help" id="first_name_help">{{ trans('beautician::beauticians.form.create_flow.first_name_help') }}</span>
                            @error('first_name')
                                <span class="bc-field-error" id="first_name_error"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> {{ $message }}</span>
                            @enderror
                        </div>

                        <div class="bc-field{{ $errors->has('last_name') ? ' has-error' : '' }}">
                            <label for="last_name">
                                {{ trans('beautician::beauticians.form.create_flow.last_name') }}
                                <span class="bc-required" aria-hidden="true">*</span>
                            </label>
                            <input
                                type="text"
                                name="last_name"
                                id="last_name"
                                value="{{ $lastName }}"
                                class="form-control"
                                placeholder="{{ trans('beautician::beauticians.form.create_flow.last_name_placeholder') }}"
                                autocomplete="family-name"
                                aria-describedby="last_name_help{{ $errors->has('last_name') ? ' last_name_error' : '' }}"
                                required
                            >
                            <span class="bc-field-help" id="last_name_help">{{ trans('beautician::beauticians.form.create_flow.last_name_help') }}</span>
                            @error('last_name')
                                <span class="bc-field-error" id="last_name_error"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="bc-form-grid">
                        <div class="bc-field{{ $errors->has('phone') ? ' has-error' : '' }}">
                            <label for="phone">
                                {{ trans('beautician::beauticians.form.create_flow.phone') }}
                                <span class="bc-required" aria-hidden="true">*</span>
                            </label>
                            <input
                                type="tel"
                                name="phone"
                                id="phone"
                                value="{{ $phone }}"
                                class="form-control modern-phone-input"
                                placeholder="12 345 6789"
                                autocomplete="tel"
                                inputmode="tel"
                                aria-describedby="phone_help{{ $errors->has('phone') ? ' phone_error' : '' }}"
                                required
                            >
                            <span class="bc-field-help" id="phone_help">{{ trans('beautician::beauticians.form.create_flow.phone_help') }}</span>
                            @error('phone')
                                <span class="bc-field-error" id="phone_error"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> {{ $message }}</span>
                            @enderror
                        </div>

                        <div class="bc-field{{ $errors->has('job_title') ? ' has-error' : '' }}">
                            <label for="job_title">
                                {{ trans('beautician::beauticians.form.create_flow.job_title') }}
                                <span class="bc-optional">{{ trans('beautician::beauticians.form.create_flow.optional') }}</span>
                            </label>
                            <select
                                name="job_title"
                                id="job_title"
                                class="form-control custom-select-black"
                                aria-describedby="job_title_help{{ $errors->has('job_title') ? ' job_title_error' : '' }}"
                            >
                                @foreach ($jobTitleOptions as $optionValue => $optionLabel)
                                    <option value="{{ $optionValue }}" {{ $jobTitle === $optionValue ? 'selected' : '' }}>{{ $optionLabel }}</option>
                                @endforeach
                            </select>
                            <span class="bc-field-help" id="job_title_help">{{ trans('beautician::beauticians.form.create_flow.job_title_help') }}</span>
                            @error('job_title')
                                <span class="bc-field-error" id="job_title_error"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="bc-disclosure">
                        <div class="bc-disclosure__heading">
                            <span><i class="fa fa-paint-brush" aria-hidden="true"></i> {{ trans('beautician::beauticians.form.create_flow.appearance') }}</span>
                            <small>{{ trans('beautician::beauticians.form.create_flow.appearance_hint') }}</small>
                        </div>
                        <div class="bc-disclosure__body">
                            <div class="bc-color-field{{ $errors->has('profile_color') ? ' has-error' : '' }}">
                                <input type="color" name="profile_color" id="profile_color" value="{{ $profileColor }}" aria-describedby="profile_color_help">
                                <div>
                                    <label for="profile_color">{{ trans('beautician::beauticians.form.create_flow.avatar_color') }}</label>
                                    <span id="profile_color_help">{{ trans('beautician::beauticians.form.create_flow.avatar_color_help') }}</span>
                                </div>
                            </div>
                            @error('profile_color')
                                <span class="bc-field-error"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> {{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

            </section>

            <section
                class="bc-card"
                id="bc-section-booking"
            >
                <div class="bc-card__header">
                    <h3>{{ trans('beautician::beauticians.form.create_flow.booking_title') }}</h3>
                    <p>{{ trans('beautician::beauticians.form.create_flow.booking_help') }}</p>
                </div>

                <div class="bc-card__body">
                    @if (is_module_enabled('SpaBranch'))
                        @if (($spaBranches ?? collect())->isNotEmpty())
                            <fieldset class="bc-fieldset{{ $errors->has('spa_branches') || $errors->has('branch_scope') ? ' has-error' : '' }}">
                                <legend>{{ trans('beautician::beauticians.form.create_flow.branch_legend') }}</legend>

                                <label class="bc-choice{{ $branchScope === 'all' ? ' is-selected' : '' }}" data-bc-choice>
                                    <input type="radio" name="branch_scope" value="all" {{ $branchScope === 'all' ? 'checked' : '' }}>
                                    <span class="bc-choice__icon"><i class="fa fa-globe" aria-hidden="true"></i></span>
                                    <span class="bc-choice__copy">
                                        <strong>{{ trans('beautician::beauticians.form.create_flow.all_branches') }}</strong>
                                        <small>{{ trans('beautician::beauticians.form.create_flow.all_branches_help') }}</small>
                                    </span>
                                    <span class="bc-choice__check"><i class="fa fa-check" aria-hidden="true"></i></span>
                                </label>

                                <label class="bc-choice{{ $branchScope === 'specific' ? ' is-selected' : '' }}" data-bc-choice>
                                    <input type="radio" name="branch_scope" value="specific" {{ $branchScope === 'specific' ? 'checked' : '' }}>
                                    <span class="bc-choice__icon"><i class="fa fa-map-marker" aria-hidden="true"></i></span>
                                    <span class="bc-choice__copy">
                                        <strong>{{ trans('beautician::beauticians.form.create_flow.selected_branches') }}</strong>
                                        <small>{{ trans('beautician::beauticians.form.create_flow.selected_branches_help') }}</small>
                                    </span>
                                    <span class="bc-choice__check"><i class="fa fa-check" aria-hidden="true"></i></span>
                                </label>

                                <div class="bc-branch-list" data-bc-branch-list @if ($branchScope !== 'specific') hidden @endif>
                                    <span class="bc-branch-list__label">{{ trans('beautician::beauticians.form.create_flow.choose_branches') }}</span>
                                    @foreach ($spaBranches as $branchId => $branchName)
                                        <label class="bc-branch-option">
                                            <input
                                                type="checkbox"
                                                name="spa_branches[]"
                                                value="{{ $branchId }}"
                                                {{ in_array((int) $branchId, $selectedSpaBranchIds, true) ? 'checked' : '' }}
                                                {{ $branchScope !== 'specific' ? 'disabled' : '' }}
                                            >
                                            <span>{{ $branchName }}</span>
                                        </label>
                                    @endforeach
                                </div>

                                @error('branch_scope')
                                    <span class="bc-field-error"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> {{ $message }}</span>
                                @enderror
                                @error('spa_branches')
                                    <span class="bc-field-error"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> {{ $message }}</span>
                                @enderror
                            </fieldset>
                        @else
                            <div class="bc-empty-state">
                                <i class="fa fa-map-marker" aria-hidden="true"></i>
                                <div>
                                    <strong>{{ trans('beautician::beauticians.form.create_flow.no_branches_title') }}</strong>
                                    <p>{{ trans('beautician::beauticians.form.create_flow.no_branches_help') }}</p>
                                    @hasAccess('admin.spa_branches.create')
                                        <a href="{{ route('admin.spa_branches.create') }}" class="btn btn-default btn-sm" target="_blank" rel="noopener noreferrer">
                                            {{ trans('beautician::beauticians.form.create_spa_branch') }}
                                        </a>
                                    @endHasAccess
                                </div>
                            </div>
                        @endif
                    @endif

                    <div class="bc-booking-toggle{{ $isActive ? '' : ' is-off' }}" data-bc-booking-toggle>
                        <div class="bc-booking-toggle__icon"><i class="fa fa-calendar-check-o" aria-hidden="true"></i></div>
                        <div class="bc-booking-toggle__copy">
                            <strong>{{ trans('beautician::beauticians.form.create_flow.allow_bookings') }}</strong>
                            <span>{{ trans('beautician::beauticians.form.create_flow.allow_bookings_help') }}</span>
                        </div>
                        <label class="bc-switch" for="beautician-is-active">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" id="beautician-is-active" {{ $isActive ? 'checked' : '' }}>
                            <span class="bc-switch__track" aria-hidden="true"></span>
                            <span class="sr-only">{{ trans('beautician::beauticians.form.create_flow.allow_bookings') }}</span>
                        </label>
                    </div>
                </div>

            </section>

            <section
                class="bc-card"
                id="bc-section-access"
            >
                <div class="bc-card__header">
                    <h3>{{ trans('beautician::beauticians.form.create_flow.access_title') }}</h3>
                    <p>{{ trans('beautician::beauticians.form.create_flow.access_help') }}</p>
                </div>

                <div class="bc-card__body">
                    <div class="bc-auto-account">
                        <span class="bc-auto-account__icon"><i class="fa fa-shield" aria-hidden="true"></i></span>
                        <div>
                            <strong>{{ trans('beautician::beauticians.form.create_flow.auto_account_title') }}</strong>
                            <p>{{ trans('beautician::beauticians.form.create_flow.auto_account_help') }}</p>
                            <span class="bc-status-pill"><i class="fa fa-check" aria-hidden="true"></i> {{ trans('beautician::beauticians.form.create_flow.recommended') }}</span>
                        </div>
                    </div>

                </div>

                <div class="bc-card__footer bc-card__footer--final">
                    <a href="{{ route('admin.beauticians.index') }}" class="btn btn-default">{{ trans('admin::admin.buttons.cancel') }}</a>
                    <div class="bc-final-action">
                        <span>{{ trans('beautician::beauticians.form.create_flow.create_hint') }}</span>
                        <button type="submit" class="btn btn-primary" data-loading>
                            <i class="fa fa-user-plus" aria-hidden="true"></i>
                            {{ trans('beautician::beauticians.form.create_beautician') }}
                        </button>
                    </div>
                </div>
            </section>
        </main>

        <aside class="bc-summary" aria-labelledby="bc-summary-title">
            <div class="bc-summary__avatar" data-bc-summary-avatar style="--bc-avatar-color: {{ $profileColor }};">
                <span data-bc-summary-initial>{{ strtoupper(mb_substr($firstName ?: 'B', 0, 1)) }}</span>
            </div>
            <div class="bc-summary__heading">
                <span class="bc-status-dot"></span>
                <span>{{ trans('beautician::beauticians.form.create_flow.live_summary') }}</span>
            </div>
            <h3 id="bc-summary-title" data-bc-summary-name>{{ trim("{$firstName} {$lastName}") ?: trans('beautician::beauticians.form.new_profile') }}</h3>
            <p data-bc-summary-title>{{ $jobTitle ?: trans('beautician::beauticians.form.no_job_title') }}</p>

            <dl class="bc-summary__list">
                <div>
                    <dt><i class="fa fa-whatsapp" aria-hidden="true"></i> {{ trans('beautician::beauticians.form.create_flow.contact') }}</dt>
                    <dd data-bc-summary-phone>{{ $phone ?: '—' }}</dd>
                </div>
                <div>
                    <dt><i class="fa fa-map-marker" aria-hidden="true"></i> {{ trans('beautician::beauticians.form.create_flow.branches') }}</dt>
                    <dd data-bc-summary-branches>{{ $branchScope === 'all' ? trans('beautician::beauticians.form.create_flow.all_branches_short') : trans('beautician::beauticians.form.create_flow.selected_branches_short') }}</dd>
                </div>
                <div>
                    <dt><i class="fa fa-calendar-check-o" aria-hidden="true"></i> {{ trans('beautician::beauticians.form.create_flow.customer_booking') }}</dt>
                    <dd data-bc-summary-status class="{{ $isActive ? 'is-positive' : 'is-muted' }}">{{ $isActive ? trans('beautician::beauticians.form.create_flow.bookable') : trans('beautician::beauticians.form.create_flow.hidden') }}</dd>
                </div>
                <div>
                    <dt><i class="fa fa-lock" aria-hidden="true"></i> {{ trans('beautician::beauticians.form.create_flow.portal_login') }}</dt>
                    <dd>{{ trans('beautician::beauticians.form.create_flow.created_automatically') }}</dd>
                </div>
            </dl>

            <div class="bc-summary__tip">
                <i class="fa fa-info-circle" aria-hidden="true"></i>
                <span>{{ trans('beautician::beauticians.form.create_flow.summary_tip') }}</span>
            </div>
        </aside>
    </div>
</div>

@push('styles')
    <style>
        .bc-create-page {
            --bc-primary: #4f46e5;
            --bc-primary-dark: #4338ca;
            --bc-primary-soft: #eef2ff;
            --bc-border: #e5e7eb;
            --bc-text: #111827;
            --bc-muted: #6b7280;
            --bc-bg: #f5f6f8;
            max-width: 1080px;
            margin: 0 auto 28px;
            color: var(--bc-text);
        }

        .bc-create-intro {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 22px;
        }

        .bc-eyebrow,
        .bc-card__kicker {
            display: block;
            margin-bottom: 5px;
            color: var(--bc-primary);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .bc-create-intro h2 {
            margin: 0 0 6px;
            font-size: 22px;
            font-weight: 700;
        }

        .bc-create-intro p {
            max-width: 660px;
            margin: 0;
            color: var(--bc-muted);
            font-size: 13.5px;
            line-height: 1.55;
        }

        .bc-required-note {
            flex-shrink: 0;
            padding: 6px 10px;
            border-radius: 999px;
            color: #475569;
            background: #f1f5f9;
            font-size: 11.5px;
            font-weight: 600;
        }

        .bc-error-summary {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 18px;
            padding: 14px 16px;
            border: 1px solid #fecaca;
            border-radius: 12px;
            color: #991b1b;
            background: #fef2f2;
        }

        .bc-error-summary > i { margin-top: 2px; font-size: 17px; }
        .bc-error-summary strong { display: block; font-size: 13.5px; }
        .bc-error-summary p { margin: 3px 0 7px; font-size: 12.5px; }

        .bc-create-layout { display: grid; grid-template-columns: minmax(0, 1fr) 250px; gap: 20px; align-items: start; }
        .bc-create-main { display: flex; flex-direction: column; gap: 18px; min-width: 0; }
        .bc-card { overflow: visible; border: 1px solid var(--bc-border); border-radius: 16px; background: #fff; box-shadow: 0 4px 18px rgba(15, 23, 42, .06); }
        .bc-card__header { padding: 22px 24px 18px; border-bottom: 1px solid var(--bc-border); }
        .bc-card__header h3 { margin: 0 0 6px; font-size: 18px; font-weight: 700; color: var(--bc-text); }
        .bc-card__header p { margin: 0; color: var(--bc-muted); font-size: 13px; line-height: 1.5; }
        .bc-card__body { padding: 24px; }
        .bc-card__footer { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 16px 24px; border-top: 1px solid var(--bc-border); background: #fafafa; border-radius: 0 0 16px 16px; }
        .bc-card__footer .btn { display: inline-flex; align-items: center; gap: 8px; min-height: 42px; padding: 9px 16px; border-radius: 9px; font-weight: 600; }
        .bc-card__footer .btn-primary { background: #0068e1; border-color: #0068e1; }

        .bc-photo-section { display: flex; align-items: center; justify-content: space-between; gap: 18px; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid var(--bc-border); }
        .bc-photo-copy { display: flex; flex-direction: column; max-width: 430px; }
        .bc-photo-copy strong { margin-bottom: 5px; font-size: 13.5px; }
        .bc-photo-copy span { color: var(--bc-muted); font-size: 12.5px; line-height: 1.5; }
        .bc-photo-picker { width: 168px; flex: 0 0 168px; }
        .bc-photo-picker .ac-media-field__label { display: none; }
        .bc-photo-picker .ac-media-field__canvas { min-height: 124px; border-radius: 14px; }
        .bc-photo-picker .ac-media-dropzone { min-height: 124px; padding: 16px; border-radius: 14px; }
        .bc-photo-picker .ac-media-dropzone__icon { margin-bottom: 4px; }
        .bc-photo-picker .ac-media-dropzone__title { font-size: 11px; }
        .bc-photo-picker .ac-media-dropzone__hint,
        .bc-photo-picker .ac-media-dropzone__actions { display: none; }
        .bc-photo-picker .ac-media-preview,
        .bc-photo-picker .ac-media-preview__inner { width: 168px; height: 124px; min-height: 124px; max-height: 124px; padding: 0; border-radius: 14px; }
        .bc-photo-picker .ac-media-preview img { width: 100%; height: 100%; max-width: none; max-height: none; border-radius: 13px; object-fit: cover; }

        .bc-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
        .bc-field { display: flex; flex-direction: column; min-width: 0; margin-bottom: 20px; }
        .bc-field > label { display: flex; align-items: baseline; gap: 6px; margin-bottom: 7px; color: var(--bc-text); font-size: 13px; font-weight: 600; }
        .bc-required { color: #dc2626; }
        .bc-optional { color: var(--bc-muted); font-size: 11px; font-weight: 400; }
        .bc-field .form-control { height: 44px; border-color: #d1d5db; border-radius: 9px; box-shadow: none; font-size: 13px; }
        .bc-field .form-control:focus { border-color: var(--bc-primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, .12); }
        .bc-field.has-error .form-control { border-color: #dc2626; }
        .bc-field-help { display: block; margin-top: 6px; color: var(--bc-muted); font-size: 11.5px; line-height: 1.45; }
        .bc-field-error { display: block; margin-top: 6px; color: #b91c1c; font-size: 11.5px; font-weight: 600; }

        .bc-disclosure { margin-top: 2px; border: 1px solid var(--bc-border); border-radius: 10px; background: #fafafa; }
        .bc-disclosure > summary,
        .bc-disclosure__heading { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 12px 14px; list-style: none; }
        .bc-disclosure > summary { cursor: pointer; }
        .bc-disclosure > summary::-webkit-details-marker { display: none; }
        .bc-disclosure > summary > span,
        .bc-disclosure__heading > span { color: var(--bc-text); font-size: 12.5px; font-weight: 600; }
        .bc-disclosure > summary > span i,
        .bc-disclosure__heading > span i { width: 18px; color: var(--bc-primary); }
        .bc-disclosure > summary > small,
        .bc-disclosure__heading > small { color: var(--bc-muted); font-size: 11.5px; font-weight: 400; }
        .bc-disclosure > summary::after { content: "\f107"; font-family: FontAwesome; color: var(--bc-muted); transition: transform .15s ease; }
        .bc-disclosure[open] > summary::after { transform: rotate(180deg); }
        .bc-disclosure__body { padding: 16px; border-top: 1px solid var(--bc-border); background: #fff; border-radius: 0 0 10px 10px; }

        .bc-color-field { display: flex; align-items: center; gap: 12px; }
        .bc-color-field input { width: 52px; height: 42px; padding: 3px; border: 1px solid var(--bc-border); border-radius: 9px; background: #fff; }
        .bc-color-field div { display: flex; flex-direction: column; }
        .bc-color-field label { margin: 0 0 2px; font-size: 12.5px; }
        .bc-color-field span { color: var(--bc-muted); font-size: 11.5px; }

        .bc-fieldset { min-width: 0; margin: 0; padding: 0; border: 0; }
        .bc-fieldset legend { margin-bottom: 10px; border: 0; color: var(--bc-text); font-size: 13px; font-weight: 700; }
        .bc-choice { position: relative; display: flex; align-items: center; gap: 13px; margin-bottom: 10px; padding: 14px 15px; border: 1px solid var(--bc-border); border-radius: 11px; cursor: pointer; background: #fff; transition: border-color .15s ease, background .15s ease; }
        .bc-choice:hover { border-color: #c7d2fe; }
        .bc-choice.is-selected { border-color: #a5b4fc; background: var(--bc-primary-soft); }
        .bc-choice > input { position: absolute; width: 1px; height: 1px; opacity: 0; }
        .bc-choice:focus-within { outline: 2px solid var(--bc-primary); outline-offset: 2px; }
        .bc-choice__icon { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; flex: 0 0 36px; border-radius: 9px; color: var(--bc-primary); background: var(--bc-primary-soft); }
        .bc-choice.is-selected .bc-choice__icon { color: #fff; background: var(--bc-primary); }
        .bc-choice__copy { display: flex; flex-direction: column; flex: 1; min-width: 0; }
        .bc-choice__copy strong { font-size: 13px; }
        .bc-choice__copy small { margin-top: 3px; color: var(--bc-muted); font-size: 11.5px; line-height: 1.4; }
        .bc-choice__check { display: none; align-items: center; justify-content: center; width: 20px; height: 20px; border-radius: 50%; color: #fff; background: var(--bc-primary); font-size: 10px; }
        .bc-choice.is-selected .bc-choice__check { display: inline-flex; }

        .bc-branch-list { margin: 2px 0 16px; padding: 14px 16px; border-left: 3px solid #c7d2fe; background: #f8fafc; }
        .bc-branch-list[hidden] { display: none; }
        .bc-branch-list__label { display: block; margin-bottom: 10px; color: #475569; font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
        .bc-branch-option { display: flex; align-items: center; gap: 9px; min-height: 38px; margin: 0; padding: 7px 9px; border-radius: 8px; color: var(--bc-text); font-size: 12.5px; font-weight: 500; cursor: pointer; }
        .bc-branch-option:hover { background: #fff; }
        .bc-branch-option input { width: 17px; height: 17px; margin: 0; }

        .bc-booking-toggle { display: flex; align-items: center; gap: 13px; margin-top: 22px; padding: 16px; border: 1px solid #bbf7d0; border-radius: 11px; background: #f0fdf4; }
        .bc-booking-toggle__icon { display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 38px; flex: 0 0 38px; border-radius: 10px; color: #15803d; background: #dcfce7; }
        .bc-booking-toggle__copy { display: flex; flex-direction: column; flex: 1; }
        .bc-booking-toggle__copy strong { color: #14532d; font-size: 13px; }
        .bc-booking-toggle__copy span { margin-top: 3px; color: #3f6f4d; font-size: 11.5px; line-height: 1.4; }
        .bc-booking-toggle.is-off { border-color: var(--bc-border); background: #f8fafc; }
        .bc-booking-toggle.is-off .bc-booking-toggle__icon { color: #64748b; background: #e2e8f0; }
        .bc-booking-toggle.is-off .bc-booking-toggle__copy strong { color: var(--bc-text); }
        .bc-booking-toggle.is-off .bc-booking-toggle__copy span { color: var(--bc-muted); }

        .bc-switch { position: relative; width: 48px; height: 28px; flex: 0 0 48px; margin: 0; }
        .bc-switch input[type="checkbox"] { position: absolute; width: 1px; height: 1px; opacity: 0; }
        .bc-switch__track { position: absolute; inset: 0; border-radius: 999px; background: #cbd5e1; cursor: pointer; transition: background .2s ease; }
        .bc-switch__track::before { position: absolute; content: ""; width: 22px; height: 22px; top: 3px; left: 3px; border-radius: 50%; background: #fff; box-shadow: 0 1px 4px rgba(15, 23, 42, .2); transition: transform .2s ease; }
        .bc-switch input:checked + .bc-switch__track { background: #16a34a; }
        .bc-switch input:checked + .bc-switch__track::before { transform: translateX(20px); }
        .bc-switch input:focus-visible + .bc-switch__track { outline: 2px solid var(--bc-primary); outline-offset: 2px; }

        .bc-empty-state { display: flex; gap: 14px; padding: 16px; border: 1px dashed #cbd5e1; border-radius: 11px; background: #f8fafc; }
        .bc-empty-state > i { margin-top: 2px; color: var(--bc-primary); font-size: 18px; }
        .bc-empty-state strong { font-size: 13px; }
        .bc-empty-state p { margin: 4px 0 10px; color: var(--bc-muted); font-size: 12px; }

        .bc-auto-account { display: flex; gap: 14px; padding: 17px; border: 1px solid #bfdbfe; border-radius: 12px; background: #eff6ff; }
        .bc-auto-account__icon { display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; flex: 0 0 42px; border-radius: 11px; color: #1d4ed8; background: #dbeafe; font-size: 17px; }
        .bc-auto-account strong { display: block; color: #1e3a8a; font-size: 13.5px; }
        .bc-auto-account p { margin: 4px 0 9px; color: #475569; font-size: 12px; line-height: 1.5; }
        .bc-status-pill { display: inline-flex; align-items: center; gap: 5px; padding: 3px 8px; border-radius: 999px; color: #166534; background: #dcfce7; font-size: 10.5px; font-weight: 700; }
        .bc-card__footer--final { align-items: center; }
        .bc-final-action { display: flex; align-items: center; justify-content: flex-end; gap: 12px; }
        .bc-final-action > span { max-width: 210px; color: var(--bc-muted); font-size: 11px; line-height: 1.35; text-align: right; }

        .bc-summary { position: sticky; top: 20px; padding: 20px; border: 1px solid var(--bc-border); border-radius: 16px; background: #fff; box-shadow: 0 4px 18px rgba(15, 23, 42, .05); text-align: center; }
        .bc-summary__avatar { display: flex; align-items: center; justify-content: center; width: 76px; height: 76px; margin: 0 auto 12px; border: 4px solid #fff; border-radius: 50%; color: #fff; background: var(--bc-avatar-color, #6366f1); box-shadow: 0 0 0 1px var(--bc-border), 0 5px 14px rgba(15, 23, 42, .14); font-size: 27px; font-weight: 700; }
        .bc-summary__heading { display: flex; align-items: center; justify-content: center; gap: 6px; margin-bottom: 7px; color: var(--bc-muted); font-size: 10.5px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
        .bc-status-dot { width: 7px; height: 7px; border-radius: 50%; background: #22c55e; box-shadow: 0 0 0 3px #dcfce7; }
        .bc-summary h3 { overflow: hidden; margin: 0 0 3px; font-size: 17px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
        .bc-summary > p { overflow: hidden; margin: 0; color: var(--bc-muted); font-size: 12px; text-overflow: ellipsis; white-space: nowrap; }
        .bc-summary__list { margin: 18px 0 0; text-align: left; }
        .bc-summary__list > div { padding: 11px 0; border-top: 1px solid var(--bc-border); }
        .bc-summary__list dt { margin-bottom: 4px; color: var(--bc-muted); font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; }
        .bc-summary__list dt i { width: 16px; color: var(--bc-primary); }
        .bc-summary__list dd { overflow: hidden; margin: 0; color: var(--bc-text); font-size: 12.5px; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; }
        .bc-summary__list dd.is-positive { color: #15803d; }
        .bc-summary__list dd.is-muted { color: var(--bc-muted); }
        .bc-summary__tip { display: flex; align-items: flex-start; gap: 7px; margin-top: 12px; padding: 10px; border-radius: 9px; color: #475569; background: #f8fafc; text-align: left; }
        .bc-summary__tip i { margin-top: 2px; color: var(--bc-primary); }
        .bc-summary__tip span { font-size: 10.5px; line-height: 1.45; }

        @media (max-width: 991px) {
            .bc-create-layout { grid-template-columns: minmax(0, 1fr) 220px; }
        }

        @media (max-width: 767px) {
            .bc-create-page { margin-top: 8px; }
            .bc-create-intro { align-items: flex-start; flex-direction: column; gap: 10px; }
            .bc-create-layout { grid-template-columns: 1fr; }
            .bc-summary { position: static; order: -1; }
            .bc-summary__list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 16px; }
            .bc-card__header,
            .bc-card__body { padding-left: 18px; padding-right: 18px; }
            .bc-card__footer { padding: 14px 18px; }
            .bc-form-grid,
            .bc-photo-section { align-items: flex-start; }
            .bc-card__footer--final,
            .bc-final-action { align-items: stretch; flex-direction: column; }
            .bc-final-action { width: 100%; }
            .bc-final-action > span { max-width: none; text-align: left; }
            .bc-final-action .btn { justify-content: center; }
        }

        @media (max-width: 479px) {
            .bc-summary__list { grid-template-columns: 1fr; }
            .bc-photo-section { flex-direction: column; }
            .bc-photo-picker { width: 100%; }
            .bc-photo-picker .ac-media-field { max-width: 168px; }
            .bc-card__footer { align-items: stretch; flex-direction: column-reverse; }
            .bc-card__footer .btn { justify-content: center; width: 100%; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const root = document.querySelector('.bc-create-page');
            const form = document.getElementById('beautician-form');

            if (!root || !form) {
                return;
            }

            const firstNameInput = root.querySelector('[name="first_name"]');
            const lastNameInput = root.querySelector('[name="last_name"]');
            const phoneInput = root.querySelector('[name="phone"]');
            const jobTitleInput = root.querySelector('[name="job_title"]');
            const colorInput = root.querySelector('[name="profile_color"]');
            const activeInput = root.querySelector('#beautician-is-active');
            const branchList = root.querySelector('[data-bc-branch-list]');
            const branchScopeInputs = Array.from(root.querySelectorAll('[name="branch_scope"]'));
            const branchInputs = Array.from(root.querySelectorAll('[name="spa_branches[]"]'));

            const labels = {
                newProfile: @json(trans('beautician::beauticians.form.new_profile')),
                noTitle: @json(trans('beautician::beauticians.form.no_job_title')),
                allBranches: @json(trans('beautician::beauticians.form.create_flow.all_branches_short')),
                selectedBranches: @json(trans('beautician::beauticians.form.create_flow.selected_branches_short')),
                chooseBranch: @json(trans('beautician::beauticians.form.create_flow.choose_one_branch_error')),
                bookable: @json(trans('beautician::beauticians.form.create_flow.bookable')),
                hidden: @json(trans('beautician::beauticians.form.create_flow.hidden')),
            };

            function validateProfile() {
                const requiredInputs = [firstNameInput, lastNameInput, phoneInput].filter(Boolean);

                for (const input of requiredInputs) {
                    if (!input.checkValidity()) {
                        input.reportValidity();
                        input.focus();
                        return false;
                    }
                }

                return true;
            }

            function validateBooking() {
                const specific = root.querySelector('[name="branch_scope"][value="specific"]');

                if (specific?.checked && !branchInputs.some((input) => input.checked)) {
                    window.alert(labels.chooseBranch);
                    branchInputs[0]?.focus();
                    return false;
                }

                return true;
            }

            function syncBranchScope() {
                const specific = root.querySelector('[name="branch_scope"][value="specific"]')?.checked;

                if (branchList) {
                    branchList.hidden = !specific;
                }

                branchInputs.forEach((input) => {
                    input.disabled = !specific;
                });

                root.querySelectorAll('[data-bc-choice]').forEach((choice) => {
                    choice.classList.toggle('is-selected', Boolean(choice.querySelector('input')?.checked));
                });

                syncSummary();
            }

            function syncSummary() {
                const fullName = [firstNameInput?.value.trim(), lastNameInput?.value.trim()].filter(Boolean).join(' ');
                const jobTitle = jobTitleInput?.value.trim();
                const specific = root.querySelector('[name="branch_scope"][value="specific"]')?.checked;
                const checkedBranches = branchInputs.filter((input) => input.checked);
                const branchText = specific
                    ? (checkedBranches.length ? checkedBranches.map((input) => input.closest('label')?.innerText.trim()).join(', ') : labels.selectedBranches)
                    : labels.allBranches;
                const bookingStatus = activeInput?.checked ? labels.bookable : labels.hidden;

                const summaryName = root.querySelector('[data-bc-summary-name]');
                const summaryInitial = root.querySelector('[data-bc-summary-initial]');
                const summaryAvatar = root.querySelector('[data-bc-summary-avatar]');

                if (summaryName) summaryName.textContent = fullName || labels.newProfile;
                if (summaryInitial) summaryInitial.textContent = (firstNameInput?.value.trim() || 'B').charAt(0).toUpperCase();
                if (summaryAvatar) summaryAvatar.style.setProperty('--bc-avatar-color', colorInput?.value || '#6366f1');

                const summaryTitle = root.querySelector('[data-bc-summary-title]');
                const summaryPhone = root.querySelector('[data-bc-summary-phone]');
                const summaryBranches = root.querySelector('[data-bc-summary-branches]');
                const summaryStatus = root.querySelector('[data-bc-summary-status]');
                const bookingToggle = root.querySelector('[data-bc-booking-toggle]');
                if (summaryTitle) summaryTitle.textContent = jobTitle || labels.noTitle;
                if (summaryPhone) summaryPhone.textContent = phoneInput?.value.trim() || '—';
                if (summaryBranches) summaryBranches.textContent = branchText;
                if (summaryStatus) {
                    summaryStatus.textContent = bookingStatus;
                    summaryStatus.classList.toggle('is-positive', Boolean(activeInput?.checked));
                    summaryStatus.classList.toggle('is-muted', !activeInput?.checked);
                }
                if (bookingToggle) bookingToggle.classList.toggle('is-off', !activeInput?.checked);
            }

            [firstNameInput, lastNameInput, phoneInput, colorInput].forEach((input) => input?.addEventListener('input', syncSummary));
            jobTitleInput?.addEventListener('change', syncSummary);
            activeInput?.addEventListener('change', syncSummary);
            branchScopeInputs.forEach((input) => input.addEventListener('change', syncBranchScope));
            branchInputs.forEach((input) => input.addEventListener('change', syncSummary));

            form.addEventListener('submit', function (event) {
                if (!validateProfile() || !validateBooking()) {
                    event.preventDefault();
                }
            });

            syncBranchScope();
            syncSummary();
        });
    </script>
@endpush
