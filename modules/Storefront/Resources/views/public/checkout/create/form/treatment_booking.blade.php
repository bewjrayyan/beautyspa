<style>
.checkout-schedule-toggle { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 8px; }
.checkout-schedule-option { display: flex; align-items: flex-start; gap: 10px; margin: 0; padding: 12px 14px; border: 1.5px solid #d9c7cf; border-radius: 12px; background: #fff; cursor: pointer; font-size: 13px; line-height: 1.35; color: #413648; }
.checkout-schedule-option:has(input:checked) { border-color: #f274ac; background: #fff4f8; color: #6f2948; font-weight: 600; }
.checkout-schedule-option input { position: absolute; opacity: 0; width: 0; height: 0; margin: 0; pointer-events: none; }
.checkout-schedule-option__tick { display: inline-flex; flex-shrink: 0; align-items: center; justify-content: center; width: 20px; height: 20px; margin-top: 1px; color: transparent; background: #fff; border: 2px solid #d1d5db; border-radius: 5px; transition: all 0.15s ease; }
.checkout-schedule-option__tick i { font-size: 12px; line-height: 1; opacity: 0; transform: scale(0.85); transition: opacity 0.15s ease, transform 0.15s ease; }
.checkout-schedule-option:has(input:checked) .checkout-schedule-option__tick { color: #fff; background: #f274ac; border-color: #f274ac; }
.checkout-schedule-option:has(input:checked) .checkout-schedule-option__tick i { opacity: 1; transform: scale(1); }
.checkout-treatment-card { border: 1px solid #eadfe4; border-radius: 14px; padding: 16px; margin-bottom: 14px; background: #fffafc; }
.checkout-treatment-card__header { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
.checkout-treatment-card__number { display: inline-flex; flex-shrink: 0; align-items: center; justify-content: center; width: 28px; height: 28px; font-size: 13px; font-weight: 700; line-height: 1; color: #fff; background: #f274ac; border-radius: 50%; }
.checkout-treatment-card__title { flex: 1; min-width: 0; font-size: 15px; font-weight: 600; color: #6f2948; margin: 0; }
.checkout-treatment-card__identity { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 8px; padding-top: 2px; }
.checkout-treatment-card__variants { margin: 0; padding: 0 0 0 2px; list-style: none; }
.checkout-treatment-card__variant { display: flex; flex-wrap: wrap; gap: 4px 6px; margin: 0; font-size: 13px; line-height: 1.4; color: #6b7280; }
.checkout-treatment-card__variant label { margin: 0; font-weight: 600; color: #6b7280; }
.checkout-treatment-card__variant span { font-weight: 600; color: #4b5563; }
.checkout-treatment-card .is-disabled-field { opacity: 0.55; }
.checkout-treatment-card .is-disabled-field .checkout-input-wrap { pointer-events: auto; cursor: pointer; }
.checkout-treatment-card .is-disabled-field .form-control:disabled { pointer-events: none; }
.checkout-treatment-card .is-disabled-field .checkout-appointment-slots { pointer-events: auto; cursor: pointer; }
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
            <div class="checkout-treatment-card" :data-treatment-line-index="lineIndex">
                <div class="checkout-treatment-card__header">
                    <span
                        class="checkout-treatment-card__number"
                        x-text="lineIndex + 1"
                        :aria-label="lineHeadingText(line, lineIndex)"
                    ></span>
                    <div class="checkout-treatment-card__identity">
                        <h5 class="checkout-treatment-card__title" x-text="line.name"></h5>

                        <ul
                            class="checkout-treatment-card__variants"
                            x-cloak
                            x-show="lineHasVariants(line)"
                        >
                            <template x-for="(variant, variantIndex) in lineVariants(line)" :key="`${line.cart_item_id || line.product_id}-variant-${variantIndex}`">
                                <li class="checkout-treatment-card__variant">
                                    <label x-text="variant.name ? `${variant.name}:` : '{{ trans('storefront::checkout.selected_option') }}:'"></label>
                                    <span x-text="variant.value || variant.name"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <div class="form-group checkout-field-beautician">
                    <label class="input-label">
                        {{ trans('storefront::checkout.beautician') }} <span>*</span>
                    </label>

                    <div class="beautician-picker-dropdown" @click.outside="line.pickerOpen = false">
                        <button
                            type="button"
                            class="beautician-selected-card"
                            :class="{ 'is-open': line.pickerOpen, 'is-placeholder': !lineBeautician(line), 'is-disabled': hasSpaBranches && !hasSpaBranchSelected }"
                            @click="handleLineBeauticianPickerClick(lineIndex)"
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
                        <label class="checkout-schedule-option">
                            <input type="radio" :name="`schedule_later_${lineIndex}`" value="0" x-model="line.schedule_later" @change="onLineScheduleModeChange(lineIndex)">
                            <span class="checkout-schedule-option__tick" aria-hidden="true"><i class="las la-check"></i></span>
                            <span>{{ trans('storefront::checkout.schedule_now') }}</span>
                        </label>
                        <label class="checkout-schedule-option" x-show="canScheduleLaterForLine(line)" x-cloak>
                            <input type="radio" :name="`schedule_later_${lineIndex}`" value="1" x-model="line.schedule_later" @change="onLineScheduleModeChange(lineIndex)">
                            <span class="checkout-schedule-option__tick" aria-hidden="true"><i class="las la-check"></i></span>
                            <span>{{ trans('storefront::checkout.schedule_later_tba') }}</span>
                        </label>
                    </div>
                    <p class="help-block" x-show="isLineScheduleLater(line)" x-cloak>
                        {{ trans('storefront::checkout.schedule_later_help') }}
                    </p>
                </div>

                <div class="checkout-appointment-row" x-show="isLineScheduleNow(line)" x-cloak>
                    <div class="form-group checkout-field-icon" :class="{ 'is-disabled-field': !lineCanUseAppointmentFields(line) || line.loadingDates || (line.datesResolved && !line.availableDates.length) }">
                        <label class="input-label">{{ trans('storefront::checkout.appointment_date') }} <span>*</span></label>
                        <div
                            class="checkout-input-wrap"
                            @click="lineCanUseAppointmentFields(line) ? openLineDatePicker(lineIndex) : promptTreatmentBookingStep(lineIndex, { forAppointment: true })"
                        >
                            <i class="las la-calendar"></i>
                            <input
                                type="text"
                                class="form-control checkout-datepicker"
                                :data-line-index="lineIndex"
                                placeholder="{{ trans('storefront::checkout.appointment_date') }}"
                                readonly
                                :required="isLineScheduleNow(line)"
                                :aria-disabled="!line.beautician_id || line.loadingDates || !line.availableDates.length"
                            >
                        </div>
                        <p class="help-block" x-show="lineNeedsBranchFirst()" x-cloak>
                            {{ trans('storefront::checkout.select_spa_branch_before_date') }}
                        </p>
                        <p class="help-block" x-show="!lineNeedsBranchFirst() && lineNeedsBeautician(line)" x-cloak>
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

                    <div
                        class="form-group checkout-field-appointment-time"
                        :class="{ 'is-disabled-field': !lineCanUseAppointmentFields(line) || !line.appointment_date }"
                    >
                        <label class="input-label">{{ trans('storefront::checkout.appointment_time') }} <span>*</span></label>
                        <div
                            class="checkout-appointment-slots"
                            @click="(!lineCanUseAppointmentFields(line) || !line.appointment_date) && promptLineAppointmentTime(lineIndex)"
                        >
                            <template x-if="lineAppointmentSlotsPlaceholder(line)">
                                <p class="checkout-appointment-slots__message" x-text="lineAppointmentSlotsPlaceholder(line)"></p>
                            </template>
                            <template x-for="opt in lineAppointmentSlotGrid(line)" :key="`${lineIndex}-${opt.time}-${opt.status}`">
                                <button
                                    type="button"
                                    class="checkout-appointment-slot"
                                    :class="{
                                        'is-selected': String(line.appointment_time || '').slice(0, 5) === String(opt.time || '').slice(0, 5),
                                        'is-booked': opt.status === 'booked',
                                        'is-unavailable': opt.status === 'unavailable',
                                    }"
                                    :disabled="opt.status !== 'available'"
                                    @click.stop="selectLineAppointmentSlot(lineIndex, opt)"
                                    x-text="formatSlotOptionLabel(opt)"
                                ></button>
                            </template>
                        </div>
                        <input
                            type="hidden"
                            :name="`treatment_bookings[${lineIndex}][appointment_time]`"
                            x-model="line.appointment_time"
                        >
                        <p class="help-block text-danger" x-show="line.slotConflict" x-cloak>
                            {{ trans('storefront::checkout.appointment_time_conflicts_sibling') }}
                        </p>
                        <span class="error-message" x-show="errors.has(`treatment_bookings.${lineIndex}.appointment_time`)" x-text="errors.get(`treatment_bookings.${lineIndex}.appointment_time`)"></span>
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
