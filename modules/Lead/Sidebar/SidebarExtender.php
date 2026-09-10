<?php

namespace Modules\Lead\Sidebar;

use Maatwebsite\Sidebar\Group;
use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.insights'), function (Group $group) {
            $group->item(trans('lead::sidebar.central_management'), function (Item $item) {
                $item->icon('fa fa-th-large');
                $item->weight(7);
                $item->route('admin.leads.central');
                $item->isNewTab(true);
                $item->authorize(
                    $this->auth->hasAccess('admin.leads.index')
                );
            });
        });
    }
}
