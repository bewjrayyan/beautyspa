@php
    use Modules\Payment\Services\ChipCheckoutAvailability;
    use Modules\Payment\Services\ChipPaymentMethodConfig;

    $chipEnabled = (bool) old('chip_enabled', array_get($settings, 'chip_enabled'));
    $testMode = (bool) old('chip_test_mode', array_get($settings, 'chip_test_mode'));
    $brandId = trim((string) old('chip_brand_id', array_get($settings, 'chip_brand_id', '')));
    $hasApiKey = filled(old('chip_api_key')) || filled(array_get($settings, 'chip_api_key'));
    $hasPublicKey = filled(old('chip_public_key', array_get($settings, 'chip_public_key', '')));
    $configuredWebhook = trim((string) old('chip_webhook_url', array_get($settings, 'chip_webhook_url', '')));
    $suggestedWebhook = route('payment.chip.webhook', [], true);
    $isLocalWebhook = str_contains($suggestedWebhook, 'localhost')
        || str_contains($suggestedWebhook, '127.0.0.1');

    $credentialsReady = $brandId !== '' && $hasApiKey;
    $maskedBrandId = $brandId !== ''
        ? (strlen($brandId) > 8 ? substr($brandId, 0, 4) . '···' . substr($brandId, -4) : $brandId)
        : null;

    $enabledMethodLabels = [];

    if ($chipEnabled && ChipCheckoutAvailability::showAllMethodsGateway()) {
        $enabledMethodLabels[] = trans('setting::settings.chip.sidebar.all_methods');
    }

    foreach (ChipPaymentMethodConfig::checkoutMethodKeys() as $methodKey) {
        $config = ChipPaymentMethodConfig::configFor($methodKey);

        if (! $config || ! $chipEnabled) {
            continue;
        }

        if (old("{$methodKey}_enabled", array_get($settings, "{$methodKey}_enabled"))) {
            $enabledMethodLabels[] = setting($config['label_setting']);
        }
    }

    $checkoutUrl = Route::has('checkout.create') ? route('checkout.create') : null;

    $checklistSteps = [
        ['done' => $credentialsReady, 'label' => trans('setting::settings.chip.sidebar.check_credentials')],
        ['done' => $hasPublicKey, 'label' => trans('setting::settings.chip.sidebar.check_public_key')],
        ['done' => $enabledMethodLabels !== [], 'label' => trans('setting::settings.chip.sidebar.check_methods')],
        ['done' => ! $testMode && $chipEnabled, 'label' => trans('setting::settings.chip.sidebar.check_live')],
    ];

    $checklistDone = collect($checklistSteps)->where('done', true)->count();
@endphp

