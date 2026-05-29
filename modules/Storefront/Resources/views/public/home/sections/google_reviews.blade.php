<section x-data="GoogleReviews" class="google-reviews-section">
    <div class="container">
        <div class="google-reviews-card">
            <h3 class="google-reviews-heading">{{ $googleReviews['title'] }}</h3>

            <div class="google-reviews-grid">
                <div class="google-reviews-left">
                    <div class="google-reviews-summary">
                        <div class="google-reviews-score">{{ $googleReviews['ratingDisplay'] }}</div>

                        <div class="google-reviews-stars" aria-label="{{ $googleReviews['ratingDisplay'] }} out of 5">
                            @for ($i = 0; $i < $googleReviews['stars']['full']; $i++)
                                <i class="las la-star is-filled"></i>
                            @endfor

                            @if ($googleReviews['stars']['half'])
                                <i class="las la-star-half-alt is-filled"></i>
                            @endif

                            @for ($i = 0; $i < $googleReviews['stars']['empty']; $i++)
                                <i class="las la-star is-empty"></i>
                            @endfor
                        </div>

                        @if ($googleReviews['reviewCount'] > 0)
                            <p class="google-reviews-count">
                                ({{ number_format($googleReviews['reviewCount']) }}
                                {{ trans('storefront::google_reviews.reviews') }})
                            </p>
                        @endif
                    </div>

                    @if (count($googleReviews['items']) > 0)
                        <div class="google-reviews-carousel-wrap">
                            <h4 class="google-reviews-subheading">
                                {{ trans('storefront::google_reviews.most_liked_comments') }}
                            </h4>

                            <div class="google-reviews-carousel swiper">
                                <div class="swiper-wrapper">
                                    @foreach ($googleReviews['items'] as $review)
                                        <div class="swiper-slide">
                                            <article class="google-review-card">
                                                <div class="google-review-card-header">
                                                    <div class="google-review-avatar">
                                                        <img
                                                            src="https://ui-avatars.com/api/?name={{ urlencode($review['author']) }}&background=E8F0FE&color=1E5EFF&size=96"
                                                            alt="{{ $review['author'] }}"
                                                            loading="lazy"
                                                        >
                                                    </div>

                                                    <div class="google-review-meta">
                                                        <strong class="google-review-author">{{ $review['author'] }}</strong>

                                                        @if ($review['date'])
                                                            <span class="google-review-date">{{ $review['date'] }}</span>
                                                        @endif

                                                        <div class="google-review-stars" aria-hidden="true">
                                                            @for ($i = 1; $i <= 5; $i++)
                                                                <i class="las la-star {{ $i <= $review['rating'] ? 'is-filled' : 'is-empty' }}"></i>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                </div>

                                                @if ($review['text'])
                                                    <p class="google-review-text">{{ $review['text'] }}</p>
                                                @endif

                                                @if ($review['likes'] > 0)
                                                    <div class="google-review-likes">
                                                        <i class="las la-thumbs-up"></i>
                                                        <span>{{ number_format($review['likes']) }} {{ trans('storefront::google_reviews.liked') }}</span>
                                                    </div>
                                                @endif
                                            </article>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="swiper-button-prev"></div>
                                <div class="swiper-button-next"></div>
                            </div>
                        </div>
                    @endif
                </div>

                @if (count($googleReviews['metrics']) > 0)
                    <div class="google-reviews-right">
                        <h4 class="google-reviews-subheading">
                            {{ trans('storefront::google_reviews.element_of_evaluation') }}
                        </h4>

                        <ul class="google-reviews-metrics">
                            @foreach ($googleReviews['metrics'] as $metric)
                                @php
                                    $percent = $metric['percent'];
                                    $tone = match (true) {
                                        $percent >= 80 => 'great',
                                        $percent >= 60 => 'good',
                                        $percent >= 40 => 'average',
                                        $percent >= 20 => 'bad',
                                        default => 'worst',
                                    };
                                @endphp

                                <li class="google-reviews-metric is-{{ $tone }}">
                                    <div class="google-reviews-metric-top">
                                        <span class="google-reviews-metric-percent">{{ $percent }}%</span>
                                        <span class="google-reviews-metric-label">
                                            {{ trans('storefront::google_reviews.average') }}:
                                            {{ $metric['sentiment'] }}
                                        </span>
                                    </div>

                                    <div class="google-reviews-metric-bar">
                                        <span class="google-reviews-metric-fill" style="width: {{ $percent }}%"></span>
                                    </div>

                                    <span class="google-reviews-metric-name">{{ $metric['label'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
