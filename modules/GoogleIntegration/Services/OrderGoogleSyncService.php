<?php

namespace Modules\GoogleIntegration\Services;

use Exception;
use Modules\GoogleIntegration\Support\GoogleSheetsStatusConfig;
use Modules\Order\Entities\Order;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

class OrderGoogleSyncService
{
    public function __construct(
        private readonly GoogleSheetsService $sheets,
        private readonly GoogleCalendarService $calendar,
        private readonly GoogleSheetsSyncLogger $logger,
        private readonly GoogleSheetsSyncAlertNotifier $alerts,
    ) {
    }


    public function sync(Order $order, bool $forceSheets = false, string $trigger = 'auto'): void
    {
        if (! GoogleServiceAccountClient::isConfigured()) {
            return;
        }

        if (GoogleSheetsService::isEnabled()) {
            $this->markSyncAttempted($order);

            try {
                if ($forceSheets && $this->sheets->hasSheetRow($order)) {
                    $this->sheets->removeOrderFromSheet($order->fresh());
                } elseif ($forceSheets) {
                    $order->forceFill([
                        'google_sheets_synced_at' => null,
                        'google_sheets_tab' => null,
                        'google_sheets_row' => null,
                        'google_sheets_sync_error' => null,
                    ])->save();
                }

                if (GoogleSheetsStatusConfig::isStatusEnabled($order->status)) {
                    $this->sheets->syncOrder($order->fresh());
                    $order->refresh();

                    $this->logger->log(
                        $order,
                        $trigger,
                        true,
                        $order->google_sheets_tab,
                        trans('setting::messages.google_sheets_log_synced'),
                    );
                } elseif ($this->sheets->hasSheetRow($order)) {
                    $tab = $order->google_sheets_tab;
                    $this->sheets->removeOrderFromSheet($order->fresh());
                    $order->refresh();

                    $this->logger->log(
                        $order,
                        $trigger,
                        true,
                        $tab,
                        trans('setting::messages.google_sheets_log_removed'),
                    );
                }
            } catch (Exception $exception) {
                $order = $order->fresh();
                $this->sheets->markSyncFailed($order, $exception->getMessage());
                $this->logger->log(
                    $order,
                    $trigger,
                    false,
                    $order->google_sheets_tab,
                    $exception->getMessage(),
                );
                $this->alerts->notify($order, $exception->getMessage(), $trigger);

                throw $exception;
            }
        }

        if (
            GoogleCalendarService::isEnabled()
            && $order->status === Order::COMPLETED
        ) {
            $this->syncCalendarAppointment($order, $trigger);
        }
    }


    /**
     * @return array{ok: bool, error: ?string, event_id: ?string, created: bool, skipped: bool}
     */
    public function syncCalendarAppointment(Order $order, string $_trigger = 'auto', bool $verifyExisting = false): array
    {
        if (! GoogleCalendarService::isEnabled()) {
            return [
                'ok' => false,
                'error' => trans('setting::messages.google_calendar_sync_all_disabled'),
                'event_id' => null,
                'created' => false,
                'skipped' => false,
            ];
        }

        if ($order->status !== Order::COMPLETED) {
            return ['ok' => false, 'error' => null, 'event_id' => null, 'created' => false, 'skipped' => false];
        }

        $bookings = TreatmentBooking::query()
            ->where('order_id', $order->id)
            ->where(function ($query): void {
                $query->whereNotNull('google_calendar_event_id')
                    ->orWhere(function ($scheduled): void {
                        $scheduled->whereNotNull('appointment_date')
                            ->whereNotNull('appointment_time');
                    });
            })
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->orderBy('id')
            ->get();

        if ($bookings->isNotEmpty()) {
            return $this->syncTreatmentAppointments($order, $bookings, $verifyExisting);
        }

        if (! $order->appointment_date) {
            return ['ok' => false, 'error' => null, 'event_id' => null, 'created' => false, 'skipped' => true];
        }

        if ($order->google_calendar_event_id) {
            try {
                if ($verifyExisting && ! $this->calendar->eventExists($order->google_calendar_event_id)) {
                    $order->forceFill(['google_calendar_event_id' => null])->save();
                } else {
                    $this->calendar->updateAppointmentEvent($order, $order->google_calendar_event_id);

                    return [
                        'ok' => true,
                        'error' => null,
                        'event_id' => $order->google_calendar_event_id,
                        'created' => false,
                        'skipped' => false,
                    ];
                }
            } catch (Exception $exception) {
                report($exception);

                return [
                    'ok' => false,
                    'error' => $exception->getMessage(),
                    'event_id' => $order->google_calendar_event_id,
                    'created' => false,
                    'skipped' => false,
                ];
            }
        }

        try {
            $eventId = $this->calendar->createAppointmentEvent($order);

            $order->forceFill(['google_calendar_event_id' => $eventId])->save();

            return [
                'ok' => true,
                'error' => null,
                'event_id' => $eventId,
                'created' => true,
                'skipped' => false,
            ];
        } catch (Exception $exception) {
            report($exception);

            return [
                'ok' => false,
                'error' => $exception->getMessage(),
                'event_id' => null,
                'created' => false,
                'skipped' => false,
            ];
        }
    }


