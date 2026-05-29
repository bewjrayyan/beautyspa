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
            const typePreview = document.getElementById('coupon-form-type-preview');
            const shippingPreview = document.getElementById('coupon-form-shipping-preview');
            const datesPreview = document.getElementById('coupon-form-dates-preview');
            const freeShippingInput = document.querySelector('[name="free_shipping"]');
            const startDateInput = document.querySelector('[name="start_date"]');
            const endDateInput = document.querySelector('[name="end_date"]');

            const hints = {
                percent: @json(trans('coupon::coupons.form.value_hint_percent')),
                fixed: @json(trans('coupon::coupons.form.value_hint_fixed')),
            };

            const typeLabels = {
                '0': @json(trans('coupon::coupons.form.price_types.0')),
                '1': @json(trans('coupon::coupons.form.price_types.1')),
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

                if (typePreview && isPercent) {
                    typePreview.textContent = typeLabels[isPercent.value] || typePreview.textContent;
                }

                if (discountPreview && valueInput && isPercent) {
                    const raw = parseFloat(valueInput.value);

                    if (Number.isNaN(raw)) {
                        discountPreview.textContent = '—';
                    } else {
                        discountPreview.textContent = isPercent.value === '1'
                            ? (raw % 1 === 0 ? parseInt(raw, 10) : raw) + '%'
                            : valueInput.value;
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

            [codeInput, nameInput, valueInput, startDateInput, endDateInput].forEach((el) => {
                if (el) {
                    el.addEventListener('input', syncSidebarPreview);
                    el.addEventListener('change', syncSidebarPreview);
                }
            });
        })();
    </script>
@endpush
