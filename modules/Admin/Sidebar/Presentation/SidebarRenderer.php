<?php

namespace Modules\Admin\Sidebar\Presentation;

use Illuminate\Contracts\View\Factory;
use Maatwebsite\Sidebar\Presentation\SidebarRenderer as SidebarRendererContract;
use Maatwebsite\Sidebar\Sidebar;

class SidebarRenderer implements SidebarRendererContract
{
    protected string $view = 'sidebar::menu';

    public function __construct(protected Factory $factory)
    {
    }

    public function render(Sidebar $sidebar)
    {
        $menu = $sidebar->getMenu();

        if (! $menu->isAuthorized()) {
            return null;
        }

        $groups = [];
        foreach ($menu->getGroups() as $group) {
            $rendered = (new GroupRenderer($this->factory))->render($group);
            if ($rendered !== null) {
                $groups[] = $rendered;
            }
        }

        return $this->factory->make($this->view, [
            'groups' => $groups,
        ]);
    }
}
