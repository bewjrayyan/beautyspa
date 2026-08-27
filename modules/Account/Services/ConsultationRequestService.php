<?php

namespace Modules\Account\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Account\Entities\ConsultationFormTemplate;
use Modules\Account\Entities\ConsultationSubmission;
use Modules\Account\Exceptions\ConsultationRequestException;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Entities\User;
use Modules\User\Services\OneSenderWhatsAppService;
use Modules\User\Support\PhoneNumber;
use Illuminate\Support\Facades\Log;

class ConsultationRequestService
{
    public function __construct(private readonly ConsultationContextService $context)
    {
    }

    public function createForBooking(
        TreatmentBooking $booking,
        User $sender,
        ?ConsultationFormTemplate $template = null
    ): ConsultationSubmission {
        $booking->loadMissing(['order.customer', 'beautician', 'product']);
        $template = $this->resolveTemplate($template);
        $this->guardBookingCanReceiveConsultation($booking);
        [$user, $email, $phone] = $this->resolveCustomer($booking);

        return DB::transaction(function () use ($booking, $sender, $template, $user, $email, $phone) {
            TreatmentBooking::query()->whereKey($booking->id)->lockForUpdate()->firstOrFail();

            $existing = $this->pendingForBooking($booking);

            if ($existing) {
                return $existing;
            }

            $publicToken = Str::random(64);

            return ConsultationSubmission::create([
                'template_id' => $template->id,
                'user_id' => $user?->id,
                'order_id' => $booking->order_id,
                'product_id' => $booking->product_id,
                'treatment_booking_id' => $booking->id,
                'beautician_id' => $booking->beautician_id,
                'sent_by_user_id' => $sender->id,
                'public_token_hash' => hash('sha256', $publicToken),
                'public_token_ciphertext' => Crypt::encryptString($publicToken),
                'public_token_expires_at' => now()->addDays(30),
                'customer_name' => $booking->customer_full_name,
                'customer_email' => $email ?: null,
                'customer_phone' => $phone ?: null,
                'sent_at' => now(),
                'template_version' => max(1, (int) $template->version),
                'form_title' => $template->title,
                'form_intro' => $template->intro,
                'consent_text' => $template->consent_text,
                'questions_snapshot' => $template->questions ?: [],
                'context_snapshot' => $this->context->capture($booking),
            ]);
        });
    }

    public function shareUrl(ConsultationSubmission $submission): string
    {
        $token = $submission->public_token_ciphertext
            ? Crypt::decryptString($submission->public_token_ciphertext)
            : $submission->public_token;

        return route('consultations.access', ['token' => $token]);
    }

    public function whatsAppMessage(ConsultationSubmission $submission): string
    {
        return trans('account::consultation.request.whatsapp_message', [
            'name' => $submission->customer_name ?: trans('account::consultation.request.customer'),
            'url' => $this->shareUrl($submission),
        ]);
    }

    public function whatsAppUrl(ConsultationSubmission $submission): ?string
    {
        $phone = PhoneNumber::normalize((string) $submission->customer_phone);

        if ($phone === '') {
            return null;
        }

        return 'https://wa.me/' . rawurlencode($phone) . '?text=' . rawurlencode($this->whatsAppMessage($submission));
    }

    /**
     * Send the consultation link to the customer via OneSender WhatsApp API.
     *
     * @throws ConsultationRequestException
     */
    public function sendViaOneSender(ConsultationSubmission $submission): bool
    {
        if (! OneSenderWhatsAppService::isConfigured()) {
            throw new ConsultationRequestException(
                trans('account::consultation.request.whatsapp_not_configured')
            );
        }

        $phone = PhoneNumber::normalize((string) $submission->customer_phone);

        if ($phone === '') {
            throw new ConsultationRequestException(
                trans('account::consultation.request.no_phone')
            );
        }

        try {
            $delivered = app(OneSenderWhatsAppService::class)->sendNotification(
                $phone,
                $this->whatsAppMessage($submission),
                [
                    'source' => 'consultation.request.customer',
                    'dedupe_key' => 'consultation:' . $submission->id . ':send:' . now()->format('YmdHis'),
                    'immediate' => true,
                ]
            );
        } catch (\Throwable $exception) {
            Log::warning('Consultation WhatsApp via OneSender failed', [
                'consultation_id' => $submission->id,
                'booking_id' => $submission->treatment_booking_id,
                'message' => $exception->getMessage(),
            ]);

            throw new ConsultationRequestException(
                $exception->getMessage() ?: trans('account::consultation.request.send_failed')
            );
        }

        if (! $delivered) {
            throw new ConsultationRequestException(
                trans('account::consultation.request.send_failed')
            );
        }

        return true;
    }

    private function resolveTemplate(?ConsultationFormTemplate $template): ConsultationFormTemplate
    {
        $template ??= ConsultationFormTemplate::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->first();

        if (! $template) {
            throw new ConsultationRequestException(
                trans('account::consultation.request.no_template')
            );
        }

        return $template;
    }

    private function guardBookingCanReceiveConsultation(TreatmentBooking $booking): void
    {
        if ($booking->status === TreatmentBooking::STATUS_CANCELED) {
            throw new ConsultationRequestException(
                trans('account::consultation.request.canceled_booking')
            );
        }
    }

    private function resolveCustomer(TreatmentBooking $booking): array
    {
        $email = mb_strtolower(trim((string) (
            $booking->customer_email ?: $booking->order?->customer_email
        )));
        $phone = PhoneNumber::normalize((string) (
            $booking->customer_phone ?: $booking->order?->customer_phone
        ));
        $user = $booking->order?->customer
            ?: ($email !== '' ? User::query()->where('email', $email)->first() : null)
            ?: ($phone !== '' ? User::findByPhone($phone) : null);

        if (! $user && $email === '' && $phone === '') {
            throw new ConsultationRequestException(
                trans('account::consultation.request.no_contact')
            );
        }

        return [$user, $email, $phone];
    }

    private function pendingForBooking(TreatmentBooking $booking): ?ConsultationSubmission
    {
        return ConsultationSubmission::query()
            ->where('treatment_booking_id', $booking->id)
            ->whereNull('submitted_at')
            ->whereNull('revoked_at')
            ->where('public_token_expires_at', '>', now())
            ->latest('id')
            ->first();
    }
}
