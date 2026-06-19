@if ($orderReviewItems->isNotEmpty())
    <section class="account-order-show__section account-order-reviews">
        <h2 class="account-order-show__section-title">
            <i class="las la-star"></i>
            {{ trans('storefront::account.view_order.leave_reviews') }}
        </h2>

        <p class="account-order-reviews__hint">
            {{ trans('storefront::account.view_order.leave_reviews_hint') }}
        </p>

        <div class="account-order-reviews__list">
            @foreach ($orderReviewItems as $item)
                <div
                    class="account-order-review-card"
                    x-data="OrderProductReview({
                        productId: {{ $item['product_id'] }},
                        productName: {{ \Illuminate\Support\Js::from($item['name']) }},
                        productUrl: {{ \Illuminate\Support\Js::from($item['url']) }},
                        productImage: {{ \Illuminate\Support\Js::from($item['image']) }},
                        reviewerName: {{ \Illuminate\Support\Js::from($reviewerName) }},
                        existingReview: {{ \Illuminate\Support\Js::from($item['review']) }}
                    })"
                >
                    <div class="account-order-review-card__header">
                        <a :href="productUrl" class="account-order-review-card__thumb">
                            <img
                                :src="productImage || '{{ asset('build/assets/image-placeholder.png') }}'"
                                :class="{ 'image-placeholder': !productImage }"
                                :alt="productName"
                                loading="lazy"
                            >
                        </a>

                        <div class="account-order-review-card__info">
                            <a :href="productUrl" class="account-order-review-card__name" x-text="productName"></a>

                            <template x-if="hasReview">
                                <div class="account-order-review-card__meta">
                                    <span
                                        class="badge"
                                        :class="submittedReview.is_approved ? 'badge-success' : 'badge-warning'"
                                        x-text="submittedReview.status"
                                    ></span>
                                    <span class="account-order-review-card__date" x-text="submittedReview.created_at_formatted"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <template x-if="hasReview">
                        <div class="account-order-review-card__submitted">
                            <div class="account-order-review-card__stars">
                                @include('storefront::public.partials.product_rating', [
                                    'data' => 'submittedReview',
                                ])
                            </div>

                            <p class="account-order-review-card__comment" x-text="submittedReview.comment"></p>
                        </div>
                    </template>

                    <template x-if="!hasReview">
                        <form
                            x-ref="reviewForm"
                            class="account-order-review-card__form"
                            @submit.prevent="addNewReview"
                            @input="errors.clear($event.target.name)"
                        >
                            @honeypot

                            <div class="form-group">
                                <label>{{ trans('storefront::product.review_form.your_rating') }}<span>*</span></label>

                                <div class="account-order-review-card__rating">
                                    <input type="radio" name="rating" :value="5" x-model.number="reviewForm.rating" id="order-review-star-5-{{ $item['product_id'] }}">
                                    <label for="order-review-star-5-{{ $item['product_id'] }}" @click="reviewForm.rating = 5">
                                        <i class="las la-star"></i>
                                    </label>

                                    <input type="radio" name="rating" :value="4" x-model.number="reviewForm.rating" id="order-review-star-4-{{ $item['product_id'] }}">
                                    <label for="order-review-star-4-{{ $item['product_id'] }}" @click="reviewForm.rating = 4">
                                        <i class="las la-star"></i>
                                    </label>

                                    <input type="radio" name="rating" :value="3" x-model.number="reviewForm.rating" id="order-review-star-3-{{ $item['product_id'] }}">
                                    <label for="order-review-star-3-{{ $item['product_id'] }}" @click="reviewForm.rating = 3">
                                        <i class="las la-star"></i>
                                    </label>

                                    <input type="radio" name="rating" :value="2" x-model.number="reviewForm.rating" id="order-review-star-2-{{ $item['product_id'] }}">
                                    <label for="order-review-star-2-{{ $item['product_id'] }}" @click="reviewForm.rating = 2">
                                        <i class="las la-star"></i>
                                    </label>

                                    <input type="radio" name="rating" :value="1" x-model.number="reviewForm.rating" id="order-review-star-1-{{ $item['product_id'] }}">
                                    <label for="order-review-star-1-{{ $item['product_id'] }}" @click="reviewForm.rating = 1">
                                        <i class="las la-star"></i>
                                    </label>
                                </div>

                                <template x-if="errors.has('rating')">
                                    <span class="error-message" x-text="errors.get('rating')"></span>
                                </template>
                            </div>

                            <div class="form-group">
                                <label for="order-review-name-{{ $item['product_id'] }}">
                                    {{ trans('storefront::product.review_form.name') }}<span>*</span>
                                </label>

                                <input
                                    type="text"
                                    name="reviewer_name"
                                    autocomplete="name"
                                    id="order-review-name-{{ $item['product_id'] }}"
                                    class="form-control"
                                    x-model="reviewForm.reviewer_name"
                                >

                                <template x-if="errors.has('reviewer_name')">
                                    <span class="error-message" x-text="errors.get('reviewer_name')"></span>
                                </template>
                            </div>

                            <div class="form-group">
                                <label for="order-review-comment-{{ $item['product_id'] }}">
                                    {{ trans('storefront::product.review_form.comment') }}<span>*</span>
                                </label>

                                <textarea
                                    rows="4"
                                    name="comment"
                                    id="order-review-comment-{{ $item['product_id'] }}"
                                    class="form-control"
                                    x-model="reviewForm.comment"
                                ></textarea>

                                <template x-if="errors.has('comment')">
                                    <span class="error-message" x-text="errors.get('comment')"></span>
                                </template>
                            </div>

                            <button
                                type="submit"
                                class="btn btn-primary account-order-review-card__submit"
                                :class="{ 'btn-loading': addingNewReview }"
                            >
                                {{ trans('storefront::product.review_form.submit_review') }}
                            </button>
                        </form>
                    </template>
                </div>
            @endforeach
        </div>
    </section>
@endif
