<?php

namespace Modules\Menu\MegaMenu;

use Throwable;
use Illuminate\Support\Facades\Cache;
use Modules\Menu\Entities\Menu as MenuModel;

class MegaMenu
{
    private $menuId;


    public function __construct($menuId)
    {
        $this->menuId = $menuId;
    }


    public function menus()
    {
        try {
            return Cache::tags(['mega_menu', 'menu_items', 'pages', 'categories'])
                ->rememberForever(md5("mega_menu.{$this->menuId}:" . locale()), function () {
                    return $this->mapMenus();
                });
        } catch (Throwable) {
            return $this->mapMenus();
        }
    }


    private function getMenus()
    {
        return MenuModel::for($this->menuId)->where('menu_id', $this->menuId);
    }

    private function mapMenus()
    {
        return $this->getMenus()->map(function ($menu) {
            return new Menu($menu);
        });
    }
}
