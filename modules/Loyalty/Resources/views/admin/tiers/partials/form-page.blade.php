@php
    use Modules\Loyalty\Support\LoyaltyLang;

    $lt = fn (string $key): string => LoyaltyLang::get($key);
    $isEdit = $isEdit ?? $tier->exists;
    $currencySymbol = $currencySymbol ?? currency_symbol(setting('default_currency'));
    $tierAccent = match ($tier->slug) {
        'gold' => 3,
        'platinum' => 4,
        'silver' => 1,
        default => 2,
    };
    $benefitsLines = old('benefits_text')
        ? preg_split('/\r\n|\r|\n/', (string) old('benefits_text'))
        : (is_array($tier->benefits) ? $tier->benefits : []);
    $benefitsLines = array_values(array_filter(array_map('trim', $benefitsLines)));
@endphp

<div class="loyalty-admin loyalty-tier-form">
    <header class="loyalty-page-hero loyalty-page-hero--tiers loyalty-page-hero--tier-form">
        <div class="loyalty-page-hero__main">
            <p class="loyalty-tier-form__eyebrow">
                <i class="fa fa-star" aria-hidden="true"></i>
                {{ $lt('tiers.tier') }}
            </p>
            <h1 class="loyalty-page-hero__title loyalty-tier-form__title" id="tier-preview-name">
                {{ $isEdit ? $tier->translatedName() : ($tier->name ?: $lt('tiers.form.new_tier_title')) }}
            </h1>
            <p class="loyalty-page-hero__lead">
                @if ($isEdit)
                    {{ $lt('tiers.form.edit_lead') }}
                @else
                    {{ $lt('tiers.form.create_lead') }}
                @endif
            </p>
            <div class="loyalty-tier-form__hero-meta">
                <span class="loyalty-tier-form__slug" id="tier-preview-slug">
                    <span class="loyalty-tier-form__slug-label">{{ $lt('tiers.form.slug') }}:</span>
                    <span class="loyalty-tier-form__slug-value" data-preview="slug">{{ $tier->slug ?: '—' }}</span>
                </span>
                <span
                    class="loyalty-tier-form__status loyalty-tier-form__status--{{ ($tier->is_active ?? true) ? 'active' : 'inactive' }}"
                    id="tier-preview-status"
                >
                    {{ ($tier->is_active ?? true)
                        ? $lt('tiers.form.status_active')
                        : $lt('tiers.form.status_inactive') }}
                </span>
            </div>
        </div>
        <div class="loyalty-page-hero__actions">
            <a href="{{ route('admin.loyalty.tiers.index') }}" class="btn btn-default loyalty-page-hero__btn">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                {{ $lt('tiers.form.back') }}
            </a>
        </div>
    </header>

    <form
        method="POST"
        action="{{ $formAction }}"
        class="loyalty-tier-form__form"
        id="loyalty-tier-form"
        novalidate
    >
        @csrf
        @if (! empty($formMethod))
            @method($formMethod)
        @endif

        <div class="loyalty-tier-form__layout">
            <div class="loyalty-tier-form__main">
                @include('loyalty::admin.tiers.partials.form')

                <footer class="loyalty-tier-form__footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save" aria-hidden="true"></i>
                        {{ trans('admin::admin.buttons.save') }}
                    </button>
                    <a href="{{ route('admin.loyalty.tiers.index') }}" class="btn btn-default">
                        {{ trans('admin::admin.buttons.cancel') }}
                    </a>
                </footer>
            </div>

            <aside class="loyalty-tier-form__aside" aria-label="{{ $lt('tiers.form.preview_title') }}">
                <div class="loyalty-page-card loyalty-tier-form__preview-card">
                    <div class="loyalty-page-card__head">
                        <h2>{{ $lt('tiers.form.preview_title') }}</h2>
                    </div>
                    <div class="loyalty-page-card__body">
                        <div
                            class="loyalty-tier-card loyalty-tier-card--preview loyalty-tier-card--{{ $tierAccent }} {{ ($tier->is_active ?? true) ? '' : 'loyalty-tier-card--inactive' }}"
                            id="tier-preview-card"
                        >
                            <span class="loyalty-tier-card__badge" id="tier-preview-multiplier">
                                <i class="fa fa-star" aria-hidden="true"></i>
                                {{ number_format((float) old('earn_multiplier', $tier->earn_multiplier ?? 1), 2) }}×
                            </span>
                            <h3 class="loyalty-tier-card__name" data-preview="name">
                                {{ $isEdit ? $tier->translatedName() : ($tier->name ?: $lt('tiers.form.new_tier_title')) }}
                            </h3>
                            <ul class="loyalty-tier-card__meta">
                                <li>
                                    <span>{{ $lt('tiers.table.min_spend') }}</span>
                                    <strong id="tier-preview-min-spend">
                                        {{ $currencySymbol }}
                                        {{ number_format((float) old('min_lifetime_spend', $tier->min_lifetime_spend ?? 0), 2) }}
                                    </strong>
                                </li>
                                @if ($isEdit)
                                    <li>
                                        <span>{{ $lt('tiers.table.members') }}</span>
                                        <strong>
                                            {{ __('loyalty::tiers.index.members_count', ['count' => number_format($tier->wallets_count ?? 0)]) }}
                                        </strong>
                                    </li>
                                @endif
                                <li>
                                    <span>{{ $lt('tiers.form.sort_order') }}</span>
                                    <strong id="tier-preview-sort">{{ old('sort_order', $tier->sort_order ?? 0) }}</strong>
                                </li>
                            </ul>
                        </div>

                        <div class="loyalty-tier-form__benefits-preview">
                                <h3>{{ $lt('tiers.form.benefits') }}</h3>
                                <ul class="loyalty-tier-form__benefits-list" id="tier-preview-benefits">
                                    @forelse ($benefitsLines as $line)
                                        <li>{{ $line }}</li>
                                    @empty
                                        <li class="loyalty-tier-form__benefits-empty">
                                            {{ $lt('tiers.form.benefits_empty') }}
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                    </div>
                </div>

                <div class="loyalty-tier-form__tips">
                    <h3><i class="fa fa-lightbulb-o" aria-hidden="true"></i> {{ $lt('tiers.form.tips_title') }}</h3>
                    <ul>
                        <li>{{ $lt('tiers.form.tip_slug') }}</li>
                        <li>{{ $lt('tiers.form.tip_spend') }}</li>
                        <li>{{ $lt('tiers.form.tip_multiplier') }}</li>
                    </ul>
                </div>
            </aside>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('loyalty-tier-form');
            if (!form) return;

            const currency = @json($currencySymbol);
            const activeLabel = @json($lt('tiers.form.status_active'));
            const inactiveLabel = @json($lt('tiers.form.status_inactive'));
            const benefitsEmpty = @json($lt('tiers.form.benefits_empty'));
            const newTierTitle = @json($lt('tiers.form.new_tier_title'));

            const fields = {
                name: form.querySelector('#name'),
                slug: form.querySelector('#slug'),
                minSpend: form.querySelector('#min_lifetime_spend'),
                multiplier: form.querySelector('#earn_multiplier'),
                sortOrder: form.querySelector('#sort_order'),
                benefits: form.querySelector('#benefits_text'),
                active: form.querySelector('#is_active'),
            };

            const preview = {
                title: document.getElementById('tier-preview-name'),
                slug: document.getElementById('tier-preview-slug'),
                status: document.getElementById('tier-preview-status'),
                card: document.getElementById('tier-preview-card'),
                multiplier: document.getElementById('tier-preview-multiplier'),
                minSpend: document.getElementById('tier-preview-min-spend'),
                sort: document.getElementById('tier-preview-sort'),
                benefits: document.getElementById('tier-preview-benefits'),
                nameNodes: form.querySelectorAll('[data-preview="name"]'),
                slugNodes: form.querySelectorAll('[data-preview="slug"]'),
            };

            function formatMoney(value) {
                const num = parseFloat(value);
                if (Number.isNaN(num)) return currency + ' 0.00';
                return currency + ' ' + num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function formatMultiplier(value) {
                const num = parseFloat(value);
                if (Number.isNaN(num)) return '1.00';
                return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function slugAccent(slug) {
                if (slug === 'gold') return 3;
                if (slug === 'platinum') return 4;
                if (slug === 'silver') return 1;
                return 2;
            }

            function updateBenefitsList() {
                if (!preview.benefits || !fields.benefits) return;
                const lines = fields.benefits.value
                    .split(/\r?\n/)
                    .map((line) => line.trim())
                    .filter(Boolean);

                preview.benefits.innerHTML = lines.length
                    ? lines.map((line) => '<li>' + line.replace(/</g, '&lt;') + '</li>').join('')
                    : '<li class="loyalty-tier-form__benefits-empty">' + benefitsEmpty + '</li>';
            }

            function syncPreview() {
                const name = fields.name?.value.trim() || newTierTitle;
                const slug = fields.slug?.value.trim() || 'tier-slug';
                const isActive = fields.active?.checked ?? true;

                preview.title.textContent = name;
                preview.slugNodes.forEach((node) => { node.textContent = slug || '—'; });
                preview.nameNodes.forEach((node) => { node.textContent = name; });

                if (preview.multiplier) {
                    preview.multiplier.innerHTML =
                        '<i class="fa fa-star" aria-hidden="true"></i> ' + formatMultiplier(fields.multiplier?.value) + '×';
                }

                if (preview.minSpend) {
                    preview.minSpend.textContent = formatMoney(fields.minSpend?.value);
                }

                if (preview.sort) {
                    preview.sort.textContent = fields.sortOrder?.value ?? '0';
                }

                if (preview.status) {
                    preview.status.textContent = isActive ? activeLabel : inactiveLabel;
                    preview.status.classList.toggle('loyalty-tier-form__status--active', isActive);
                    preview.status.classList.toggle('loyalty-tier-form__status--inactive', !isActive);
                }

                if (preview.card) {
                    preview.card.classList.toggle('loyalty-tier-card--inactive', !isActive);
                    [1, 2, 3, 4].forEach((n) => preview.card.classList.remove('loyalty-tier-card--' + n));
                    preview.card.classList.add('loyalty-tier-card--' + slugAccent(slug));
                }

                updateBenefitsList();
            }

            form.addEventListener('input', syncPreview);
            form.addEventListener('change', syncPreview);
            syncPreview();
        })();
    </script>
@endpush
