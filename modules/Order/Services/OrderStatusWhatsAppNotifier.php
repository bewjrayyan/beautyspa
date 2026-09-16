<?php

namespace Modules\Order\Services;

use Illuminate\Support\Facades\Log;
use Modules\Order\Entities\Order;
use Modules\Setting\Support\WhatsAppMessageTemplate;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Services\OneSenderWhatsAppService;

class OrderStatusWhatsAppNotifier
{
    public function __construct(
        private readonly OrderWhatsAppMessageBuilder $messageBuilder,
        private readonly OneSenderWhatsAppService $oneSender,
    ) {}


    /**
     * @param  'order'|'payment'|'treatment'|'order_and_payment'  $changeType
     */
    public function notify(Order $order, string $changeType, ?string $previousValue = null, ?string $newValue = null): void
    {
        if (! OneSenderWhatsAppService::isConfigured()) {
            return;
        }

        if (! $this->shouldNotify($order, $changeType, $newValue)) {
            return;
        }

        $order->loadMissing(['beautician', 'treatmentBookings']);

        $replacements = $this->replacements($order, $changeType, $previousValue, $newValue);

        if (setting('whatsapp_status_notify_customer_enabled', true)) {
            $this->notifyCustomer($order, $replacements, $changeType, $newValue);
        }

        if (setting('whatsapp_status_notify_beautician_enabled', true)) {
            $this->notifyBeautician($order, $replacements, $changeType, $newValue);
        }
    }


    /**
     * @param  'order'|'payment'|'treatment'|'order_and_payment'  $changeType
     */
    private function shouldNotify(Order $order, string $changeType, ?string $newValue): bool
    {
        if (in_array($changeType, ['payment', 'treatment', 'order_and_payment'], true)) {
            return true;
        }

        // Order status: only for statuses selected in Settings → WhatsApp order statuses.
        $allowed = setting(
            'sms_order_statuses',
            config('setting.whatsapp_notifications.sms_order_statuses', [])
        );

        if (! is_array($allowed)) {
            return false;
        }

        $status = $newValue ?: $order->status;

        return in_array($status, $allowed, true);
    }


    /**
     * @param  array<string, string>  $replacements
     */
    private function notifyCustomer(Order $order, array $replacements, string $changeType, ?string $newValue): void
    {
        $phone = trim((string) $order->customer_phone);

        if ($phone === '') {
            return;
        }

        $message = WhatsAppMessageTemplate::render(
            'whatsapp_order_status_message',
            $replacements,
            implode("\n", [
                'Hai :first_name,',
                '',
                'Status :status_type pesanan #:order_id telah dikemas kini.',
                '',
                'Status baharu: *:status*',
                'Sebelum: :previous_status',
                '',
                'Pesanan: :order_status',
                'Bayaran: :payment_status',
                ':treatment_line',
                '',
                'Terima kasih — :store',
            ])
        );

        if ($message === '') {
            return;
        }

        try {
            $this->oneSender->sendNotification($phone, $message, [
                'source' => 'order.status.customer.' . $changeType,
                'dedupe_key' => sprintf(
                    'order:%s:status:%s:%s:customer',
                    $order->id,
                    $changeType,
                    $newValue ?: $order->status
                ),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Order status WhatsApp to customer failed', [
                'order_id' => $order->id,
                'change_type' => $changeType,
                'message' => $exception->getMessage(),
            ]);
        }
    }


