@php
    $hasChildren = $item->hasItems();
    $classes = trim(implode(' ', array_filter([
        $item->getItemClass() ?: null,
        $active ? 'active' : null,
        $hasChildren ? 'treeview' : null,
        'clearfix',
    ])));
@endphp
<li class="{{ $classes }}" @if($hasChildren) data-sidebar-treeview @endif>
    <a
        href="{{ $item->getUrl() }}"
        class="sidebar-menu__link @if(count($appends) > 0) hasAppend @endif"
        @if($item->getNewTab()) target="_blank" rel="noopener" @endif
        @if($hasChildren)
            aria-expanded="{{ $active ? 'true' : 'false' }}"
            data-sidebar-toggle
        @endif
    >
        <span class="sidebar-menu__icon" aria-hidden="true">
            <i class="{{ $item->getIcon() }}"></i>
        </span>
        <span class="sidebar-menu__label">{{ $item->getName() }}</span>

        @foreach($badges as $badge)
            {!! $badge !!}
        @endforeach

        @if($hasChildren)
            <span class="pull-right-container" aria-hidden="true">
                <i class="fa fa-angle-left sidebar-menu__chevron"></i>
            </span>
        @endif
    </a>

    @foreach($appends as $append)
        {!! $append !!}
    @endforeach

    @if(count($items) > 0)
        <ul class="treeview-menu" @if($active) style="display: block;" @endif>
            @foreach($items as $child)
                {!! $child !!}
            @endforeach
        </ul>
    @endif
</li>
