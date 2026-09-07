<?php

namespace Modules\Shipping\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.catalog'), function (Group $group) {
            $group->item(trans('product::sidebar.products'), function (Item $item) {
                $item->item(trans('shipping::sidebar.shipping_classes'), function (Item $item) {
                    $item->weight(25);
                    $item->route('admin.shipping_classes.index');
                    $item->authorize(
                        $this->auth->hasAccess('admin.shipping_classes.index')
                    );
                });
            });
        });
    }
}
