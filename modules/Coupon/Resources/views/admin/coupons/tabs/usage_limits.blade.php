@php
    $field = ['labelCol' => 2];
@endphp

<div class="coupon-form-tab">
    <div class="coupon-form-sheet coupon-form-sheet--narrow">
        <div class="coupon-form-section">
            <h3 class="coupon-form-section__title">{{ trans('coupon::coupons.form.sections.limits') }}</h3>
            <div class="coupon-form-section__fields coupon-form-section__fields--pair">
                {{ Form::number('usage_limit_per_coupon', trans('coupon::attributes.usage_limit_per_coupon'), $errors, $coupon, array_merge($field, ['min' => 0, 'placeholder' => trans('coupon::coupons.form.unlimited')])) }}
                {{ Form::number('usage_limit_per_customer', trans('coupon::attributes.usage_limit_per_customer'), $errors, $coupon, array_merge($field, ['min' => 0, 'placeholder' => trans('coupon::coupons.form.unlimited')])) }}
            </div>
        </div>
    </div>
</div>
