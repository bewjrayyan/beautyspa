<div class="product-variants-mobile d-lg-none">
    @foreach ($product->variations as $variation)
        <button
            type="button"
            class="variant-picker-row"
            :class="{ 'variant-picker-row--selected': !!activeVariationValues['{{ $variation->uid }}'] }"
            @click="openVariationSheet('{{ $variation->uid }}')"
        >
            <span class="variant-picker-row__lead">
                <span class="variant-picker-row__icon" aria-hidden="true">
                    @if ($variation->type === 'color')
                        <i class="las la-palette"></i>
                    @elseif ($variation->type === 'image')
                        <i class="las la-image"></i>
                    @elseif ($product->is_virtual)
                        <i class="las la-spa"></i>
                    @else
                        <i class="las la-sliders-h"></i>
                    @endif
                </span>

                <span class="variant-picker-row__label">{{ $variation->name }}</span>
            </span>

            <span class="variant-picker-row__value">
                <span
                    class="variant-picker-row__selection"
                    x-show="activeVariationValues['{{ $variation->uid }}']"
                    x-text="activeVariationValues['{{ $variation->uid }}']"
                ></span>

                <span
                    class="variant-picker-row__placeholder"
                    x-show="!activeVariationValues['{{ $variation->uid }}']"
                >
                    {{ trans('storefront::product.options.choose_an_option') }}
                </span>
            </span>

            <span class="variant-picker-row__chevron" aria-hidden="true">
                <i class="las la-angle-right"></i>
            </span>
        </button>
    @endforeach
</div>

<div class="product-variants-desktop d-none d-lg-block">
    @foreach ($product->variations as $variation)
        <div class="variant-custom-selection">
            <div
                class="variant-selection-summary"
                :class="{ 'is-selected': !!activeVariationValues['{{ $variation->uid }}'] }"
            >
                <div class="variant-selection-summary__inner">
                    <span class="variant-selection-summary__label">
                        {{ $variation->name }}
                    </span>

                    <span
                        class="variant-selection-summary__value"
                        x-show="activeVariationValues['{{ $variation->uid }}']"
                        x-text="activeVariationValues['{{ $variation->uid }}']"
                    ></span>

                    <span
                        class="variant-selection-summary__placeholder"
                        x-show="!activeVariationValues['{{ $variation->uid }}']"
                    >
                        {{ trans('storefront::product.options.choose_an_option') }}
                    </span>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-18">
                    <ul class="list-inline form-custom-radio custom-selection">
                        @foreach ($variation->values as $value)
                            <li
                                :title="
                                    isVariationValueEnabled('{{ $variation->uid }}', {{ $loop->parent->index }}, '{{ $value->uid }}') &&
                                    !isActiveVariationValue('{{ $variation->uid }}', '{{ $value->uid }}') ?
                                    '{{ trans('storefront::product.click_to_select') }} {{ $value->label }}' :
                                    ''
                                "
                                class="
                                    {{ $variation->type === 'color' ? 'variation-color' : '' }}
                                    {{ $variation->type === 'image' ? 'variation-image' : '' }}
                                "
                                :class="{
                                    active: isActiveVariationValue('{{ $variation->uid }}', '{{ $value->uid }}'),
                                    disabled: !isVariationValueEnabled('{{ $variation->uid }}', {{ $loop->parent->index }}, '{{ $value->uid }}')
                                }"
                                @mouseenter="previewVariationValue({{ $loop->parent->index }}, {{ $loop->index }})"
                                @mouseleave="setActiveVariationValueLabel({{ $loop->parent->index }})"
                                @click="syncVariationValue(
                                    '{{ $variation->uid }}',
                                    {{ $loop->parent->index }},
                                    '{{ $value->uid }}',
                                    {{ $loop->index }}
                                )"
                            >
                                @if ($variation->type === 'text')
                                    {{ $value->label }}
                                @elseif ($variation->type === 'color')
                                    <div style="background-color: {{ $value->color }};"></div>
                                @elseif ($variation->type === 'image')
                                    <img src="{{ $value->image->path }}" alt="{{ $value->label }}">
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endforeach
</div>
