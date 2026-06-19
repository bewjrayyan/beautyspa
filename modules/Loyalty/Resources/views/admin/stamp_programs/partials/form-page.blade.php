@php
    use Modules\Loyalty\Support\LoyaltyLang;

    $lt = fn (string $key): string => LoyaltyLang::get($key);
    $isEdit = $isEdit ?? $program->exists;
    $isActive = (bool) old('is_active', $program->is_active ?? true);
@endphp

<div class="loyalty-admin loyalty-tier-form loyalty-stamp-program-form">
    <header class="loyalty-page-hero loyalty-page-hero--tiers loyalty-page-hero--tier-form">
        <div class="loyalty-page-hero__main">
            <p class="loyalty-tier-form__eyebrow">
                <i class="fa fa-ticket" aria-hidden="true"></i>
                {{ $lt('stamp_programs.program') }}
            </p>
            <h1 class="loyalty-page-hero__title loyalty-tier-form__title" id="stamp-program-preview-title">
                {{ $isEdit ? $program->name : $lt('stamp_programs.form.new_program_title') }}
            </h1>
            <p class="loyalty-page-hero__lead">
                {{ $isEdit ? $lt('stamp_programs.form.edit_lead') : $lt('stamp_programs.form.create_lead') }}
            </p>
        </div>
        <div class="loyalty-page-hero__aside loyalty-tier-form__hero-aside">
            <div class="loyalty-tier-form__hero-meta">
                @if ($isEdit && ($program->wallets_count ?? 0) > 0)
                    <span class="loyalty-stamp-program-form__stat">
                        <i class="fa fa-id-card-o" aria-hidden="true"></i>
                        {{ $lt('stamp_programs.form.wallets_count', ['count' => number_format($program->wallets_count)]) }}
                    </span>
                @endif
                <span
                    class="loyalty-tier-form__status loyalty-tier-form__status--{{ $isActive ? 'active' : 'inactive' }}"
                    id="stamp-program-preview-status"
                >
                    {{ $isActive ? $lt('stamp_programs.form.status_active') : $lt('stamp_programs.form.status_inactive') }}
                </span>
            </div>
            <div class="loyalty-page-hero__actions">
                <a href="{{ route('admin.loyalty.stamp_programs.index') }}" class="btn btn-default loyalty-page-hero__btn">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    {{ $lt('stamp_programs.form.back') }}
                </a>
            </div>
        </div>
    </header>

    <form
        method="POST"
        action="{{ $formAction }}"
        class="loyalty-tier-form__form"
        id="loyalty-stamp-program-form"
        novalidate
    >
        @csrf
        @if (! empty($formMethod))
            @method($formMethod)
        @endif

        <div class="loyalty-tier-form__layout">
            <div class="loyalty-tier-form__main">
                @include('loyalty::admin.stamp_programs.partials.form', [
                    'program' => $program,
                    'eligibleSelection' => $eligibleSelection ?? ['category_ids' => [], 'products' => []],
                    'categories' => $categories ?? [],
                ])

                <footer class="loyalty-tier-form__footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save" aria-hidden="true"></i>
                        {{ trans('admin::admin.buttons.save') }}
                    </button>
                    <a href="{{ route('admin.loyalty.stamp_programs.index') }}" class="btn btn-default">
                        {{ trans('admin::admin.buttons.cancel') }}
                    </a>
                </footer>
            </div>

            <aside class="loyalty-tier-form__aside" aria-label="{{ $lt('stamp_programs.form.preview_title') }}">
                <div class="loyalty-page-card loyalty-tier-form__preview-card">
                    <div class="loyalty-page-card__head">
                        <h2>{{ $lt('stamp_programs.form.preview_title') }}</h2>
                    </div>
                    <div class="loyalty-page-card__body">
                        @include('loyalty::admin.stamp_programs.partials.preview-stamp-card', ['program' => $program])
                    </div>
                </div>

                <div class="loyalty-tier-form__tips">
                    <h3><i class="fa fa-lightbulb-o" aria-hidden="true"></i> {{ $lt('stamp_programs.form.tips_title') }}</h3>
                    <ul>
                        <li>{{ $lt('stamp_programs.form.tip_reward') }}</li>
                        <li>{{ $lt('stamp_programs.form.tip_validity') }}</li>
                        <li>{{ $lt('stamp_programs.form.tip_eligibility') }}</li>
                    </ul>
                </div>
            </aside>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('loyalty-stamp-program-form');
            if (!form) return;

            const activeLabel = @json($lt('stamp_programs.form.status_active'));
            const inactiveLabel = @json($lt('stamp_programs.form.status_inactive'));
            const newTitle = @json($lt('stamp_programs.form.new_program_title'));
            const rewardPlaceholder = @json($lt('stamp_programs.form.reward_description'));
            const progressTpl = @json($lt('stamp_programs.form.preview_progress'));
            const expiryTpl = @json($lt('stamp_programs.form.preview_expiry'));
            const demoEarned = 3;

            const fields = {
                name: form.querySelector('#name'),
                reward: form.querySelector('#reward_description'),
                stampsRequired: form.querySelector('#stamps_required'),
                validityDays: form.querySelector('#validity_days'),
                active: form.querySelector('#is_active'),
            };

            const preview = {
                title: document.getElementById('stamp-program-preview-title'),
                status: document.getElementById('stamp-program-preview-status'),
                card: form.querySelector('[data-stamp-preview-card]'),
                name: form.querySelector('[data-preview="name"]'),
                reward: form.querySelector('[data-preview="reward"]'),
                stamps: form.querySelector('[data-preview="stamps"]'),
                progress: form.querySelector('[data-preview="progress"]'),
                expiryText: form.querySelector('[data-preview="expiry-text"]'),
            };

            function clamp(value, min, max) {
                return Math.min(max, Math.max(min, value));
            }

            function renderStamps(required, earned) {
                if (!preview.stamps) return;

                const safeRequired = clamp(required, 2, 30);
                const safeEarned = clamp(earned, 0, safeRequired);
                let html = '';

                for (let i = 1; i <= safeRequired; i += 1) {
                    const filled = i <= safeEarned;
                    html += filled
                        ? '<span class="loyalty-stamp-preview__stamp loyalty-stamp-preview__stamp--filled"><i class="fa fa-check" aria-hidden="true"></i></span>'
                        : '<span class="loyalty-stamp-preview__stamp"></span>';
                }

                preview.stamps.innerHTML = html;
            }

            function syncPreview() {
                const name = fields.name?.value.trim() || newTitle;
                const reward = fields.reward?.value.trim() || rewardPlaceholder;
                const required = clamp(parseInt(fields.stampsRequired?.value || '7', 10), 2, 30);
                const days = clamp(parseInt(fields.validityDays?.value || '30', 10), 1, 365);
                const isActive = fields.active?.checked ?? true;
                const earned = Math.min(demoEarned, required);

                if (preview.title) preview.title.textContent = name;
                if (preview.name) preview.name.textContent = name;
                if (preview.reward) preview.reward.textContent = reward;

                renderStamps(required, earned);

                if (preview.progress) {
                    preview.progress.textContent = progressTpl
                        .replace(':earned', earned)
                        .replace(':required', required);
                }

                if (preview.expiryText) {
                    preview.expiryText.textContent = expiryTpl.replace(':days', days);
                }

                if (preview.status) {
                    preview.status.textContent = isActive ? activeLabel : inactiveLabel;
                    preview.status.classList.toggle('loyalty-tier-form__status--active', isActive);
                    preview.status.classList.toggle('loyalty-tier-form__status--inactive', !isActive);
                }

                if (preview.card) {
                    preview.card.classList.toggle('loyalty-stamp-preview__card--inactive', !isActive);
                }
            }

            form.addEventListener('input', syncPreview);
            form.addEventListener('change', syncPreview);
            syncPreview();
        })();
    </script>
@endpush
