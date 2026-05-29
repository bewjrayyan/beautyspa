@php
    $isEdit = $coupon->exists;

    if ($isEdit) {
        if ($coupon->is_percent) {
            $raw = (float) ($coupon->getAttributes()['value'] ?? 0);
            $discountPreview = fmod($raw, 1.0) === 0.0
                ? (int) $raw . '%'
                : rtrim(rtrim(number_format($raw, 2, '.', ''), '0'), '.') . '%';
            $typePreview = trans('coupon::coupons.form.price_types.1');
        } else {
            $discountPreview = $coupon->value->format();
            $typePreview = trans('coupon::coupons.form.price_types.0');
        }

        if (! $coupon->is_active) {
            $statusKey = 'status_inactive';
            $statusClass = 'inactive';
        } elseif (! $coupon->valid()) {
            $scheduled = $coupon->start_date && today()->lt($coupon->start_date);
            $statusKey = $scheduled ? 'status_scheduled' : 'status_expired';
            $statusClass = $scheduled ? 'scheduled' : 'expired';
        } else {
            $statusKey = 'status_valid';
            $statusClass = 'active';
        }

        $dateRange = collect([$coupon->start_date, $coupon->end_date])
            ->filter()
            ->map(fn ($d) => $d->format('d M Y'))
            ->implode(' – ');
    } else {
        $discountPreview = '—';
        $typePreview = trans('coupon::coupons.form.price_types.1');
        $statusKey = null;
        $statusClass = 'draft';
        $dateRange = null;
    }
@endphp

<aside class="coupon-preview-sidebar" aria-label="{{ trans('coupon::coupons.form.preview_title') }}">
    <p class="coupon-preview-sidebar__eyebrow">{{ trans('coupon::coupons.form.preview_title') }}</p>

    <div class="coupon-preview-sidebar__ticket">
        <div class="coupon-preview-sidebar__stub">
            <span class="coupon-preview-sidebar__stub-label">{{ trans('coupon::coupons.index.ticket_off') }}</span>
            <span class="coupon-preview-sidebar__discount" id="coupon-form-discount-preview">{{ $discountPreview }}</span>
            <span class="coupon-preview-sidebar__type" id="coupon-form-type-preview">{{ $typePreview }}</span>
        </div>

        <div class="coupon-preview-sidebar__tear" aria-hidden="true"></div>

        <div class="coupon-preview-sidebar__body">
            <span class="coupon-preview-sidebar__code" id="coupon-form-code-preview">{{ $isEdit ? $coupon->code : 'CODE' }}</span>
            <h2 class="coupon-preview-sidebar__name" id="coupon-form-name-preview">{{ $isEdit ? $coupon->name : trans('coupon::coupons.form.preview_name_placeholder') }}</h2>

            @if ($isEdit && $statusKey)
                <span class="coupon-preview-sidebar__status coupon-preview-sidebar__status--{{ $statusClass }}" id="coupon-form-status-preview">
                    {{ trans('coupon::coupons.index.' . $statusKey) }}
                </span>
            @else
                <span class="coupon-preview-sidebar__status coupon-preview-sidebar__status--draft" id="coupon-form-status-preview">
                    {{ trans('coupon::coupons.form.preview_draft') }}
                </span>
            @endif

            <span
                class="coupon-preview-sidebar__tag"
                id="coupon-form-shipping-preview"
                @if (! $isEdit || ! $coupon->free_shipping) hidden @endif
            >
                <i class="fa fa-truck" aria-hidden="true"></i>
                {{ trans('coupon::coupons.index.free_shipping') }}
            </span>

            @if ($isEdit && $dateRange)
                <p class="coupon-preview-sidebar__dates" id="coupon-form-dates-preview">{{ $dateRange }}</p>
            @else
                <p class="coupon-preview-sidebar__dates" id="coupon-form-dates-preview" hidden></p>
            @endif
        </div>
    </div>

    <p class="coupon-preview-sidebar__hint">{{ trans('coupon::coupons.form.preview_hint') }}</p>
</aside>
