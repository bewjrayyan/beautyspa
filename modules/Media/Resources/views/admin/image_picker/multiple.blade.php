<div class="multiple-images-wrapper ac-media-field ac-media-field--multiple" data-input-name="{{ $inputName }}">
    @if (filled($title ?? ''))
        <label class="ac-media-field__label">{{ $title }}</label>
    @endif

    <div class="ac-media-dropzone ac-media-dropzone--compact" tabindex="0" role="button" aria-label="{{ trans('media::media.dropzone_title') }}">
        <div class="ac-media-dropzone__content">
            <span class="ac-media-dropzone__icon" aria-hidden="true">
                <i class="fa fa-cloud-upload"></i>
            </span>
            <p class="ac-media-dropzone__title">{{ trans('media::media.dropzone_title_multiple') }}</p>
            <p class="ac-media-dropzone__hint">{{ trans('media::media.dropzone_hint') }}</p>

            <div class="ac-media-dropzone__actions">
                <button type="button" class="btn btn-default btn-sm image-picker-browse" data-input-name="{{ $inputName }}" data-multiple>
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
            multiple
            tabindex="-1"
        >
    </div>

    <div class="multiple-images">
        <div class="image-list image-holder-wrapper ac-media-preview-grid clearfix">
            @if ($files->isEmpty())
                <div class="image-holder placeholder cursor-auto ac-media-preview-grid__empty">
                    <i class="fa fa-picture-o" aria-hidden="true"></i>
                </div>
            @else
                @foreach ($files as $file)
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

                        <input type="hidden" name="{{ $inputName }}" value="{{ $file->id }}">
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
