<?php

namespace Modules\Beautician\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;

class BeauticianTabs extends Tabs
{
    protected $buttonOffset = false;

    public function make()
    {
        $this->group('beautician_information', trans('beautician::beauticians.tabs.group.beautician_information'))
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

        return view('beautician::admin.beauticians.form_wrapper', [
            'contents' => $this->contents($data),
            'buttonOffset' => $this->buttonOffset,
        ]);
    }

    private function general()
    {
        return tap(new Tab('general', trans('beautician::beauticians.tabs.general')), function (Tab $tab) {
            $tab->active();
            $tab->weight(5);
            $tab->fields(['user_id', 'first_name', 'last_name', 'phone', 'profile_color', 'job_title', 'position', 'is_active', 'spa_branches']);
            $tab->view('beautician::admin.beauticians.tabs.general');
        });
    }
}
