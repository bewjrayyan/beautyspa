<div
    class="variant-sheet d-lg-none"
    :class="{ 'is-open': !!openVariationUid }"
    x-cloak
    @keydown.escape.window="closeVariationSheet()"
>
    <div class="variant-sheet__backdrop" @click="closeVariationSheet()"></div>

    <div
        class="variant-sheet__panel"
        role="dialog"
        aria-modal="true"
        :aria-label="openVariationUid ? '{{ trans('storefront::product.select_option') }}' : ''"
    >
        @foreach ($product->variations as $variation)
            <div
                class="variant-sheet__content"
                x-show="openVariationUid === '{{ $variation->uid }}'"
            >
                <div class="variant-sheet__header">
                    <h3 class="variant-sheet__title">{{ $variation->name }}</h3>

                    <button
                        type="button"
                        class="variant-sheet__close"
                        aria-label="{{ trans('storefront::product.close') }}"
                        @click="closeVariationSheet()"
                    >
                        <i class="las la-times"></i>
                    </button>
                </div>

                <div class="variant-sheet__body">
                    <ul class="variant-sheet__list">
                        @foreach ($variation->values as $value)
                            <li
                                class="
                                    {{ $variation->type === 'text' ? 'variation-text' : '' }}
                                    {{ $variation->type === 'color' ? 'variation-color' : '' }}
                                    {{ $variation->type === 'image' ? 'variation-image' : '' }}
                                "
                                :class="{
                                    active: isActiveVariationValue('{{ $variation->uid }}', '{{ $value->uid }}'),
                                    disabled: !isVariationValueEnabled('{{ $variation->uid }}', {{ $loop->parent->index }}, '{{ $value->uid }}')
                                }"
                                @click="syncVariationValue(
                                    '{{ $variation->uid }}',
                                    {{ $loop->parent->index }},
                                    '{{ $value->uid }}',
                                    {{ $loop->index }}
                                )"
                            >
                                <div class="variant-sheet__option-main">
                                    @if ($variation->type === 'text')
                                        <span class="variant-sheet__option-label">{{ $value->label }}</span>
                                    @elseif ($variation->type === 'color')
                                        <span class="variant-sheet__option-label variant-sheet__option-label--color">
                                            <span class="variant-sheet__swatch" style="background-color: {{ $value->color }};"></span>
                                            {{ $value->label }}
                                        </span>
                                    @elseif ($variation->type === 'image')
                                        <span class="variant-sheet__option-label variant-sheet__option-label--image">
                                            <img src="{{ $value->image->path }}" alt="{{ $value->label }}">
                                            {{ $value->label }}
                                        </span>
                                    @endif

                                    <span
                                        class="variant-sheet__option-price"
                                        x-text="formatVariationValuePrice('{{ $variation->uid }}', '{{ $value->uid }}')"
                                    ></span>
                                </div>

                                <span class="variant-sheet__option-check" aria-hidden="true">
                                    <i class="las la-check"></i>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach
    </div>
</div>
