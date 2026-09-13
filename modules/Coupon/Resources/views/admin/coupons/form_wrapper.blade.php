<div class="coupon-form-layout">
    <nav class="coupon-form-tabs" aria-label="{{ trans('coupon::coupons.tabs.group.coupon_information') }}">
        <ul class="coupon-form-tabs__list" role="tablist">
            @foreach ($navTabs as $tab)
                <li
                    class="coupon-form-tabs__item {{ $tab['active'] ? 'active coupon-form-tabs__item--active' : '' }} {{ $tab['hasError'] ? 'coupon-form-tabs__item--error' : '' }}"
                    role="presentation"
                >
                    <a
                        href="#tab-{{ $tab['name'] }}"
                        class="coupon-form-tabs__link"
                        role="tab"
                        data-toggle="tab"
                        data-tab-name="{{ $tab['name'] }}"
                        aria-controls="tab-{{ $tab['name'] }}"
                        aria-selected="{{ $tab['active'] ? 'true' : 'false' }}"
                        aria-expanded="{{ $tab['active'] ? 'true' : 'false' }}"
                    >
                        <span class="coupon-form-tabs__step">{{ $tab['step'] }}</span>
                        <span class="coupon-form-tabs__copy">
                            <strong>{{ $tab['label'] }}</strong>
                            <small>{{ $tab['description'] }}</small>
                        </span>
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
            <p class="coupon-form-layout__save-note">
                <i class="fa fa-shield" aria-hidden="true"></i>
                {{ trans('coupon::coupons.form.save_note') }}
            </p>
            @include('admin::form.footer', ['buttonOffset' => $buttonOffset])
        </div>
    </div>
</div>
