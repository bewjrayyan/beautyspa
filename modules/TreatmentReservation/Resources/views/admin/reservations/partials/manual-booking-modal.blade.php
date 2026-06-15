@php
    $portalMode = ! empty($portalMode);
    $lockedBeautician = $lockedBeautician ?? null;
    $allowBeauticianSelect = ! empty($allowBeauticianSelect);
    $defaultBeauticianId = $defaultBeauticianId ?? $lockedBeautician?->id;
    $showBeauticianSelect = ! $portalMode || $allowBeauticianSelect;
    $scheduleColumnClass = $showBeauticianSelect ? 'col-md-6' : 'col-md-12';
    $selectScheduleMessage = $portalMode
        ? ($showBeauticianSelect
            ? trans('treatmentreservation::admin.manual_booking.portal_select_beautician_date')
            : trans('treatmentreservation::admin.manual_booking.portal_select_date'))
        : trans('treatmentreservation::admin.manual_booking.select_beautician_date');
    $scheduleHelp = $portalMode
        ? ($showBeauticianSelect
            ? trans('treatmentreservation::admin.manual_booking.portal_schedule_help_select')
            : trans('treatmentreservation::admin.manual_booking.portal_schedule_help'))
        : trans('treatmentreservation::admin.manual_booking.section_schedule_help');
    $customerHelp = $portalMode
        ? trans('treatmentreservation::admin.manual_booking.portal_customer_help')
        : trans('treatmentreservation::admin.manual_booking.section_customer_help');
    $treatmentHelp = $portalMode
        ? trans('treatmentreservation::admin.manual_booking.portal_treatment_help')
        : trans('treatmentreservation::admin.manual_booking.section_treatment_help');
    $modalId = $modalId ?? 'tr-manual-booking-modal';
    $formId = $portalMode ? 'tr-portal-manual-booking-form' : 'tr-manual-booking-form';
    $beauticianFieldId = $portalMode ? 'tr-portal-manual-booking-beautician' : 'tr-manual-booking-beautician';
    $dateFieldId = $portalMode ? 'tr-portal-manual-booking-date' : 'tr-manual-booking-date';
    $timeFieldId = $portalMode ? 'tr-portal-manual-booking-time' : 'tr-manual-booking-time';
    $slotsRootId = $portalMode ? 'tr-portal-manual-booking-slots' : 'tr-manual-booking-slots';
    $errorBoxId = $portalMode ? 'tr-portal-manual-booking-error' : 'tr-manual-booking-error';
    $submitBtnId = $portalMode ? 'tr-portal-manual-booking-submit' : 'tr-manual-booking-submit';
    $subtitle = $portalMode
        ? trans('treatmentreservation::admin.manual_booking.portal_subtitle')
        : trans('treatmentreservation::admin.manual_booking.subtitle');
    $beauticianPickerOptions = $beauticianPickerOptions ?? [];
    $manualBookingProductCatalog = $manualBookingProductCatalog ?? [];
    $customersUrl = $customersUrl ?? null;
    $updateUrlTemplate = $updateUrlTemplate ?? null;
    $cancelUrlTemplate = $cancelUrlTemplate ?? null;
@endphp

<div
    class="modal fade tr-manual-booking-modal{{ $portalMode ? ' tr-manual-booking-modal--portal' : '' }}"
    id="{{ $modalId }}"
    tabindex="-1"
    role="dialog"
    aria-labelledby="{{ $modalId }}-title"
    data-slots-url="{{ $slotsUrl }}"
    data-store-url="{{ $storeUrl }}"
    @if ($customersUrl)
        data-customers-url="{{ $customersUrl }}"
    @endif
    @if ($updateUrlTemplate)
        data-update-url-template="{{ $updateUrlTemplate }}"
    @endif
    @if ($cancelUrlTemplate)
        data-cancel-url-template="{{ $cancelUrlTemplate }}"
    @endif
    @if ($defaultBeauticianId)
        data-default-beautician-id="{{ $defaultBeauticianId }}"
    @endif
    data-select-schedule="{{ $selectScheduleMessage }}"
    data-loading-slots="{{ trans('treatmentreservation::admin.manual_booking.loading_slots') }}"
    data-no-slots="{{ trans('treatmentreservation::admin.manual_booking.no_slots') }}"
    data-slot-required="{{ trans('treatmentreservation::admin.manual_booking.slot_required') }}"
    data-saving="{{ trans('treatmentreservation::admin.manual_booking.saving') }}"
    data-save="{{ trans('treatmentreservation::admin.manual_booking.save') }}"
    data-edit-title="{{ trans('treatmentreservation::admin.manual_booking.edit_title') }}"
    data-edit-save="{{ trans('treatmentreservation::admin.manual_booking.edit_save') }}"
    data-edit-saving="{{ trans('treatmentreservation::admin.manual_booking.edit_saving') }}"
    data-customer-lookup-hint="{{ trans('treatmentreservation::admin.manual_booking.customer_lookup_hint') }}"
    data-customer-lookup-empty="{{ trans('treatmentreservation::admin.manual_booking.customer_lookup_empty') }}"
    data-invalid-phone="{{ trans('core::validation.phone') }}"
    @if ($portalMode)
        data-portal-mode="1"
        @if (! $showBeauticianSelect && $lockedBeautician)
            data-fixed-beautician-id="{{ $lockedBeautician->id }}"
        @endif
    @endif
