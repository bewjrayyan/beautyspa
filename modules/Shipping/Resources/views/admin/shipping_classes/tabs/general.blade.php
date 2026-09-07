<div class="row">
    <div class="col-md-8">
        {{ Form::text('name', trans('shipping::attributes.name'), $errors, $shippingClass, ['required' => true]) }}
        {{ Form::number('cost', trans('shipping::attributes.cost'), $errors, $shippingClass, ['min' => 0, 'step' => '0.01', 'required' => true]) }}
        <span class="help-block">{{ trans('shipping::shipping_classes.form.cost_help') }}</span>
        {{ Form::checkbox('is_active', trans('shipping::attributes.is_active'), trans('shipping::shipping_classes.form.enable_the_shipping_class'), $errors, $shippingClass) }}
    </div>
</div>
