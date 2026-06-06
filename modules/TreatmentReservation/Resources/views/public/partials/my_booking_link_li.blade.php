@if (app('modules')->isEnabled('TreatmentReservation'))
    <li>
        <a href="{{ route('treatment_reservations.booking.lookup') }}">
            @isset($menuIcon)
                {!! $menuIcon !!}
            @endisset
            {{ trans('treatmentreservation::public.nav_link') }}
        </a>
    </li>
@endif
