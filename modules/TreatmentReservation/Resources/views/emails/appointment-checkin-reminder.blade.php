<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('treatmentreservation::public.reminder_email_subject', ['reference' => $booking->referenceCode()]) }}</title>
</head>
<body style="margin:0;background:#f4f7fb;color:#182230;font-family:Arial,Helvetica,sans-serif;">
    <div style="max-width:640px;margin:0 auto;padding:32px 16px;">
        <div style="background:#ffffff;border:1px solid #e4eaf1;border-radius:16px;overflow:hidden;">
            <div style="padding:28px 32px;background:#123a63;color:#ffffff;">
                <div style="font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;opacity:.8;">{{ setting('store_name') }}</div>
                <h1 style="margin:10px 0 0;font-size:25px;line-height:1.3;">{{ trans('treatmentreservation::public.reminder_email_heading') }}</h1>
            </div>

            <div style="padding:30px 32px;">
                <p style="margin:0 0 22px;line-height:1.65;">{{ trans('treatmentreservation::public.reminder_email_intro', ['customer' => $booking->customer_full_name]) }}</p>

                <table role="presentation" style="width:100%;border-collapse:collapse;margin:0 0 26px;">
                    <tr><td style="padding:9px 0;color:#627084;">{{ trans('treatmentreservation::public.reference') }}</td><td style="padding:9px 0;text-align:right;font-weight:700;">{{ $booking->referenceCode() }}</td></tr>
                    <tr><td style="padding:9px 0;color:#627084;">{{ trans('treatmentreservation::public.treatment') }}</td><td style="padding:9px 0;text-align:right;font-weight:700;">{{ $booking->product?->name ?: '—' }}</td></tr>
                    <tr><td style="padding:9px 0;color:#627084;">{{ trans('treatmentreservation::public.date') }}</td><td style="padding:9px 0;text-align:right;font-weight:700;">{{ $booking->appointment_date?->format('d M Y') ?: '—' }}</td></tr>
                    <tr><td style="padding:9px 0;color:#627084;">{{ trans('treatmentreservation::public.time') }}</td><td style="padding:9px 0;text-align:right;font-weight:700;">{{ $booking->displayAppointmentTime() ?: '—' }}</td></tr>
                    @if ($booking->spaBranchLabel())
                        <tr><td style="padding:9px 0;color:#627084;">{{ trans('treatmentreservation::public.location') }}</td><td style="padding:9px 0;text-align:right;font-weight:700;">{{ $booking->spaBranchLabel() }}</td></tr>
                    @endif
                </table>

                <p style="margin:0 0 22px;line-height:1.65;">{{ trans('treatmentreservation::public.reminder_email_checkin_help') }}</p>
                <p style="margin:0 0 24px;text-align:center;">
                    <a href="{{ $checkinUrl }}" style="display:inline-block;padding:13px 22px;border-radius:9px;background:#1769e0;color:#ffffff;text-decoration:none;font-weight:700;">{{ trans('treatmentreservation::public.checkin_pass_open') }}</a>
                </p>
                <p style="margin:0;color:#627084;font-size:12px;line-height:1.55;word-break:break-all;">{{ trans('treatmentreservation::public.reminder_email_link_fallback') }}<br><a href="{{ $checkinUrl }}" style="color:#1769e0;">{{ $checkinUrl }}</a></p>
            </div>
        </div>
    </div>
</body>
</html>
