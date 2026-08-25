<style>
.checkout-schedule-toggle { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px; }
.checkout-schedule-option { display: flex; align-items: flex-start; gap: 10px; margin: 0; padding: 12px 14px; border: 1.5px solid #d9c7cf; border-radius: 12px; background: #fff; cursor: pointer; font-size: 13px; line-height: 1.35; color: #413648; }
.checkout-schedule-option:has(input:checked) { border-color: #f274ac; background: #fff4f8; color: #6f2948; font-weight: 600; }
.checkout-schedule-option input { position: absolute; opacity: 0; width: 0; height: 0; margin: 0; pointer-events: none; }
.checkout-schedule-option__tick { display: inline-flex; flex-shrink: 0; align-items: center; justify-content: center; width: 20px; height: 20px; margin-top: 1px; color: transparent; background: #fff; border: 2px solid #d1d5db; border-radius: 5px; transition: all 0.15s ease; }
.checkout-schedule-option__tick i { font-size: 12px; line-height: 1; }
.checkout-schedule-option:has(input:checked) .checkout-schedule-option__tick { color: #fff; background: #f274ac; border-color: #f274ac; }
.checkout-treatment-card { border: 1px solid #eadfe4; border-radius: 14px; padding: 16px; margin-bottom: 14px; background: #fffafc; }
.checkout-treatment-card__title { font-size: 15px; font-weight: 600; color: #6f2948; margin: 0 0 12px; }
.checkout-treatment-card .is-disabled-field { opacity: 0.55; pointer-events: none; }
@media (max-width: 640px) { .checkout-schedule-toggle { grid-template-columns: 1fr; } }
</style>
<template x-if="requiresTreatmentBooking">
<div
        class="checkout-card checkout-card-treatment treatment-booking-section"
        x-init="$nextTick(() => bootTreatmentSchedules())"
    >
        <div class="checkout-card-header">
            <div class="checkout-card-heading">
                <span class="checkout-card-icon"><i class="las la-calendar-alt"></i></span>
                <h4 class="checkout-card-title">{{ trans('storefront::checkout.treatment_booking') }}</h4>
            </div>
        </div>

        <template x-for="(line, lineIndex) in treatmentSchedules" :key="line.cart_item_id || ('p-' + line.product_id + '-' + lineIndex)">
            <div class="checkout-treatment-card">
                <h5 class="checkout-treatment-card__title" x-text="line.name"></h5>

                <div class="form-group checkout-field-beautician">
                    <label class="input-label">
                        {{ trans('storefront::checkout.beautician') }} <span>*</span>
                    </label>

                    <div class="beautician-picker-dropdown" @click.outside="line.pickerOpen = false">
                        <button
                            type="button"
                            class="beautician-selected-card"
                            :class="{ 'is-open': line.pickerOpen, 'is-placeholder': !lineBeautician(line), 'is-disabled': hasSpaBranches && !hasSpaBranchSelected }"
                            @click="spaBranchPickerOpen = false; hasSpaBranches && !hasSpaBranchSelected ? null : (line.pickerOpen = !line.pickerOpen)"
                            :disabled="hasSpaBranches && !hasSpaBranchSelected"
                            :aria-expanded="line.pickerOpen"
                            aria-haspopup="listbox"
                        >
                            <span x-show="lineBeautician(line)" x-cloak class="beautician-selected-card-inner">
                                <img
                                    x-show="lineBeautician(line)?.profile_image"
                                    :src="lineBeautician(line)?.profile_image"
                                    :alt="lineBeautician(line)?.name"
                                    class="beautician-selected-avatar beautician-selected-avatar--photo"
                                >
                                <span
                                    x-show="lineBeautician(line) && !lineBeautician(line).profile_image"
                                    class="beautician-selected-avatar"
                                    :style="{ backgroundColor: lineBeautician(line)?.profile_color || '#f274ac' }"
                                    x-text="lineBeautician(line)?.name?.charAt(0)?.toUpperCase()"
                                ></span>
                                <span class="beautician-selected-text">
                                    <span class="beautician-selected-name" x-text="lineBeautician(line)?.name"></span>
                                    <span class="beautician-selected-title" x-show="lineBeautician(line)?.job_title" x-text="lineBeautician(line)?.job_title"></span>
                                </span>
                            </span>
                            <span x-show="!lineBeautician(line)" x-cloak class="beautician-selected-placeholder" x-text="beauticianPlaceholderText"></span>
                            <i class="las la-angle-down beautician-selected-chevron" :class="{ 'is-open': line.pickerOpen }"></i>
                        </button>

                        <ul x-cloak x-show="line.pickerOpen && (!hasSpaBranches || hasSpaBranchSelected)" class="beautician-picker-options" role="listbox">
                            <li x-cloak x-show="hasSpaBranchSelected && !availableBeauticians.length" class="beautician-picker-empty" role="presentation" x-text="slotLabels.no_beauticians_at_branch"></li>
                            <template x-for="beautician in availableBeauticians" :key="beautician.id">
                                <li role="option">
                                    <button type="button" class="beautician-picker-option" :class="{ 'is-active': String(line.beautician_id) === String(beautician.id) }" @click="selectLineBeautician(lineIndex, beautician)">
                                        <img x-show="beautician.profile_image" :src="beautician.profile_image" :alt="beautician.name" class="beautician-selected-avatar beautician-selected-avatar--photo">
                                        <span x-show="!beautician.profile_image" class="beautician-selected-avatar" :style="{ backgroundColor: beautician.profile_color || '#6366f1' }" x-text="beautician.name.charAt(0).toUpperCase()"></span>
                                        <span class="beautician-selected-text">
                                            <span class="beautician-selected-name" x-text="beautician.name"></span>
                                            <span class="beautician-selected-title" x-show="beautician.job_title" x-text="beautician.job_title"></span>
                                        </span>
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </div>
                    <span class="error-message" x-show="errors.has(`treatment_bookings.${lineIndex}.beautician_id`)" x-text="errors.get(`treatment_bookings.${lineIndex}.beautician_id`)"></span>
                </div>

                <div class="form-group checkout-schedule-mode">
                    <div class="checkout-schedule-toggle" role="group" aria-label="{{ trans('storefront::checkout.schedule_mode') }}">
                        <label class="checkout-schedule-option" x-show="canScheduleLaterForLine(line)" x-cloak>
                            <input type="radio" :name="`schedule_later_${lineIndex}`" value="1" x-model="line.schedule_later" @change="onLineScheduleModeChange(lineIndex)">
                            <span class="checkout-schedule-option__tick" aria-hidden="true"><i class="las la-check"></i></span>
                            <span>{{ trans('storefront::checkout.schedule_later_tba') }}</span>
                        </label>
                        <label class="checkout-schedule-option">
                            <input type="radio" :name="`schedule_later_${lineIndex}`" value="0" x-model="line.schedule_later" @change="onLineScheduleModeChange(lineIndex)">
                            <span class="checkout-schedule-option__tick" aria-hidden="true"><i class="las la-check"></i></span>
                            <span>{{ trans('storefront::checkout.schedule_now') }}</span>
                        </label>
                    </div>
                    <p class="help-block" x-show="isLineScheduleLater(line)" x-cloak>
                        {{ trans('storefront::checkout.schedule_later_help') }}
                    </p>
                </div>

                <div class="row checkout-appointment-row" x-show="!isLineScheduleLater(line)" x-cloak>
                    <div class="col-md-9">
                        <div class="form-group checkout-field-icon" :class="{ 'is-disabled-field': !line.beautician_id || line.loadingDates || (line.datesResolved && !line.availableDates.length) }">
                            <label class="input-label">{{ trans('storefront::checkout.appointment_date') }} <span>*</span></label>
                            <div
                                class="checkout-input-wrap"
                                @click="openLineDatePicker(lineIndex)"
                            >
                                <i class="las la-calendar"></i>
                                <input
                                    type="text"
                                    class="form-control checkout-datepicker"
                                    :data-line-index="lineIndex"
                                    placeholder="{{ trans('storefront::checkout.appointment_date') }}"
                                    readonly
                                    :required="!isLineScheduleLater(line)"
                                    :aria-disabled="!line.beautician_id || line.loadingDates || !line.availableDates.length"
                                >
                            </div>
                            <p class="help-block" x-show="!line.beautician_id" x-cloak>
                                {{ trans('storefront::checkout.select_beautician_before_date') }}
                            </p>
                            <p class="help-block" x-show="line.beautician_id && line.loadingDates" x-cloak>
                                {{ trans('storefront::checkout.loading_appointment_dates') }}
                            </p>
                            <p class="help-block text-danger" x-show="line.beautician_id && line.datesLoadFailed" x-cloak>
                                {{ trans('storefront::checkout.appointment_dates_load_failed') }}
                            </p>
                            <p class="help-block" x-show="line.beautician_id && line.datesResolved && !line.loadingDates && !line.datesLoadFailed && !line.availableDates.length && line.dateOptions.some(o => o.status === 'fully_booked')" x-cloak>
                                {{ trans('storefront::checkout.no_available_appointment_dates_all_booked') }}
                            </p>
                            <p class="help-block" x-show="line.beautician_id && line.datesResolved && !line.loadingDates && !line.datesLoadFailed && !line.availableDates.length && !line.dateOptions.some(o => o.status === 'fully_booked')" x-cloak>
                                {{ trans('storefront::checkout.no_available_appointment_dates') }}
                            </p>
                            <span class="error-message" x-show="errors.has(`treatment_bookings.${lineIndex}.appointment_date`)" x-text="errors.get(`treatment_bookings.${lineIndex}.appointment_date`)"></span>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="form-group checkout-field-icon" :class="{ 'is-disabled-field': !line.beautician_id || !line.appointment_date }">
                            <label class="input-label">{{ trans('storefront::checkout.appointment_time') }} <span>*</span></label>
                            <div class="checkout-input-wrap">
                                <i class="las la-clock"></i>
                                <select
                                    class="form-control"
                                    x-model="line.appointment_time"
                                    @change="onLineAppointmentTimeChange(lineIndex)"
                                    :disabled="isLineScheduleLater(line) || !line.beautician_id || !line.appointment_date || (!line.slots.length && !line.appointment_time && !line.loadingSlots)"
                                    :required="!isLineScheduleLater(line)"
                                >
                                    <template x-for="opt in lineAppointmentTimeOptions(line)" :key="opt.key">
                                        <option :value="opt.value" :disabled="opt.disabled" x-text="opt.label"></option>
                                    </template>
                                </select>
                            </div>
                            <p class="help-block text-muted" x-show="line.beautician_id && line.appointment_date && lineAvailableScheduleTimes(line)" x-cloak>
                                <span>{{ trans('storefront::checkout.appointment_times_bookable') }} </span>
                                <span x-text="lineAvailableScheduleTimes(line)"></span>
                            </p>
                            <p class="help-block text-danger" x-show="line.slotConflict" x-cloak>
                                {{ trans('storefront::checkout.appointment_time_conflicts_sibling') }}
                            </p>
                            <p class="help-block" x-show="line.beautician_id && line.appointment_date && !line.loadingSlots && !line.slots.length" x-cloak>
                                <span x-text="slotLabels.empty || 'No available times on this date.'"></span>
                            </p>
                            <span class="error-message" x-show="errors.has(`treatment_bookings.${lineIndex}.appointment_time`)" x-text="errors.get(`treatment_bookings.${lineIndex}.appointment_time`)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="form-group order-notes order-notes--modern">
            <label for="order-note">{{ trans('checkout::attributes.order_note') }}</label>
            <textarea
                name="order_note"
                cols="30"
                rows="4"
                id="order-note"
                class="form-control"
                placeholder="{{ trans('storefront::checkout.special_note_for_delivery') }}"
                x-model="form.order_note"
            ></textarea>
        </div>
    </div>
</template>
