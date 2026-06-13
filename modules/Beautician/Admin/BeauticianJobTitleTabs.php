<?php

namespace Modules\Beautician\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;

class BeauticianJobTitleTabs extends Tabs
{
    public function make()
    {
        $this->group('job_title_information', trans('beautician::job_titles.tabs.group.job_title_information'))
            ->active()
            ->add($this->general());
    }


    private function general()
    {
        return tap(new Tab('general', trans('beautician::job_titles.tabs.general')), function (Tab $tab) {
            $tab->active();
            $tab->weight(5);
            $tab->fields(['name', 'position', 'is_active']);
            $tab->view('beautician::admin.job_titles.tabs.general');
        });
    }
}
