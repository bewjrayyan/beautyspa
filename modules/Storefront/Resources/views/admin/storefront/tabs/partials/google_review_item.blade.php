<div class="google-reviews-item box m-b-15" data-index="{{ $index }}">
    <div class="row">
        <div class="col-sm-4">
            <label>{{ trans('storefront::storefront.form.review_author') }}</label>
            <input type="text" class="form-control gr-item-author" value="{{ $review['author'] }}">
        </div>
        <div class="col-sm-3">
            <label>{{ trans('storefront::storefront.form.review_date') }}</label>
            <input type="text" class="form-control gr-item-date" value="{{ $review['date'] }}" placeholder="18 APR 2025">
        </div>
        <div class="col-sm-2">
            <label>{{ trans('storefront::storefront.form.review_rating') }}</label>
            <input type="number" class="form-control gr-item-rating" min="1" max="5" value="{{ $review['rating'] }}">
        </div>
        <div class="col-sm-2">
            <label>{{ trans('storefront::storefront.form.review_likes') }}</label>
            <input type="number" class="form-control gr-item-likes" min="0" value="{{ $review['likes'] }}">
        </div>
        <div class="col-sm-1 text-right">
            <label>&nbsp;</label>
            <button type="button" class="btn btn-danger btn-block gr-item-remove" title="{{ trans('storefront::storefront.form.remove_review') }}">
                <i class="fa fa-trash"></i>
            </button>
        </div>
        <div class="col-sm-12 m-t-10">
            <label>{{ trans('storefront::storefront.form.review_text') }}</label>
            <textarea class="form-control gr-item-text" rows="3">{{ $review['text'] }}</textarea>
        </div>
    </div>
</div>
