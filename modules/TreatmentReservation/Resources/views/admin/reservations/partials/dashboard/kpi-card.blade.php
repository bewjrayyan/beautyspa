@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;
@endphp

@php
    $variant = $variant ?? 'default';
    $icon = $icon ?? 'fa-circle';
    $label = $label ?? '';
    $value = $value ?? '0';
    $hint = $hint ?? '';
    $subhint = $subhint ?? '';
@endphp

<article class="tr-crm-kpi tr-crm-kpi--{{ $variant }}">
    <div class="tr-crm-kpi__icon-wrap" aria-hidden="true">
        <i class="fa {{ $icon }}"></i>
    </div>
    <div class="tr-crm-kpi__body">
        <span class="tr-crm-kpi__label">{{ $label }}</span>
        <div class="tr-crm-kpi__value-row">
            <span class="tr-crm-kpi__value">{{ $value }}</span>
            <span class="tr-crm-kpi__unit">{{ TrLang::trans('admin.crm.kpi_unit') }}</span>
        </div>
        @if ($hint !== '')
            <p class="tr-crm-kpi__hint">{{ $hint }}</p>
        @endif
        @if ($subhint !== '')
            <p class="tr-crm-kpi__subhint">{{ $subhint }}</p>
        @endif
    </div>
</article>
