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
