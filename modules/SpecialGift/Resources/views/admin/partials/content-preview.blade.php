<aside class="gv-live-preview" aria-label="{{ trans('specialgift::admin.live_preview_title') }}">
    <div class="gv-live-preview__head">
        <span class="gv-live-preview__badge">{{ trans('specialgift::admin.live_preview_badge') }}</span>
        <h5 class="gv-live-preview__title">{{ trans('specialgift::admin.live_preview_title') }}</h5>
    </div>

    <div class="gv-live-preview__device">
        <div class="gv-live-preview__screen" id="gv-content-preview-screen">
            <div class="gv-live-preview__hero">
                <p class="gv-live-preview__tagline" data-preview="tagline">
                    {{ trans('specialgift::messages.page_tagline') }}
                </p>
                <h6 class="gv-live-preview__page-title" data-preview="title">
                    {{ trans('specialgift::messages.page_title') }}
                </h6>
                <p class="gv-live-preview__lead" data-preview="lead">
                    {{ trans('specialgift::messages.page_lead') }}
                </p>
            </div>

            <ol class="gv-live-preview__steps">
                <li data-preview="step-order">{{ trans('specialgift::messages.step_order') }}</li>
                <li data-preview="step-details">{{ trans('specialgift::messages.step_details') }}</li>
                <li data-preview="step-send">{{ trans('specialgift::messages.step_send') }}</li>
            </ol>

            <div class="gv-live-preview__card">
                <p class="gv-live-preview__card-label" data-preview="preview-label">
                    {{ trans('specialgift::messages.preview_label') }}
                </p>
                <div class="gv-live-preview__voucher"></div>
                <p class="gv-live-preview__trust" data-preview="trust">
                    {{ trans('specialgift::messages.trust_note') }}
                </p>
            </div>

            <div class="gv-live-preview__form">
                <p class="gv-live-preview__form-title" data-preview="form-title">
                    {{ trans('specialgift::messages.form_title') }}
                </p>
                <div class="gv-live-preview__field"></div>
                <div class="gv-live-preview__field"></div>
                <button type="button" class="gv-live-preview__submit" data-preview="submit">
                    {{ trans('specialgift::messages.submit') }}
                </button>
            </div>
        </div>
    </div>

    <p class="gv-live-preview__note">{{ trans('specialgift::admin.live_preview_note') }}</p>
</aside>
