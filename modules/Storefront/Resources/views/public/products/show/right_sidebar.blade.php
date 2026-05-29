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

    @if (setting('storefront_product_consultation_enabled', true))
        <div class="product-consultation-cta">
            <a
                href="{{ product_consultation_url($product) }}"
                class="btn btn-primary btn-consultation"
                target="_blank"
                rel="noopener noreferrer"
            >
                {{ product_consultation_label() }}
            </a>
        </div>
    @endif
</aside>
