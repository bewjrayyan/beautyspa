<section x-data="HomeFeatures" class="features-wrap">
    @php
        $featureTints = [
            '#fce7f3',
            '#ede9fe',
            '#d1fae5',
            '#ffedd5',
            '#e0f2fe',
            '#fef3c7',
            '#f3e8ff',
            '#ecfdf5',
        ];
    @endphp

    <div class="container">
        <div class="features swiper overflow-hidden">
            <div class="feature-list swiper-wrapper" x-ref="featureList">
                @foreach ($features as $index => $feature)
                    <div
                        class="single-feature swiper-slide"
                        style="--feature-tint: {{ $featureTints[$index % count($featureTints)] }};"
                    >
                        <div class="feature-icon">
                            <i class="{{ $feature->icon }}"></i>
                        </div>

                        <div class="feature-details">
                            <h6>{{ $feature->title }}</h6>
                            
                            <span>{{ $feature->subtitle }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
    </div>
</section>
