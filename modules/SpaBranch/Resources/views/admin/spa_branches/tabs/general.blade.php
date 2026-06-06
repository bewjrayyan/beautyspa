@php
    $displayName = old('name', $spaBranch->name ?? '');
    $displayCode = old('code', $spaBranch->code ?? '');
    $displayPhone = old('phone', $spaBranch->phone ?? '');
    $displayEmail = old('email', $spaBranch->email ?? '');
    $displayAddress = old('address', $spaBranch->address ?? '');
    $displayPosition = old('position', $spaBranch->position ?? 0);
    $isActive = (bool) old('is_active', $spaBranch->is_active ?? true);
    $selectedBeauticianIds = array_map('intval', (array) old(
        'beauticians',
        $selectedBeauticianIds ?? (
            $spaBranch->exists && $spaBranch->relationLoaded('beauticians')
                ? $spaBranch->beauticians->pluck('id')->all()
                : ($spaBranch->exists ? $spaBranch->beauticians()->pluck('beauticians.id')->all() : [])
        )
    ));
    $branchBeauticianOptions = $branchBeauticianOptions ?? [];
    $selectedBeauticianCount = count(array_intersect(
        $selectedBeauticianIds,
        array_column($branchBeauticianOptions, 'id')
    ));
    $memberSince = $spaBranch->exists && $spaBranch->created_at
        ? $spaBranch->created_at->timezone(config('app.timezone'))->format('d M Y')
        : null;
@endphp

