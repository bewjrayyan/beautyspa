@if (setting('pwa_enabled'))
    @php
        $pwaIconUrl = is_file(public_path('pwa/icons/192x192.png'))
            ? asset('pwa/icons/192x192.png')
            : ($logo ?? null);
    @endphp

    <div
        x-data="PwaInstallModal"
        x-cloak
        class="modal pwa-install-modal fade"
        id="pwaInstallModal"
        tabindex="-1"
        aria-labelledby="pwaInstallModalTitle"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="pwa-install-modal__inner">
                        <button
                            type="button"
                            class="close"
                            data-bs-dismiss="modal"
                            aria-label="{{ trans('storefront::pwa.close') }}"
                            @click="dismiss(true)"
                        >
                            <i class="las la-times"></i>
                        </button>

                        <div class="pwa-install-modal__hero">
                            @if ($pwaIconUrl)
                                <img
                                    src="{{ $pwaIconUrl }}"
                                    alt="{{ setting('store_name') }}"
                                    class="pwa-install-modal__icon"
                                    width="72"
                                    height="72"
                                >
                            @else
                                <div class="pwa-install-modal__icon pwa-install-modal__icon--placeholder">
                                    <i class="las la-mobile-alt"></i>
                                </div>
                            @endif

                            <h2 id="pwaInstallModalTitle" class="pwa-install-modal__title">
                                {{ trans('storefront::pwa.title') }}
                            </h2>

                            <p class="pwa-install-modal__subtitle">
                                {{ trans('storefront::pwa.subtitle', ['store' => setting('store_name')]) }}
                            </p>
                        </div>

                        <div class="pwa-install-modal__steps">
                            <h3 class="pwa-install-modal__steps-title" x-text="stepsTitle"></h3>

                            <ol class="pwa-install-modal__steps-list">
                                <template x-for="(step, index) in steps" :key="index">
                                    <li x-text="step"></li>
                                </template>
                            </ol>
                        </div>

                        <div class="pwa-install-modal__actions">
                            <button
                                type="button"
                                class="btn btn-primary"
                                x-show="canInstall"
                                x-cloak
                                :class="{ 'btn-loading': installing }"
                                :disabled="installing"
                                @click="install"
                            >
                                <span x-text="installing ? '{{ trans('storefront::pwa.installing') }}' : '{{ trans('storefront::pwa.install') }}'"></span>
                            </button>

                            <button
                                type="button"
                                class="btn btn-link pwa-install-modal__dismiss"
                                data-bs-dismiss="modal"
                                @click="dismiss(true)"
                            >
                                {{ trans('storefront::pwa.not_now') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
