@php
    $portalMode = ! empty($portalMode);
    $lockedBeautician = $lockedBeautician ?? null;
    $modalId = $modalId ?? 'tr-manual-booking-modal';
    $formId = $portalMode ? 'tr-portal-manual-booking-form' : 'tr-manual-booking-form';
    $beauticianFieldId = $portalMode ? 'tr-portal-manual-booking-beautician' : 'tr-manual-booking-beautician';
    $dateFieldId = $portalMode ? 'tr-portal-manual-booking-date' : 'tr-manual-booking-date';
    $timeFieldId = $portalMode ? 'tr-portal-manual-booking-time' : 'tr-manual-booking-time';
    $slotsRootId = $portalMode ? 'tr-portal-manual-booking-slots' : 'tr-manual-booking-slots';
    $errorBoxId = $portalMode ? 'tr-portal-manual-booking-error' : 'tr-manual-booking-error';
    $submitBtnId = $portalMode ? 'tr-portal-manual-booking-submit' : 'tr-manual-booking-submit';
    $selectScheduleMessage = $portalMode
        ? trans('treatmentreservation::admin.manual_booking.portal_select_date')
        : trans('treatmentreservation::admin.manual_booking.select_beautician_date');
    $subtitle = $portalMode
        ? trans('treatmentreservation::admin.manual_booking.portal_subtitle')
        : trans('treatmentreservation::admin.manual_booking.subtitle');
    $scheduleHelp = $portalMode
        ? trans('treatmentreservation::admin.manual_booking.portal_schedule_help')
        : trans('treatmentreservation::admin.manual_booking.section_schedule_help');
    $customersUrl = $customersUrl ?? null;
    $updateUrlTemplate = $updateUrlTemplate ?? null;
    $cancelUrlTemplate = $cancelUrlTemplate ?? null;
@endphp

<div
    class="modal fade tr-manual-booking-modal"
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
    @if ($portalMode && $lockedBeautician)
        data-portal-mode="1"
        data-fixed-beautician-id="{{ $lockedBeautician->id }}"
    @endif