<div class="spa-branch-page">
    <header class="sb-hero" id="sb-hero">
        <div class="sb-hero-main">
            <div class="sb-hero-icon-wrap">
                <span class="sb-hero-icon" id="sb-hero-icon">
                    <i class="fa fa-building"></i>
                </span>
            </div>

            <div class="sb-hero-identity">
                <div class="sb-hero-name-row">
                    <h2 class="sb-hero-name" id="sb-hero-name">
                        {{ $displayName ?: trans('spabranch::spa_branches.form.new_branch') }}
                    </h2>
                    <span class="sb-hero-status-badge {{ $isActive ? 'is-active' : 'is-inactive' }}" id="sb-hero-status-badge">
                        {{ $isActive ? trans('spabranch::spa_branches.active') : trans('spabranch::spa_branches.inactive') }}
                    </span>
                </div>

                <p class="sb-hero-meta">
                    <i class="fa fa-tag"></i>
                    <span id="sb-hero-code">{{ $displayCode ?: trans('spabranch::spa_branches.form.no_code') }}</span>
                </p>

                <p class="sb-hero-meta">
                    <i class="fa fa-phone"></i>
                    <span id="sb-hero-phone">{{ $displayPhone ?: trans('spabranch::spa_branches.form.no_phone') }}</span>
                </p>

                <p class="sb-hero-meta">
                    <i class="fa fa-envelope"></i>
                    <span id="sb-hero-email">{{ $displayEmail ?: trans('spabranch::spa_branches.form.no_email') }}</span>
                </p>
            </div>
        </div>

        <div class="sb-hero-insights">
            <article class="sb-hero-insight">
                <div class="sb-hero-insight-icon">
                    <i class="fa fa-shopping-cart"></i>
                </div>
                <div class="sb-hero-insight-body">
                    <span class="sb-hero-insight-label">{{ trans('spabranch::spa_branches.form.hero_checkout') }}</span>
                    <span class="sb-hero-insight-value sb-hero-insight-checkout {{ $isActive ? 'is-active' : 'is-inactive' }}" id="sb-hero-insight-checkout">
                        {{ $isActive ? trans('spabranch::spa_branches.form.hero_visible_at_checkout') : trans('spabranch::spa_branches.form.hero_hidden_at_checkout') }}
                    </span>
                </div>
            </article>

            <article class="sb-hero-insight">
                <div class="sb-hero-insight-icon">
                    <i class="fa fa-user-md"></i>
                </div>
                <div class="sb-hero-insight-body">
                    <span class="sb-hero-insight-label">{{ trans('spabranch::spa_branches.form.hero_beauticians') }}</span>
                    <span class="sb-hero-insight-value" id="sb-hero-insight-beauticians">
                        @if ($selectedBeauticianCount > 0)
                            {{ trans('spabranch::spa_branches.form.hero_beauticians_count', ['count' => $selectedBeauticianCount]) }}
                        @else
                            {{ trans('spabranch::spa_branches.form.hero_no_beauticians') }}
                        @endif
                    </span>
                </div>
            </article>

            <article class="sb-hero-insight">
                <div class="sb-hero-insight-icon">
                    <i class="fa fa-map-marker"></i>
                </div>
                <div class="sb-hero-insight-body">
                    <span class="sb-hero-insight-label">{{ trans('spabranch::attributes.address') }}</span>
                    <span class="sb-hero-insight-value" id="sb-hero-insight-address">
                        {{ $displayAddress ?: trans('spabranch::spa_branches.form.no_address') }}
                    </span>
                </div>
            </article>

            <article class="sb-hero-insight">
                <div class="sb-hero-insight-icon">
                    <i class="fa fa-calendar"></i>
                </div>
                <div class="sb-hero-insight-body">
                    <span class="sb-hero-insight-label">{{ trans('spabranch::spa_branches.form.hero_created') }}</span>
                    <span class="sb-hero-insight-value" id="sb-hero-insight-since">
                        {{ $memberSince ?: trans('spabranch::spa_branches.form.hero_not_saved_yet') }}
                    </span>
                </div>
            </article>
        </div>

        <ul class="sb-hero-stats">
            <li>
                <span class="sb-hero-stat-label">{{ trans('spabranch::attributes.is_active') }}</span>
                <span class="sb-hero-stat-value sb-hero-stat-status {{ $isActive ? 'is-active' : 'is-inactive' }}" id="sb-hero-stat-status">
                    {{ $isActive ? trans('spabranch::spa_branches.active') : trans('spabranch::spa_branches.inactive') }}
                </span>
            </li>
            <li>
                <span class="sb-hero-stat-label">{{ trans('spabranch::spa_branches.spa_branch') }}</span>
                <span class="sb-hero-stat-value" id="sb-hero-stat-id">
                    {{ $spaBranch->id ? '#'.$spaBranch->id : '—' }}
                </span>
            </li>
            <li>
                <span class="sb-hero-stat-label">{{ trans('spabranch::attributes.position') }}</span>
                <span class="sb-hero-stat-value" id="sb-hero-stat-position">{{ $displayPosition }}</span>
            </li>
        </ul>
    </header>

    <div class="row sb-layout">
        <div class="col-lg-3 sb-layout-sidebar">
            <div class="sb-card">
                <div class="sb-card-header">
                    <h3>{{ trans('spabranch::spa_branches.form.sections.visibility') }}</h3>
                    <p>{{ trans('spabranch::spa_branches.form.sections.visibility_help') }}</p>
                </div>
                <div class="sb-card-body">
                    <div class="sb-toggle-row">
                        <div class="sb-toggle-copy">
                            <strong>{{ trans('spabranch::spa_branches.form.enable_branch') }}</strong>
                            <span>{{ trans('spabranch::spa_branches.form.enable_branch_help') }}</span>
                        </div>
                        <label class="sb-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                {{ $isActive ? 'checked' : '' }}
                                id="spa-branch-is-active"
                            >
                            <span class="sb-switch-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="sb-card">
                <div class="sb-card-header">
                    <h3>{{ trans('spabranch::spa_branches.form.sections.ordering') }}</h3>
                    <p>{{ trans('spabranch::spa_branches.form.sections.ordering_help') }}</p>
                </div>
                <div class="sb-card-body">
                    {{ Form::number('position', trans('spabranch::attributes.position'), $errors, $spaBranch, [
                        'min' => 0,
                        'class' => 'form-control sb-input',
                        'value' => $displayPosition,
                    ]) }}
                    <p class="sb-field-hint">{{ trans('spabranch::spa_branches.form.sort_order_help') }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-9 sb-layout-main">
            <div class="sb-card">
                <div class="sb-card-header">
                    <h3>{{ trans('spabranch::spa_branches.form.sections.basic') }}</h3>
                    <p>{{ trans('spabranch::spa_branches.form.sections.basic_help') }}</p>
                </div>
                <div class="sb-card-body">
                    <div class="sb-form-split">
                        <div class="sb-form-split__col">
                            {{ Form::text('name', trans('spabranch::attributes.name'), $errors, $spaBranch, [
                                'required' => true,
                                'class' => 'form-control sb-input',
                                'value' => $displayName,
                            ]) }}
                        </div>

                        <div class="sb-form-split__col">
                            {{ Form::text('code', trans('spabranch::attributes.code'), $errors, $spaBranch, [
                                'class' => 'form-control sb-input',
                                'value' => $displayCode,
                                'placeholder' => 'JB01',
                            ]) }}
                            <p class="sb-field-hint">{{ trans('spabranch::spa_branches.form.code_help') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sb-card">
                <div class="sb-card-header">
                    <h3>{{ trans('spabranch::spa_branches.form.sections.contact') }}</h3>
                    <p>{{ trans('spabranch::spa_branches.form.sections.contact_help') }}</p>
                </div>
                <div class="sb-card-body">
                    <div class="sb-form-split">
                        <div class="sb-form-split__col">
                            {{ Form::text('phone', trans('spabranch::attributes.phone'), $errors, $spaBranch, [
                                'class' => 'form-control sb-input',
                                'value' => $displayPhone,
                                'placeholder' => '60123456789',
                            ]) }}

                            {{ Form::text('email', trans('spabranch::attributes.email'), $errors, $spaBranch, [
                                'class' => 'form-control sb-input',
                                'value' => $displayEmail,
                                'placeholder' => 'branch@example.com',
                            ]) }}
                        </div>

                        <div class="sb-form-split__col">
                            {{ Form::textarea('address', trans('spabranch::attributes.address'), $errors, $spaBranch, [
                                'rows' => 5,
                                'class' => 'form-control sb-textarea',
                                'value' => $displayAddress,
                            ]) }}
                        </div>
                    </div>
                </div>
            </div>

            @if (is_module_enabled('Beautician'))
                <div class="sb-card">
                    <div class="sb-card-header sb-card-header--with-actions">
                        <div>
                            <h3>{{ trans('spabranch::spa_branches.form.sections.beauticians') }}</h3>
                            <p>{{ trans('spabranch::spa_branches.form.sections.beauticians_help') }}</p>
                        </div>
                        <span class="sb-selected-count" id="sb-selected-count">
                            {{ trans('spabranch::spa_branches.form.selected_count', ['count' => $selectedBeauticianCount]) }}
                        </span>
                    </div>
                    <div class="sb-card-body">
                        @if (! empty($branchBeauticianOptions))
                            <div class="sb-beautician-toolbar">
                                <div class="sb-beautician-search-wrap">
                                    <i class="fa fa-search"></i>
                                    <input
                                        type="search"
                                        id="sb-beautician-search"
                                        class="form-control sb-input"
                                        placeholder="{{ trans('spabranch::spa_branches.form.search_beauticians') }}"
                                        autocomplete="off"
                                    >
                                </div>
                                <div class="sb-beautician-toolbar-actions">
                                    <button type="button" class="btn btn-default btn-sm" id="sb-beautician-select-all">
                                        {{ trans('spabranch::spa_branches.form.select_all_beauticians') }}
                                    </button>
                                    <button type="button" class="btn btn-default btn-sm" id="sb-beautician-clear">
                                        {{ trans('spabranch::spa_branches.form.clear_beauticians') }}
                                    </button>
                                </div>
                            </div>

                            <div class="sb-beautician-grid" id="sb-beautician-grid">
                                @foreach ($branchBeauticianOptions as $beautician)
                                    @php
                                        $isChecked = in_array((int) $beautician['id'], $selectedBeauticianIds, true);
                                    @endphp
                                    <label
                                        class="sb-beautician-card {{ $isChecked ? 'is-selected' : '' }}"
                                        data-name="{{ strtolower($beautician['name']) }}"
                                        data-title="{{ strtolower($beautician['job_title'] ?? '') }}"
                                    >
                                        <input
                                            type="checkbox"
                                            name="beauticians[]"
                                            value="{{ $beautician['id'] }}"
                                            class="sb-beautician-checkbox"
                                            {{ $isChecked ? 'checked' : '' }}
                                        >
                                        <span class="sb-beautician-card-check">
                                            <i class="fa fa-check"></i>
                                        </span>
                                        @if (! empty($beautician['profile_image']))
                                            <img
                                                src="{{ $beautician['profile_image'] }}"
                                                alt=""
                                                class="sb-beautician-card-avatar sb-beautician-card-avatar--photo"
                                            >
                                        @else
                                            <span
                                                class="sb-beautician-card-avatar"
                                                style="background-color: {{ $beautician['profile_color'] }};"
                                            >
                                                {{ strtoupper(mb_substr($beautician['name'], 0, 1)) }}
                                            </span>
                                        @endif
                                        <span class="sb-beautician-card-body">
                                            <span class="sb-beautician-card-name">{{ $beautician['name'] }}</span>
                                            @if (! empty($beautician['job_title']))
                                                <span class="sb-beautician-card-title">{{ $beautician['job_title'] }}</span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            </div>

                            <p class="sb-field-hint sb-beautician-empty" id="sb-beautician-empty" hidden>
                                {{ trans('spabranch::spa_branches.form.no_beauticians_match') }}
                            </p>

                            @if ($errors->has('beauticians'))
                                <span class="help-block text-red">{{ $errors->first('beauticians') }}</span>
                            @endif

                            <p class="sb-field-hint">{{ trans('spabranch::spa_branches.form.beauticians_help') }}</p>
                        @else
                            <div class="sb-empty-state">
                                <i class="fa fa-user-md"></i>
                                <p>{{ trans('spabranch::spa_branches.form.no_beauticians_yet') }}</p>
                                @hasAccess('admin.beauticians.create')
                                    <a href="{{ route('admin.beauticians.create') }}" class="btn btn-default btn-sm">
                                        {{ trans('spabranch::spa_branches.form.create_beautician') }}
                                    </a>
                                @endHasAccess
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="sb-form-actions">
        <button type="submit" class="btn btn-primary" data-loading>
            {{ trans('admin::admin.buttons.save') }}
        </button>
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

        .spa-branch-form-layout .accordion-box-content {
            padding: 0;
            border: none;
            box-shadow: none;
            background: transparent;
        }

        .tab-content.clearfix:has(.spa-branch-page) > .form-group {
            display: none;
        }

        .tab-pane#general .accordion-box-content,
        .tab-pane#general .tab-content {
            overflow: visible;
        }

        .spa-branch-page {
            --sb-primary: #ec4899;
            --sb-primary-soft: #fdf2f8;
            --sb-border: #e5e7eb;
            --sb-text: #111827;
            --sb-muted: #6b7280;
            --sb-card-radius: 16px;
            margin: -10px 0 10px;
        }

        .spa-branch-page .sb-layout-sidebar {
            padding-right: 8px;
        }

        .spa-branch-page .sb-layout-main {
            padding-left: 8px;
        }

        @@media (max-width: 991px) {
            .spa-branch-page .sb-layout-sidebar,
            .spa-branch-page .sb-layout-main {
                padding-left: 15px;
                padding-right: 15px;
            }
        }

        .spa-branch-page .form-group {
            margin-bottom: 18px;
        }

        .spa-branch-page .sb-card-body .form-group::before,
        .spa-branch-page .sb-card-body .form-group::after {
            display: none;
        }

        .spa-branch-page .sb-card-body .form-group > label,
        .spa-branch-page .sb-card-body .form-group > .col-md-3,
        .spa-branch-page .sb-card-body .form-group > .col-md-9 {
            width: 100%;
            max-width: 100%;
            padding-left: 0;
            padding-right: 0;
            float: none;
        }

        .spa-branch-page .sb-card-body .form-group > label {
            padding-top: 0;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--sb-text);
        }

        .spa-branch-page .sb-card-body .form-group {
            margin-left: 0;
            margin-right: 0;
        }

        .sb-hero {
            display: grid;
            grid-template-columns: auto minmax(280px, 1fr) auto;
            align-items: center;
            gap: 28px 32px;
            margin-bottom: 28px;
            padding: 28px 32px;
            border-radius: var(--sb-card-radius);
            border: 1px solid var(--sb-border);
            background: linear-gradient(135deg, #fdf2f8 0%, #ffffff 55%);
            box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
        }

        .sb-hero-main {
            display: flex;
            align-items: center;
            gap: 20px;
            min-width: 0;
        }

        .sb-hero-icon-wrap {
            flex-shrink: 0;
        }

        .sb-hero-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 88px;
            height: 88px;
            border-radius: 20px;
            background: var(--sb-primary);
            color: #fff;
            font-size: 34px;
            box-shadow: 0 8px 24px rgba(236, 72, 153, 0.28);
        }

        .sb-hero-icon i {
            line-height: 1;
        }

        .sb-hero-identity {
            min-width: 0;
        }

        .sb-hero-name-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }

        .sb-hero-name {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: var(--sb-text);
            line-height: 1.2;
            word-break: break-word;
        }

        .sb-hero-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .sb-hero-status-badge.is-active {
            color: #166534;
            background: #dcfce7;
        }

        .sb-hero-status-badge.is-inactive {
            color: #991b1b;
            background: #fee2e2;
        }

        .sb-hero-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 0 6px;
            font-size: 14px;
            color: var(--sb-muted);
            word-break: break-word;
        }

        .sb-hero-meta i {
            flex-shrink: 0;
            width: 16px;
            text-align: center;
            color: var(--sb-primary);
        }

        .sb-hero-insights {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            width: 100%;
            max-width: 560px;
            margin: 0 auto;
        }

        .sb-hero-insight {
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

        .sb-hero-insight-icon {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            color: var(--sb-primary);
            background: var(--sb-primary-soft);
            font-size: 15px;
        }

        .sb-hero-insight-label {
            display: block;
            margin-bottom: 3px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--sb-muted);
        }

        .sb-hero-insight-value {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--sb-text);
            line-height: 1.35;
            word-break: break-word;
        }

        .sb-hero-insight-checkout.is-active {
            color: #166534;
        }

        .sb-hero-insight-checkout.is-inactive {
            color: #991b1b;
        }

        .sb-hero-stats {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin: 0;
            padding: 0;
            list-style: none;
            min-width: 200px;
        }

        .sb-hero-stat-label {
            display: block;
            margin-bottom: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--sb-muted);
        }

        .sb-hero-stat-value {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--sb-text);
        }

        .sb-hero-stat-status.is-active {
            color: #166534;
        }

        .sb-hero-stat-status.is-inactive {
            color: #991b1b;
        }

        .sb-card {
            background: #fff;
            border: 1px solid var(--sb-border);
            border-radius: var(--sb-card-radius);
            margin-bottom: 20px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .sb-card-header {
            padding: 20px 32px 0;
        }

        .sb-card-header--with-actions {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

        .sb-card-header h3 {
            margin: 0 0 6px;
            font-size: 16px;
            font-weight: 700;
            color: var(--sb-text);
        }

        .sb-card-header p {
            margin: 0;
            font-size: 13px;
            color: var(--sb-muted);
            line-height: 1.5;
        }

        .sb-selected-count {
            flex-shrink: 0;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            color: var(--sb-primary);
            background: var(--sb-primary-soft);
        }

        .sb-card-body {
            padding: 24px 32px 28px;
        }

        .sb-layout-sidebar .sb-card-header {
            padding: 18px 20px 0;
        }

        .sb-layout-sidebar .sb-card-body {
            padding: 16px 20px 20px;
        }

        .sb-input,
        .sb-textarea {
            border-radius: 10px;
            border-color: var(--sb-border);
            box-shadow: none;
        }

        .sb-input {
            height: 44px;
        }

        .sb-input:focus,
        .sb-textarea:focus {
            border-color: var(--sb-primary);
            box-shadow: 0 0 0 3px rgba(236, 72, 153, 0.12);
        }

        .sb-textarea {
            min-height: 132px;
            resize: vertical;
        }

        .sb-field-hint {
            display: block;
            margin-top: 8px;
            font-size: 12px;
            color: var(--sb-muted);
            line-height: 1.45;
        }

        .sb-form-split {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0 48px;
            align-items: start;
            padding: 4px 8px 0;
        }

        .sb-form-split__col {
            min-width: 0;
            padding: 0 8px;
        }

        .sb-form-split__col + .sb-form-split__col {
            padding-left: 32px;
            border-left: 1px solid var(--sb-border);
        }

        .sb-toggle-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 18px;
            background: #f9fafb;
            border-radius: 12px;
            border: 1px solid var(--sb-border);
        }

        .sb-toggle-copy {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sb-toggle-copy strong {
            font-size: 14px;
            color: var(--sb-text);
        }

        .sb-toggle-copy span {
            font-size: 13px;
            color: var(--sb-muted);
        }

        .sb-switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 28px;
            flex-shrink: 0;
            margin: 0;
        }

        .sb-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .sb-switch-slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background: #d1d5db;
            border-radius: 999px;
            transition: 0.2s ease;
        }

        .sb-switch-slider:before {
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

        .sb-switch input:checked + .sb-switch-slider {
            background: var(--sb-primary);
        }

        .sb-switch input:checked + .sb-switch-slider:before {
            transform: translateX(20px);
        }

        .sb-beautician-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }

        .sb-beautician-search-wrap {
            position: relative;
            flex: 1 1 240px;
            max-width: 360px;
        }

        .sb-beautician-search-wrap i {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: var(--sb-muted);
            pointer-events: none;
        }

        .sb-beautician-search-wrap .sb-input {
            padding-left: 38px;
        }

        .sb-beautician-toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .sb-beautician-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .sb-beautician-card {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
            padding: 14px 16px;
            border: 2px solid var(--sb-border);
            border-radius: 14px;
            background: #fff;
            cursor: pointer;
            transition:
                border-color 0.15s ease,
                box-shadow 0.15s ease,
                background 0.15s ease;
        }

        .sb-beautician-card:hover {
            border-color: #f9a8d4;
            background: #fffbfd;
        }

        .sb-beautician-card.is-selected {
            border-color: var(--sb-primary);
            background: var(--sb-primary-soft);
            box-shadow: 0 0 0 1px var(--sb-primary);
        }

        .sb-beautician-card.is-hidden {
            display: none;
        }

        .sb-beautician-checkbox {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .sb-beautician-card-check {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid var(--sb-border);
            background: #fff;
            color: transparent;
            font-size: 11px;
            transition: 0.15s ease;
        }

        .sb-beautician-card.is-selected .sb-beautician-card-check {
            border-color: var(--sb-primary);
            background: var(--sb-primary);
            color: #fff;
        }

        .sb-beautician-card-avatar {
            display: inline-flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            font-size: 18px;
            font-weight: 700;
            color: #fff;
        }

        .sb-beautician-card-avatar--photo {
            object-fit: cover;
        }

        .sb-beautician-card-body {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
            padding-right: 24px;
        }

        .sb-beautician-card-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--sb-text);
            line-height: 1.3;
            word-break: break-word;
        }

        .sb-beautician-card-title {
            font-size: 12px;
            color: var(--sb-muted);
            line-height: 1.35;
            word-break: break-word;
        }

        .sb-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            padding: 32px 20px;
            text-align: center;
            border: 1px dashed var(--sb-border);
            border-radius: 14px;
            background: #f9fafb;
        }

        .sb-empty-state i {
            font-size: 28px;
            color: var(--sb-muted);
        }

        .sb-empty-state p {
            margin: 0;
            max-width: 420px;
            font-size: 14px;
            color: var(--sb-muted);
        }

        .sb-form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 4px;
            padding: 20px 0 4px;
            border-top: 1px solid var(--sb-border);
        }

        .sb-form-actions .btn-primary {
            min-width: 120px;
            height: 42px;
            border-radius: 10px;
            font-weight: 600;
        }

        @@media (max-width: 991px) {
            .sb-hero {
                grid-template-columns: 1fr;
                padding: 22px 20px;
            }

            .sb-hero-insights {
                max-width: none;
            }

            .sb-hero-stats {
                width: 100%;
                flex-direction: row;
                flex-wrap: wrap;
                gap: 20px;
            }

            .sb-form-split {
                grid-template-columns: 1fr;
                gap: 0;
                padding: 0;
            }

            .sb-form-split__col {
                padding: 0;
            }

            .sb-form-split__col + .sb-form-split__col {
                margin-top: 24px;
                padding-top: 24px;
                padding-left: 0;
                border-left: none;
                border-top: 1px solid var(--sb-border);
            }

            .sb-beautician-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const nameInput = document.querySelector('[name="name"]');
            const codeInput = document.querySelector('[name="code"]');
            const phoneInput = document.querySelector('[name="phone"]');
            const emailInput = document.querySelector('[name="email"]');
            const addressInput = document.querySelector('[name="address"]');
            const positionInput = document.querySelector('[name="position"]');
            const activeInput = document.getElementById('spa-branch-is-active');
            const heroName = document.getElementById('sb-hero-name');
            const heroCode = document.getElementById('sb-hero-code');
            const heroPhone = document.getElementById('sb-hero-phone');
            const heroEmail = document.getElementById('sb-hero-email');
            const heroStatusBadge = document.getElementById('sb-hero-status-badge');
            const heroStatStatus = document.getElementById('sb-hero-stat-status');
            const heroStatPosition = document.getElementById('sb-hero-stat-position');
            const heroInsightCheckout = document.getElementById('sb-hero-insight-checkout');
            const heroInsightBeauticians = document.getElementById('sb-hero-insight-beauticians');
            const heroInsightAddress = document.getElementById('sb-hero-insight-address');
            const selectedCount = document.getElementById('sb-selected-count');
            const searchInput = document.getElementById('sb-beautician-search');
            const beauticianGrid = document.getElementById('sb-beautician-grid');
            const beauticianEmpty = document.getElementById('sb-beautician-empty');
            const selectAllBtn = document.getElementById('sb-beautician-select-all');
            const clearBtn = document.getElementById('sb-beautician-clear');

            const defaultName = @json(trans('spabranch::spa_branches.form.new_branch'));
            const defaultCode = @json(trans('spabranch::spa_branches.form.no_code'));
            const defaultPhone = @json(trans('spabranch::spa_branches.form.no_phone'));
            const defaultEmail = @json(trans('spabranch::spa_branches.form.no_email'));
            const defaultAddress = @json(trans('spabranch::spa_branches.form.no_address'));
            const activeLabel = @json(trans('spabranch::spa_branches.active'));
            const inactiveLabel = @json(trans('spabranch::spa_branches.inactive'));
            const visibleCheckoutLabel = @json(trans('spabranch::spa_branches.form.hero_visible_at_checkout'));
            const hiddenCheckoutLabel = @json(trans('spabranch::spa_branches.form.hero_hidden_at_checkout'));
            const beauticiansCountLabel = @json(trans('spabranch::spa_branches.form.hero_beauticians_count'));
            const noBeauticiansLabel = @json(trans('spabranch::spa_branches.form.hero_no_beauticians'));
            const selectedCountLabel = @json(trans('spabranch::spa_branches.form.selected_count'));

            const syncHeroText = () => {
                if (heroName) {
                    heroName.textContent = nameInput?.value.trim() || defaultName;
                }

                if (heroCode) {
                    heroCode.textContent = codeInput?.value.trim() || defaultCode;
                }

                if (heroPhone) {
                    heroPhone.textContent = phoneInput?.value.trim() || defaultPhone;
                }

                if (heroEmail) {
                    heroEmail.textContent = emailInput?.value.trim() || defaultEmail;
                }

                if (heroInsightAddress) {
                    heroInsightAddress.textContent = addressInput?.value.trim() || defaultAddress;
                }

                if (heroStatPosition) {
                    heroStatPosition.textContent = positionInput?.value !== '' ? positionInput.value : '0';
                }

                const isActive = Boolean(activeInput?.checked);

                if (heroStatStatus) {
                    heroStatStatus.textContent = isActive ? activeLabel : inactiveLabel;
                    heroStatStatus.classList.toggle('is-active', isActive);
                    heroStatStatus.classList.toggle('is-inactive', ! isActive);
                }

                if (heroStatusBadge) {
                    heroStatusBadge.textContent = isActive ? activeLabel : inactiveLabel;
                    heroStatusBadge.classList.toggle('is-active', isActive);
                    heroStatusBadge.classList.toggle('is-inactive', ! isActive);
                }

                if (heroInsightCheckout) {
                    heroInsightCheckout.textContent = isActive ? visibleCheckoutLabel : hiddenCheckoutLabel;
                    heroInsightCheckout.classList.toggle('is-active', isActive);
                    heroInsightCheckout.classList.toggle('is-inactive', ! isActive);
                }
            };

            const syncBeauticianSelection = () => {
                const cards = beauticianGrid?.querySelectorAll('.sb-beautician-card') ?? [];
                let checkedCount = 0;

                cards.forEach((card) => {
                    const checkbox = card.querySelector('.sb-beautician-checkbox');
                    card.classList.toggle('is-selected', Boolean(checkbox?.checked));

                    if (checkbox?.checked && ! card.classList.contains('is-hidden')) {
                        checkedCount += 1;
                    }
                });

                if (heroInsightBeauticians) {
                    heroInsightBeauticians.textContent = checkedCount > 0
                        ? beauticiansCountLabel.replace(':count', String(checkedCount))
                        : noBeauticiansLabel;
                }

                if (selectedCount) {
                    selectedCount.textContent = selectedCountLabel.replace(':count', String(checkedCount));
                }
            };

            const filterBeauticians = () => {
                if (! beauticianGrid || ! searchInput) {
                    return;
                }

                const query = searchInput.value.trim().toLowerCase();
                let visibleCount = 0;

                beauticianGrid.querySelectorAll('.sb-beautician-card').forEach((card) => {
                    const name = card.dataset.name || '';
                    const title = card.dataset.title || '';
                    const matches = ! query || name.includes(query) || title.includes(query);

                    card.classList.toggle('is-hidden', ! matches);

                    if (matches) {
                        visibleCount += 1;
                    }
                });

                if (beauticianEmpty) {
                    beauticianEmpty.hidden = visibleCount > 0;
                }
            };

            beauticianGrid?.addEventListener('change', (event) => {
                if (event.target.matches('.sb-beautician-checkbox')) {
                    syncBeauticianSelection();
                }
            });

            selectAllBtn?.addEventListener('click', () => {
                beauticianGrid?.querySelectorAll('.sb-beautician-card:not(.is-hidden) .sb-beautician-checkbox').forEach((checkbox) => {
                    checkbox.checked = true;
                });
                syncBeauticianSelection();
            });

            clearBtn?.addEventListener('click', () => {
                beauticianGrid?.querySelectorAll('.sb-beautician-checkbox').forEach((checkbox) => {
                    checkbox.checked = false;
                });
                syncBeauticianSelection();
            });

            nameInput?.addEventListener('input', syncHeroText);
            codeInput?.addEventListener('input', syncHeroText);
            phoneInput?.addEventListener('input', syncHeroText);
            emailInput?.addEventListener('input', syncHeroText);
            addressInput?.addEventListener('input', syncHeroText);
            positionInput?.addEventListener('input', syncHeroText);
            activeInput?.addEventListener('change', syncHeroText);
            searchInput?.addEventListener('input', filterBeauticians);

            syncHeroText();
            syncBeauticianSelection();
        });
    </script>
@endpush
