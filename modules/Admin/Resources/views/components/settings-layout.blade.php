<div class="admin-settings" data-active-tab="{{ $activeTab }}">
    <aside class="settings-sidebar" aria-label="{{ trans('setting::settings.settings') }}">
        <div class="settings-sidebar__inner">
            {!! $navigation !!}
        </div>
    </aside>

    <div class="settings-main">
        <div class="settings-panel">
            <div class="settings-panel__content tab-content clearfix settings-form">
                {{ $contents }}
            </div>

            <div class="settings-panel__footer">
                @include('admin::form.footer')
            </div>
        </div>
    </div>
</div>
