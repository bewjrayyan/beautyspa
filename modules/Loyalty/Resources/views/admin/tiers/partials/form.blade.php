<div class="loyalty-tier-form__sections">
    <section class="loyalty-page-card loyalty-tier-form__section">
        <div class="loyalty-page-card__head">
            <div>
                <h2><i class="fa fa-id-card-o" aria-hidden="true"></i> {{ $lt('tiers.form.section_identity') }}</h2>
                <p>{{ $lt('tiers.form.section_identity_lead') }}</p>
            </div>
        </div>
        <div class="loyalty-page-card__body">
            <div class="loyalty-tier-form__grid loyalty-tier-form__grid--2">
                <div class="loyalty-tier-form__field">
                    <label for="name" class="loyalty-tier-form__label">{{ $lt('tiers.form.name') }}</label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control"
                        value="{{ old('name', $tier->name) }}"
                        required
                        autocomplete="off"
                    >
                </div>
                <div class="loyalty-tier-form__field">
                    <label for="slug" class="loyalty-tier-form__label">{{ $lt('tiers.form.slug') }}</label>
                    <input
                        type="text"
                        name="slug"
                        id="slug"
                        class="form-control loyalty-tier-form__input-mono"
                        value="{{ old('slug', $tier->slug) }}"
                        required
                        pattern="[a-z0-9\-]+"
                        autocomplete="off"
                    >
                    <p class="loyalty-tier-form__hint">{{ $lt('tiers.form.slug_help') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="loyalty-page-card loyalty-tier-form__section">
        <div class="loyalty-page-card__head">
            <div>
                <h2><i class="fa fa-line-chart" aria-hidden="true"></i> {{ $lt('tiers.form.section_rules') }}</h2>
                <p>{{ $lt('tiers.form.section_rules_lead') }}</p>
            </div>
        </div>
        <div class="loyalty-page-card__body">
            <div class="loyalty-tier-form__grid loyalty-tier-form__grid--3">
                <div class="loyalty-tier-form__field">
                    <label for="min_lifetime_spend" class="loyalty-tier-form__label">
                        {{ $lt('tiers.form.min_lifetime_spend') }}
                    </label>
                    <div class="loyalty-tier-form__input-group">
                        <span class="loyalty-tier-form__input-addon">{{ $currencySymbol ?? currency_symbol(setting('default_currency')) }}</span>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="min_lifetime_spend"
                            id="min_lifetime_spend"
                            class="form-control"
                            value="{{ old('min_lifetime_spend', $tier->min_lifetime_spend ?? 0) }}"
                            required
                        >
                    </div>
                </div>
                <div class="loyalty-tier-form__field">
                    <label for="earn_multiplier" class="loyalty-tier-form__label">
                        {{ $lt('tiers.form.earn_multiplier') }}
                    </label>
                    <div class="loyalty-tier-form__input-group">
                        <input
                            type="number"
                            step="0.01"
                            min="0.01"
                            name="earn_multiplier"
                            id="earn_multiplier"
                            class="form-control"
                            value="{{ old('earn_multiplier', $tier->earn_multiplier ?? 1) }}"
                            required
                        >
                        <span class="loyalty-tier-form__input-addon loyalty-tier-form__input-addon--suffix">×</span>
                    </div>
                    <p class="loyalty-tier-form__hint">{{ $lt('tiers.form.multiplier_help') }}</p>
                </div>
                <div class="loyalty-tier-form__field">
                    <label for="sort_order" class="loyalty-tier-form__label">{{ $lt('tiers.form.sort_order') }}</label>
                    <input
                        type="number"
                        min="0"
                        name="sort_order"
                        id="sort_order"
                        class="form-control"
                        value="{{ old('sort_order', $tier->sort_order ?? 0) }}"
                    >
                    <p class="loyalty-tier-form__hint">{{ $lt('tiers.form.sort_order_help') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="loyalty-page-card loyalty-tier-form__section">
        <div class="loyalty-page-card__head">
            <div>
                <h2><i class="fa fa-gift" aria-hidden="true"></i> {{ $lt('tiers.form.benefits') }}</h2>
                <p>{{ $lt('tiers.form.benefits_lead') }}</p>
            </div>
        </div>
        <div class="loyalty-page-card__body">
            <div class="loyalty-tier-form__field">
                <label for="benefits_text" class="loyalty-tier-form__label">{{ $lt('tiers.form.benefits') }}</label>
                <textarea
                    name="benefits_text"
                    id="benefits_text"
                    class="form-control loyalty-tier-form__textarea"
                    rows="6"
                    placeholder="{{ $lt('tiers.form.benefits_placeholder') }}"
                >{{ old('benefits_text', is_array($tier->benefits) ? implode("\n", $tier->benefits) : '') }}</textarea>
            </div>
        </div>
    </section>

    <section class="loyalty-page-card loyalty-tier-form__section loyalty-tier-form__section--status">
        <div class="loyalty-page-card__body">
            <div class="loyalty-tier-form__status-row">
                <div>
                    <h2 class="loyalty-tier-form__status-title">{{ $lt('tiers.form.section_status') }}</h2>
                    <p class="loyalty-tier-form__status-lead">{{ $lt('tiers.form.section_status_lead') }}</p>
                </div>
                <label class="loyalty-tier-form__toggle" for="is_active">
                    <input type="hidden" name="is_active" value="0">
                    <input
                        type="checkbox"
                        name="is_active"
                        id="is_active"
                        value="1"
                        {{ old('is_active', $tier->is_active ?? true) ? 'checked' : '' }}
                    >
                    <span class="loyalty-tier-form__toggle-track" aria-hidden="true"></span>
                    <span class="loyalty-tier-form__toggle-text">{{ $lt('tiers.form.is_active') }}</span>
                </label>
            </div>
        </div>
    </section>
</div>
