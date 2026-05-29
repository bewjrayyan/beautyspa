<?php

namespace Modules\Setting\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.system'), function (Group $group) {
            $group->item(trans('setting::sidebar.settings'), function (Item $item) {
                $item->weight(25);
                $item->icon('fa fa-cogs');
                $item->authorize(
                    $this->auth->hasAccess('admin.settings.edit')
                );

                $item->item(trans('setting::sidebar.all_settings'), function (Item $child) {
                    $child->weight(1);
                    $child->route('admin.settings.edit');
                    $child->authorize(
                        $this->auth->hasAccess('admin.settings.edit')
                    );
                });

                $item->item(trans('setting::sidebar.onesender_queue'), function (Item $child) {
                    $child->weight(4);
                    $child->route('admin.onesender_queue.index');
                    $child->authorize(
                        $this->auth->hasAccess('admin.settings.edit')
                    );
                });

                $item->item(trans('setting::sidebar.onesender_logs'), function (Item $child) {
                    $child->weight(5);
                    $child->route('admin.onesender_logs.index');
                    $child->authorize(
                        $this->auth->hasAccess('admin.settings.edit')
                    );
                });
            });
        });
    }
}
