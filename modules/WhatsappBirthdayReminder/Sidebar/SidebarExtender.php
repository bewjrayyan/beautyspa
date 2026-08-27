<?php

namespace Modules\WhatsappBirthdayReminder\Sidebar;

use Maatwebsite\Sidebar\Menu;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        // Settings live under Admin → Settings → WhatsApp (tab=sms).
        // No top-level sidebar item.
    }
}
