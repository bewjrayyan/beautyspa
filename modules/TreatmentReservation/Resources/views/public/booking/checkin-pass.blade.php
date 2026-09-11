<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ trans('treatmentreservation::public.checkin_pass_title') }}</title>
    <style>
        :root{color-scheme:light;font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:#172033;background:#f5f7fb}
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:20px;background:linear-gradient(145deg,#f7f9fc,#edf3fb)}
        .pass{width:min(100%,480px);padding:28px;border:1px solid #dce4ef;border-radius:24px;background:#fff;box-shadow:0 18px 50px rgba(22,47,75,.12)}
        .eyebrow{margin:0 0 8px;color:#087cb8;font-size:12px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.ref{display:inline-flex;padding:5px 10px;border-radius:999px;background:#eaf2ff;color:#1d4ed8;font-weight:800}
        h1{margin:12px 0 6px;font-size:26px;line-height:1.2}.valid{margin:0 0 22px;color:#526079;font-size:14px}.details{display:grid;gap:10px;margin:0 0 20px}.details div{padding:12px 14px;border:1px solid #e8edf4;border-radius:12px;background:#fafcff}.details dt{font-size:11px;font-weight:800;color:#68758b;text-transform:uppercase}.details dd{margin:4px 0 0;font-size:15px;font-weight:700}
        .qr{display:grid;place-items:center;margin:0 0 20px;padding:18px;border:1px solid #dce4ef;border-radius:16px;background:#fff}.qr canvas{display:block;max-width:100%;height:auto}.notice{padding:14px;border-radius:12px;background:#eef8ff;color:#174b70;font-size:14px;line-height:1.55}.notice.success{background:#eaf8f1;color:#08784e}.notice.error{background:#fff1f1;color:#a12626}.actions{display:grid;gap:10px;margin-top:18px}.btn{display:inline-flex;min-height:46px;align-items:center;justify-content:center;padding:0 18px;border:1px solid #d7e0eb;border-radius:12px;background:#fff;color:#172033;font:inherit;font-weight:800;text-decoration:none;cursor:pointer}.btn.primary{border-color:#2563eb;background:#2563eb;color:#fff}.btn:focus-visible{outline:3px solid rgba(37,99,235,.28);outline-offset:3px}@media(max-width:480px){.pass{padding:22px;border-radius:18px}h1{font-size:23px}}
    </style>
</head>
<body>
<main class="pass">
    <p class="eyebrow">{{ trans('treatmentreservation::public.checkin_pass') }}</p>
    <span class="ref">{{ $booking->referenceCode() }}</span>
    <h1>{{ trans('treatmentreservation::public.checkin_pass_title') }}</h1>
    <p class="valid">{{ trans('treatmentreservation::public.checkin_pass_valid') }}</p>

    <dl class="details">
        <div><dt>{{ trans('treatmentreservation::public.treatment') }}</dt><dd>{{ $booking->product?->name ?: '—' }}</dd></div>
        <div><dt>{{ trans('treatmentreservation::public.date') }}</dt><dd>{{ $booking->appointment_date?->translatedFormat('d M Y') ?: '—' }}</dd></div>
        <div><dt>{{ trans('treatmentreservation::public.time') }}</dt><dd>{{ $booking->displayAppointmentTime() ?: '—' }}</dd></div>
        <div><dt>{{ trans('treatmentreservation::public.location') }}</dt><dd>{{ $booking->spaBranchLabel() ?: '—' }}</dd></div>
    </dl>

    @if ($booking->status === \Modules\TreatmentReservation\Entities\TreatmentBooking::STATUS_PENDING && ! $booking->checked_in_at)
        <div class="qr">
            <canvas data-checkin-pass="{{ $checkinUrl }}" role="img" aria-label="{{ trans('treatmentreservation::public.checkin_pass') }}"></canvas>
        </div>
    @endif

    @if (session('checkin_success'))
        <p class="notice success" role="status">{{ session('checkin_success') }}</p>
    @elseif ($errors->has('checkin'))
        <p class="notice error" role="alert">{{ $errors->first('checkin') }}</p>
    @elseif ($booking->checked_in_at)
        <p class="notice success">{{ trans('treatmentreservation::public.checkin_pass_confirmed', ['time' => $booking->checked_in_at->format('g:i A')]) }}</p>
    @else
        <p class="notice">{{ $canConfirm ? trans('treatmentreservation::public.checkin_pass_staff_hint') : trans('treatmentreservation::public.checkin_pass_customer_hint') }}</p>
    @endif

    <div class="actions">
        @if ($canConfirm && $booking->status === \Modules\TreatmentReservation\Entities\TreatmentBooking::STATUS_PENDING && ! $booking->checked_in_at)
            <form method="post" action="{{ route('admin.leads.checkin.confirm', ['booking' => $booking->id]) }}">
                @csrf
                <button class="btn primary" style="width:100%" type="submit">{{ trans('treatmentreservation::public.checkin_pass_confirm') }}</button>
            </form>
        @endif
        <a class="btn" href="{{ route('treatment_reservations.booking.lookup') }}">{{ trans('treatmentreservation::public.checkin_pass_back') }}</a>
    </div>
</main>
<script src="{{ asset('modules/lead/central/qrcode.js') }}?v={{ @filemtime(public_path('modules/lead/central/qrcode.js')) ?: time() }}"></script>
<script>
    (function () {
        const canvas = document.querySelector('[data-checkin-pass]');
        if (! canvas || ! window.QRCentral) {
            return;
        }

        try {
            window.QRCentral.render(canvas, canvas.dataset.checkinPass || '', { size: 240 });
        } catch (error) {
            console.error('Could not render arrival pass QR', error);
            canvas.closest('.qr')?.remove();
        }
    })();
</script>
</body>
</html>
