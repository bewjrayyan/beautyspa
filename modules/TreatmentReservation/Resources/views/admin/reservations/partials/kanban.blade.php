@php
    $embedded = $embedded ?? false;
@endphp

<div class="tr-kanban {{ $embedded ? 'tr-kanban--embedded' : '' }}">
    @unless ($embedded)
        <div class="tr-kanban-header box">
            <div class="tr-section-head tr-section-head--flush">
                <h2 class="tr-section-head__title">{{ trans('treatmentreservation::admin.kanban.title') }}</h2>
                <p class="tr-section-head__lead">{{ trans('treatmentreservation::admin.kanban.subtitle') }}</p>
            </div>
        </div>
    @endunless

    <div class="tr-kanban-board" id="tr-kanban-board">
        @foreach (['pending', 'in_progress', 'completed'] as $status)
            <div class="tr-kanban-column" data-status="{{ $status }}">
                <div class="tr-kanban-column-header tr-kanban-column-header--{{ $status }}">
                    <span>{{ trans("treatmentreservation::admin.kanban.{$status}") }}</span>
                    <span class="tr-kanban-count" data-count="{{ $status }}">0</span>
                </div>
                <div class="tr-kanban-column-body" id="tr-kanban-{{ $status }}">
                    <div class="tr-kanban-loading text-muted text-center">
                        <i class="fa fa-spinner fa-spin"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@include('treatmentreservation::admin.reservations.partials.kanban-card-template', [
    'showBeautician' => ! ($embedded ?? false),
])

<template id="tr-calendar-event-template">
    <div class="tr-cal-event">
        <span class="tr-cal-event-time"></span>
        <strong class="tr-cal-event-customer"></strong>
        <span class="tr-cal-event-meta"></span>
    </div>
</template>
