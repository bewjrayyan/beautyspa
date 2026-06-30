<aside class="gv-live-preview gv-live-preview--design" aria-label="{{ trans('specialgift::admin.design_preview_title') }}">
    <div class="gv-live-preview__head">
        <span class="gv-live-preview__badge">{{ trans('specialgift::admin.live_preview_badge') }}</span>
        <h5 class="gv-live-preview__title">{{ trans('specialgift::admin.design_preview_title') }}</h5>
    </div>

    <div class="gv-live-preview__device gv-live-preview__device--wide">
        <div
            class="gv-design-preview__canvas gv-design-preview__canvas--rich"
            id="gift-voucher-design-preview-canvas"
            data-store-color="{{ $storeThemeColor }}"
        >
            <span class="gv-design-preview__orb gv-design-preview__orb--one"></span>
            <span class="gv-design-preview__orb gv-design-preview__orb--two"></span>
            <span class="gv-design-preview__sparkle gv-design-preview__sparkle--1">✦</span>
            <span class="gv-design-preview__sparkle gv-design-preview__sparkle--2">♥</span>

            <div class="gv-design-preview__mock">
                <p class="gv-design-preview__mock-tagline">{{ trans('specialgift::messages.page_tagline') }}</p>
                <h6 class="gv-design-preview__mock-title">{{ trans('specialgift::messages.page_title') }}</h6>

                <div class="gv-design-preview__mock-grid">
                    <div class="gv-design-preview__mock-card gv-design-preview__mock-card--preview">
                        <span>{{ trans('specialgift::messages.preview_label') }}</span>
                    </div>
                    <div class="gv-design-preview__mock-card gv-design-preview__mock-card--form">
                        <span>{{ trans('specialgift::messages.form_title') }}</span>
                        <button type="button">{{ trans('specialgift::messages.submit') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <p class="gv-live-preview__note">{{ trans('specialgift::admin.design_preview_help') }}</p>
</aside>
