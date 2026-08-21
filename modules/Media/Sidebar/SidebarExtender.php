<?php

namespace Modules\Media\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.website'), function (Group $group) {
            $group->item(trans('media::media.media'), function (Item $item) {
                $item->weight(25);
                $item->icon('fa fa-camera');
                $item->route('admin.media.index');
                $item->authorize(
                    $this->auth->hasAccess('admin.media.index')
                );
            });
        });
    }
}
