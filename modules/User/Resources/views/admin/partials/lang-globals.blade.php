@php
    $userLangGroups = array_filter([
        'user::users.index' => __('user::users.index'),
        'user::users.create_page' => __('user::users.create_page'),
    ]);
@endphp

<script>
    @foreach ($userLangGroups as $prefix => $lines)
        @foreach ($lines as $key => $line)
            AestheticCart.langs['{{ $prefix }}.{{ $key }}'] = @json($line);
        @endforeach
    @endforeach
    @foreach ([
        'loyalty_browse',
        'loyalty_enroll_button',
        'loyalty_enroll_confirm',
        'loyalty_enroll_bulk_button',
        'loyalty_enroll_bulk_confirm',
        'loyalty_enroll_bulk_select_hint',
    ] as $loyaltyIndexKey)
        AestheticCart.langs['user::users.index.{{ $loyaltyIndexKey }}'] = @json(__('user::users.index.' . $loyaltyIndexKey));
    @endforeach
    AestheticCart.langs['user::users.navigation.back_to_index'] = @json(trans('user::users.navigation.back_to_index'));
</script>
