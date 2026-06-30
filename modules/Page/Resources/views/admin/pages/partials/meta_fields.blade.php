@php
    $metaTranslation = $entity->meta?->translate(locale(), true);
    $ogImageId = old('meta.og_image_id', $metaTranslation?->og_image_id);
    $ogFile = ($ogImageId && (int) $ogImageId > 0)
        ? \Modules\Media\Entities\File::findOrNew((int) $ogImageId)
        : new \Modules\Media\Entities\File();
    $ogPreviewSrc = ($ogFile->exists && $ogFile->path) ? $ogFile->path : '';
    $robots = old('meta.meta_robots', $metaTranslation?->meta_robots ?? 'index, follow');
@endphp

<div class="page-seo-field">
    <label for="meta-title" class="page-seo-field__label">
        {{ trans('meta::attributes.meta_title') }}
    </label>
    <input type="text"
        name="meta[meta_title]"
        class="form-control page-seo-field__input"
        id="meta-title"
        maxlength="70"
        data-seo-counter="meta-title"
        value="{{ old('meta.meta_title', $metaTranslation?->meta_title ?? '') }}"
    >
    <p class="page-seo-field__hint">{{ trans('page::pages.form.seo_title_hint') }}</p>
    <p class="page-seo-field__count" id="meta-title-count" aria-live="polite"></p>
</div>

<div class="page-seo-field">
    <label for="meta-description" class="page-seo-field__label">
        {{ trans('meta::attributes.meta_description') }}
    </label>
    <textarea name="meta[meta_description]"
        class="form-control page-seo-field__input"
        id="meta-description"
        rows="4"
        maxlength="320"
        data-seo-counter="meta-description"
    >{{ old('meta.meta_description', $metaTranslation?->meta_description ?? '') }}</textarea>
    <p class="page-seo-field__hint">{{ trans('page::pages.form.seo_description_hint') }}</p>
    <p class="page-seo-field__count" id="meta-description-count" aria-live="polite"></p>
</div>

<div class="page-seo-field page-seo-field--og">
    @include('media::admin.image_picker.single', [
        'title' => trans('meta::attributes.og_image'),
        'aspect' => 'og',
        'inputName' => 'meta[og_image_id]',
        'file' => $ogFile,
    ])
    <p class="page-seo-field__hint">{{ trans('page::pages.form.seo_og_image_hint') }}</p>
</div>

<div class="page-seo-field">
    <label for="meta-robots" class="page-seo-field__label">
        {{ trans('meta::attributes.meta_robots') }}
    </label>
    <select name="meta[meta_robots]" id="meta-robots" class="form-control custom-select-black">
        <option value="index, follow" {{ $robots === 'index, follow' ? 'selected' : '' }}>
            {{ trans('page::pages.form.robots_index') }}
        </option>
        <option value="noindex, follow" {{ $robots === 'noindex, follow' ? 'selected' : '' }}>
            {{ trans('page::pages.form.robots_noindex') }}
        </option>
    </select>
    <p class="page-seo-field__hint">{{ trans('page::pages.form.seo_robots_hint') }}</p>
</div>

<div class="page-seo-preview" id="page-seo-social-preview">
    <p class="page-seo-preview__label">{{ trans('page::pages.form.seo_preview') }}</p>
    <div class="page-seo-preview__card">
        <div class="page-seo-preview__image{{ $ogPreviewSrc ? ' page-seo-preview__image--filled' : '' }}" id="page-seo-preview-image" data-empty-label="{{ trans('page::pages.form.seo_preview_empty') }}">
            @if ($ogPreviewSrc)
                <img src="{{ $ogPreviewSrc }}" alt="" id="page-seo-preview-image-img">
            @else
                <div class="page-seo-preview__placeholder" id="page-seo-preview-placeholder">
                    <i class="fa fa-image" aria-hidden="true"></i>
                    <span>{{ trans('page::pages.form.seo_preview_empty') }}</span>
                </div>
            @endif
        </div>
        <div class="page-seo-preview__body">
            <span class="page-seo-preview__site">{{ parse_url(url('/'), PHP_URL_HOST) }}</span>
            <strong class="page-seo-preview__title" id="page-seo-preview-title"></strong>
            <p class="page-seo-preview__description" id="page-seo-preview-description"></p>
        </div>
    </div>
</div>
