@hasAccess('admin.media.index')
    @php
        $hasFile = $file->exists && filled($file->path);
        $mime = (string) ($file->mime ?? '');
        $isVideo = $hasFile && str_starts_with($mime, 'video/');
    @endphp

    <div class="ac-media-field ac-media-field--banner mobile-promo-video-picker" data-input-name="{{ $inputName }}">
        <div class="ac-media-field__canvas{{ $hasFile ? ' is-filled' : '' }}">
            <div class="ac-media-dropzone mobile-promo-video-dropzone{{ $hasFile ? ' hide' : '' }}">
                <div class="ac-media-dropzone__content">
                    <span class="ac-media-dropzone__icon" aria-hidden="true">
                        <i class="fa fa-film"></i>
                    </span>
                    <p class="ac-media-dropzone__title">{{ trans('storefront::storefront.form.mobile_home_promo_video_dropzone') }}</p>
                    <p class="ac-media-dropzone__hint">{{ trans('storefront::storefront.form.mobile_home_promo_video_hint') }}</p>

                    <div class="ac-media-dropzone__actions">
                        <button type="button" class="btn btn-default btn-sm video-picker-browse" data-input-name="{{ $inputName }}">
                            <i class="fa fa-folder-open" aria-hidden="true"></i>
                            {{ trans('media::media.browse_library') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="ac-media-preview mobile-promo-video-preview{{ $hasFile ? '' : ' hide' }}">
                @if ($hasFile)
                    <div class="ac-media-preview__inner mobile-promo-video-preview__inner">
                        @if ($isVideo)
                            <video src="{{ $file->path }}" controls playsinline preload="metadata"></video>
                        @else
                            <div class="mobile-promo-video-preview__placeholder">
                                <i class="fa fa-file-video-o" aria-hidden="true"></i>
                                <span>{{ $file->filename }}</span>
                            </div>
                        @endif

                        <button
                            type="button"
                            class="ac-media-preview__remove remove-video"
                            data-input-name="{{ $inputName }}"
                            aria-label="{{ trans('storefront::storefront.form.mobile_home_promo_remove_video') }}"
                        >
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </button>

                        <div class="ac-media-preview__overlay">
                            <button type="button" class="btn btn-default btn-sm video-picker-browse" data-input-name="{{ $inputName }}">
                                <i class="fa fa-refresh" aria-hidden="true"></i>
                                {{ trans('storefront::storefront.form.mobile_home_promo_replace_video') }}
                            </button>
                        </div>

                        <input type="hidden" name="{{ $inputName }}" value="{{ $file->id }}">
                    </div>
                @endif
            </div>
        </div>
    </div>
@endHasAccess
