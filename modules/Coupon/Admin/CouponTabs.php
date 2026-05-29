<?php

namespace Modules\Coupon\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;
use Modules\Coupon\Entities\Coupon;
use Modules\Category\Entities\Category;

class CouponTabs extends Tabs
{
    protected $buttonOffset = false;

    public function make()
    {
        $paneOpen = $this->tabPaneOpen();
        $paneClose = $this->tabPaneClose();

        $this->group('coupon_information', trans('coupon::coupons.tabs.group.coupon_information'))
            ->active()
            ->add($this->general($paneOpen, $paneClose))
            ->add($this->usageRestrictions($paneOpen, $paneClose))
            ->add($this->usageLimits($paneOpen, $paneClose));
    }


    public function render($data = [])
    {
        $this->activateTabFromQuery();

        $coupon = $data['coupon'] ?? null;

        return view('coupon::admin.coupons.form_wrapper', [
            'navTabs' => $this->sortedNavTabs(),
            'contents' => $this->contents($data),
            'buttonOffset' => $this->buttonOffset,
            'formUrl' => $coupon?->exists
                ? route('admin.coupons.edit', $coupon)
                : route('admin.coupons.create'),
        ]);
    }


    protected function sortedNavTabs(): array
    {
        $errors = request()->session()->get('errors') ?: new \Illuminate\Support\ViewErrorBag;
        $items = [];

        foreach (array_keys($this->groups) as $groupName) {
            $sorted = collect($this->tabs[$groupName] ?? [])->sortBy(fn (Tab $tab) => $tab->getWeight());

            foreach ($sorted as $tab) {
                $items[] = [
                    'name' => $tab->name,
                    'label' => $tab->label,
                    'active' => $tab->active,
                    'hasError' => $errors->hasAny($tab->getFields()),
                    'icon' => match ($tab->name) {
                        'general' => 'fa-ticket',
                        'usage_restrictions' => 'fa-filter',
                        default => 'fa-sliders',
                    },
                ];
            }
        }

        return $items;
    }


    public function general($paneOpen, $paneClose)
    {
        return tap(new Tab('general', trans('coupon::coupons.tabs.general')), function (Tab $tab) use ($paneOpen, $paneClose) {
            $tab->active();
            $tab->weight(5);

            $tab->fields([
                'name',
                'code',
                'is_percent',
                'value',
                'free_shipping',
                'start_date',
                'end_date',
                'is_active',
            ]);

            $tab->view('coupon::admin.coupons.tabs.general', [], $paneOpen, $paneClose);
        });
    }


    public function usageRestrictions($paneOpen, $paneClose)
    {
        return tap(new Tab('usage_restrictions', trans('coupon::coupons.tabs.usage_restrictions')), function (Tab $tab) use ($paneOpen, $paneClose) {
            $tab->weight(10);
            $tab->fields(['minimum_spend', 'maximum_spend', 'products', 'exclude_products', 'categories', 'exclude_categories']);

            $coupon = Coupon::withoutGlobalScope('active')->findOrNew(request('id'));

            $tab->view('coupon::admin.coupons.tabs.usage_restrictions', [
                'products' => $coupon->productList(),
                'excludeProducts' => $coupon->excludeProductList(),
                'categories' => Category::treeList(),
            ], $paneOpen, $paneClose);
        });
    }


    private function usageLimits($paneOpen, $paneClose)
    {
        return tap(new Tab('usage_limits', trans('coupon::coupons.tabs.usage_limits')), function (Tab $tab) use ($paneOpen, $paneClose) {
            $tab->weight(15);
            $tab->fields(['usage_limit_per_coupon', 'usage_limit_per_customer']);
            $tab->view('coupon::admin.coupons.tabs.usage_limits', [], $paneOpen, $paneClose);
        });
    }


    private function activateTabFromQuery(): void
    {
        if (! request()->filled('tab')) {
            return;
        }

        $requested = request()->query('tab');

        if (! $this->collect()->pluck('*.name')->flatten()->contains($requested)) {
            return;
        }

        foreach ($this->groups as $groupName => $group) {
            $this->groups[$groupName]['active'] = false;
        }

        foreach ($this->tabs as $groupName => $group) {
            foreach ($group as $tab) {
                $isActive = $tab->name === $requested;
                $tab->active = $isActive;

                if ($isActive) {
                    $this->groups[$groupName]['active'] = true;
                }
            }
        }
    }


    private function tabPaneOpen(): callable
    {
        return function ($name, $label, $activeClass) {
            return '<div class="coupon-form-tab-pane tab-pane fade in ' . $activeClass . '" id="tab-' . $name . '">';
        };
    }


    private function tabPaneClose(): callable
    {
        return function () {
            return '</div>';
        };
    }
}
