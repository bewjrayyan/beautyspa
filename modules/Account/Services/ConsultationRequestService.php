<?php

namespace Modules\Account\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Account\Entities\ConsultationFormTemplate;
use Modules\Account\Entities\ConsultationSubmission;
use Modules\Account\Exceptions\ConsultationRequestException;
use Modules\TreatmentReservation\Entities\TreatmentBooking;
use Modules\User\Entities\User;
use Modules\User\Support\PhoneNumber;

class ConsultationRequestService
{
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

            return ConsultationSubmission::create([
                'template_id' => $template->id,
                'user_id' => $user?->id,
                'order_id' => $booking->order_id,
                'treatment_booking_id' => $booking->id,
                'beautician_id' => $booking->beautician_id,
                'sent_by_user_id' => $sender->id,
                'public_token' => Str::random(64),
                'customer_name' => $booking->customer_full_name,
                'customer_email' => $email ?: null,
                'customer_phone' => $phone ?: null,
                'sent_at' => now(),
                'template_version' => max(1, (int) $template->version),
                'form_title' => $template->title,
                'form_intro' => $template->intro,
                'consent_text' => $template->consent_text,
                'questions_snapshot' => $template->questions ?: [],
            ]);
        });
    }

    public function shareUrl(ConsultationSubmission $submission): string
    {
        return route('consultations.access', ['token' => $submission->public_token]);
    }

    public function whatsAppUrl(ConsultationSubmission $submission): ?string
    {
        $phone = PhoneNumber::normalize((string) $submission->customer_phone);

        if ($phone === '') {
            return null;
        }

        $message = trans('account::consultation.request.whatsapp_message', [
            'name' => $submission->customer_name ?: trans('account::consultation.request.customer'),
            'url' => $this->shareUrl($submission),
        ]);

        return 'https://wa.me/' . rawurlencode($phone) . '?text=' . rawurlencode($message);
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
            ?: ($email !== '' ? User::query()->whereRaw('LOWER(email) = ?', [$email])->first() : null)
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
            ->latest('id')
            ->first();
    }
}
