<?php

namespace Modules\GoogleIntegration\Services;

use Exception;
use Modules\GoogleIntegration\Support\GoogleSheetsStatusConfig;
use Modules\Order\Entities\Order;

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
            && ! $order->google_calendar_event_id
            && $order->appointment_date
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

        if ($order->status !== Order::COMPLETED || ! $order->appointment_date) {
            return ['ok' => false, 'error' => null, 'event_id' => null, 'created' => false, 'skipped' => false];
        }

        if ($order->google_calendar_event_id) {
            if ($verifyExisting) {
                try {
                    if ($this->calendar->eventExists($order->google_calendar_event_id)) {
                        return [
                            'ok' => true,
                            'error' => null,
                            'event_id' => $order->google_calendar_event_id,
                            'created' => false,
                            'skipped' => true,
                        ];
                    }
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

                $order->forceFill(['google_calendar_event_id' => null])->save();
            } else {
                return ['ok' => false, 'error' => null, 'event_id' => null, 'created' => false, 'skipped' => false];
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
