@hasAccess('admin.media.index')
    @php
        $hasFile = $file->exists;
        $showLabel = filled($title ?? '');
        $aspect = $aspect ?? 'logo';
        $defaultPreviewUrl = $defaultPreviewUrl ?? null;
        $defaultPreviewBadge = $defaultPreviewBadge ?? null;
        $usingDefaultPreview = ! $hasFile && filled($defaultPreviewUrl);
        $fieldClass = 'single-image-wrapper ac-media-field';

        if ($aspect === 'square') {
            $fieldClass .= ' ac-media-field--square';
        } elseif ($aspect === 'banner') {
            $fieldClass .= ' ac-media-field--banner';
        } elseif ($aspect === 'logo') {
            $fieldClass .= ' ac-media-field--logo';
        }
    @endphp

    <div
        class="{{ $fieldClass }}"
        data-input-name="{{ $inputName }}"
        @if (filled($defaultPreviewUrl))
            data-default-preview-url="{{ $defaultPreviewUrl }}"
            @if (filled($defaultPreviewBadge))
                data-default-preview-badge="{{ $defaultPreviewBadge }}"
            @endif
        @endif
    >
        @if ($showLabel)
            <label class="ac-media-field__label">{{ $title }}</label>
        @endif

        <div class="ac-media-field__canvas{{ ($hasFile || $usingDefaultPreview) ? ' is-filled' : '' }}">
            <div
                class="ac-media-dropzone{{ ($hasFile || $usingDefaultPreview) ? ' hide' : '' }}"
                tabindex="0"
                role="button"
                aria-label="{{ trans('media::media.dropzone_title') }}"
            >
                <div class="ac-media-dropzone__content">
                    <span class="ac-media-dropzone__icon" aria-hidden="true">
                        <i class="fa fa-cloud-upload"></i>
                    </span>
                    <p class="ac-media-dropzone__title">{{ trans('media::media.dropzone_title') }}</p>
                    <p class="ac-media-dropzone__hint">{{ trans('media::media.dropzone_hint') }}</p>

                    <div class="ac-media-dropzone__actions">
                        <button type="button" class="btn btn-default btn-sm image-picker-browse" data-input-name="{{ $inputName }}">
                            <i class="fa fa-folder-open" aria-hidden="true"></i>
                            {{ trans('media::media.browse_library') }}
                        </button>
                    </div>
                </div>

                <div class="ac-media-dropzone__progress" aria-hidden="true">
                    <span class="ac-media-dropzone__progress-bar"></span>
                </div>

                <input
                    type="file"
                    class="ac-media-dropzone__file"
                    accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml"
                    tabindex="-1"
                >
            </div>

            <div class="ac-media-preview single-image image-holder-wrapper{{ ($hasFile || $usingDefaultPreview) ? '' : ' hide' }}">
                @if ($hasFile)
                    <div class="ac-media-preview__inner image-holder">
                        <img src="{{ $file->path }}" alt="">

                        <button
                            type="button"
                            class="ac-media-preview__remove remove-image"
                            data-input-name="{{ $inputName }}"
                            aria-label="{{ trans('media::media.remove_image') }}"
                        >
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </button>

                        <div class="ac-media-preview__overlay">
                            <button type="button" class="btn btn-default btn-sm image-picker-browse" data-input-name="{{ $inputName }}">
                                <i class="fa fa-refresh" aria-hidden="true"></i>
                                {{ trans('media::media.replace_image') }}
                            </button>
                        </div>

                        <input type="hidden" name="{{ $inputName }}" value="{{ $file->id }}">
                    </div>
                @elseif ($usingDefaultPreview)
                    <div class="ac-media-preview__inner image-holder ac-media-preview__inner--default">
                        <img src="{{ $defaultPreviewUrl }}" alt="">

                        @if (filled($defaultPreviewBadge))
                            <span class="ac-media-preview__badge">{{ $defaultPreviewBadge }}</span>
                        @endif

                        <div class="ac-media-preview__overlay">
                            <button type="button" class="btn btn-default btn-sm image-picker-browse" data-input-name="{{ $inputName }}">
                                <i class="fa fa-refresh" aria-hidden="true"></i>
                                {{ trans('media::media.replace_image') }}
                            </button>
                        </div>

                        <input type="hidden" name="{{ $inputName }}" value="">
                    </div>
                @endif
            </div>
        </div>
    </div>
@endHasAccess
