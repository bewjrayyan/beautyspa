<?php

namespace Modules\Beautician\Admin;

use Modules\Admin\Ui\AdminTable;
use Modules\Beautician\Entities\Beautician;

class BeauticianTable extends AdminTable
{
    protected array $rawColumns = ['profile'];

    public function make()
    {
        return $this->newTable()
            ->addColumn('profile', function (Beautician $beautician) {
                if ($beautician->profile_image->exists) {
                    return view('admin::partials.table.image', [
                        'file' => $beautician->profile_image,
                    ]);
                }

                $color = $beautician->profile_color ?: '#6366f1';
                $initial = $beautician->initials;

                return '<span class="beautician-table-avatar" style="background-color:' . e($color) . ';">' . e($initial) . '</span>';
            })
            ->editColumn('name', function (Beautician $beautician) {
                return e($beautician->full_name);
            })
            ->editColumn('job_title', function (Beautician $beautician) {
                return e($beautician->job_title ?: '—');
            });
    }
}
