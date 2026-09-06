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
                <section class="tr-pos-customer-desk" aria-labelledby="tr-pos-customer-title">
                    <div class="tr-pos-customer-desk__head">
                        <div><span class="tr-pos-kicker">Customer & payment</span><strong id="tr-pos-customer-title">Start this booking</strong></div>
                        <div class="tr-pos-offline-badge" aria-label="Payment method: offline only">
                            <span aria-hidden="true">▣</span>
                            <div><small>Payment method</small><strong>Offline payment only</strong></div>
                        </div>
                    </div>
                    <div class="tr-pos-customer-desk__grid">
                        <div class="tr-pos-membership-lookup">
                            <label class="tr-pos-field-title" for="tr-pos-membership-id">Membership ID</label>
                            <div class="tr-pos-membership-row">
                                <input id="tr-pos-membership-id" type="search" data-pos-membership-id placeholder="Enter membership ID" inputmode="numeric" autocomplete="off">
                                <button type="button" data-pos-membership-lookup>Lookup</button>
                            </div>
                            <p class="tr-pos-membership-feedback" data-pos-membership-feedback role="status" aria-live="polite"></p>
                        </div>
                        <div>
                            <label class="tr-pos-input tr-pos-customer-search">
                                <span class="sr-only">Find customer</span>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
                                <input type="search" data-pos-customer-search placeholder="Search name, phone or email" autocomplete="off">
                            </label>
                            <div class="tr-pos-customer-results" data-pos-customer-results></div>
                            <div class="tr-pos-selected-customer" data-pos-selected-customer hidden></div>
                        </div>
                    </div>
                    <label class="tr-pos-receipt-dropzone" data-pos-receipt-dropzone for="tr-pos-payment-receipt">
                        <input
                            id="tr-pos-payment-receipt"
                            type="file"
                            data-pos-payment-receipt
                            accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf"
                            required
                        >
                        <span class="tr-pos-receipt-dropzone__icon" aria-hidden="true">↑</span>
                        <span class="tr-pos-receipt-dropzone__copy">
                            <strong data-pos-receipt-title>Upload payment receipt</strong>
                            <small data-pos-receipt-meta>Required · JPG, PNG, WEBP or PDF · maximum 10 MB</small>
                        </span>
                        <span class="tr-pos-receipt-dropzone__action">Choose file</span>
                    </label>
                    <div class="tr-pos-rewards" data-pos-rewards hidden></div>
                </section>
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
                    <div class="tr-pos-empty-cart" data-pos-empty-cart>Select a treatment from the catalog to begin.</div>
                    <div class="tr-pos-cart-item" data-pos-cart-item hidden></div>
                </div>

                <div class="tr-pos-summary">
                    <div><span>Service total</span><strong data-pos-total>MYR 0.00</strong></div>
                    <div><span>Payment</span><strong class="tr-pos-summary-payment" data-pos-payment-summary>Offline · receipt required</strong></div>
                </div>
                <button class="tr-pos-submit" type="button" data-pos-submit disabled><span>Save booking</span><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"></path></svg></button>
                <p class="tr-pos-feedback" data-pos-feedback role="status" aria-live="polite"></p>
            </aside>
        </main>

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
                    <button type="button" class="tr-pos-wizard__next" data-pos-wizard-next>Continue</button>
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
