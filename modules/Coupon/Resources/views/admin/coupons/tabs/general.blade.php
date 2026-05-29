@php
    $field = ['labelCol' => 2];
@endphp

<div class="coupon-form-tab">
    <div class="coupon-form-sheet">
        <div class="coupon-form-sheet__col">
            <div class="coupon-form-section">
                <h3 class="coupon-form-section__title">{{ trans('coupon::coupons.form.sections.basic') }}</h3>
                <div class="coupon-form-section__fields coupon-form-section__fields--pair">
                    {{ Form::text('name', trans('coupon::attributes.name'), $errors, $coupon, array_merge($field, ['required' => true])) }}
                    {{ Form::text('code', trans('coupon::attributes.code'), $errors, $coupon, array_merge($field, ['required' => true])) }}
                </div>
            </div>

            <div class="coupon-form-section">
                <h3 class="coupon-form-section__title">{{ trans('coupon::coupons.form.sections.discount') }}</h3>
                <div class="coupon-form-section__fields coupon-form-section__fields--pair">
                    {{ Form::select('is_percent', trans('coupon::attributes.is_percent'), $errors, trans('coupon::coupons.form.price_types'), $coupon, $field) }}
                    {{ Form::number('value', trans('coupon::attributes.value'), $errors, $coupon, array_merge($field, ['min' => 0, 'step' => 'any'])) }}
                </div>
                <p class="coupon-form-hint" id="coupon-value-hint" hidden></p>
                <div class="coupon-form-section__fields coupon-form-section__fields--inline-check">
                    {{ Form::checkbox('free_shipping', trans('coupon::attributes.free_shipping'), trans('coupon::coupons.form.allow_free_shipping'), $errors, $coupon->freeShipping(), $field) }}
                </div>
            </div>
        </div>

        <div class="coupon-form-sheet__col">
            <div class="coupon-form-section">
                <h3 class="coupon-form-section__title">{{ trans('coupon::coupons.form.sections.schedule') }}</h3>
                <div class="coupon-form-section__fields coupon-form-section__fields--pair">
                    {{ Form::text('start_date', trans('coupon::attributes.start_date'), $errors, $coupon, array_merge($field, ['class' => 'datetime-picker', 'data-default-date' => $coupon->start_date, 'data-time' => true])) }}
                    {{ Form::text('end_date', trans('coupon::attributes.end_date'), $errors, $coupon, array_merge($field, ['class' => 'datetime-picker', 'data-default-date' => $coupon->end_date, 'data-time' => true])) }}
                </div>
            </div>

            <div class="coupon-form-section">
                <h3 class="coupon-form-section__title">{{ trans('coupon::coupons.form.sections.status') }}</h3>
                <div class="coupon-form-section__fields coupon-form-section__fields--inline-check">
                    {{ Form::checkbox('is_active', trans('coupon::attributes.is_active'), trans('coupon::coupons.form.enable_the_coupon'), $errors, $coupon, $field) }}
                </div>
            </div>
        </div>
    </div>
</div>
