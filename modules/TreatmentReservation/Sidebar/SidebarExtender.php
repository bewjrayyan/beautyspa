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
                $this->registerAdminPreviewPortalItems($group, $portalPreview->beautician());
            } elseif (Beautician::findForUser($this->auth->id())) {
                $this->registerBeauticianPortalItems($group);
            }

            $group->item(trans('treatmentreservation::sidebar.agenda'), function (Item $item) {
                $item->icon('fa fa-calendar-check-o');
                $item->weight(17);
                $item->route('admin.treatment_reservations.index', ['view' => 'dashboard']);
                $item->authorize(
                    $this->auth->hasAccess('admin.treatment_reservations.index')
                );
            });

            $group->item(trans('treatmentreservation::sidebar.calendar'), function (Item $item) {
                $item->icon('fa fa-calendar');
                $item->weight(18);
                $item->route('admin.treatment_reservations.index', ['view' => 'calendar']);
                $item->isActiveWhen(route('admin.treatment_reservations.index', ['view' => 'calendar'], false));
                $item->authorize(
                    $this->auth->hasAccess('admin.treatment_reservations.index')
                );
            });

            $group->item(trans('treatmentreservation::sidebar.holidays'), function (Item $item) {
                $item->icon('fa fa-flag-o');
                $item->weight(19);
                $item->route('admin.treatment_reservations.holidays.index');
                $item->isActiveWhen(route('admin.treatment_reservations.holidays.index', [], false));
                $item->authorize(
                    $this->auth->hasAccess('admin.treatment_reservations.index')
                );
            });
        });
    }


    private function registerAdminPreviewPortalItems(Group $group, Beautician $beautician): void
    {
        $beauticianId = $beautician->id;

        $group->item(trans('treatmentreservation::sidebar.my_job_sheet'), function (Item $item) use ($beauticianId) {
            $item->icon('fa fa-tachometer');
            $item->weight(16);
            $item->route('admin.beauticians.portal.dashboard', $beauticianId);
            $item->authorize(true);
        });

        $group->item(trans('treatmentreservation::sidebar.my_job_sheet_kanban'), function (Item $item) use ($beauticianId) {
            $item->icon('fa fa-columns');
            $item->weight(17);
            $item->route('admin.beauticians.portal', $beauticianId);
            $item->isActiveWhen(route('admin.beauticians.portal', $beauticianId, false));
            $item->authorize(true);
        });

        $group->item(trans('treatmentreservation::sidebar.my_calendar'), function (Item $item) use ($beauticianId) {
            $item->icon('fa fa-calendar');
            $item->weight(18);
            $item->route('admin.beauticians.portal.calendar_page', $beauticianId);
            $item->authorize(true);
        });

        $group->item(trans('treatmentreservation::sidebar.my_account'), function (Item $item) use ($beauticianId) {
            $item->icon('fa fa-user');
            $item->weight(19);
            $item->route('admin.beauticians.portal.account', $beauticianId);
            $item->authorize(true);
        });
    }


    private function registerBeauticianPortalItems(Group $group): void
    {
        $beautician = Beautician::findForUser($this->auth->id());

        if (! $beautician) {
            return;
        }

        $beauticianId = $beautician->id;

        $group->item(trans('treatmentreservation::sidebar.my_job_sheet'), function (Item $item) use ($beauticianId) {
            $item->icon('fa fa-tachometer');
            $item->weight(16);
            $item->route('admin.beauticians.portal.dashboard', $beauticianId);
            $item->authorize(true);
        });

        $group->item(trans('treatmentreservation::sidebar.my_job_sheet_kanban'), function (Item $item) use ($beauticianId) {
            $item->icon('fa fa-columns');
            $item->weight(17);
            $item->route('admin.beauticians.portal', $beauticianId);
            $item->isActiveWhen(route('admin.beauticians.portal', $beauticianId, false));
            $item->authorize(true);
        });

        $group->item(trans('treatmentreservation::sidebar.my_calendar'), function (Item $item) use ($beauticianId) {
            $item->icon('fa fa-calendar');
            $item->weight(18);
            $item->route('admin.beauticians.portal.calendar_page', $beauticianId);
            $item->authorize(true);
        });

        $group->item(trans('treatmentreservation::sidebar.my_account'), function (Item $item) use ($beauticianId) {
            $item->icon('fa fa-user');
            $item->weight(19);
            $item->route('admin.beauticians.portal.account', $beauticianId);
            $item->authorize(true);
        });
    }
}
