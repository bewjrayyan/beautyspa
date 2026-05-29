@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;

    $showBeautician = $showBeautician ?? true;
@endphp

<template id="tr-kanban-card-template">
    <article class="tr-kanban-card" draggable="true">
        <div class="tr-kanban-card-accent"></div>
        <h5 class="tr-kanban-card-customer"></h5>
        <div class="tr-kanban-card-product">
            <p class="tr-kanban-card-product__name"></p>
            <p class="tr-kanban-card-treatment-line" hidden>
                <span class="tr-kanban-card-treatment-line__label">{{ TrLang::trans('admin.calendar.preview_treatment') }}:</span>
                <span class="tr-kanban-card-treatment-line__value"></span>
            </p>
        </div>
        @if ($showBeautician)
            <div class="tr-kanban-card-staff">
                <span class="tr-kanban-card-beautician">
                    <span class="tr-kanban-card-beautician-avatar" aria-hidden="true"></span>
                    <span class="tr-kanban-card-beautician-name"></span>
                </span>
                <span class="tr-kanban-card-position" hidden></span>
            </div>
        @endif
        <div class="tr-kanban-card-datetime">
            <span class="tr-kanban-card-date"><i class="fa fa-calendar"></i> <span></span></span>
            <span class="tr-kanban-card-time-slot"><i class="fa fa-clock-o"></i> <span></span></span>
        </div>
        <a href="#" class="tr-kanban-card-link" target="_blank" hidden>
            {{ trans('treatmentreservation::admin.kanban.view_order') }}
        </a>
    </article>
</template>
