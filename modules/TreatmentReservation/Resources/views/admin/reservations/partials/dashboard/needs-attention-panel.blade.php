@php
    use Modules\TreatmentReservation\Support\TreatmentReservationLang as TrLang;

    $needsAttention = $needsAttention ?? ['total' => 0, 'buckets' => [], 'items' => []];
    $total = (int) ($needsAttention['total'] ?? 0);
    $buckets = $needsAttention['buckets'] ?? [];
    $items = $needsAttention['items'] ?? [];
@endphp

<section class="tr-crm-panel tr-crm-panel--needs-attention" aria-labelledby="tr-crm-needs-attention-title">
    <header class="tr-crm-panel__head">
        <div>
            <h3 class="tr-crm-panel__title" id="tr-crm-needs-attention-title">
                {{ TrLang::trans('admin.crm.needs_attention_title') }}
                <span class="tr-crm-panel__count{{ $total > 0 ? ' tr-crm-panel__count--alert' : '' }}">{{ $total }}</span>
            </h3>
            <p class="tr-crm-panel__lead">{{ TrLang::trans('admin.crm.needs_attention_lead') }}</p>
        </div>
    </header>

    <div class="tr-crm-needs" data-crm-needs-attention>
        <div class="tr-crm-needs__buckets" role="list">
            @foreach ($buckets as $bucket)
                <div
                    class="tr-crm-needs__bucket tr-crm-needs__bucket--{{ $bucket['urgency'] ?? 'info' }}{{ ((int) ($bucket['count'] ?? 0)) === 0 ? ' is-empty' : '' }}"
                    role="listitem"
                    title="{{ $bucket['hint'] ?? ($bucket['label'] ?? '') }}"
                >
                    <span class="tr-crm-needs__bucket-count">{{ number_format((int) ($bucket['count'] ?? 0)) }}</span>
                    <span class="tr-crm-needs__bucket-label">{{ $bucket['label'] ?? '' }}</span>
                </div>
            @endforeach
        </div>

        <ul class="tr-crm-needs__list" data-crm-list>
            @forelse ($items as $item)
                @php
                    $canOpen = ! array_key_exists('can_open_detail', $item) || ! empty($item['can_open_detail']);
                @endphp
                <li
                    class="tr-crm-needs__item tr-crm-needs__item--{{ $item['urgency'] ?? 'info' }} tr-crm-appointment{{ $canOpen ? ' tr-crm-appointment--clickable' : '' }}"
                    data-booking-id="{{ $item['id'] ?? '' }}"
                    data-own-booking="{{ $canOpen ? '1' : '0' }}"
                    data-search="{{ strtolower(($item['customer_name'] ?? '') . ' ' . ($item['treatment_name'] ?? '') . ' ' . ($item['reason_label'] ?? '') . ' ' . ($item['appointment_label'] ?? '') . ' ' . ($item['beautician_name'] ?? '') . ' ' . ($item['id'] ?? '')) }}"
                    @if ($canOpen) role="button" tabindex="0" @endif
                >
                    <span class="tr-crm-needs__item-dot" aria-hidden="true"></span>
                    <div class="tr-crm-needs__item-body">
                        <div class="tr-crm-needs__item-top">
                            <strong class="tr-crm-needs__item-customer">{{ $item['customer_name'] ?? '—' }}</strong>
                            <span class="tr-crm-needs__item-reason">{{ $item['reason_label'] ?? '' }}</span>
                        </div>
                        <p class="tr-crm-needs__item-treatment">{{ $item['treatment_name'] ?? '—' }}</p>
                        <p class="tr-crm-needs__item-meta">
                            <span>{{ $item['appointment_label'] ?? '—' }}</span>
                            @if (! empty($item['beautician_name']))
                                <span class="tr-crm-needs__item-sep">·</span>
                                <span>{{ $item['beautician_name'] }}</span>
                            @endif
                        </p>
                    </div>
                    <span class="tr-crm-needs__item-open" aria-hidden="true">
                        <i class="fa fa-chevron-right"></i>
                    </span>
                </li>
            @empty
                <li class="tr-crm-needs__empty">
                    <i class="fa fa-check-circle" aria-hidden="true"></i>
                    <div>
                        <strong>{{ TrLang::trans('admin.crm.needs_attention_empty_title') }}</strong>
                        <p>{{ TrLang::trans('admin.crm.needs_attention_empty_lead') }}</p>
                    </div>
                </li>
            @endforelse
        </ul>
    </div>
</section>
