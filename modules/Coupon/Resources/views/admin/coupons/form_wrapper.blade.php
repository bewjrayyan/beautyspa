<div class="coupon-form-layout">
    <nav class="coupon-form-tabs" aria-label="{{ trans('coupon::coupons.tabs.group.coupon_information') }}">
        <ul class="coupon-form-tabs__list" role="tablist">
            @foreach ($navTabs as $tab)
                <li
                    class="coupon-form-tabs__item {{ $tab['active'] ? 'coupon-form-tabs__item--active' : '' }} {{ $tab['hasError'] ? 'coupon-form-tabs__item--error' : '' }}"
                    role="presentation"
                >
                    <a
                        href="{{ $formUrl }}?tab={{ $tab['name'] }}"
                        class="coupon-form-tabs__link"
                        role="tab"
                        aria-selected="{{ $tab['active'] ? 'true' : 'false' }}"
                    >
                        <i class="fa {{ $tab['icon'] }}" aria-hidden="true"></i>
                        {{ $tab['label'] }}
                        @if ($tab['hasError'])
                            <i class="fa fa-exclamation-circle" aria-hidden="true"></i>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="coupon-form-layout__body">
        <div class="tab-content coupon-form-layout__content">
            {{ $contents }}
        </div>

        <div class="coupon-form-layout__footer">
            @include('admin::form.footer', ['buttonOffset' => $buttonOffset])
        </div>
    </div>
</div>
