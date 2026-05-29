<div class="page-editor-general">
    <div class="page-editor-title">
        {{ Form::text('name', trans('page::attributes.name'), $errors, $page, [
            'required' => true,
            'placeholder' => trans('page::pages.form.title_placeholder'),
            'class' => 'page-editor-title__input',
        ]) }}
    </div>

    <div class="page-editor-content" data-page-content-editor>
        <div class="page-editor-content__head">
            <div class="page-editor-content__head-main">
                <h2 class="page-editor-content__title">{{ trans('page::pages.form.content') }}</h2>
                <p class="page-editor-content__subtitle">{{ trans('page::pages.form.content_subtitle') }}</p>
            </div>

            <div class="page-editor-content__modes" role="tablist" aria-label="{{ trans('page::pages.form.editor_mode') }}">
                <button type="button" class="page-editor-content__mode page-editor-content__mode--active" data-editor-mode="visual" role="tab" aria-selected="true">
                    <i class="fa fa-eye" aria-hidden="true"></i>
                    {{ trans('page::pages.form.mode_visual') }}
                </button>
                <button type="button" class="page-editor-content__mode" data-editor-mode="code" role="tab" aria-selected="false">
                    <i class="fa fa-code" aria-hidden="true"></i>
                    {{ trans('page::pages.form.mode_code') }}
                </button>
            </div>
        </div>

        <div class="page-editor-content__canvas">
            {{ Form::wysiwyg('body', trans('page::attributes.body'), $errors, $page, [
                'labelCol' => 0,
                'required' => true,
                'class' => 'page-content-editor',
                'rows' => 16,
            ]) }}
        </div>

        <ul class="page-editor-content__guidelines">
            <li>{{ trans('page::pages.form.guideline_headings') }}</li>
            <li>{{ trans('page::pages.form.guideline_images') }}</li>
            <li>{{ trans('page::pages.form.guideline_links') }}</li>
            <li>{{ trans('page::pages.form.guideline_seo') }}</li>
        </ul>
    </div>
</div>
