<?php

namespace Modules\BeauticianReport\Sidebar;

use Maatwebsite\Sidebar\Group;
use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu): void
    {
        $menu->group(trans('admin::sidebar.insights'), function (Group $group) {
            $group->item(trans('beauticianreport::sidebar.analytics'), function (Item $item) {
                $item->icon('fa fa-line-chart');
                $item->weight(10);
                $item->route('admin.beautician_reports.index');
                $item->authorize(
                    $this->auth->hasAccess('admin.beautician_reports.index')
                );
            });
        });
    }
}
