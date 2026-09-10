<?php

namespace Modules\TreatmentReservation\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

class AppointmentCheckinReminder extends Mailable
{
    use Queueable, SerializesModels;


    public function __construct(
        public TreatmentBooking $booking,
        public string $checkinUrl,
    ) {}


    public function build(): self
    {
        return $this->subject(trans('treatmentreservation::public.reminder_email_subject', [
            'reference' => $this->booking->referenceCode(),
        ]))->view('treatmentreservation::emails.appointment-checkin-reminder');
    }
}
