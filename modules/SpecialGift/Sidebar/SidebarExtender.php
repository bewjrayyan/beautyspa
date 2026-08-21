<?php

namespace Modules\SpecialGift\Sidebar;

use Maatwebsite\Sidebar\Group;
use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.commerce'), function (Group $group) {
            $group->item(trans('specialgift::sidebar.gifts'), function (Item $item) {
                $item->icon('fa fa-gift');
                $item->weight(25);
                $item->route('admin.gift_voucher_submissions.index');
                $item->authorize($this->auth->hasAccess('admin.gift_voucher_submissions.index'));
            });
        });
    }
}
