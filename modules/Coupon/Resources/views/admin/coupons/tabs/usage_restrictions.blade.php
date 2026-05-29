@php
    $field = ['labelCol' => 2];
@endphp

<div class="coupon-form-tab">
    <div class="coupon-form-sheet">
        <div class="coupon-form-sheet__col">
            <div class="coupon-form-section">
                <h3 class="coupon-form-section__title">{{ trans('coupon::coupons.form.sections.spend') }}</h3>
                <div class="coupon-form-section__fields coupon-form-section__fields--pair">
                    {{ Form::number('minimum_spend', trans('coupon::attributes.minimum_spend'), $errors, $coupon, array_merge($field, ['min' => 0])) }}
                    {{ Form::number('maximum_spend', trans('coupon::attributes.maximum_spend'), $errors, $coupon, array_merge($field, ['min' => 0])) }}
                </div>
            </div>

            <div class="coupon-form-section">
                <h3 class="coupon-form-section__title">{{ trans('coupon::coupons.form.sections.products') }}</h3>
                <div class="coupon-form-section__fields">
                    {{ Form::select('products', trans('coupon::attributes.products'), $errors, $products, $coupon, array_merge($field, ['class' => 'selectize prevent-creation', 'data-url' => route('admin.products.index'), 'multiple' => true])) }}
                    {{ Form::select('exclude_products', trans('coupon::attributes.exclude_products'), $errors, $excludeProducts, $coupon, array_merge($field, ['class' => 'selectize prevent-creation', 'data-url' => route('admin.products.index'), 'multiple' => true])) }}
                </div>
            </div>
        </div>

        <div class="coupon-form-sheet__col">
            <div class="coupon-form-section">
                <h3 class="coupon-form-section__title">{{ trans('coupon::coupons.form.sections.categories') }}</h3>
                <div class="coupon-form-section__fields">
                    {{ Form::select('categories', trans('coupon::attributes.categories'), $errors, $categories, $coupon, array_merge($field, ['class' => 'selectize prevent-creation', 'multiple' => true])) }}
                    {{ Form::select('exclude_categories', trans('coupon::attributes.exclude_categories'), $errors, $categories, $coupon, array_merge($field, ['class' => 'selectize prevent-creation', 'multiple' => true])) }}
                </div>
            </div>
        </div>
    </div>
</div>
