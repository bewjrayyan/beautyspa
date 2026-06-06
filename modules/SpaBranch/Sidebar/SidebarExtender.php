<?php

namespace Modules\SpaBranch\Sidebar;

use Maatwebsite\Sidebar\Group;
use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(trans('spabranch::sidebar.spa_branches'), function (Item $item) {
                $item->icon('fa fa-building');
                $item->weight(19);
                $item->route('admin.spa_branches.index');
                $item->authorize(
                    $this->auth->hasAccess('admin.spa_branches.index')
                );
            });
        });
    }
}
