<?php

namespace Modules\Page\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(trans('page::sidebar.pages'), function (Item $item) {
                $item->icon('fa fa-file');
                $item->weight(25);
                $item->route('admin.pages.index');
                $item->authorize(
                    $this->auth->hasAccess('admin.pages.index')
                );
            });

            $group->item(trans('page::sidebar.legal_content'), function (Item $item) {
                $item->icon('fa fa-shield');
                $item->weight(26);
                $item->route('admin.legal_content.index');
                $item->isActiveWhen(route('admin.legal_content.index', null, false));
                $item->authorize($this->auth->hasAccess('admin.pages.index'));
            });
        });
    }
}
