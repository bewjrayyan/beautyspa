@extends('admin::layout')

@section('title', $pageTitle)

@section('content')
    <div class="tr-pos-page" data-tr-pos="1" data-portal="{{ $isPortal ? '1' : '0' }}" data-api-base="{{ url('api') }}">
        <script type="application/json" data-pos-catalog>@json($catalog)</script>
        <header class="tr-pos-topbar">
            <div class="tr-pos-brand">
                <span class="tr-pos-brand-mark" aria-hidden="true">+</span>
                <div>
                    <span class="tr-pos-eyebrow">IMMASERILARIS</span>
                    <h1>Treatment POS</h1>
                </div>
            </div>
            <div class="tr-pos-session">
                <span class="tr-pos-live"><i aria-hidden="true"></i> Live workspace</span>
                <span class="tr-pos-session-user">{{ auth()->user()?->first_name ?: auth()->user()?->email }}</span>
                <a class="tr-pos-close" href="{{ $isPortal ? route('admin.treatment_reservations.portal') : route('admin.treatment_reservations.index', ['view' => 'calendar']) }}">Back to appointments</a>
            </div>
        </header>

        <main class="tr-pos-workspace">
            <section class="tr-pos-catalog-panel" aria-labelledby="tr-pos-catalog-title">
                <div class="tr-pos-heading-row">
                    <div>
                        <p class="tr-pos-kicker">Service catalog</p>
                        <h2 id="tr-pos-catalog-title">Treatment catalog</h2>
                        <p class="tr-pos-muted">Select a treatment, then configure its variant and appointment in the guided booking modal.</p>
                    </div>
                    <div class="tr-pos-count" data-pos-count>0 treatments</div>
                </div>
                <div class="tr-pos-catalog-toolbar">
                    <label class="tr-pos-search">
                        <span class="sr-only">Search treatments</span>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
                        <input type="search" data-pos-search placeholder="Search treatments or category" autocomplete="off">
                    </label>
                    <div class="tr-pos-view-switch" role="group" aria-label="Catalog view">
                        <button type="button" class="is-active" data-pos-view="grid" aria-label="Grid view"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="6" height="6"></rect><rect x="14" y="4" width="6" height="6"></rect><rect x="4" y="14" width="6" height="6"></rect><rect x="14" y="14" width="6" height="6"></rect></svg></button>
                        <button type="button" data-pos-view="list" aria-label="List view"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 6h14M5 12h14M5 18h14"></path></svg></button>
                    </div>
                </div>
                <div class="tr-pos-category-strip" data-pos-categories role="tablist" aria-label="Treatment categories"></div>
                <div class="tr-pos-products" data-pos-products aria-live="polite">
                    <div class="tr-pos-loading">Loading treatments…</div>
                </div>
            </section>

            <div class="tr-pos-side-rail">
                <div class="tr-pos-mobile-sheet-head">
                    <span aria-hidden="true"></span>
                    <strong>Booking summary</strong>
                    <button type="button" data-pos-mobile-close aria-label="Close booking summary"><i class="fa fa-times"></i></button>
                </div>
                <section class="tr-pos-customer-desk" aria-labelledby="tr-pos-customer-title" data-pos-customer-desk>
                    <div class="tr-pos-customer-desk__head">
                        <div>
                            <span class="tr-pos-kicker">Customer</span>
                            <strong id="tr-pos-customer-title">Find or look up member</strong>
                        </div>
                        <span class="tr-pos-customer-desk__status" data-pos-customer-status>No member yet</span>
                    </div>

                    <div class="tr-pos-customer-tools" data-pos-customer-tools>
                        <div class="tr-pos-membership-lookup">
                            <label class="tr-pos-field-title" for="tr-pos-membership-id">Membership ID</label>
                            <div class="tr-pos-membership-row">
                                <input id="tr-pos-membership-id" type="search" data-pos-membership-id placeholder="e.g. 0000000000000002" inputmode="numeric" autocomplete="off">
                                <button type="button" data-pos-membership-lookup>Lookup</button>
                            </div>
                            <p class="tr-pos-membership-feedback" data-pos-membership-feedback role="status" aria-live="polite"></p>
                        </div>

                        <div class="tr-pos-customer-desk__divider" aria-hidden="true"><span>or search</span></div>

                        <div class="tr-pos-customer-search-wrap">
                            <label class="tr-pos-input tr-pos-customer-search">
                                <span class="sr-only">Find customer</span>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
                                <input type="search" data-pos-customer-search placeholder="Search name, phone or email" autocomplete="off">
                            </label>
                            <div class="tr-pos-customer-results" data-pos-customer-results></div>
                        </div>
                    </div>

                    <div class="tr-pos-selected-customer" data-pos-selected-customer hidden></div>
                    <div class="tr-pos-rewards" data-pos-rewards hidden></div>
                </section>

                <aside class="tr-pos-order-panel" aria-labelledby="tr-pos-order-title">
                    <div class="tr-pos-order-head">
                        <div>
                            <p class="tr-pos-kicker">New booking</p>
                            <h2 id="tr-pos-order-title">Booking summary</h2>
                        </div>
                        <button class="tr-pos-reset" type="button" data-pos-reset>Clear</button>
                    </div>

                    <div class="tr-pos-customer-block tr-pos-customer-block--summary">
                        <div class="tr-pos-section-label"><span>Customer</span><span class="tr-pos-required">Summary</span></div>
                        <div class="tr-pos-summary-customer" data-pos-summary-customer>No customer selected.</div>
                        <div class="tr-pos-summary-rewards" data-pos-summary-rewards hidden></div>
                    </div>

                    <div class="tr-pos-line-items">
                        <div class="tr-pos-section-label"><span>Selected treatment</span><span data-pos-item-count>0</span></div>
                        <div class="tr-pos-empty-cart" data-pos-empty-cart>
                            <strong>{{ trans('treatmentreservation::admin.pos.empty_cart_title') }}</strong>
                            <span>{{ trans('treatmentreservation::admin.pos.empty_cart_hint') }}</span>
                        </div>
                        <div class="tr-pos-cart-item" data-pos-cart-item hidden></div>
                    </div>

                    <div class="tr-pos-summary">
                        <div><span>Service total</span><strong data-pos-total>MYR 0.00</strong></div>
                        <div data-pos-coupon-discount-row hidden><span>Coupon discount</span><strong class="tr-pos-summary-discount" data-pos-coupon-discount>− MYR 0.00</strong></div>
                        <div data-pos-loyalty-discount-row hidden><span>Points discount</span><strong class="tr-pos-summary-discount" data-pos-loyalty-discount>− MYR 0.00</strong></div>
                        <div data-pos-payable-row hidden><span>Amount due</span><strong data-pos-payable>MYR 0.00</strong></div>
                        <div><span>Payment</span><strong class="tr-pos-summary-payment" data-pos-payment-summary>Offline · receipt required</strong></div>
                    </div>

                    <section class="tr-pos-loyalty-redeem" data-pos-loyalty-redeem hidden aria-labelledby="tr-pos-loyalty-title">
                        <div class="tr-pos-loyalty-redeem__head">
                            <span class="tr-pos-loyalty-redeem__icon" aria-hidden="true"><i class="fa fa-star"></i></span>
                            <strong id="tr-pos-loyalty-title">{{ trans('loyalty::checkout.rewards') }}</strong>
                        </div>
                        <p class="tr-pos-loyalty-redeem__balance" data-pos-loyalty-balance></p>
                        <p class="tr-pos-loyalty-redeem__empty" data-pos-loyalty-empty>{{ trans('loyalty::checkout.select_treatment_first') }}</p>
                        <div class="tr-pos-loyalty-redeem__form" data-pos-loyalty-form hidden>
                            <input
                                type="number"
                                min="0"
                                step="1"
                                class="tr-pos-loyalty-redeem__input"
                                data-pos-loyalty-points
                                placeholder="{{ trans('loyalty::checkout.points_to_use') }}"
                                inputmode="numeric"
                            >
                            <div class="tr-pos-loyalty-redeem__actions">
                                <button type="button" class="tr-pos-loyalty-redeem__max" data-pos-loyalty-max>{{ trans('loyalty::checkout.use_max') }}</button>
                                <button type="button" class="tr-pos-loyalty-redeem__apply" data-pos-loyalty-apply>{{ trans('loyalty::checkout.apply') }}</button>
                            </div>
                            <p class="tr-pos-loyalty-redeem__error" data-pos-loyalty-error role="status" aria-live="polite"></p>
                        </div>
                        <div class="tr-pos-loyalty-redeem__applied" data-pos-loyalty-applied hidden>
                            <span data-pos-loyalty-applied-label></span>
                            <button type="button" class="tr-pos-loyalty-redeem__remove" data-pos-loyalty-remove>{{ trans('loyalty::checkout.remove') }}</button>
                        </div>
                    </section>

                    <section class="tr-pos-loyalty-redeem tr-pos-coupon-redeem" data-pos-coupon-redeem hidden aria-labelledby="tr-pos-coupon-title">
                        <div class="tr-pos-loyalty-redeem__head">
                            <span class="tr-pos-loyalty-redeem__icon" aria-hidden="true"><i class="fa fa-ticket"></i></span>
                            <strong id="tr-pos-coupon-title">Coupon discount</strong>
                        </div>
                        <p class="tr-pos-loyalty-redeem__empty" data-pos-coupon-empty>Select a member and treatment to validate a coupon.</p>
                        <div class="tr-pos-loyalty-redeem__form" data-pos-coupon-form hidden>
                            <input type="text" class="tr-pos-loyalty-redeem__input" data-pos-coupon-input placeholder="Enter coupon code" autocomplete="off">
                            <div class="tr-pos-loyalty-redeem__actions">
                                <button type="button" class="tr-pos-loyalty-redeem__apply" data-pos-coupon-apply>Apply coupon</button>
                            </div>
                            <p class="tr-pos-loyalty-redeem__error" data-pos-coupon-error role="status" aria-live="polite"></p>
                        </div>
                        <div class="tr-pos-loyalty-redeem__applied" data-pos-coupon-applied hidden>
                            <span data-pos-coupon-applied-label></span>
                            <button type="button" class="tr-pos-loyalty-redeem__remove" data-pos-coupon-remove>Remove</button>
                        </div>
                    </section>

                    <section class="tr-pos-payment-upload tr-pos-payment-upload--embedded" aria-labelledby="tr-pos-payment-title">
                        <div class="tr-pos-payment-upload__head">
                            <div>
                                <span class="tr-pos-kicker">Payment</span>
                                <strong id="tr-pos-payment-title">Upload payment proof</strong>
                            </div>
                            <p class="tr-pos-payment-upload__method">Offline payment · receipt required</p>
                        </div>

                        <div
                            class="tr-pos-payment-dropzone"
                            data-pos-receipt-dropzone
                        >
                            <input
                                id="tr-pos-payment-receipt"
                                class="tr-pos-payment-dropzone__input"
                                type="file"
                                data-pos-payment-receipt
                                accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf"
                                required
                            >

                            <label class="tr-pos-payment-dropzone__empty" data-pos-receipt-empty for="tr-pos-payment-receipt">
                                <span class="tr-pos-payment-dropzone__icon" aria-hidden="true">
                                    <i class="fa fa-cloud-upload"></i>
                                </span>
                                <span class="tr-pos-payment-dropzone__copy">
                                    <span class="tr-pos-payment-dropzone__title">Drop receipt here or browse</span>
                                    <span class="tr-pos-payment-dropzone__hint">JPG, PNG, WEBP or PDF · maximum 10 MB</span>
                                    <span class="tr-pos-payment-dropzone__browse">Choose file</span>
                                </span>
                            </label>

                            <div class="tr-pos-payment-dropzone__preview" data-pos-receipt-preview hidden>
                                <div class="tr-pos-payment-dropzone__preview-media">
                                    <img class="tr-pos-payment-dropzone__preview-image" data-pos-receipt-image alt="" hidden>
                                    <span class="tr-pos-payment-dropzone__preview-file" data-pos-receipt-file hidden aria-hidden="true">
                                        <i class="fa fa-file-pdf-o"></i>
                                    </span>
                                </div>
                                <div class="tr-pos-payment-dropzone__preview-meta">
                                    <strong class="tr-pos-payment-dropzone__preview-name" data-pos-receipt-title>Upload payment receipt</strong>
                                    <span class="tr-pos-payment-dropzone__preview-size" data-pos-receipt-meta></span>
                                    <div class="tr-pos-payment-dropzone__actions">
                                        <label class="tr-pos-payment-dropzone__action" for="tr-pos-payment-receipt">Replace</label>
                                        <button type="button" class="tr-pos-payment-dropzone__action tr-pos-payment-dropzone__action--danger" data-pos-receipt-clear>Remove</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <button class="tr-pos-submit" type="button" data-pos-submit disabled><span>Save booking</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"></path></svg></button>
                    <p class="tr-pos-feedback" data-pos-feedback role="status" aria-live="polite"></p>
                </aside>
            </div>
        </main>

        <div class="tr-pos-mobile-scrim" data-pos-mobile-scrim data-pos-mobile-close hidden></div>
        <button type="button" class="tr-pos-mobile-checkout" data-pos-mobile-checkout aria-expanded="false">
            <span class="tr-pos-mobile-checkout__count" data-pos-mobile-count>0 items</span>
            <span class="tr-pos-mobile-checkout__copy"><strong>Booking summary</strong><small>Customer, payment &amp; save</small></span>
            <span class="tr-pos-mobile-checkout__total" data-pos-mobile-total>MYR 0.00</span>
            <i class="fa fa-chevron-up" aria-hidden="true"></i>
        </button>

        <div class="tr-pos-wizard" data-pos-wizard hidden>
            <div class="tr-pos-wizard__backdrop" data-pos-wizard-close></div>
            <section class="tr-pos-wizard__dialog" role="dialog" aria-modal="true" aria-labelledby="tr-pos-wizard-title">
                <header class="tr-pos-wizard__header">
                    <div>
                        <p class="tr-pos-kicker">Configure treatment</p>
                        <h2 id="tr-pos-wizard-title" data-pos-wizard-title>New appointment</h2>
                    </div>
                    <button type="button" class="tr-pos-wizard__close" data-pos-wizard-close aria-label="Close">×</button>
                </header>
                <div class="tr-pos-wizard__steps" data-pos-wizard-steps></div>
                <div class="tr-pos-wizard__body" data-pos-wizard-body></div>
                <p class="tr-pos-wizard__feedback" data-pos-wizard-feedback role="status" aria-live="polite"></p>
                <footer class="tr-pos-wizard__footer">
                    <button type="button" class="tr-pos-wizard__back" data-pos-wizard-back>Back</button>
                </footer>
            </section>
        </div>
    </div>
@endsection

@push('globals')
    @vite([
        'modules/TreatmentReservation/Resources/assets/admin/sass/main.scss',
        'modules/TreatmentReservation/Resources/assets/admin/js/main.js',
    ])
@endpush
