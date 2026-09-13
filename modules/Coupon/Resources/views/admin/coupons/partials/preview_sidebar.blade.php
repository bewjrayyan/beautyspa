@php
    $isEdit = $coupon->exists;

    if ($isEdit) {
        if ($coupon->is_percent) {
            $raw = (float) ($coupon->getAttributes()['value'] ?? 0);
            $discountPreview = fmod($raw, 1.0) === 0.0
                ? (int) $raw . '%'
                : rtrim(rtrim(number_format($raw, 2, '.', ''), '0'), '.') . '%';
        } else {
            $discountPreview = currency_symbol(setting('default_currency'))
                . number_format((float) ($coupon->getAttributes()['value'] ?? 0), 2);
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
        $statusKey = null;
        $statusClass = 'draft';
        $dateRange = null;
    }
@endphp

<aside class="coupon-preview-sidebar" aria-label="{{ trans('coupon::coupons.form.preview_title') }}">
    <p class="coupon-preview-sidebar__eyebrow">{{ trans('coupon::coupons.form.preview_title') }}</p>

    <div class="coupon-template">
        <div class="coupon-header">
            <div class="discount-badge badge-left" aria-hidden="true">%</div>
            <h2 class="coupon-template__title" id="coupon-form-name-preview">{{ $isEdit ? $coupon->name : trans('coupon::coupons.form.preview_name_placeholder') }}</h2>
            <div class="discount-badge badge-right" aria-hidden="true">%</div>
        </div>

        <div class="coupon-content">
            <div class="coupon-template__value">
                <span id="coupon-form-discount-preview">{{ $discountPreview }}</span>
            </div>

            <div class="coupon-divider" aria-hidden="true">
                <span></span>
                <span></span>
            </div>

            <div class="coupon-template__details">
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
            </div>

            <span class="coupon-template__code-label">{{ trans('coupon::coupons.form.use_code') }}</span>
            <button type="button" class="coupon-button" aria-label="{{ trans('coupon::coupons.form.use_code') }}">
                <span id="coupon-form-code-preview">{{ $isEdit ? $coupon->code : 'CODE' }}</span>
            </button>

            <p class="coupon-template__dates" id="coupon-form-dates-preview" @if (! $isEdit || ! $dateRange) hidden @endif>{{ $dateRange }}</p>
        </div>
    </div>

    <p class="coupon-preview-sidebar__hint">{{ trans('coupon::coupons.form.preview_hint') }}</p>

    <div class="coupon-preview-sidebar__readiness">
        <div class="coupon-preview-sidebar__readiness-heading">
            <span>{{ trans('coupon::coupons.form.readiness_title') }}</span>
            <strong id="coupon-readiness-count">0/4</strong>
        </div>
        <ul>
            @foreach (['identity', 'discount', 'schedule', 'limits'] as $check)
                <li data-readiness="{{ $check }}">
                    <i class="fa fa-circle-o" aria-hidden="true"></i>
                    <span>{{ trans('coupon::coupons.form.readiness.' . $check) }}</span>
                </li>
            @endforeach
        </ul>
    </div>
</aside>
