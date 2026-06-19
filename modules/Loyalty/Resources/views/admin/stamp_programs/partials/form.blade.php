@php
    use Modules\Loyalty\Support\LoyaltyLang;

    $lt = fn (string $key): string => LoyaltyLang::get($key);
@endphp

<div class="loyalty-tier-form__sections">
    <section class="loyalty-page-card loyalty-tier-form__section">
        <div class="loyalty-page-card__head">
            <div>
                <h2><i class="fa fa-id-card-o" aria-hidden="true"></i> {{ $lt('stamp_programs.form.section_identity') }}</h2>
                <p>{{ $lt('stamp_programs.form.section_identity_lead') }}</p>
            </div>
        </div>
        <div class="loyalty-page-card__body">
            <div class="loyalty-tier-form__field">
                <label for="name" class="loyalty-tier-form__label">
                    {{ $lt('stamp_programs.form.name') }}
                    <span class="text-red">*</span>
                </label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    class="form-control"
                    value="{{ old('name', $program->name) }}"
                    required
                    autocomplete="off"
                >
            </div>

            <div class="loyalty-tier-form__field">
                <label for="reward_description" class="loyalty-tier-form__label">
                    {{ $lt('stamp_programs.form.reward_description') }}
                </label>
                <textarea
                    name="reward_description"
                    id="reward_description"
                    class="form-control loyalty-tier-form__textarea"
                    rows="3"
                >{{ old('reward_description', $program->reward_description) }}</textarea>
                <p class="loyalty-tier-form__hint">{{ $lt('stamp_programs.form.reward_description_help') }}</p>
            </div>
        </div>
    </section>

    <section class="loyalty-page-card loyalty-tier-form__section">
        <div class="loyalty-page-card__head">
            <div>
                <h2><i class="fa fa-check-circle-o" aria-hidden="true"></i> {{ $lt('stamp_programs.form.section_rules') }}</h2>
                <p>{{ $lt('stamp_programs.form.section_rules_lead') }}</p>
            </div>
        </div>
        <div class="loyalty-page-card__body">
            <div class="loyalty-tier-form__grid loyalty-tier-form__grid--2">
                <div class="loyalty-tier-form__field">
                    <label for="stamps_required" class="loyalty-tier-form__label">
                        {{ $lt('stamp_programs.form.stamps_required') }}
                        <span class="text-red">*</span>
                    </label>
                    <div class="loyalty-tier-form__input-group">
                        <input
                            type="number"
                            name="stamps_required"
                            id="stamps_required"
                            class="form-control"
                            min="2"
                            max="30"
                            value="{{ old('stamps_required', $program->stamps_required ?? 7) }}"
                            required
                        >
                        <span class="loyalty-tier-form__input-addon loyalty-tier-form__input-addon--suffix">
                            {{ $lt('stamp_programs.form.stamps_unit') }}
                        </span>
                    </div>
                </div>

                <div class="loyalty-tier-form__field">
                    <label for="validity_days" class="loyalty-tier-form__label">
                        {{ $lt('stamp_programs.form.validity_days') }}
                        <span class="text-red">*</span>
                    </label>
                    <div class="loyalty-tier-form__input-group">
                        <input
                            type="number"
                            name="validity_days"
                            id="validity_days"
                            class="form-control"
                            min="1"
                            max="365"
                            value="{{ old('validity_days', $program->validity_days ?? 30) }}"
                            required
                        >
                        <span class="loyalty-tier-form__input-addon loyalty-tier-form__input-addon--suffix">
                            {{ $lt('stamp_programs.form.days_unit') }}
                        </span>
                    </div>
                    <p class="loyalty-tier-form__hint">{{ $lt('stamp_programs.form.validity_days_help') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="loyalty-page-card loyalty-tier-form__section">
        <div class="loyalty-page-card__head">
            <div>
                <h2><i class="fa fa-filter" aria-hidden="true"></i> {{ $lt('stamp_programs.form.section_eligibility') }}</h2>
                <p>{{ $lt('stamp_programs.form.section_eligibility_lead') }}</p>
            </div>
        </div>
        <div class="loyalty-page-card__body">
            <div class="loyalty-stamp-program-form__eligibility">
                <label class="loyalty-stamp-program-form__option" for="virtual_treatments_only">
                    <input type="hidden" name="virtual_treatments_only" value="0">
                    <input
                        type="checkbox"
                        name="virtual_treatments_only"
                        id="virtual_treatments_only"
                        value="1"
                        {{ old('virtual_treatments_only', $program->virtual_treatments_only ?? true) ? 'checked' : '' }}
                    >
                    <span class="loyalty-stamp-program-form__option-body">
                        <span class="loyalty-stamp-program-form__option-title">
                            <i class="fa fa-calendar-check-o" aria-hidden="true"></i>
                            {{ $lt('stamp_programs.form.virtual_treatments_only') }}
                        </span>
                        <span class="loyalty-stamp-program-form__option-lead">
                            {{ $lt('stamp_programs.form.virtual_treatments_only_lead') }}
                        </span>
                    </span>
                </label>
            </div>

            @include('loyalty::admin.stamp_programs.partials.product-rules', [
                'eligibleSelection' => $eligibleSelection ?? ['category_ids' => [], 'products' => []],
                'categories' => $categories ?? [],
            ])
        </div>
    </section>

    <section class="loyalty-page-card loyalty-tier-form__section">
        <div class="loyalty-page-card__head">
            <div>
                <h2><i class="fa fa-sort" aria-hidden="true"></i> {{ $lt('stamp_programs.form.section_display') }}</h2>
                <p>{{ $lt('stamp_programs.form.section_display_lead') }}</p>
            </div>
        </div>
        <div class="loyalty-page-card__body">
            <div class="loyalty-tier-form__field">
                <label for="sort_order" class="loyalty-tier-form__label">{{ $lt('stamp_programs.form.sort_order') }}</label>
                <input
                    type="number"
                    name="sort_order"
                    id="sort_order"
                    class="form-control"
                    min="0"
                    value="{{ old('sort_order', $program->sort_order ?? 0) }}"
                >
                <p class="loyalty-tier-form__hint">{{ $lt('stamp_programs.form.sort_order_help') }}</p>
            </div>
        </div>
    </section>

    <section class="loyalty-page-card loyalty-tier-form__section loyalty-tier-form__section--status">
        <div class="loyalty-page-card__body">
            <div class="loyalty-tier-form__status-row">
                <div>
                    <h2 class="loyalty-tier-form__status-title">{{ $lt('stamp_programs.form.section_status') }}</h2>
                    <p class="loyalty-tier-form__status-lead">{{ $lt('stamp_programs.form.section_status_lead') }}</p>
                </div>
                <label class="loyalty-tier-form__toggle" for="is_active">
                    <input type="hidden" name="is_active" value="0">
                    <input
                        type="checkbox"
                        name="is_active"
                        id="is_active"
                        value="1"
                        {{ old('is_active', $program->is_active ?? true) ? 'checked' : '' }}
                    >
                    <span class="loyalty-tier-form__toggle-track" aria-hidden="true"></span>
                    <span class="loyalty-tier-form__toggle-text">{{ $lt('stamp_programs.form.is_active') }}</span>
                </label>
            </div>
        </div>
    </section>
</div>
