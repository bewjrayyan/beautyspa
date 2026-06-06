<?php

namespace Modules\SpaBranch\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;

class SpaBranchTabs extends Tabs
{
    protected $buttonOffset = false;

    public function make()
    {
        $this->group('spa_branch_information', trans('spabranch::spa_branches.tabs.group.spa_branch_information'))
            ->active()
            ->add($this->general());
    }

    public function render($data = [])
    {
        if (request()->filled('tab')
            && $this->collect()
                ->pluck('*.name')
                ->flatten()
                ->contains(request()->query('tab'))
        ) {
            foreach ($this->groups as $groupName => $group) {
                $this->groups[$groupName]['active'] = false;
            }

            foreach ($this->tabs as $groupName => $group) {
                foreach ($group as $tab) {
                    if ($tab->name === request()->query('tab')) {
                        $tab->active = true;
                        $this->groups[$groupName]['active'] = true;
                    } else {
                        $tab->active = false;
                    }
                }
            }
        }

        return view('spabranch::admin.spa_branches.form_wrapper', [
            'contents' => $this->contents($data),
            'buttonOffset' => $this->buttonOffset,
        ]);
    }

    private function general()
    {
        return tap(new Tab('general', trans('spabranch::spa_branches.tabs.general')), function (Tab $tab) {
            $tab->active();
            $tab->weight(5);
            $tab->fields(['name', 'code', 'phone', 'email', 'address', 'position', 'is_active', 'beauticians']);
            $tab->view('spabranch::admin.spa_branches.tabs.general');
        });
    }
}
