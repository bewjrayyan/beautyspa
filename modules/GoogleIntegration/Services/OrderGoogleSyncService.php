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
    ) {
    }


    public function sync(Order $order, bool $forceSheets = false): void
    {
        if (! GoogleServiceAccountClient::isConfigured()) {
            return;
        }

        if (GoogleSheetsService::isEnabled()) {
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
                } elseif ($this->sheets->hasSheetRow($order)) {
                    $this->sheets->removeOrderFromSheet($order->fresh());
                }
            } catch (Exception $exception) {
                $this->sheets->markSyncFailed($order->fresh(), $exception->getMessage());

                throw $exception;
            }
        }

        if (
            GoogleCalendarService::isEnabled()
            && $order->status === Order::COMPLETED
            && ! $order->google_calendar_event_id
            && $order->appointment_date
        ) {
            $eventId = $this->calendar->createAppointmentEvent($order);

            $order->forceFill(['google_calendar_event_id' => $eventId])->save();
        }
    }


    /**
     * @return array{synced: int, failed: int, skipped: int}
     */
    public function syncAll(?int $limit = null): array
    {
        return $this->sheets->syncAllOrders($limit);
    }
}
