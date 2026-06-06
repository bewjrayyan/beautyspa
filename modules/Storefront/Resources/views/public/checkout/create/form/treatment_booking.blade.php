<template x-if="requiresTreatmentBooking">
    <div
        class="checkout-card checkout-card-treatment treatment-booking-section"
        x-init="initAppointmentPickers()"
    >
        <div class="checkout-card-header">
            <div class="checkout-card-heading">
                <span class="checkout-card-icon"><i class="las la-calendar-alt"></i></span>
                <h4 class="checkout-card-title">{{ trans('storefront::checkout.treatment_booking') }}</h4>
            </div>
        </div>

        <div class="form-group checkout-field-beautician">
            <label class="input-label" for="beautician-select">
                {{ trans('storefront::checkout.beautician') }} <span>*</span>
            </label>

            <div
                class="beautician-picker-dropdown"
                @click.outside="beauticianPickerOpen = false"
            >
                <select
                    id="beautician-select"
                    name="beautician_id"
                    class="beautician-picker-native"
                    x-model="form.beautician_id"
                    required
                    tabindex="-1"
                    aria-hidden="true"
                >
                    <option value="">{{ trans('storefront::checkout.select_beautician') }}</option>
                    <template x-for="beautician in availableBeauticians" :key="beautician.id">
                        <option :value="beautician.id" x-text="beautician.name"></option>
                    </template>
                </select>

                <button
                    type="button"
                    class="beautician-selected-card"
                    :class="{ 'is-open': beauticianPickerOpen, 'is-placeholder': !selectedBeautician, 'is-disabled': hasSpaBranches && !hasSpaBranchSelected }"
                    @click="spaBranchPickerOpen = false; hasSpaBranches && !hasSpaBranchSelected ? null : (beauticianPickerOpen = !beauticianPickerOpen)"
                    :disabled="hasSpaBranches && !hasSpaBranchSelected"
                    :aria-expanded="beauticianPickerOpen"
                    aria-haspopup="listbox"
                >
                    <template x-if="selectedBeautician">
                        <span class="beautician-selected-card-inner">
                            <img
                                x-show="selectedBeautician.profile_image"
                                :src="selectedBeautician.profile_image"
                                :alt="selectedBeautician.name"
                                class="beautician-selected-avatar beautician-selected-avatar--photo"
                            >
                            <span
                                x-show="!selectedBeautician.profile_image"
                                class="beautician-selected-avatar"
                                :style="{ backgroundColor: selectedBeautician.profile_color || '#f274ac' }"
                                x-text="selectedBeautician.name.charAt(0).toUpperCase()"
                            ></span>
                            <span class="beautician-selected-text">
                                <span class="beautician-selected-name" x-text="selectedBeautician.name"></span>
                                <span
                                    class="beautician-selected-title"
                                    x-show="selectedBeautician.job_title"
                                    x-text="selectedBeautician.job_title"
                                ></span>
                            </span>
                        </span>
                    </template>

                    <template x-if="!selectedBeautician">
                        <span class="beautician-selected-placeholder">
                            <template x-if="hasSpaBranches && !hasSpaBranchSelected">
                                {{ trans('storefront::checkout.select_spa_branch_first') }}
                            </template>
                            <template x-if="!hasSpaBranches || hasSpaBranchSelected">
                                {{ trans('storefront::checkout.select_beautician') }}
                            </template>
                        </span>
                    </template>

                    <i class="las la-angle-down beautician-selected-chevron" :class="{ 'is-open': beauticianPickerOpen }"></i>
                </button>

                <ul
                    x-cloak
                    x-show="beauticianPickerOpen && (!hasSpaBranches || hasSpaBranchSelected)"
                    class="beautician-picker-options"
                    role="listbox"
                >
                    <template x-if="hasSpaBranchSelected && !availableBeauticians.length">
                        <li class="beautician-picker-empty" role="presentation">
                            {{ trans('storefront::checkout.no_beauticians_at_branch') }}
                        </li>
                    </template>

                    <template x-for="beautician in availableBeauticians" :key="beautician.id">
                        <li role="option">
                            <button
                                type="button"
                                class="beautician-picker-option"
                                :class="{ 'is-active': String(form.beautician_id) === String(beautician.id) }"
                                @click="selectBeautician(beautician)"
                            >
                                <img
                                    x-show="beautician.profile_image"
                                    :src="beautician.profile_image"
                                    :alt="beautician.name"
                                    class="beautician-selected-avatar beautician-selected-avatar--photo"
                                >
                                <span
                                    x-show="!beautician.profile_image"
                                    class="beautician-selected-avatar"
                                    :style="{ backgroundColor: beautician.profile_color || '#6366f1' }"
                                    x-text="beautician.name.charAt(0).toUpperCase()"
                                ></span>
                                <span class="beautician-selected-text">
                                    <span class="beautician-selected-name" x-text="beautician.name"></span>
                                    <span
                                        class="beautician-selected-title"
                                        x-show="beautician.job_title"
                                        x-text="beautician.job_title"
                                    ></span>
                                </span>
                            </button>
                        </li>
                    </template>
                </ul>
            </div>

            <span class="error-message" x-show="errors.has('beautician_id')" x-text="errors.get('beautician_id')"></span>
        </div>

        <div class="row checkout-appointment-row">
            <div class="col-md-9">
                <div class="form-group checkout-field-icon">
                    <label for="appointment-date" class="input-label">
                        {{ trans('storefront::checkout.appointment_date') }} <span>*</span>
                    </label>

                    <div class="checkout-input-wrap">
                        <i class="las la-calendar"></i>
                        <input
                            type="text"
                            id="appointment-date"
                            name="appointment_date"
                            class="form-control checkout-datepicker"
                            placeholder="{{ trans('storefront::checkout.appointment_date') }}"
                            readonly
                            required
                        >
                    </div>

                    <span class="error-message" x-show="errors.has('appointment_date')" x-text="errors.get('appointment_date')"></span>
                </div>
            </div>

            <div class="col-md-9">
                <div class="form-group checkout-field-icon">
                    <label for="appointment-time" class="input-label">
                        {{ trans('storefront::checkout.appointment_time') }} <span>*</span>
                    </label>

                    <template x-if="availabilitySlotsUrl">
                        <div>
                            <div class="checkout-input-wrap">
                                <i class="las la-clock"></i>
                                <select
                                    id="appointment-time"
                                    name="appointment_time"
                                    class="form-control"
                                    x-model="form.appointment_time"
                                    :disabled="loadingAppointmentSlots || !appointmentSlots.length"
                                    required
                                >
                                    <template x-if="loadingAppointmentSlots">
                                        <option value="" disabled x-text="slotLabels.loading || 'Loading…'"></option>
                                    </template>
                                    <template x-if="!loadingAppointmentSlots && !appointmentSlots.length">
                                        <option value="" disabled x-text="slotLabels.empty || 'No available times'"></option>
                                    </template>
                                    <template x-for="slot in appointmentSlots" :key="slot">
                                        <option :value="slot" x-text="formatAppointmentSlot(slot)"></option>
                                    </template>
                                </select>
                            </div>

                            <p class="help-block" x-show="!loadingAppointmentSlots && !appointmentSlots.length && form.beautician_id && form.appointment_date">
                                <span x-text="slotLabels.empty || 'No available times on this date.'"></span>
                            </p>
                        </div>
                    </template>

                    <template x-if="!availabilitySlotsUrl">
                        <div class="checkout-input-wrap">
                            <i class="las la-clock"></i>
                            <input
                                type="text"
                                id="appointment-time"
                                name="appointment_time"
                                class="form-control checkout-timepicker"
                                placeholder="{{ trans('storefront::checkout.appointment_time') }}"
                                readonly
                                required
                            >
                        </div>
                    </template>

                    <span class="error-message" x-show="errors.has('appointment_time')" x-text="errors.get('appointment_time')"></span>
                </div>
            </div>
        </div>

        <div class="form-group order-notes order-notes--modern">
            <label for="order-note">
                {{ trans('checkout::attributes.order_note') }}
            </label>

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
