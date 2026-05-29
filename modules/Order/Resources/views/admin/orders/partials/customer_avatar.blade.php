@php
    $avatarUrl = $order->customerAvatarUrl();
    $avatarClass = 'order-show__customer-avatar' . ($avatarUrl ? ' order-show__customer-avatar--photo' : ' order-show__customer-avatar--initial');
@endphp

@if ($order->customer_id)
    @can('admin.users.edit')
        <a
            href="{{ route('admin.users.edit', $order->customer_id) }}"
            class="order-show__customer-avatar-link"
            title="{{ $order->customer_full_name }}"
        >
    @endcan
@endif

<span
    class="{{ $avatarClass }}"
    @unless ($avatarUrl) style="background-color: {{ $order->customerAvatarBackgroundColor() }};" @endunless
>
    @if ($avatarUrl)
        <img src="{{ $avatarUrl }}" alt="{{ $order->customer_full_name }}">
    @else
        {{ $order->customerAvatarInitial() }}
    @endif
</span>

@if ($order->customer_id)
    @can('admin.users.edit')
        </a>
    @endcan
@endif
