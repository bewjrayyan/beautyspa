<?php

namespace Modules\Admin\Sidebar\Presentation;

use Illuminate\Contracts\View\Factory;
use Maatwebsite\Sidebar\Group;

class GroupRenderer
{
    public function __construct(protected Factory $factory)
    {
    }

    public function render(Group $group): ?string
    {
        if (! $group->isAuthorized()) {
            return null;
        }

        $items = [];
        foreach ($group->getItems() as $item) {
            $rendered = (new ItemRenderer($this->factory))->render($item);
            if ($rendered !== null) {
                $items[] = $rendered;
            }
        }

        return $this->factory->make('sidebar::group', [
            'group' => $group,
            'items' => $items,
        ])->render();
    }
}
