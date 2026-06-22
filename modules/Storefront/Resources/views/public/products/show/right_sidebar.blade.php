<aside class="right-sidebar">
    @if (setting('storefront_features_section_enabled') && $features->isNotEmpty())
        <div class="feature-list">
            @foreach ($features as $feature)
                <div class="single-feature">
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
    @endif

    @include('storefront::public.products.show.consultation_cta', ['product' => $product])
</aside>
