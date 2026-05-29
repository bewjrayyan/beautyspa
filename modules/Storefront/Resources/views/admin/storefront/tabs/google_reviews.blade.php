@php
    $googleReviews = \Modules\Storefront\Support\GoogleReviewsSettings::items(
        setting('storefront_google_reviews_items')
    );
    $googleMetrics = \Modules\Storefront\Support\GoogleReviewsSettings::decodeMetrics(
        setting('storefront_google_reviews_metrics')
    );
@endphp

<div class="row google-reviews-admin">
    <div class="col-md-8">
        {{ Form::checkbox('storefront_google_reviews_section_enabled', trans('storefront::attributes.section_status'), trans('storefront::storefront.form.enable_google_reviews_section'), $errors, $settings) }}
        {{ Form::text('translatable[storefront_google_reviews_section_title]', trans('storefront::attributes.section_title'), $errors, $settings) }}

        <div class="row">
            <div class="col-sm-6">
                {{ Form::number('storefront_google_reviews_rating', trans('storefront::attributes.google_reviews_rating'), $errors, $settings, ['min' => 0, 'max' => 5, 'step' => '0.01', 'value' => setting('storefront_google_reviews_rating', 3.75)]) }}
            </div>
            <div class="col-sm-6">
                {{ Form::number('storefront_google_reviews_review_count', trans('storefront::attributes.google_reviews_review_count'), $errors, $settings, ['min' => 0, 'value' => setting('storefront_google_reviews_review_count', 1297)]) }}
            </div>
        </div>

        <hr>

        <h4 class="m-t-0">{{ trans('storefront::storefront.form.google_reviews_items') }}</h4>
        <p class="help-block text-info">
            <i class="fa fa-info-circle"></i>
            {{ trans('storefront::storefront.form.google_reviews_edit_help') }}
        </p>

        <input type="hidden" name="storefront_google_reviews_items" id="google-reviews-items-json" value='@json($googleReviews)'>

        <div id="google-reviews-items-list" class="google-reviews-items-list">
            @foreach ($googleReviews as $index => $review)
                @include('storefront::admin.storefront.tabs.partials.google_review_item', [
                    'index' => $index,
                    'review' => $review,
                ])
            @endforeach
        </div>

        <button type="button" class="btn btn-default m-t-10" id="google-reviews-add-item">
            <i class="fa fa-plus"></i> {{ trans('storefront::storefront.form.add_review') }}
        </button>

        <hr>

        <h4>{{ trans('storefront::storefront.form.google_reviews_metrics') }}</h4>
        <p class="help-block">{{ trans('storefront::storefront.form.google_reviews_metrics_help') }}</p>

        <input type="hidden" name="storefront_google_reviews_metrics" id="google-reviews-metrics-json" value='@json($googleMetrics)'>

        <div id="google-reviews-metrics-list" class="google-reviews-metrics-list">
            @foreach ($googleMetrics as $index => $metric)
                @include('storefront::admin.storefront.tabs.partials.google_review_metric', [
                    'index' => $index,
                    'metric' => $metric,
                ])
            @endforeach
        </div>

        <button type="button" class="btn btn-default m-t-10" id="google-reviews-add-metric">
            <i class="fa fa-plus"></i> {{ trans('storefront::storefront.form.add_metric') }}
        </button>
    </div>
</div>
