<?php

namespace Modules\Admin\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.overview'), function (Group $group) {
            $group->weight(1);
            $group->hideHeading();

            $group->item(trans('admin::dashboard.dashboard'), function (Item $item) {
                $item->icon('fa fa-dashboard');
                $item->weight(1);
                $item->route('admin.dashboard.index');
                $item->isActiveWhen(route('admin.dashboard.index', null, false));
                $item->authorize(! $this->auth->user()?->isBeauticianOnly());
            });
        });

        $menu->group(trans('admin::sidebar.operations'), function (Group $group) {
            $group->weight(2);
        });

        $menu->group(trans('admin::sidebar.catalog'), function (Group $group) {
            $group->weight(3);
        });

        $menu->group(trans('admin::sidebar.commerce'), function (Group $group) {
            $group->weight(4);
        });

        $menu->group(trans('admin::sidebar.website'), function (Group $group) {
            $group->weight(5);
        });

        $menu->group(trans('admin::sidebar.insights'), function (Group $group) {
            $group->weight(6);
        });

        $menu->group(trans('admin::sidebar.system'), function (Group $group) {
            $group->weight(10);

            $group->item(trans('admin::sidebar.appearance'), function (Item $item) {
                $item->icon('fa fa-paint-brush');
                $item->weight(15);
                $item->route('admin.sliders.index');
                $item->authorize(
                    ! $this->auth->user()?->isBeauticianOnly()
                    && $this->auth->hasAnyAccess(['admin.sliders.index', 'admin.storefront.edit'])
                );
            });
        });
    }
}
