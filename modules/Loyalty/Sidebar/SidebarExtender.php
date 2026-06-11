<?php

namespace Modules\Loyalty\Sidebar;

use Maatwebsite\Sidebar\Group;
use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(trans('loyalty::sidebar.loyalty'), function (Item $item) {
                $item->icon('fa fa-star');
                $item->weight(19);
                $item->authorize(
                    $this->auth->hasAccess('admin.loyalty.members.index')
                    || $this->auth->hasAccess('admin.loyalty.tiers.index')
                    || $this->auth->hasAccess('admin.loyalty.stamp_programs.index')
                    || $this->auth->hasAccess('admin.loyalty.reports.index')
                );

                $item->item(trans('loyalty::sidebar.members'), function (Item $child) {
                    $child->weight(1);
                    $child->route('admin.loyalty.members.index');
                    $child->authorize($this->auth->hasAccess('admin.loyalty.members.index'));
                });

                $item->item(trans('loyalty::sidebar.tiers'), function (Item $child) {
                    $child->weight(2);
                    $child->route('admin.loyalty.tiers.index');
                    $child->authorize($this->auth->hasAccess('admin.loyalty.tiers.index'));
                });

                $item->item(trans('loyalty::sidebar.stamp_programs'), function (Item $child) {
                    $child->weight(3);
                    $child->route('admin.loyalty.stamp_programs.index');
                    $child->authorize($this->auth->hasAccess('admin.loyalty.stamp_programs.index'));
                });

                $item->item(trans('loyalty::sidebar.reports'), function (Item $child) {
                    $child->weight(4);
                    $child->route('admin.loyalty.reports.index');
                    $child->authorize($this->auth->hasAccess('admin.loyalty.reports.index'));
                });
            });
        });
    }
}