>
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="{{ $formId }}" class="tr-manual-booking-form" novalidate>
                <input type="hidden" name="booking_id" value="" class="tr-manual-booking-id">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('admin::admin.buttons.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="{{ $modalId }}-title">
                        {{ trans('treatmentreservation::admin.manual_booking.title') }}
                    </h4>
                    <p class="tr-manual-booking-modal__lead">{{ $subtitle }}</p>
                </div>

                <div class="modal-body">
                    <section class="tr-manual-booking-section">
                        <header class="tr-manual-booking-section__head">
                            <h5>{{ trans('treatmentreservation::admin.manual_booking.section_schedule') }}</h5>
                            <p>{{ $scheduleHelp }}</p>
                        </header>

                        <div class="row">
                            <div class="{{ $portalMode ? 'col-md-12' : 'col-md-6' }}">
                                <div class="form-group">
                                    <label for="{{ $beauticianFieldId }}">
                                        {{ trans('treatmentreservation::admin.manual_booking.beautician_id') }}
                                    </label>

                                    @if ($portalMode && $lockedBeautician)
                                        <input type="hidden" name="beautician_id" id="{{ $beauticianFieldId }}" value="{{ $lockedBeautician->id }}">
                                        <p class="tr-manual-booking-locked-beautician">
                                            <i class="fa fa-user-md"></i>
                                            {{ $lockedBeautician->name }}
                                            @if ($lockedBeautician->job_title)
                                                <span>· {{ $lockedBeautician->job_title }}</span>
                                            @endif
                                        </p>
                                    @else
                                        <select class="form-control" id="{{ $beauticianFieldId }}" name="beautician_id" required>
                                            <option value="">{{ trans('admin::admin.form.please_select') }}</option>
                                            @foreach ($beauticians as $beautician)
                                                <option value="{{ $beautician->id }}">{{ $beautician->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            </div>

                            <div class="{{ $portalMode ? 'col-md-12' : 'col-md-6' }}">
                                <div class="form-group">
                                    <label for="{{ $dateFieldId }}">
                                        {{ trans('treatmentreservation::admin.manual_booking.appointment_date') }}
                                    </label>
                                    <input
                                        type="date"
                                        class="form-control"
                                        id="{{ $dateFieldId }}"
                                        name="appointment_date"
                                        min="{{ now()->format('Y-m-d') }}"
                                        required
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>{{ trans('treatmentreservation::admin.manual_booking.appointment_time') }}</label>
                            <p class="help-block tr-manual-booking-slot-hint">
                                {{ trans('treatmentreservation::admin.manual_booking.slot_duration_hint') }}
                            </p>
                            <input type="hidden" name="appointment_time" id="{{ $timeFieldId }}" value="">
                            <div class="tr-manual-booking-slots" id="{{ $slotsRootId }}" data-empty>
                                <p class="tr-manual-booking-slots__message">{{ $selectScheduleMessage }}</p>
                            </div>
                        </div>
                    </section>

                    <section class="tr-manual-booking-section">
                        <header class="tr-manual-booking-section__head">
                            <h5>{{ trans('treatmentreservation::admin.manual_booking.section_customer') }}</h5>
                        </header>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="{{ $modalId }}-first-name">
                                        {{ trans('treatmentreservation::admin.manual_booking.customer_first_name') }}
                                    </label>
                                    <input type="text" class="form-control" id="{{ $modalId }}-first-name" name="customer_first_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="{{ $modalId }}-last-name">
                                        {{ trans('treatmentreservation::admin.manual_booking.customer_last_name') }}
                                    </label>
                                    <input type="text" class="form-control" id="{{ $modalId }}-last-name" name="customer_last_name" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="{{ $modalId }}-phone">
                                        {{ trans('treatmentreservation::admin.manual_booking.customer_phone') }}
                                    </label>
                                    <input type="text" class="form-control" id="{{ $modalId }}-phone" name="customer_phone" required autocomplete="off">
                                    <div class="tr-manual-booking-customer-lookup" hidden>
                                        <ul class="tr-manual-booking-customer-lookup__list"></ul>
                                    </div>
                                    <p class="help-block tr-manual-booking-customer-lookup-hint">
                                        {{ trans('treatmentreservation::admin.manual_booking.customer_lookup_hint') }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="{{ $modalId }}-email">
                                        {{ trans('treatmentreservation::admin.manual_booking.customer_email') }}
                                    </label>
                                    <input type="email" class="form-control" id="{{ $modalId }}-email" name="customer_email">
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="tr-manual-booking-section">
                        <header class="tr-manual-booking-section__head">
                            <h5>{{ trans('treatmentreservation::admin.manual_booking.section_treatment') }}</h5>
                        </header>

                        <div class="form-group">
                            <label for="{{ $modalId }}-product">
                                {{ trans('treatmentreservation::admin.manual_booking.product_id') }}
                            </label>
                            <select class="form-control" id="{{ $modalId }}-product" name="product_id" required>
                                <option value="">{{ trans('admin::admin.form.please_select') }}</option>
                                @foreach ($treatmentProducts as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="{{ $modalId }}-notes">
                                {{ trans('treatmentreservation::admin.manual_booking.notes') }}
                            </label>
                            <textarea class="form-control" id="{{ $modalId }}-notes" name="notes" rows="3"></textarea>
                            <p class="help-block">{{ trans('treatmentreservation::admin.manual_booking.notes_help') }}</p>
                        </div>
                    </section>

                    <div class="alert alert-danger tr-manual-booking-error" id="{{ $errorBoxId }}" hidden></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        {{ trans('admin::admin.buttons.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" id="{{ $submitBtnId }}">
                        {{ trans('treatmentreservation::admin.manual_booking.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
