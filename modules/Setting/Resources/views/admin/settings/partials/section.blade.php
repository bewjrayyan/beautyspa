@php
    $icon = $icon ?? 'fa-cog';
    $title = $title ?? '';
    $description = $description ?? null;
    $class = trim('st-section ' . ($class ?? ''));
@endphp

<section class="{{ $class }}">
    @if ($title !== '')
        <header class="st-section__head">
            <h5 class="st-section__title">
                @if ($icon)
                    <i class="fa {{ $icon }}" aria-hidden="true"></i>
                @endif
                {{ $title }}
            </h5>
            @if (! empty($description))
                <p class="st-section__desc">{{ $description }}</p>
            @endif
        </header>
    @endif

    <div class="st-section__body">
        @if (! empty($columns) && (int) $columns === 2)
            <div class="st-fields-grid st-fields-grid--in-section">
                {{ $slot }}
            </div>
        @else
            {{ $slot }}
        @endif
    </div>
</section>
