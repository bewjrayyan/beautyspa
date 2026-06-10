<li class="{{ mega_menu_classes($menu, $type) }}">
    <a
        href="{{ $menu->url() }}"
        class="nav-link menu-item"
        target="{{ $menu->target() }}"
        title="{{ $menu->name() }}"
    >
        @php($menuIcon = $menu->hasIcon() ? $menu->icon() : (($type ?? null) === 'category_menu' ? 'las la-tag' : null))

        @if ($menuIcon)
            <span class="menu-item-icon">
                <i class="{{ $menuIcon }}"></i>
            </span>
        @endif

        {{ $menu->name() }}

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
