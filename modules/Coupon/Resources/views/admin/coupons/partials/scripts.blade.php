@include('admin::partials.selectize_remote')

@push('shortcuts')
    <dl class="dl-horizontal">
        <dt><code>b</code></dt>
        <dd>{{ trans('admin::admin.shortcuts.back_to_index', ['name' => trans('coupon::coupons.coupon')]) }}</dd>
    </dl>
@endpush

@push('scripts')
    <script type="module">
        keypressAction([
            { key: 'b', route: "{{ route('admin.coupons.index') }}" },
        ]);

        (function () {
            const isPercent = document.getElementById('is_percent');
            const valueInput = document.getElementById('value');
            const valueHint = document.getElementById('coupon-value-hint');
            const codeInput = document.querySelector('[name="code"]');
            const nameInput = document.querySelector('[name="name"]');
            const codePreview = document.getElementById('coupon-form-code-preview');
            const namePreview = document.getElementById('coupon-form-name-preview');
            const discountPreview = document.getElementById('coupon-form-discount-preview');
            const shippingPreview = document.getElementById('coupon-form-shipping-preview');
            const datesPreview = document.getElementById('coupon-form-dates-preview');
            const freeShippingInput = document.querySelector('[name="free_shipping"]');
            const startDateInput = document.querySelector('[name="start_date"]');
            const endDateInput = document.querySelector('[name="end_date"]');
            const generateCodeButton = document.getElementById('coupon-generate-code');
            const totalLimitInput = document.querySelector('[name="usage_limit_per_coupon"]');
            const customerLimitInput = document.querySelector('[name="usage_limit_per_customer"]');
            const readinessCount = document.getElementById('coupon-readiness-count');
            const currencySymbol = @json(currency_symbol(setting('default_currency')));

            const hints = {
                percent: @json(trans('coupon::coupons.form.value_hint_percent')),
                fixed: @json(trans('coupon::coupons.form.value_hint_fixed')),
            };

            function syncValueHint() {
                if (!isPercent || !valueHint) {
                    return;
                }

                const percent = isPercent.value === '1';
                valueHint.textContent = percent ? hints.percent : hints.fixed;
                valueHint.hidden = false;
            }

            function syncSidebarPreview() {
                if (codePreview && codeInput) {
                    codePreview.textContent = codeInput.value.trim() || 'CODE';
                }

                if (namePreview && nameInput) {
                    const name = nameInput.value.trim();
                    namePreview.textContent = name || @json(trans('coupon::coupons.form.preview_name_placeholder'));
                }

                if (discountPreview && valueInput && isPercent) {
                    const raw = parseFloat(valueInput.value);

                    if (Number.isNaN(raw)) {
                        discountPreview.textContent = '—';
                    } else {
                        discountPreview.textContent = isPercent.value === '1'
                            ? (raw % 1 === 0 ? parseInt(raw, 10) : raw) + '%'
                            : currencySymbol + raw.toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            });
                    }
                }

                if (shippingPreview && freeShippingInput) {
                    shippingPreview.hidden = !freeShippingInput.checked;
                }

                if (datesPreview && (startDateInput || endDateInput)) {
                    const start = startDateInput?.value?.trim() || '';
                    const end = endDateInput?.value?.trim() || '';
                    const range = [start, end].filter(Boolean).join(' – ');

                    datesPreview.textContent = range;
                    datesPreview.hidden = !range;
                }

                syncReadiness();
            }

            function randomSuffix(length = 4) {
                const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                const values = new Uint32Array(length);

                if (window.crypto?.getRandomValues) {
                    window.crypto.getRandomValues(values);
                } else {
                    values.forEach((value, index) => values[index] = Math.floor(Math.random() * alphabet.length));
                }

                return Array.from(values, value => alphabet[value % alphabet.length]).join('');
            }

            function generateCouponCode() {
                const source = nameInput?.value || '';
                const prefix = source
                    .normalize('NFKD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toUpperCase()
                    .replace(/[^A-Z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '')
                    .slice(0, 18) || 'SAVE';

                codeInput.value = `${prefix}-${randomSuffix()}`;
                codeInput.dispatchEvent(new Event('input', { bubbles: true }));
                codeInput.focus();
                codeInput.select();
            }

            function normalizeCode() {
                if (!codeInput) {
                    return;
                }

                codeInput.value = codeInput.value
                    .toUpperCase()
                    .replace(/\s+/g, '-')
                    .replace(/[^A-Z0-9_-]/g, '');
            }

            function isValidDateRange() {
                const start = startDateInput?.value?.trim();
                const end = endDateInput?.value?.trim();

                if (!start || !end) {
                    return true;
                }

                const startTime = Date.parse(start);
                const endTime = Date.parse(end);

                return Number.isNaN(startTime) || Number.isNaN(endTime) || endTime >= startTime;
            }

            function isValidLimit(value) {
                return value === '' || (Number.isInteger(Number(value)) && Number(value) >= 1);
            }

            function syncReadiness() {
                const value = Number(valueInput?.value);
                const percent = isPercent?.value === '1';
                const totalLimit = totalLimitInput?.value?.trim() || '';
                const customerLimit = customerLimitInput?.value?.trim() || '';
                const limitsValid = isValidLimit(totalLimit)
                    && isValidLimit(customerLimit)
                    && (!totalLimit || !customerLimit || Number(customerLimit) <= Number(totalLimit));
                const checks = {
                    identity: Boolean(nameInput?.value?.trim() && codeInput?.value?.trim()),
                    discount: Number.isFinite(value) && value > 0 && (!percent || value <= 100),
                    schedule: isValidDateRange(),
                    limits: limitsValid,
                };
                let completed = 0;

                Object.entries(checks).forEach(([key, ready]) => {
                    const item = document.querySelector(`[data-readiness="${key}"]`);

                    if (!item) {
                        return;
                    }

                    item.classList.toggle('is-ready', ready);
                    const icon = item.querySelector('.fa');
                    icon?.classList.toggle('fa-circle-o', !ready);
                    icon?.classList.toggle('fa-check-circle', ready);
                    completed += ready ? 1 : 0;
                });

                if (readinessCount) {
                    readinessCount.textContent = `${completed}/4`;
                }
            }

            if (isPercent) {
                isPercent.addEventListener('change', () => {
                    syncValueHint();
                    syncSidebarPreview();
                });
                syncValueHint();
            }

            if (freeShippingInput) {
                freeShippingInput.addEventListener('change', syncSidebarPreview);
            }

            if (generateCodeButton && codeInput) {
                generateCodeButton.addEventListener('click', generateCouponCode);
                codeInput.addEventListener('input', normalizeCode);
            }

            [codeInput, nameInput, valueInput, startDateInput, endDateInput, totalLimitInput, customerLimitInput].forEach((el) => {
                if (el) {
                    el.addEventListener('input', syncSidebarPreview);
                    el.addEventListener('change', syncSidebarPreview);
                }
            });

            $('.coupon-form-tabs__link[data-toggle="tab"]').on('shown.bs.tab', function () {
                    const tab = this;
                    document.querySelectorAll('.coupon-form-tabs__item').forEach((item) => item.classList.remove('coupon-form-tabs__item--active'));
                    tab.closest('.coupon-form-tabs__item')?.classList.add('coupon-form-tabs__item--active');
                    document.querySelectorAll('.coupon-form-tabs__link').forEach((link) => link.setAttribute('aria-selected', 'false'));
                    tab.setAttribute('aria-selected', 'true');

                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', tab.dataset.tabName);
                    window.history.replaceState({}, '', url);
            });

            syncSidebarPreview();
        })();
    </script>
@endpush
