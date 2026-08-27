<?php

namespace Modules\Admin\Sidebar\Presentation;

use Illuminate\Contracts\View\Factory;
use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Presentation\Illuminate\IlluminateAppendRenderer;
use Maatwebsite\Sidebar\Presentation\Illuminate\IlluminateBadgeRenderer;
use Modules\Admin\Sidebar\ActiveStateChecker;

class ItemRenderer
{
    public function __construct(protected Factory $factory)
    {
    }

    public function render(Item $item): ?string
    {
        if (! $item->isAuthorized()) {
            return null;
        }

        $items = [];
        foreach ($item->getItems() as $child) {
            $rendered = (new self($this->factory))->render($child);
            if ($rendered !== null) {
                $items[] = $rendered;
            }
        }

        $badges = [];
        foreach ($item->getBadges() as $badge) {
            $badges[] = (new IlluminateBadgeRenderer($this->factory))->render($badge);
        }

        $appends = [];
        foreach ($item->getAppends() as $append) {
            $appends[] = (new IlluminateAppendRenderer($this->factory))->render($append);
        }

        return $this->factory->make('sidebar::item', [
            'item' => $item,
            'items' => $items,
            'badges' => $badges,
            'appends' => $appends,
            'active' => (new ActiveStateChecker())->isActive($item),
        ])->render();
    }
}
