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
    AestheticCart.langs['user::users.navigation.back_to_index'] = @json(trans('user::users.navigation.back_to_index'));
</script>
