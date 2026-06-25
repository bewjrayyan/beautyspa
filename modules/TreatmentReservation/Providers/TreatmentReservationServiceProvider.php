<?php

namespace Modules\TreatmentReservation\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Order\Entities\Order;
use Modules\Order\Events\OrderStatusChanged;
use Modules\TreatmentReservation\Console\GrantManualBookingPermissionsCommand;
use Modules\TreatmentReservation\Console\SendBeauticianAppointmentRemindersCommand;
use Modules\TreatmentReservation\Console\SendCustomerAppointmentRemindersCommand;
use Modules\TreatmentReservation\Console\SendCustomerFollowUpNotificationsCommand;
use Modules\TreatmentReservation\Console\SyncTreatmentBookingsCommand;
use Modules\TreatmentReservation\Console\SyncTreatmentProductDurationsCommand;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\TreatmentReservation\Http\Middleware\BeauticianPortalMiddleware;
use Modules\TreatmentReservation\Http\Middleware\PortalBeauticianFromRouteMiddleware;
use Modules\TreatmentReservation\Http\Middleware\RestrictBeauticianPortalMiddleware;
use Modules\TreatmentReservation\Listeners\SyncTreatmentBookingFromOrder;
use Modules\Beautician\Entities\Beautician;
use Modules\TreatmentReservation\Observers\OrderTreatmentBookingObserver;
use Modules\TreatmentReservation\Observers\TreatmentBookingObserver;
use Modules\TreatmentReservation\Services\AdminPortalPreview;
use Modules\TreatmentReservation\Services\UpcomingJobUrgencyService;
use Nwidart\Modules\Facades\Module;

class TreatmentReservationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AdminPortalPreview::class);
    }


    public function boot(): void
    {
        $this->app['router']->aliasMiddleware('beautician.portal', BeauticianPortalMiddleware::class);
        $this->app['router']->aliasMiddleware('beautician.portal.from_route', PortalBeauticianFromRouteMiddleware::class);
        $this->app['router']->aliasMiddleware('beautician.portal.restrict', RestrictBeauticianPortalMiddleware::class);

        Order::observe(OrderTreatmentBookingObserver::class);
        TreatmentBooking::observe(TreatmentBookingObserver::class);

        $this->app['events']->listen(
            OrderStatusChanged::class,
            [SyncTreatmentBookingFromOrder::class, 'handleOrderStatusChanged']
        );

        View::composer([
            'admin::dashboard.index',
            'treatmentreservation::admin.portal.job_sheet',
            'treatmentreservation::admin.portal.account',
            'treatmentreservation::admin.portal.availability',
            'treatmentreservation::admin.reservations.index',
        ], function ($view) {
            $service = app(UpcomingJobUrgencyService::class);

            if (! Module::isEnabled('TreatmentReservation')) {
                $view->with('jobUrgencyAlerts', $service->emptyPayload());

                return;
            }

            $user = auth()->user();
            $portalPreview = app(AdminPortalPreview::class);

            if (! $user) {
                $view->with('jobUrgencyAlerts', $service->emptyPayload());

                return;
            }

            if ($portalPreview->isActive() && $portalPreview->beautician()) {
                $beautician = $portalPreview->beautician();
                $alerts = $service->forBeautician(
                    $beautician->id,
                    route('admin.beauticians.portal', ['id' => $beautician->id, 'view' => 'kanban']),
                );
            } elseif ($user->isBeauticianOnly()) {
                $beautician = Beautician::findForUser($user->id);
                $alerts = $beautician
                    ? $service->forBeautician($beautician->id)
                    : $service->emptyPayload();
            } elseif ($user->hasAccess('admin.treatment_reservations.index')) {
                $alerts = $service->forAdminTeam();
            } else {
                $alerts = $service->emptyPayload();
            }

            $view->with('jobUrgencyAlerts', $alerts);
        });

        View::composer('order::admin.orders.show', function ($view) {
            $order = $view->getData()['order'] ?? null;

            if (! $order instanceof Order) {
                return;
            }

            $view->with(
                'treatmentBooking',
                TreatmentBooking::query()
                    ->with(['activities.user'])
                    ->where('order_id', $order->id)
                    ->first()
            );
        });

        Order::resolveRelationUsing('treatmentBooking', function (Order $order) {
            return $order->hasOne(TreatmentBooking::class);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncTreatmentBookingsCommand::class,
                SendBeauticianAppointmentRemindersCommand::class,
                SendCustomerAppointmentRemindersCommand::class,
                SendCustomerFollowUpNotificationsCommand::class,
                GrantManualBookingPermissionsCommand::class,
                SyncTreatmentProductDurationsCommand::class,
            ]);
        }
    }
}
