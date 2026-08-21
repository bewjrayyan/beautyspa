<?php

namespace Modules\Menu\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.website'), function (Group $group) {
            $group->item(trans('menu::sidebar.menus'), function (Item $item) {
                $item->weight(15);
                $item->icon('fa fa-bars');
                $item->route('admin.menus.index');
                $item->authorize(
                    $this->auth->hasAccess('admin.menus.index')
                );
            });
        });
    }
}