    /**
     * @param  array<string, string>  $replacements
     */
    private function notifyBeautician(Order $order, array $replacements, string $changeType, ?string $newValue): void
    {
        // Rich completed-booking message already covers completed orders
        // (fired for changeType order and order_and_payment).
        if (
            in_array($changeType, ['order', 'order_and_payment'], true)
            && $order->status === Order::COMPLETED
            && setting('whatsapp_completed_beautician_enabled', true)
        ) {
            return;
        }

        $phone = trim((string) ($order->beautician?->phone ?? ''));

        if ($phone === '') {
            return;
        }

        $message = WhatsAppMessageTemplate::render(
            'whatsapp_status_beautician_message',
            $replacements,
            implode("\n", [
                '📋 *Kemas kini status — :store*',
                '',
                'HI :staff',
                '',
                'Order #:order_id',
                'Pelanggan: :customer',
                'Telefon: :phone',
                '',
                'Jenis: :status_type',
                'Status baharu: *:status*',
                'Sebelum: :previous_status',
                '',
                'Pesanan: :order_status',
                'Bayaran: :payment_status',
                ':treatment_line',
            ])
        );

        if ($message === '') {
            return;
        }

        try {
            $this->oneSender->sendNotification($phone, $message, [
                'source' => 'order.status.beautician.' . $changeType,
                'dedupe_key' => sprintf(
                    'order:%s:status:%s:%s:beautician',
                    $order->id,
                    $changeType,
                    $newValue ?: $order->status
                ),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Order status WhatsApp to beautician failed', [
                'order_id' => $order->id,
                'change_type' => $changeType,
                'message' => $exception->getMessage(),
            ]);
        }
    }


    /**
     * @return array<string, string>
     */
    private function replacements(Order $order, string $changeType, ?string $previousValue, ?string $newValue): array
    {
        $base = $this->messageBuilder->replacements($order);

        $treatmentLabel = $this->treatmentStatusLabel($order);
        $statusLabel = $this->statusLabelForChange($order, $changeType, $newValue);
        $typeLabel = match ($changeType) {
            'payment' => trans('order::orders.payment_status'),
            'treatment' => trans('order::orders.treatment_status'),
            'order_and_payment' => trans('order::orders.order_and_payment_status'),
            default => trans('order::orders.order_status'),
        };

        $treatmentLine = $treatmentLabel !== ''
            ? trans('order::orders.treatment_status') . ': ' . $treatmentLabel
            : '';

        return array_merge($base, [
            'status_type' => $typeLabel,
            'status' => $statusLabel,
            'previous_status' => $previousValue ? $this->rawStatusLabel($changeType, $previousValue, $order) : '—',
            'treatment_status' => $treatmentLabel !== '' ? $treatmentLabel : '—',
            'treatment_line' => $treatmentLine,
        ]);
    }


    private function statusLabelForChange(Order $order, string $changeType, ?string $newValue): string
    {
        if ($changeType === 'payment') {
            return $order->paymentStatusLabel();
        }

        if ($changeType === 'treatment') {
            return $this->treatmentStatusLabel($order) ?: ($newValue ?: '—');
        }

        if ($changeType === 'order_and_payment') {
            return $order->status() . ' / ' . $order->paymentStatusLabel();
        }

        if ($newValue) {
            $key = "order::statuses.{$newValue}";
            $label = trans($key);

            return $label !== $key ? $label : $order->status();
        }

        return $order->status();
    }


    private function rawStatusLabel(string $changeType, string $value, Order $order): string
    {
        if ($changeType === 'order_and_payment' && str_contains($value, '/')) {
            [$orderStatus, $paymentStatus] = explode('/', $value, 2);

            return $this->rawStatusLabel('order', $orderStatus, $order)
                . ' / '
                . $this->rawStatusLabel('payment', $paymentStatus, $order);
        }

        if ($changeType === 'payment') {
            $key = "order::payment_statuses.{$value}";
            $label = trans($key);

            return $label !== $key ? $label : $value;
        }

        if ($changeType === 'treatment') {
            if ($value === TreatmentBooking::STATUS_CANCELED) {
                return trans('treatmentreservation::admin.crm.status_canceled');
            }

            $key = 'treatmentreservation::admin.kanban.' . $value;
            $label = trans($key);

            return $label !== $key ? $label : $value;
        }

        $key = "order::statuses.{$value}";
        $label = trans($key);

        return $label !== $key ? $label : $value;
    }


    private function treatmentStatusLabel(Order $order): string
    {
        if (! is_module_enabled('TreatmentReservation')) {
            return '';
        }

        $bookings = $order->relationLoaded('treatmentBookings')
            ? $order->treatmentBookings
            : $order->treatmentBookings()->get();

        if ($bookings->isEmpty()) {
            return '';
        }

        if ($bookings->count() > 1) {
            return trans('order::orders.appointments_count', ['count' => $bookings->count()]);
        }

        return (string) $bookings->first()->treatmentStatusLabel();
    }
}
