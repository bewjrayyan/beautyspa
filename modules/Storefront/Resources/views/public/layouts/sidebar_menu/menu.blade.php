<ul class="list-inline sidebar-menu">
    @foreach ($menu->menus() as $menu)
        <li
            class="{{ $menu->hasSubMenus() ? 'dropdown multi-level' : '' }}"
            @click="
                $($el).children('ul.list-inline').slideToggle(200);
                $($el).toggleClass('active');
            "
        >
            <a
                href="{{ $menu->url() }}"
                class="menu-item"
                target="{{ $menu->target() }}"
                title="{{ $menu->name() }}"
                @click.stop
            >
                @if ($type === 'category_menu')
                    <span class="menu-item-icon">
                        <i class="{{ category_menu_item_icon($menu) }}"></i>
                    </span>

                    <span class="menu-item-text">{{ category_menu_item_label($menu->name()) }}</span>
                @else
                    @if ($menu->hasIcon())
                        <span class="menu-item-icon">
                            <i class="{{ $menu->icon() }}"></i>
                        </span>
                    @endif

                    {{ $menu->name() }}
                @endif
            </a>

            @if ($menu->hasSubMenus())
                <i class="las la-angle-right"></i>
            @endif

            @if ($menu->hasSubMenus())
                @include('storefront::public.layouts.sidebar_menu.dropdown', ['subMenus' => $menu->subMenus()])
            @endif
        </li>
    @endforeach

    @if ($type === 'category_menu')
        <li class="more-categories">
            <a href="{{ storefront_route('categories.index') }}" class="menu-item">
                <span class="menu-item-icon">
                    <i class="las la-th-list"></i>
                </span>

                {{ trans('storefront::layouts.all_categories') }}
            </a>
        </li>
    @endif
</ul>
