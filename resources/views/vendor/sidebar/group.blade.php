@if($group->shouldShowHeading())
    <li class="menu-title" role="presentation">
        <span class="menu-title__text">{{ $group->getName() }}</span>
    </li>
@endif

@foreach($items as $item)
    {!! $item !!}
@endforeach
