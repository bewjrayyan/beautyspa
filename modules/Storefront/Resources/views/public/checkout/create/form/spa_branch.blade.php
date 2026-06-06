<template x-if="hasSpaBranches">
    <div class="checkout-card checkout-card-branch">
        <div class="checkout-card-header">
            <div class="checkout-card-heading">
                <span class="checkout-card-icon"><i class="las la-store"></i></span>
                <h4 class="checkout-card-title">{{ trans('storefront::checkout.spa_branch') }}</h4>
            </div>
        </div>

        <div class="form-group checkout-field-spa-branch">
            <label class="input-label" for="spa-branch-select">
                {{ trans('storefront::checkout.select_spa_branch') }} <span>*</span>
            </label>

            <div
                class="beautician-picker-dropdown"
                @click.outside="spaBranchPickerOpen = false"
            >
                <select
                    id="spa-branch-select"
                    name="spa_branch_id"
                    class="beautician-picker-native"
                    x-model="form.spa_branch_id"
                    required
                    tabindex="-1"
                    aria-hidden="true"
                >
                    <option value="">{{ trans('storefront::checkout.please_select') }}</option>

                    <template x-for="branch in spaBranches" :key="branch.id">
                        <option :value="String(branch.id)" x-text="branch.code ? `${branch.name} (${branch.code})` : branch.name"></option>
                    </template>
                </select>

                <button
                    type="button"
                    class="beautician-selected-card"
                    :class="{ 'is-open': spaBranchPickerOpen, 'is-placeholder': !selectedSpaBranch }"
                    @click="beauticianPickerOpen = false; spaBranchPickerOpen = !spaBranchPickerOpen"
                    :aria-expanded="spaBranchPickerOpen"
                    aria-haspopup="listbox"
                >
                    <template x-if="selectedSpaBranch">
                        <span class="beautician-selected-card-inner">
                            <span class="beautician-selected-avatar spa-branch-picker-icon">
                                <i class="las la-store"></i>
                            </span>
                            <span class="beautician-selected-text">
                                <span class="beautician-selected-name" x-text="selectedSpaBranch.name"></span>
                                <span
                                    class="beautician-selected-title"
                                    x-show="selectedSpaBranch.code"
                                    x-text="selectedSpaBranch.code"
                                ></span>
                            </span>
                        </span>
                    </template>

                    <template x-if="!selectedSpaBranch">
                        <span class="beautician-selected-placeholder">
                            {{ trans('storefront::checkout.please_select') }}
                        </span>
                    </template>

                    <i class="las la-angle-down beautician-selected-chevron" :class="{ 'is-open': spaBranchPickerOpen }"></i>
                </button>

                <ul
                    x-cloak
                    x-show="spaBranchPickerOpen"
                    class="beautician-picker-options"
                    role="listbox"
                >
                    <template x-for="branch in spaBranches" :key="branch.id">
                        <li role="option">
                            <button
                                type="button"
                                class="beautician-picker-option"
                                :class="{ 'is-active': String(form.spa_branch_id) === String(branch.id) }"
                                @click="selectSpaBranch(branch)"
                            >
                                <span class="beautician-selected-avatar spa-branch-picker-icon">
                                    <i class="las la-store"></i>
                                </span>
                                <span class="beautician-selected-text">
                                    <span class="beautician-selected-name" x-text="branch.name"></span>
                                    <span
                                        class="beautician-selected-title"
                                        x-show="branch.code"
                                        x-text="branch.code"
                                    ></span>
                                </span>
                            </button>
                        </li>
                    </template>
                </ul>
            </div>

            <span class="error-message" x-show="errors.has('spa_branch_id')" x-text="errors.get('spa_branch_id')"></span>
        </div>
    </div>
</template>
