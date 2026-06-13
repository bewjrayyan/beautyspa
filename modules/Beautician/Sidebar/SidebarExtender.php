<?php

namespace Modules\Beautician\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(trans('beautician::sidebar.beauticians'), function (Item $item) {
                $item->icon('fa fa-user-md');
                $item->weight(18);
                $item->route('admin.beauticians.index');
                $item->authorize(
                    $this->auth->hasAnyAccess([
                        'admin.beauticians.index',
                        'admin.beautician_job_titles.index',
                    ])
                );

                $item->item(trans('beautician::sidebar.beauticians'), function (Item $item) {
                    $item->weight(5);
                    $item->route('admin.beauticians.index');
                    $item->authorize(
                        $this->auth->hasAccess('admin.beauticians.index')
                    );
                });

                $item->item(trans('beautician::sidebar.job_titles'), function (Item $item) {
                    $item->weight(10);
                    $item->route('admin.beautician_job_titles.index');
                    $item->authorize(
                        $this->auth->hasAccess('admin.beautician_job_titles.index')
                    );
                });
            });
        });
    }
}
