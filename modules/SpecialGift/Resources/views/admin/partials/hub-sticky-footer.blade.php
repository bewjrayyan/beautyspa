<div class="gv-hub-sticky-footer">
    <div class="gv-hub-sticky-footer__inner">
        <p class="gv-hub-sticky-footer__hint">
            <i class="fa fa-info-circle" aria-hidden="true"></i>
            {{ $hint ?? trans('specialgift::admin.save_hint') }}
        </p>

        <div class="gv-hub-sticky-footer__actions">
            @if (! empty($sendGiftUrl))
                <a
                    href="{{ $sendGiftUrl }}"
                    class="btn btn-default"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <i class="fa fa-external-link" aria-hidden="true"></i>
                    {{ trans('specialgift::admin.view_public_page') }}
                </a>
            @endif

            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save" aria-hidden="true"></i>
                {{ trans('admin::admin.buttons.save') }}
            </button>
        </div>
    </div>
</div>
