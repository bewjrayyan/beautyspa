<?php

namespace Modules\TreatmentReservation\Sidebar;

use Maatwebsite\Sidebar\Group;
use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Modules\Admin\Sidebar\BaseSidebarExtender;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Services\AdminPortalPreview;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $portalPreview = app(AdminPortalPreview::class);

        $menu->group(trans('admin::sidebar.content'), function (Group $group) use ($portalPreview) {
            if ($portalPreview->isActive() && $portalPreview->beautician()) {
                $beautician = $portalPreview->beautician();

                $group->item(trans('treatmentreservation::sidebar.my_job_sheet'), function (Item $item) use ($beautician) {
                    $item->icon('fa fa-columns');
                    $item->weight(16);
                    $item->route('admin.beauticians.portal.dashboard', $beautician->id);
                    $item->authorize(true);
                });
            } elseif (Beautician::findForUser($this->auth->id())) {
                $group->item(trans('treatmentreservation::sidebar.my_job_sheet'), function (Item $item) {
                    $item->icon('fa fa-columns');
                    $item->weight(16);
                    $item->route('admin.treatment_reservations.portal');
                    $item->authorize(true);
                });

                $group->item(trans('treatmentreservation::sidebar.my_account'), function (Item $item) {
                    $item->icon('fa fa-user');
                    $item->weight(16);
                    $item->route('admin.treatment_reservations.portal.account');
                    $item->authorize(true);
                });
            }

            $group->item(trans('treatmentreservation::sidebar.reservations'), function (Item $item) {
                $item->icon('fa fa-calendar-check-o');
                $item->weight(17);
                $item->route('admin.treatment_reservations.index');
                $item->authorize(
                    $this->auth->hasAccess('admin.treatment_reservations.index')
                );
            });
        });
    }
}
