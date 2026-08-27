<?php

namespace Modules\Admin\Sidebar;

use Maatwebsite\Sidebar\Item;

class AdminBreadcrumbTrail
{
    public function __construct(
        protected AdminSidebar $sidebar,
        protected ActiveStateChecker $activeStateChecker,
    ) {
    }

    /**
     * @return array<int, array{label: string, url: string|null, active: bool}>
     */
    public function crumbs(): array
    {
        $best = [];
        $menu = $this->sidebar->getMenu();

        foreach ($menu->getGroups() as $group) {
            foreach ($group->getItems() as $item) {
                $trail = $this->findTrail($item, []);

                if ($trail !== null && count($trail) > count($best)) {
                    $best = $trail;
                }
            }
        }

        return $best;
    }

    /**
     * @param  array<int, array{label: string, url: string|null, active: bool}>  $ancestors
     * @return array<int, array{label: string, url: string|null, active: bool}>|null
     */
    protected function findTrail(Item $item, array $ancestors): ?array
    {
        if (! $item->isAuthorized()) {
            return null;
        }

        $current = array_merge($ancestors, [[
            'label' => (string) $item->getName(),
            'url' => $this->usableUrl($item),
            'active' => false,
        ]]);

        $bestChild = null;

        foreach ($item->getItems() as $child) {
            $found = $this->findTrail($child, $current);

            if ($found !== null && ($bestChild === null || count($found) > count($bestChild))) {
                $bestChild = $found;
            }
        }

        if ($bestChild !== null) {
            return $bestChild;
        }

        if (! $this->activeStateChecker->isActive($item)) {
            return null;
        }

        $last = count($current) - 1;
        $current[$last]['active'] = true;
        $current[$last]['url'] = null;

        return $current;
    }

    protected function usableUrl(Item $item): ?string
    {
        $url = trim((string) $item->getUrl());

        if ($url === '' || $url === '#') {
            return null;
        }

        return $url;
    }
}
