@hasAnyAccess(['admin.storefront.edit', 'admin.reports.index'])
    @if (empty($compact))
        <div class="form-group">
            <label class="control-label">{{ trans('storefront::attributes.storefront_most_searched_keywords') }}</label>

            <p class="help-block">{{ trans('product::search_terms.reset_help') }}</p>

            <button
                type="button"
                class="btn btn-default reset-most-searched-keywords"
                data-url="{{ route('admin.search_terms.destroy') }}"
                data-confirm="{{ trans('product::search_terms.reset_confirm') }}"
            >
                <i class="fa fa-refresh"></i> {{ trans('product::search_terms.reset_most_searched') }}
            </button>
        </div>
    @else
        <button
            type="button"
            class="btn btn-default btn-sm reset-most-searched-keywords"
            data-url="{{ route('admin.search_terms.destroy') }}"
            data-confirm="{{ trans('product::search_terms.reset_confirm') }}"
            title="{{ trans('product::search_terms.reset_help') }}"
        >
            <i class="fa fa-refresh"></i> {{ trans('product::search_terms.reset_most_searched') }}
        </button>
    @endif
@endHasAnyAccess

@once
    @push('scripts')
        <script>
            $(document).on('click', '.reset-most-searched-keywords', function () {
                const $button = $(this);

                if (!confirm($button.data('confirm'))) {
                    return;
                }

                $button.prop('disabled', true);

                $.ajax({
                    url: $button.data('url'),
                    type: 'POST',
                    data: {
                        _token: FleetCart.csrfToken,
                        _method: 'DELETE',
                    },
                })
                    .done(function () {
                        window.location.reload();
                    })
                    .fail(function (xhr) {
                        $button.prop('disabled', false);

                        const message = xhr.responseJSON?.message
                            || FleetCart.langs['core::messages.something_went_wrong'];

                        if (typeof notify === 'function') {
                            notify('error', message);
                        } else {
                            alert(message);
                        }
                    });
            });
        </script>
    @endpush
@endonce
