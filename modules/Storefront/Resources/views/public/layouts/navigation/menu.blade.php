<li class="{{ mega_menu_classes($menu, $type) }}">
    @php
        $isCategoryMenu = ($type ?? null) === 'category_menu';
        $menuIcon = $isCategoryMenu
            ? category_menu_item_icon($menu)
            : ($menu->hasIcon() ? $menu->icon() : null);
        $menuLabel = $isCategoryMenu ? category_menu_item_label($menu->name()) : $menu->name();
    @endphp

    <a
        href="{{ $menu->url() }}"
        class="nav-link menu-item"
        target="{{ $menu->target() }}"
        title="{{ $menu->name() }}"
    >
        @if ($menuIcon)
            <span class="menu-item-icon">
                <i class="{{ $menuIcon }}"></i>
            </span>
        @endif

        <span class="menu-item-text">{{ $menuLabel }}</span>

        @if ($menu->hasSubMenus())
            <i class="las la-angle-right"></i>
        @endif
    </a>

    @if ($menu->isFluid())
        @include('storefront::public.layouts.navigation.fluid', ['subMenus' => $menu->subMenus()])
    @else
        @include('storefront::public.layouts.navigation.dropdown', ['subMenus' => $menu->subMenus()])
    @endif
</li>
