@push('globals')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('send-gift-form');
            if (!form) return;

            const submitBtn = document.getElementById('send-gift-submit');
            const label = submitBtn.querySelector('.send-gift-submit-label');
            const loading = submitBtn.querySelector('.send-gift-submit-loading');
            const errorBox = document.getElementById('send-gift-error');
            const successBox = document.getElementById('send-gift-success');
            const successText = successBox.querySelector('.sg-alert__text');
            const previewName = document.getElementById('sg-preview-name');
            const previewOrder = document.getElementById('sg-preview-order');
            const recipientInput = document.getElementById('recipient_name');
            const orderInput = document.getElementById('order_number');
            const sampleName = @json(trans('specialgift::messages.preview_sample_name'));
            const orderPrefix = @json(trans('specialgift::messages.preview_order_prefix'));

            function updatePreview() {
                const name = recipientInput.value.trim();
                previewName.textContent = name || sampleName;

                const order = orderInput.value.trim().replace(/^#/, '');
                previewOrder.textContent = order
                    ? `${orderPrefix} #${order}`
                    : @json(trans('specialgift::messages.preview_sample_order'));
            }

            recipientInput.addEventListener('input', updatePreview);
            orderInput.addEventListener('input', updatePreview);
            updatePreview();

            function extractErrorMessage(data, fallback) {
                if (data && typeof data.message === 'string' && data.message !== '') {
                    return data.message;
                }

                if (data && data.errors && typeof data.errors === 'object') {
                    const messages = Object.values(data.errors)
                        .flat()
                        .filter((value) => typeof value === 'string' && value !== '');

                    if (messages.length) {
                        return messages.join(' ');
                    }
                }

                return fallback;
            }

            const fallbackError = @json(trans('specialgift::messages.send_failed'));

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                errorBox.hidden = true;
                successBox.hidden = true;
                submitBtn.disabled = true;
                label.hidden = true;
                loading.hidden = false;

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': @json(csrf_token()),
                        },
                        credentials: 'same-origin',
                        body: new FormData(form),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (response.ok && data.success) {
                        successText.textContent = data.message || @json(trans('specialgift::messages.sent_success'));
                        successBox.hidden = false;
                        form.reset();
                        updatePreview();
                        successBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    } else {
                        errorBox.textContent = extractErrorMessage(data, fallbackError);
                        errorBox.hidden = false;
                        errorBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                } catch (e) {
                    errorBox.textContent = fallbackError;
                    errorBox.hidden = false;
                } finally {
                    submitBtn.disabled = false;
                    label.hidden = false;
                    loading.hidden = true;
                }
            });
        });
    </script>
@endpush
