<a href="{{ $authLogoHref }}" class="auth-form-header-logo">
    @if (! empty($logo))
        <img src="{{ $logo }}" alt="{{ setting('store_name') }}">
    @else
        <h3>{{ setting('store_name') }}</h3>
    @endif
</a>
