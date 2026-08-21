<?php

namespace Modules\FlashSale\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.commerce'), function (Group $group) {
            $group->item(trans('flashsale::flash_sales.flash_sales'), function (Item $item) {
                $item->icon('fa fa-bolt');
                $item->weight(20);
                $item->route('admin.flash_sales.index');
                $item->authorize(
                    $this->auth->hasAccess('admin.flash_sales.index')
                );
            });
        });
    }
}