    /**
     * @param \Illuminate\Support\Collection<int, TreatmentBooking> $bookings
     * @return array{ok: bool, error: ?string, event_id: ?string, created: bool, skipped: bool}
     */
    private function syncTreatmentAppointments(Order $order, $bookings, bool $verifyExisting): array
    {
        $firstEventId = null;
        $created = false;
        $legacyEventId = trim((string) $order->google_calendar_event_id);
        $legacyAssigned = $bookings->contains(
            fn (TreatmentBooking $booking): bool => trim((string) $booking->google_calendar_event_id) === $legacyEventId
        );

        try {
            foreach ($bookings as $booking) {
                $eventId = trim((string) $booking->google_calendar_event_id);

                if ($booking->status === TreatmentBooking::STATUS_CANCELED) {
                    if ($eventId !== '') {
                        $this->calendar->deleteAppointmentEvent($eventId);
                        $booking->forceFill(['google_calendar_event_id' => null])->save();
                    }

                    continue;
                }

                if (! $booking->appointment_date || ! $booking->appointment_time) {
                    continue;
                }

                if ($eventId === '' && $legacyEventId !== '' && ! $legacyAssigned) {
                    $eventId = $legacyEventId;
                    $legacyAssigned = true;
                }

                if ($eventId !== '' && $verifyExisting && ! $this->calendar->eventExists($eventId)) {
                    $eventId = '';
                }

                if ($eventId !== '') {
                    $this->calendar->updateTreatmentAppointmentEvent($order, $booking, $eventId);
                } else {
                    $eventId = $this->calendar->createTreatmentAppointmentEvent($order, $booking);
                    $created = true;
                }

                if ($booking->google_calendar_event_id !== $eventId) {
                    $booking->forceFill(['google_calendar_event_id' => $eventId])->save();
                }

                $firstEventId ??= $eventId;
            }

            $order->forceFill(['google_calendar_event_id' => $firstEventId])->save();

            return [
                'ok' => $firstEventId !== null,
                'error' => null,
                'event_id' => $firstEventId,
                'created' => $created,
                'skipped' => $firstEventId === null,
            ];
        } catch (Exception $exception) {
            report($exception);

            return [
                'ok' => false,
                'error' => $exception->getMessage(),
                'event_id' => $firstEventId,
                'created' => $created,
                'skipped' => false,
            ];
        }
    }


    /**
     * @return array{synced: int, failed: int, skipped: int}
     */
    public function syncAll(?int $limit = null): array
    {
        return $this->sheets->syncAllOrders($limit, 'bulk');
    }


    private function markSyncAttempted(Order $order): void
    {
        $order->forceFill([
            'google_sheets_sync_attempted_at' => now(),
        ])->save();
    }
}
