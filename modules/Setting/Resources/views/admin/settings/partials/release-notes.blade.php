@php
    $entry = $entry ?? null;
    $highlight = ! empty($highlight);
@endphp

@if (! empty($entry))
    <div class="app-release-notes {{ $highlight ? 'is-highlight' : '' }}">
        <div class="app-release-notes__head">
            <strong class="app-release-notes__version">v{{ $entry['version'] }}</strong>
            @if (! empty($entry['summary']))
                <p class="app-release-notes__summary">{{ $entry['summary'] }}</p>
            @endif
        </div>

        @if (! empty($entry['changes']))
            <ul class="app-release-notes__list">
                @foreach ($entry['changes'] as $change)
                    <li>{{ $change }}</li>
                @endforeach
            </ul>
        @endif
    </div>
@endif