<aside class="chip-sidebar-panel" aria-label="{{ trans('setting::settings.chip.sidebar.aria') }}">
    <header class="chip-sidebar-panel__header">
        <div class="chip-sidebar-panel__identity">
            <div class="chip-sidebar-panel__logo payment-gateway-logo" aria-hidden="true">
                {!! file_get_contents(module_path('Payment') . '/Resources/assets/admin/images/chip-logo.svg') !!}
            </div>
            <div class="chip-sidebar-panel__titles">
                <h4 class="chip-sidebar-panel__name">CHIP</h4>
                <p class="chip-sidebar-panel__subtitle">{{ trans('setting::settings.chip.sidebar.subtitle') }}</p>
            </div>
        </div>

        <div class="chip-sidebar-panel__status">
            @if ($chipEnabled)
                <span class="chip-sidebar-panel__pill chip-sidebar-panel__pill--on">
                    {{ trans('setting::settings.chip.sidebar.gateway_on') }}
                </span>
            @else
                <span class="chip-sidebar-panel__pill">
                    {{ trans('setting::settings.chip.sidebar.gateway_off') }}
                </span>
            @endif

            @if ($chipEnabled)
                <span class="chip-sidebar-panel__pill {{ $testMode ? 'chip-sidebar-panel__pill--warn' : 'chip-sidebar-panel__pill--on' }}">
                    {{ $testMode
                        ? trans('setting::settings.chip.sidebar.mode_sandbox')
                        : trans('setting::settings.chip.sidebar.mode_live') }}
                </span>
            @endif
        </div>
    </header>

    <div class="chip-sidebar-panel__tiles">
        <article @class(['chip-sidebar-panel__tile', 'chip-sidebar-panel__tile--ok' => $credentialsReady, 'chip-sidebar-panel__tile--warn' => ! $credentialsReady])>
            <span class="chip-sidebar-panel__tile-icon" aria-hidden="true">
                <i class="fa {{ $credentialsReady ? 'fa-key' : 'fa-exclamation-triangle' }}"></i>
            </span>
            <div>
                <h6>{{ trans('setting::settings.chip.sidebar.credentials') }}</h6>
                <p>
                    @if ($credentialsReady)
                        {{ trans('setting::settings.chip.sidebar.credentials_ready', ['brand' => $maskedBrandId]) }}
                    @else
                        {{ trans('setting::settings.chip.sidebar.credentials_missing') }}
                    @endif
                </p>
            </div>
        </article>

        <article @class(['chip-sidebar-panel__tile', 'chip-sidebar-panel__tile--ok' => $hasPublicKey, 'chip-sidebar-panel__tile--muted' => ! $hasPublicKey])>
            <span class="chip-sidebar-panel__tile-icon" aria-hidden="true">
                <i class="fa fa-shield"></i>
            </span>
            <div>
                <h6>{{ trans('setting::settings.chip.sidebar.public_key') }}</h6>
                <p>
                    {{ $hasPublicKey
                        ? trans('setting::settings.chip.sidebar.public_key_set')
                        : trans('setting::settings.chip.sidebar.public_key_auto') }}
                </p>
            </div>
        </article>
    </div>

    <section class="chip-sidebar-panel__section chip-sidebar-panel__section--progress">
        <div class="chip-sidebar-panel__progress-head">
            <h5 class="chip-sidebar-panel__section-title">{{ trans('setting::settings.chip.sidebar.checklist_title') }}</h5>
            <span class="chip-sidebar-panel__progress-count">{{ $checklistDone }}/{{ count($checklistSteps) }}</span>
        </div>

        <div class="chip-sidebar-panel__progress-bar" role="progressbar" aria-valuenow="{{ $checklistDone }}" aria-valuemin="0" aria-valuemax="{{ count($checklistSteps) }}">
            <span class="chip-sidebar-panel__progress-fill" style="width: {{ count($checklistSteps) > 0 ? round(($checklistDone / count($checklistSteps)) * 100) : 0 }}%"></span>
        </div>

        <ul class="chip-sidebar-panel__steps">
            @foreach ($checklistSteps as $step)
                <li @class(['is-done' => $step['done']])>
                    <span class="chip-sidebar-panel__step-icon" aria-hidden="true">
                        <i class="fa {{ $step['done'] ? 'fa-check' : 'fa-circle-o' }}"></i>
                    </span>
                    <span>{{ $step['label'] }}</span>
                </li>
            @endforeach
        </ul>
    </section>

    <section class="chip-sidebar-panel__section">
        <h5 class="chip-sidebar-panel__section-title">{{ trans('setting::settings.chip.sidebar.checkout') }}</h5>

        @if ($enabledMethodLabels !== [])
            <div class="chip-sidebar-panel__method-chips">
                @foreach ($enabledMethodLabels as $label)
                    <span class="chip-sidebar-panel__method-chip">{{ $label }}</span>
                @endforeach
            </div>
        @else
            <p class="chip-sidebar-panel__empty">{{ trans('setting::settings.chip.sidebar.no_checkout_methods') }}</p>
        @endif
    </section>

    <section class="chip-sidebar-panel__section chip-sidebar-panel__section--webhook">
        <h5 class="chip-sidebar-panel__section-title">{{ trans('setting::settings.chip.sidebar.webhook_title') }}</h5>
        <p class="chip-sidebar-panel__section-lead">{{ trans('setting::settings.chip.sidebar.webhook_lead') }}</p>

        <div class="chip-sidebar-panel__copy-row">
            <code class="chip-sidebar-panel__copy-value" id="chip-suggested-webhook-url">{{ $suggestedWebhook }}</code>
            <button
                type="button"
                class="btn btn-default btn-sm chip-sidebar-panel__copy-btn"
                data-chip-copy-target="#chip-suggested-webhook-url"
                data-chip-copy-done="{{ trans('setting::settings.chip.sidebar.copied') }}"
                title="{{ trans('setting::settings.chip.sidebar.copy') }}"
            >
                <i class="fa fa-copy" aria-hidden="true"></i>
                <span class="chip-sidebar-panel__copy-label">{{ trans('setting::settings.chip.sidebar.copy') }}</span>
            </button>
        </div>

        @if ($configuredWebhook !== '')
            <p class="chip-sidebar-panel__hint">{{ trans('setting::settings.chip.sidebar.webhook_custom_set') }}</p>
        @endif

        @if ($isLocalWebhook)
            <p class="chip-sidebar-panel__alert">
                <i class="fa fa-warning" aria-hidden="true"></i>
                {{ trans('setting::settings.chip.sidebar.webhook_local_warning') }}
            </p>
        @endif
    </section>

    <nav class="chip-sidebar-panel__actions" aria-label="{{ trans('setting::settings.chip.sidebar.quick_links') }}">
        <a href="https://portal.chip-in.asia/collect/developers/api-keys" class="chip-sidebar-panel__action" target="_blank" rel="noopener noreferrer">
            <i class="fa fa-external-link" aria-hidden="true"></i>
            <span>{{ trans('setting::settings.chip.sidebar.link_portal') }}</span>
        </a>
        <a href="https://docs.chip-in.asia/chip-collect/api-reference/purchases/create" class="chip-sidebar-panel__action" target="_blank" rel="noopener noreferrer">
            <i class="fa fa-book" aria-hidden="true"></i>
            <span>{{ trans('setting::settings.chip.sidebar.link_api_docs') }}</span>
        </a>
        @if ($checkoutUrl)
            <a href="{{ $checkoutUrl }}" class="chip-sidebar-panel__action" target="_blank" rel="noopener noreferrer">
                <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                <span>{{ trans('setting::settings.chip.sidebar.link_checkout') }}</span>
            </a>
        @endif
    </nav>
</aside>
