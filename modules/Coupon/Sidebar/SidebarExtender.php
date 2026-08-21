<?php

namespace Modules\Coupon\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.commerce'), function (Group $group) {
            $group->item(trans('coupon::coupons.coupons'), function (Item $item) {
                $item->icon('fa fa-tags');
                $item->weight(15);
                $item->route('admin.coupons.index');
                $item->authorize(
                    $this->auth->hasAccess('admin.coupons.index')
                );
            });
        });
    }
}
