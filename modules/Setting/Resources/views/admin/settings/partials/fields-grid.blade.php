@php
    $class = trim('st-fields-grid ' . ($class ?? ''));
@endphp

<div class="{{ $class }}">
    @if (! empty($left))
        <div class="st-fields-grid__col">
            {!! $left !!}
        </div>
    @endif

    @if (! empty($right))
        <div class="st-fields-grid__col">
            {!! $right !!}
        </div>
    @endif

    @if (! empty($full))
        <div class="st-fields-grid__full">
            {!! $full !!}
        </div>
    @endif

    {{ $slot ?? '' }}
</div>