>
    <div class="modal-dialog modal-lg tr-manual-booking-modal__dialog" role="document">
        <div class="modal-content tr-manual-booking-modal__shell">
            <form id="{{ $formId }}" class="tr-manual-booking-form" novalidate enctype="multipart/form-data">
                <input type="hidden" name="booking_id" value="" class="tr-manual-booking-id">

                @if ($portalMode)
                    <div class="tr-manual-booking-modal__hero">
                        <button
                            type="button"
                            class="tr-manual-booking-modal__close"
                            data-dismiss="modal"
                            aria-label="{{ trans('admin::admin.buttons.close') }}"
                        >
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </button>

                        <div class="tr-manual-booking-modal__hero-icon" aria-hidden="true">
                            <i class="fa fa-calendar-plus-o"></i>
                        </div>

                        <div class="tr-manual-booking-modal__hero-copy">
                            <h4 class="modal-title" id="{{ $modalId }}-title">
                                {{ trans('treatmentreservation::admin.manual_booking.title') }}
                            </h4>
                            <p class="tr-manual-booking-modal__lead">{{ $subtitle }}</p>
                        </div>

                        <ol class="tr-manual-booking-modal__steps" aria-label="{{ trans('treatmentreservation::admin.manual_booking.portal_steps_label') }}">
                            <li class="is-active">
                                <span>1</span>
                                {{ trans('treatmentreservation::admin.manual_booking.section_schedule') }}
                            </li>
                            <li>
                                <span>2</span>
                                {{ trans('treatmentreservation::admin.manual_booking.section_customer') }}
                            </li>
                            <li>
                                <span>3</span>
                                {{ trans('treatmentreservation::admin.manual_booking.section_treatment') }}
                            </li>
                        </ol>
                    </div>
                @else
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('admin::admin.buttons.close') }}">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="{{ $modalId }}-title">
                            {{ trans('treatmentreservation::admin.manual_booking.title') }}
                        </h4>
                        <p class="tr-manual-booking-modal__lead">{{ $subtitle }}</p>
                    </div>
                @endif

                <div class="modal-body tr-manual-booking-modal__body">
                    <section class="tr-manual-booking-section tr-manual-booking-section--schedule">
                        <header class="tr-manual-booking-section__head">
                            @if ($portalMode)
                                <span class="tr-manual-booking-section__badge" aria-hidden="true">
                                    <i class="fa fa-clock-o"></i>
                                </span>
                            @endif
                            <div>
                                <h5>{{ trans('treatmentreservation::admin.manual_booking.section_schedule') }}</h5>
                                <p>{{ $scheduleHelp }}</p>
                            </div>
                        </header>

                        <div class="row">
                            <div class="{{ $scheduleColumnClass }}">
                                <div class="form-group tr-manual-booking-field">
                                    <label for="{{ $beauticianFieldId }}" id="{{ $beauticianFieldId }}-label">
                                        {{ trans('treatmentreservation::admin.manual_booking.beautician_id') }}
                                    </label>

                                    @if ($showBeauticianSelect)
                                        @include('treatmentreservation::admin.reservations.partials.beautician-picker', [
                                            'pickerId' => $beauticianFieldId,
                                            'pickerOptions' => $beauticianPickerOptions,
                                            'selectedId' => $defaultBeauticianId,
                                            'placeholder' => trans('treatmentreservation::admin.manual_booking.select_beautician'),
                                            'placeholderHint' => trans('treatmentreservation::admin.manual_booking.select_beautician_hint'),
                                        ])
                                    @else
                                        <input type="hidden" name="beautician_id" id="{{ $beauticianFieldId }}" value="{{ $lockedBeautician->id }}">
                                        <p class="tr-manual-booking-locked-beautician">
                                            <i class="fa fa-user-md"></i>
                                            {{ $lockedBeautician->name }}
                                            @if ($lockedBeautician->job_title)
                                                <span>· {{ $lockedBeautician->job_title }}</span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <div class="{{ $scheduleColumnClass }}">
                                <div class="form-group tr-manual-booking-field">
                                    <label for="{{ $dateFieldId }}">
                                        {{ trans('treatmentreservation::admin.manual_booking.appointment_date') }}
                                    </label>
                                    <div class="tr-manual-booking-field__control tr-manual-booking-field__control--date">
                                        <i class="fa fa-calendar" aria-hidden="true"></i>
                                        <input
                                            type="text"
                                            class="form-control tr-manual-booking-datepicker"
                                            id="{{ $dateFieldId }}"
                                            name="appointment_date"
                                            data-min-date="{{ now()->format('Y-m-d') }}"
                                            placeholder="{{ trans('treatmentreservation::admin.manual_booking.appointment_date_placeholder') }}"
                                            autocomplete="off"
                                            readonly
                                            required
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group tr-manual-booking-field tr-manual-booking-field--slots">
                            <div class="tr-manual-booking-field__label-row">
                                <label>{{ trans('treatmentreservation::admin.manual_booking.appointment_time') }}</label>
                                <span class="tr-manual-booking-field__hint">
                                    {{ trans('treatmentreservation::admin.manual_booking.slot_duration_hint') }}
                                </span>
                            </div>
                            <input type="hidden" name="appointment_time" id="{{ $timeFieldId }}" value="">
                            <div class="tr-manual-booking-slots" id="{{ $slotsRootId }}" data-empty>
                                <p class="tr-manual-booking-slots__message">{{ $selectScheduleMessage }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="tr-manual-booking-section tr-manual-booking-section--customer">
                        <header class="tr-manual-booking-section__head">
                            @if ($portalMode)
                                <span class="tr-manual-booking-section__badge" aria-hidden="true">
                                    <i class="fa fa-user"></i>
                                </span>
                            @endif
                            <div>
                                <h5>{{ trans('treatmentreservation::admin.manual_booking.section_customer') }}</h5>
                                <p>{{ $customerHelp }}</p>
                            </div>
                        </header>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group tr-manual-booking-field">
                                    <label for="{{ $modalId }}-first-name">
                                        {{ trans('treatmentreservation::admin.manual_booking.customer_first_name') }}
                                    </label>
                                    <div class="tr-manual-booking-field__control">
                                        <i class="fa fa-user-o" aria-hidden="true"></i>
                                        <input type="text" class="form-control" id="{{ $modalId }}-first-name" name="customer_first_name" required autocomplete="given-name">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group tr-manual-booking-field">
                                    <label for="{{ $modalId }}-last-name">
                                        {{ trans('treatmentreservation::admin.manual_booking.customer_last_name') }}
                                    </label>
                                    <div class="tr-manual-booking-field__control">
                                        <i class="fa fa-user-o" aria-hidden="true"></i>
                                        <input type="text" class="form-control" id="{{ $modalId }}-last-name" name="customer_last_name" required autocomplete="family-name">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group tr-manual-booking-field">
                                    <label for="{{ $modalId }}-phone">
                                        {{ trans('treatmentreservation::admin.manual_booking.customer_phone') }}
                                    </label>
                                    <div class="tr-manual-booking-field__control tr-manual-booking-field__control--phone">
                                        <input
                                            type="tel"
                                            class="form-control modern-phone-input"
                                            id="{{ $modalId }}-phone"
                                            name="customer_phone"
                                            required
                                            autocomplete="tel"
                                            data-preferred-countries="my,sg"
                                        >
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group tr-manual-booking-field">
                                    <label for="{{ $modalId }}-email">
                                        {{ trans('treatmentreservation::admin.manual_booking.customer_email') }}
                                    </label>
                                    <div class="tr-manual-booking-field__control">
                                        <i class="fa fa-envelope-o" aria-hidden="true"></i>
                                        <input type="email" class="form-control" id="{{ $modalId }}-email" name="customer_email" autocomplete="email">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tr-manual-booking-customer-lookup" hidden>
                            <ul class="tr-manual-booking-customer-lookup__list"></ul>
                        </div>
                        <p class="help-block tr-manual-booking-customer-lookup-hint">
                            {{ trans('treatmentreservation::admin.manual_booking.customer_lookup_hint') }}
                        </p>
                    </section>

                    <section
                        class="tr-manual-booking-section tr-manual-booking-section--treatment tr-manual-booking-treatment"
                        data-empty-products="{{ trans('treatmentreservation::admin.manual_booking.products_empty') }}"
                        data-has-variations-label="{{ trans('treatmentreservation::admin.manual_booking.has_variations') }}"
                        data-has-options-label="{{ trans('treatmentreservation::admin.manual_booking.has_options') }}"
                        data-select-product="{{ trans('treatmentreservation::admin.manual_booking.select_product_config') }}"
                        data-no-config-needed="{{ trans('treatmentreservation::admin.manual_booking.no_product_config') }}"
                        data-variations-label="{{ trans('treatmentreservation::admin.manual_booking.variations_label') }}"
                        data-options-label="{{ trans('treatmentreservation::admin.manual_booking.options_label') }}"
                        data-choose-option="{{ trans('treatmentreservation::admin.manual_booking.choose_option') }}"
                        data-product-required="{{ trans('treatmentreservation::admin.manual_booking.product_required') }}"
                        data-option-required="{{ trans('cart::validation.this_field_is_required') }}"
                        data-view-receipt="{{ trans('treatmentreservation::admin.manual_booking.view_receipt') }}"
                    >
                        <header class="tr-manual-booking-section__head">
                            @if ($portalMode)
                                <span class="tr-manual-booking-section__badge" aria-hidden="true">
                                    <i class="fa fa-heart"></i>
                                </span>
                            @endif
                            <div>
                                <h5>{{ trans('treatmentreservation::admin.manual_booking.section_treatment') }}</h5>
                                <p>{{ $treatmentHelp }}</p>
                            </div>
                        </header>

                        <input type="hidden" name="product_id" value="">

                        <div class="tr-manual-booking-products">
                            <div class="tr-manual-booking-products__toolbar">
                                <div class="tr-manual-booking-field__control">
                                    <i class="fa fa-search" aria-hidden="true"></i>
                                    <input
                                        type="search"
                                        class="form-control tr-manual-booking-products__search"
                                        placeholder="{{ trans('treatmentreservation::admin.manual_booking.search_treatments') }}"
                                        autocomplete="off"
                                    >
                                </div>
                                <p class="tr-manual-booking-products__price" hidden></p>
                            </div>

                            <div class="tr-manual-booking-products__list"></div>
                            <div class="tr-manual-booking-products__config"></div>
                        </div>

                        <div class="tr-manual-booking-payment">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group tr-manual-booking-field">
                                        <label for="{{ $modalId }}-payment-status">
                                            {{ trans('treatmentreservation::admin.manual_booking.payment_status') }}
                                        </label>
                                        <div class="tr-manual-booking-field__control">
                                            <i class="fa fa-credit-card" aria-hidden="true"></i>
                                            <select class="form-control" id="{{ $modalId }}-payment-status" name="payment_status" required>
                                                @foreach (\Modules\TreatmentReservation\Entities\TreatmentBooking::manualPaymentStatuses() as $paymentStatus)
                                                    <option value="{{ $paymentStatus }}" @selected($paymentStatus === \Modules\TreatmentReservation\Entities\TreatmentBooking::PAYMENT_DEPOSIT)>
                                                        {{ trans('treatmentreservation::admin.manual_booking.payment_statuses.' . $paymentStatus) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group tr-manual-booking-field">
                                        <label for="{{ $modalId }}-payment-receipt">
                                            {{ trans('treatmentreservation::admin.manual_booking.payment_receipt') }}
                                        </label>
                                        <div class="tr-manual-booking-receipt">
                                            <input
                                                type="file"
                                                class="form-control"
                                                id="{{ $modalId }}-payment-receipt"
                                                name="payment_receipt"
                                                accept="image/*,.pdf"
                                            >
                                            <p class="help-block tr-manual-booking-receipt__hint">
                                                {{ trans('treatmentreservation::admin.manual_booking.receipt_help') }}
                                            </p>
                                            <div class="tr-manual-booking-receipt__preview" hidden></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group tr-manual-booking-field">
                            <label for="{{ $modalId }}-notes">
                                {{ trans('treatmentreservation::admin.manual_booking.notes') }}
                            </label>
                            <div class="tr-manual-booking-field__control tr-manual-booking-field__control--textarea">
                                <textarea class="form-control" id="{{ $modalId }}-notes" name="notes" rows="3" placeholder="{{ trans('treatmentreservation::admin.manual_booking.notes_help') }}"></textarea>
                            </div>
                        </div>
                    </section>

                    <script type="application/json" class="tr-manual-booking-product-catalog-data">@json($manualBookingProductCatalog)</script>

                    <div class="alert alert-danger tr-manual-booking-error" id="{{ $errorBoxId }}" hidden></div>
                </div>

                <div class="modal-footer tr-manual-booking-modal__footer">
                    <button type="button" class="btn btn-default tr-manual-booking-modal__cancel" data-dismiss="modal">
                        {{ trans('admin::admin.buttons.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary tr-manual-booking-modal__submit" id="{{ $submitBtnId }}">
                        <i class="fa fa-check" aria-hidden="true"></i>
                        <span>{{ trans('treatmentreservation::admin.manual_booking.save') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
