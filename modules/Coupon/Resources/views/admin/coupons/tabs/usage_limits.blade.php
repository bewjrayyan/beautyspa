@php
    $field = ['labelCol' => 2];
@endphp

<div class="coupon-form-tab">
    <div class="coupon-form-sheet coupon-form-sheet--narrow">
        <div class="coupon-form-section">
            <div class="coupon-form-section__header">
                <span class="coupon-form-section__icon"><i class="fa fa-users" aria-hidden="true"></i></span>
                <div><h3>{{ trans('coupon::coupons.form.sections.limits') }}</h3><p>{{ trans('coupon::coupons.form.sections.limits_lead') }}</p></div>
            </div>
            <div class="coupon-form-callout">
                <i class="fa fa-info-circle" aria-hidden="true"></i>
                <span>{{ trans('coupon::coupons.form.limits_hint') }}</span>
            </div>
            <div class="coupon-form-section__fields coupon-form-section__fields--pair">
                {{ Form::number('usage_limit_per_coupon', trans('coupon::attributes.usage_limit_per_coupon'), $errors, $coupon, array_merge($field, ['min' => 1, 'step' => 1, 'placeholder' => trans('coupon::coupons.form.unlimited')])) }}
                {{ Form::number('usage_limit_per_customer', trans('coupon::attributes.usage_limit_per_customer'), $errors, $coupon, array_merge($field, ['min' => 1, 'step' => 1, 'placeholder' => trans('coupon::coupons.form.unlimited')])) }}
            </div>
        </div>
    </div>
</div>
