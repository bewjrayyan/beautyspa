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

        $menu->group(trans('admin::sidebar.operations'), function (Group $group) use ($portalPreview) {
            $group->weight(2);

            if ($portalPreview->isActive() && $portalPreview->beautician()) {
                $this->registerAdminPreviewPortalItems($group, $portalPreview->beautician());
            } elseif (Beautician::findForUser($this->auth->id())) {
                $this->registerBeauticianPortalItems($group);
            }

            $group->item(trans('treatmentreservation::sidebar.appointments'), function (Item $item) {
                $item->icon('fa fa-calendar-check-o');
                $item->weight(5);
                $item->route('admin.treatment_reservations.index', ['view' => 'dashboard']);
                $item->isActiveWhen(route('admin.treatment_reservations.index', [], false));
                $item->authorize(
                    $this->auth->hasAccess('admin.treatment_reservations.index')
                    || $this->auth->hasAccess('admin.treatment_reservations.availability')
                );

                $item->item(trans('treatmentreservation::sidebar.agenda'), function (Item $child) {
                    $child->weight(5);
                    $child->route('admin.treatment_reservations.index', ['view' => 'dashboard']);
                    $child->isActiveWhen(route('admin.treatment_reservations.index', ['view' => 'dashboard'], false));
                    $child->authorize(
                        $this->auth->hasAccess('admin.treatment_reservations.index')
                    );
                });

                $item->item(trans('treatmentreservation::sidebar.pos_booking'), function (Item $child) {
                    $child->icon('fa fa-plus-circle');
                    $child->weight(7);
                    $child->route('admin.treatment_reservations.pos');
                    $child->isActiveWhen(route('admin.treatment_reservations.pos', [], false));
                    $child->authorize(
                        $this->auth->hasAccess('admin.treatment_reservations.create')
                    );
                });

                $item->item(trans('treatmentreservation::sidebar.calendar'), function (Item $child) {
                    $child->weight(10);
                    $child->route('admin.treatment_reservations.index', ['view' => 'calendar']);
                    $child->isActiveWhen(route('admin.treatment_reservations.index', ['view' => 'calendar'], false));
                    $child->authorize(
                        $this->auth->hasAccess('admin.treatment_reservations.index')
                    );
                });

                $item->item(trans('treatmentreservation::sidebar.appointment_availability'), function (Item $child) {
                    $child->weight(15);
                    $child->route('admin.treatment_reservations.availability.index');
                    $child->isActiveWhen(route('admin.treatment_reservations.availability.index', [], false));
                    $child->authorize(
                        $this->auth->hasAccess('admin.treatment_reservations.availability')
                    );
                });

                $item->item(trans('treatmentreservation::sidebar.holidays'), function (Item $child) {
                    $child->weight(20);
                    $child->route('admin.treatment_reservations.holidays.index');
                    $child->isActiveWhen(route('admin.treatment_reservations.holidays.index', [], false));
                    $child->authorize(
                        $this->auth->hasAccess('admin.treatment_reservations.index')
                    );
                });
            });
        });
    }


    private function registerAdminPreviewPortalItems(Group $group, Beautician $beautician): void
    {
        $beauticianId = $beautician->id;

        $group->item(trans('treatmentreservation::sidebar.my_job_sheet'), function (Item $item) use ($beauticianId) {
            $item->icon('fa fa-tachometer');
            $item->weight(1);
            $item->route('admin.beauticians.portal.dashboard', $beauticianId);
            $item->authorize(true);
        });

        $group->item(trans('treatmentreservation::sidebar.my_job_sheet_kanban'), function (Item $item) use ($beauticianId) {
            $item->icon('fa fa-columns');
            $item->weight(2);
            $item->route('admin.beauticians.portal', $beauticianId);
            $item->isActiveWhen(route('admin.beauticians.portal', $beauticianId, false));
            $item->authorize(true);
        });

        $group->item(trans('treatmentreservation::sidebar.my_calendar'), function (Item $item) use ($beauticianId) {
            $item->icon('fa fa-calendar');
            $item->weight(3);
            $item->route('admin.beauticians.portal.calendar_page', $beauticianId);
            $item->isActiveWhen(route('admin.beauticians.portal.calendar_page', $beauticianId, false));
            $item->authorize(true);
        });

        $group->item(trans('treatmentreservation::sidebar.my_account'), function (Item $item) use ($beauticianId) {
            $item->icon('fa fa-user');
            $item->weight(4);
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
            $item->weight(1);
            $item->route('admin.beauticians.portal.dashboard', $beauticianId);
            $item->authorize(true);
        });

        $group->item(trans('treatmentreservation::sidebar.pos_booking'), function (Item $item) use ($beauticianId) {
            $item->icon('fa fa-plus-circle');
            $item->weight(2);
            $item->route('admin.treatment_reservations.portal.pos');
            $item->isActiveWhen(route('admin.treatment_reservations.portal.pos', [], false));
            $item->authorize($this->auth->hasAccess('admin.treatment_reservations.portal.create'));
        });

        $group->item(trans('treatmentreservation::sidebar.my_job_sheet_kanban'), function (Item $item) use ($beauticianId) {
            $item->icon('fa fa-columns');
            $item->weight(2);
            $item->route('admin.beauticians.portal', $beauticianId);
            $item->isActiveWhen(route('admin.beauticians.portal', $beauticianId, false));
            $item->authorize(true);
        });

        $group->item(trans('treatmentreservation::sidebar.my_calendar'), function (Item $item) use ($beauticianId) {
            $item->icon('fa fa-calendar');
            $item->weight(3);
            $item->route('admin.beauticians.portal.calendar_page', $beauticianId);
            $item->isActiveWhen(route('admin.beauticians.portal.calendar_page', $beauticianId, false));
            $item->authorize(true);
        });

        $group->item(trans('treatmentreservation::sidebar.my_account'), function (Item $item) use ($beauticianId) {
            $item->icon('fa fa-user');
            $item->weight(4);
            $item->route('admin.beauticians.portal.account', $beauticianId);
            $item->authorize(true);
        });
    }
}
