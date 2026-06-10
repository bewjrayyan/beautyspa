@php
    $meta = $activeTabMeta ?? ['name' => $activeTab ?? 'general', 'label' => '', 'group' => '', 'lead' => null, 'icon' => null];
    $panelIcon = $meta['icon'] ?? 'fa-cog';
@endphp

<div class="admin-settings" data-active-tab="{{ $activeTab }}">
    <aside class="settings-sidebar" aria-label="{{ trans('setting::settings.settings') }}">
        <div class="settings-sidebar__inner">
            <div class="settings-sidebar__brand">
                <span class="settings-sidebar__brand-icon" aria-hidden="true">
                    <i class="fa fa-sliders"></i>
                </span>
                <span class="settings-sidebar__brand-text">{{ trans('setting::settings.settings') }}</span>
            </div>

            <div class="settings-sidebar__search">
                <label class="sr-only" for="settings-nav-search">{{ trans('setting::settings.form.search_settings') }}</label>
                <span class="settings-sidebar__search-icon" aria-hidden="true">
                    <i class="fa fa-search"></i>
                </span>
                <input
                    type="search"
                    id="settings-nav-search"
                    class="settings-sidebar__search-input"
                    placeholder="{{ trans('setting::settings.form.search_settings') }}"
                    data-no-results="{{ trans('setting::settings.form.search_no_results') }}"
                    autocomplete="off"
                    spellcheck="false"
                >
                <kbd class="settings-sidebar__search-kbd" aria-hidden="true">/</kbd>
            </div>

            <div class="settings-sidebar__nav" id="settings-nav-groups">
                {!! $navigation !!}
            </div>
        </div>
    </aside>

    <div class="settings-main">
        <div class="settings-panel">
            <input type="hidden" name="settings_tab" value="{{ $activeTab ?? 'general' }}">

            <header class="settings-panel__head">
                <div class="settings-panel__head-main">
                    <span class="settings-panel__icon" aria-hidden="true">
                        <i class="fa {{ $panelIcon }}"></i>
                    </span>

                    <div class="settings-panel__head-text">
                        @if (! empty($meta['group']))
                            <span class="settings-panel__eyebrow">{{ $meta['group'] }}</span>
                        @endif
                        <h2 class="settings-panel__title">{{ $meta['label'] }}</h2>
                        @if (! empty($meta['lead']))
                            <p class="settings-panel__lead">{{ $meta['lead'] }}</p>
                        @endif
                    </div>
                </div>

                <div class="settings-panel__head-actions">
                    <span class="settings-unsaved-badge is-hidden" id="settings-unsaved-badge" role="status">
                        <span class="settings-unsaved-badge__dot" aria-hidden="true"></span>
                        {{ trans('setting::settings.form.unsaved_changes') }}
                    </span>
                </div>
            </header>

            <div class="settings-panel__content tab-content clearfix settings-form">
                {{ $contents }}
            </div>

            <div class="settings-panel__footer">
                <div class="settings-panel__footer-inner">
                    <p class="settings-panel__footer-hint">
                        <kbd>Ctrl</kbd> + <kbd>S</kbd> {{ trans('setting::settings.form.save_shortcut') }}
                    </p>

                    <button type="submit" class="btn btn-primary settings-save-btn" data-loading>
                        <i class="fa fa-check" aria-hidden="true"></i>
                        <span>{{ trans('admin::admin.buttons.save') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
