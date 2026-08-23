@extends('storefront::public.account.layout')

@section('title', trans('storefront::account.pages.my_reviews'))

@section('account_breadcrumb')
    <li class="active">{{ trans('storefront::account.pages.my_reviews') }}</li>
@endsection

@section('panel')
    <div x-data="Reviews" class="account-reviews-campaign">
        <section class="review-campaign-hero" aria-labelledby="review-campaign-title">
            <div class="review-campaign-hero__content">
                <span class="review-campaign-hero__eyebrow">
                    <i class="las la-gift" aria-hidden="true"></i>
                    {{ trans('storefront::account.reviews.reward_campaign') }}
                </span>

                <h1 id="review-campaign-title">{{ trans('storefront::account.reviews.hero_title') }}</h1>
                <p>{{ trans('storefront::account.reviews.hero_description') }}</p>

                @if ($reviewRewardPoints > 0)
                    <div class="review-campaign-hero__reward">
                        <span class="review-campaign-hero__reward-icon" aria-hidden="true"><i class="las la-coins"></i></span>
                        <span>
                            <strong>{{ trans('storefront::account.reviews.earn_points', ['points' => number_format($reviewRewardPoints)]) }}</strong>
                            <small>{{ trans('storefront::account.reviews.reward_terms') }}</small>
                        </span>
                    </div>
                @endif
            </div>

            <div class="review-campaign-hero__summary" aria-label="{{ trans('storefront::account.reviews.campaign_summary') }}">
                <div>
                    <strong>{{ $pendingReviewItems->count() }}</strong>
                    <span>{{ trans_choice('storefront::account.reviews.awaiting_count', $pendingReviewItems->count(), ['count' => $pendingReviewItems->count()]) }}</span>
                </div>

                @if ($reviewRewardPoints > 0)
                    <div>
                        <strong>{{ number_format($pendingReviewItems->count() * $reviewRewardPoints) }}</strong>
                        <span>{{ trans('storefront::account.reviews.available_points') }}</span>
                    </div>
                @endif
            </div>
        </section>

        <section class="review-campaign-section" aria-labelledby="pending-reviews-title">
            <div class="review-campaign-section__header">
                <div>
                    <span class="review-campaign-section__kicker">{{ trans('storefront::account.reviews.to_review') }}</span>
                    <h2 id="pending-reviews-title">{{ trans('storefront::account.reviews.pending_title') }}</h2>
                    <p>{{ trans('storefront::account.reviews.pending_description') }}</p>
                </div>

                @if ($pendingReviewItems->isNotEmpty())
                    <span class="review-campaign-section__count">
                        {{ trans_choice('storefront::account.reviews.item_count', $pendingReviewItems->count(), ['count' => $pendingReviewItems->count()]) }}
                    </span>
                @endif
            </div>

            @if ($pendingReviewItems->isNotEmpty())
                <div class="pending-review-list">
                    @foreach ($pendingReviewItems as $item)
                        <article class="pending-review-card">
                            <div class="pending-review-card__image">
                                <img
                                    src="{{ $item['image'] ?: asset('build/assets/image-placeholder.png') }}"
                                    class="{{ $item['image'] ? '' : 'image-placeholder' }}"
                                    alt="{{ $item['name'] }}"
                                    loading="lazy"
                                >
                            </div>

                            <div class="pending-review-card__content">
                                <div class="pending-review-card__meta">
                                    <span>{{ trans('storefront::account.reviews.order_number', ['id' => $item['order_id']]) }}</span>
                                    <span aria-hidden="true">•</span>
                                    <span>{{ $item['order_date'] }}</span>
                                </div>
                                <h3>{{ $item['name'] }}</h3>

                                @if ($reviewRewardPoints > 0)
                                    <div class="pending-review-card__reward">
                                        <i class="las la-coins" aria-hidden="true"></i>
                                        {{ trans('storefront::account.reviews.card_reward', ['points' => number_format($reviewRewardPoints)]) }}
                                    </div>
                                @endif
                            </div>

                            <a href="{{ $item['review_url'] }}" class="btn btn-primary pending-review-card__action">
                                <i class="las la-star" aria-hidden="true"></i>
                                {{ trans('storefront::account.reviews.review_now') }}
                            </a>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="review-campaign-empty">
                    <span class="review-campaign-empty__icon" aria-hidden="true"><i class="las la-check-circle"></i></span>
                    <div>
                        <h3>{{ trans('storefront::account.reviews.all_caught_up') }}</h3>
                        <p>{{ trans('storefront::account.reviews.all_caught_up_description') }}</p>
                    </div>
                </div>
            @endif
        </section>

        <section class="review-campaign-section review-history" aria-labelledby="review-history-title">
            <div class="review-campaign-section__header">
                <div>
                    <span class="review-campaign-section__kicker">{{ trans('storefront::account.reviews.your_activity') }}</span>
                    <h2 id="review-history-title">{{ trans('storefront::account.reviews.history_title') }}</h2>
                    <p>{{ trans('storefront::account.reviews.history_description') }}</p>
                </div>
            </div>

            <div x-cloak class="review-history__body" :class="{ loading: fetchingReviews }">
                <template x-if="reviewIsEmpty">
                    <div class="review-campaign-empty review-campaign-empty--muted">
                        <template x-if="!fetchingReviews">
                            <div>
                                <h3>{{ trans('storefront::account.reviews.no_reviews') }}</h3>
                                <p>{{ trans('storefront::account.reviews.no_reviews_description') }}</p>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="!reviewIsEmpty">
                    <div class="review-history-list">
                        <template x-for="review in reviews.data" :key="review.id">
                            <article class="review-history-card" x-data="ReviewItem(review.product)">
                                <div class="review-history-card__image">
                                    <img :src="baseImage" :class="{ 'image-placeholder': !hasBaseImage }" :alt="productName" loading="lazy">
                                </div>
                                <div class="review-history-card__content">
                                    <a :href="productUrl" class="review-history-card__name" x-text="productName"></a>
                                    <div class="review-history-card__rating">
                                        @include('storefront::public.partials.product_rating', ['data' => 'review'])
                                    </div>
                                    <div class="review-history-card__meta">
                                        <span class="badge" :class="review.is_approved ? 'badge-success' : 'badge-warning'" x-text="review.status"></span>
                                        <span x-text="review.created_at_formatted"></span>
                                    </div>
                                </div>
                            </article>
                        </template>
                    </div>
                </template>
            </div>

            <div class="review-history__footer">
                <template x-if="reviews.total > 10">
                    @include('storefront::public.partials.pagination')
                </template>
            </div>
        </section>
    </div>
@endsection

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/account/reviews/main.scss',
        'modules/Storefront/Resources/assets/public/js/pages/account/reviews/main.js',
    ])
@endpush
