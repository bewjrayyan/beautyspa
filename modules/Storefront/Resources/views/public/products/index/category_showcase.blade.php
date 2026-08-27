@php
    $showcase = category_showcase_copy($category);
@endphp

<div class="category-showcase category-showcase--{{ $showcase['theme'] }}">
    <div class="category-showcase-content">
        <span class="category-showcase-eyebrow">
            {{ $showcase['eyebrow'] }}
        </span>

        <h1>{{ $showcase['title'] }}</h1>

        <p>{{ $showcase['description'] }}</p>

        @if (($subCategories ?? collect())->isNotEmpty())
            <div class="category-showcase-links" aria-label="{{ $showcase['shop_by_category'] }}">
                @foreach ($subCategories as $subCategory)
                    @if ($subCategory->products_count > 0)
                        <a href="{{ $subCategory->url() }}">
                            <span>{{ $subCategory->name }}</span>
                            <i class="las la-arrow-right" aria-hidden="true"></i>
                        </a>
                    @else
                        <span class="is-coming-soon">
                            <span>{{ $subCategory->name }}</span>
                            <small>{{ $showcase['coming_soon'] }}</small>
                        </span>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    <div class="category-showcase-art" aria-hidden="true">
        <span class="category-showcase-orb category-showcase-orb-one"></span>
        <span class="category-showcase-orb category-showcase-orb-two"></span>
        <i class="{{ $showcase['icon'] }}"></i>
    </div>
</div>
