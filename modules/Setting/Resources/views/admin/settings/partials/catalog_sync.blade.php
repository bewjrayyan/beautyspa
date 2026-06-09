@php
    $sync = $catalogSync ?? [];
    $sourceUrl = old('catalog_sync_source_url', $sync['source_url'] ?? '');
    $exportUrl = $sync['export_url'] ?? null;
    $tokenConfigured = ! empty($sync['token_configured']);
    $bundleExists = ! empty($sync['bundle_exists']);
@endphp

@component('setting::admin.settings.partials.section', [
    'icon' => 'fa-database',
    'title' => trans('setting::settings.sections.catalog_sync'),
    'description' => trans('setting::settings.form.catalog_sync_help'),
    'class' => 'st-section--catalog-sync',
])
    <div class="artisan-command-grid catalog-sync-grid">
        <div class="artisan-command-card catalog-sync-card">
            <strong class="catalog-sync-card__title">{{ trans('setting::settings.form.catalog_sync_export_title') }}</strong>
            <p class="catalog-sync-card__text">{{ trans('setting::settings.form.catalog_sync_export_help') }}</p>

            <a
                href="{{ route('admin.catalog_sync.export') }}"
                class="btn btn-default btn-sm"
            >
                <i class="fa fa-download"></i>
                {{ trans('setting::settings.form.catalog_sync_export_button') }}
            </a>

            @if ($exportUrl)
                <p class="help-block text-muted catalog-sync-card__url">
                    {{ trans('setting::settings.form.catalog_sync_export_url') }}<br>
                    <code>{{ $exportUrl }}</code>
                </p>
            @elseif (! $tokenConfigured)
                <p class="help-block text-warning">{{ trans('setting::settings.form.catalog_sync_token_hint') }}</p>
            @endif
        </div>

        <div class="artisan-command-card catalog-sync-card">
            <strong class="catalog-sync-card__title">{{ trans('setting::settings.form.catalog_sync_import_title') }}</strong>
            <p class="catalog-sync-card__text">{{ trans('setting::settings.form.catalog_sync_import_help') }}</p>

            <form
                method="POST"
                action="{{ route('admin.catalog_sync.import') }}"
                enctype="multipart/form-data"
                class="catalog-sync-upload"
            >
                @csrf
                <div class="form-group">
                    <input type="file" name="catalog_bundle" accept=".zip,application/zip" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm(@json(trans('setting::settings.form.catalog_sync_import_confirm')));">
                    <i class="fa fa-upload"></i>
                    {{ trans('setting::settings.form.catalog_sync_import_button') }}
                </button>
            </form>

            @if ($bundleExists)
                <form method="POST" action="{{ route('admin.catalog_sync.import_stored') }}" class="catalog-sync-stored">
                    @csrf
                    <button type="submit" class="btn btn-default btn-sm" onclick="return confirm(@json(trans('setting::settings.form.catalog_sync_import_confirm')));">
                        <i class="fa fa-folder-open"></i>
                        {{ trans('setting::settings.form.catalog_sync_import_stored_button') }}
                    </button>
                </form>
            @endif
        </div>

        <div class="artisan-command-card catalog-sync-card">
            <strong class="catalog-sync-card__title">{{ trans('setting::settings.form.catalog_sync_pull_title') }}</strong>
            <p class="catalog-sync-card__text">{{ trans('setting::settings.form.catalog_sync_pull_help') }}</p>

            <form method="POST" action="{{ route('admin.catalog_sync.pull') }}" class="catalog-sync-pull">
                @csrf
                <div class="form-group">
                    <label for="catalog_sync_source_url">{{ trans('setting::settings.form.catalog_sync_source_url') }}</label>
                    <input
                        type="url"
                        name="catalog_sync_source_url"
                        id="catalog_sync_source_url"
                        class="form-control"
                        value="{{ $sourceUrl }}"
                        placeholder="http://localhost/fleetcart/catalog-sync/bundle"
                    >
                </div>
                <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm(@json(trans('setting::settings.form.catalog_sync_pull_confirm')));">
                    <i class="fa fa-cloud-download"></i>
                    {{ trans('setting::settings.form.catalog_sync_pull_button') }}
                </button>
            </form>

            <p class="help-block text-muted">{{ trans('setting::settings.form.catalog_sync_localhost_note') }}</p>
        </div>
    </div>
@endcomponent
