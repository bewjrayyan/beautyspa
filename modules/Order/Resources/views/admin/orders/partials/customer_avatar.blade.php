@php
    $avatarUrl = $order->customerAvatarUrl();
    $avatarClass = 'order-show__customer-avatar' . ($avatarUrl ? ' order-show__customer-avatar--photo' : ' order-show__customer-avatar--initial');
@endphp

@if ($customerProfileUrl ?? null)
    @hasAccess('admin.users.edit')
        <a
            href="{{ $customerProfileUrl }}"
            class="order-show__customer-avatar-link"
            title="{{ $order->customer_full_name }}"
        >
    @endHasAccess
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

@if ($customerProfileUrl ?? null)
    @hasAccess('admin.users.edit')
        </a>
    @endHasAccess
@endif
