@php
    $field = ['labelCol' => 2];
@endphp

<div class="coupon-form-tab">
    <div class="coupon-form-sheet">
        <div class="coupon-form-sheet__col">
            <div class="coupon-form-section">
                <div class="coupon-form-section__header">
                    <span class="coupon-form-section__icon"><i class="fa fa-tag" aria-hidden="true"></i></span>
                    <div><h3>{{ trans('coupon::coupons.form.sections.basic') }}</h3><p>{{ trans('coupon::coupons.form.sections.basic_lead') }}</p></div>
                </div>
                <div class="coupon-form-section__fields coupon-form-section__fields--pair">
                    {{ Form::text('name', trans('coupon::attributes.name'), $errors, $coupon, array_merge($field, ['required' => true])) }}
                    <div class="form-group coupon-code-field {{ $errors->has('code') ? 'has-error' : '' }}">
                        <label for="code" class="control-label text-left">
                            {{ trans('coupon::attributes.code') }}<span class="m-l-5 text-red">*</span>
                        </label>
                        <div class="coupon-code-generator">
                            <input
                                type="text"
                                name="code"
                                id="code"
                                class="form-control"
                                value="{{ old('code', $coupon->code) }}"
                                maxlength="255"
                                autocomplete="off"
                                spellcheck="false"
                                required
                            >
                            <button type="button" class="coupon-code-generator__button" id="coupon-generate-code">
                                <i class="fa fa-magic" aria-hidden="true"></i>
                                {{ trans('coupon::coupons.form.generate_code') }}
                            </button>
                        </div>
                        {!! $errors->first('code', '<span class="help-block text-red">:message</span>') !!}
                    </div>
                </div>
            </div>

            <div class="coupon-form-section">
                <div class="coupon-form-section__header">
                    <span class="coupon-form-section__icon"><i class="fa fa-percent" aria-hidden="true"></i></span>
                    <div><h3>{{ trans('coupon::coupons.form.sections.discount') }}</h3><p>{{ trans('coupon::coupons.form.sections.discount_lead') }}</p></div>
                </div>
                <div class="coupon-form-section__fields coupon-form-section__fields--pair">
                    {{ Form::select('is_percent', trans('coupon::attributes.is_percent'), $errors, trans('coupon::coupons.form.price_types'), $coupon, $field) }}
                    {{ Form::number('value', trans('coupon::attributes.value'), $errors, $coupon, array_merge($field, ['required' => true, 'min' => '0.01', 'step' => '0.01'])) }}
                </div>
                <p class="coupon-form-hint" id="coupon-value-hint" hidden></p>
                <div class="coupon-form-section__fields coupon-form-section__fields--inline-check">
                    {{ Form::checkbox('free_shipping', trans('coupon::attributes.free_shipping'), trans('coupon::coupons.form.allow_free_shipping'), $errors, $coupon->freeShipping(), $field) }}
                </div>
            </div>
        </div>

        <div class="coupon-form-sheet__col">
            <div class="coupon-form-section">
                <div class="coupon-form-section__header">
                    <span class="coupon-form-section__icon"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                    <div><h3>{{ trans('coupon::coupons.form.sections.schedule') }}</h3><p>{{ trans('coupon::coupons.form.sections.schedule_lead') }}</p></div>
                </div>
                <div class="coupon-form-section__fields coupon-form-section__fields--pair">
                    {{ Form::text('start_date', trans('coupon::attributes.start_date'), $errors, $coupon, array_merge($field, ['class' => 'datetime-picker', 'data-default-date' => $coupon->start_date, 'data-time' => true])) }}
                    {{ Form::text('end_date', trans('coupon::attributes.end_date'), $errors, $coupon, array_merge($field, ['class' => 'datetime-picker', 'data-default-date' => $coupon->end_date, 'data-time' => true])) }}
                </div>
            </div>

            <div class="coupon-form-section">
                <div class="coupon-form-section__header">
                    <span class="coupon-form-section__icon"><i class="fa fa-toggle-on" aria-hidden="true"></i></span>
                    <div><h3>{{ trans('coupon::coupons.form.sections.status') }}</h3><p>{{ trans('coupon::coupons.form.sections.status_lead') }}</p></div>
                </div>
                <div class="coupon-form-section__fields coupon-form-section__fields--inline-check">
                    {{ Form::checkbox('is_active', trans('coupon::attributes.is_active'), trans('coupon::coupons.form.enable_the_coupon'), $errors, $coupon, $field) }}
                </div>
            </div>
        </div>
    </div>
</div>
