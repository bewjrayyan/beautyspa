<?php

namespace Modules\Beautician\Admin;

use Modules\Admin\Ui\AdminTable;
use Modules\Beautician\Entities\BeauticianJobTitle;

class BeauticianJobTitleTable extends AdminTable
{
    public function make()
    {
        return $this->newTable()
            ->editColumn('name', function (BeauticianJobTitle $jobTitle) {
                return e($jobTitle->name);
            })
            ->editColumn('position', function (BeauticianJobTitle $jobTitle) {
                return (string) $jobTitle->position;
            });
    }
}
