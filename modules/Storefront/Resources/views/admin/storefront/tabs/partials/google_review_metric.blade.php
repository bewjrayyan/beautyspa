<div class="google-reviews-metric box m-b-10" data-index="{{ $index }}">
    <div class="row">
        <div class="col-sm-5">
            <label>{{ trans('storefront::storefront.form.metric_label') }}</label>
            <input type="text" class="form-control gr-metric-label" value="{{ $metric['label'] }}">
        </div>
        <div class="col-sm-3">
            <label>{{ trans('storefront::storefront.form.metric_percent') }}</label>
            <input type="number" class="form-control gr-metric-percent" min="0" max="100" value="{{ $metric['percent'] }}">
        </div>
        <div class="col-sm-3">
            <label>{{ trans('storefront::storefront.form.metric_sentiment') }}</label>
            <input type="text" class="form-control gr-metric-sentiment" value="{{ $metric['sentiment'] }}" placeholder="Great">
        </div>
        <div class="col-sm-1 text-right">
            <label>&nbsp;</label>
            <button type="button" class="btn btn-danger btn-block gr-metric-remove" title="{{ trans('storefront::storefront.form.remove_metric') }}">
                <i class="fa fa-trash"></i>
            </button>
        </div>
    </div>
</div>
