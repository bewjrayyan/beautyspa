@if (setting('storefront_product_consultation_enabled', true))
    <div @class(['product-consultation-cta', $class ?? null])>
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
