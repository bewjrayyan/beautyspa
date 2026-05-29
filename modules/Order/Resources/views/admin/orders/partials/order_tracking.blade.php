<div class="order-show__card order-show__card--tracking">
    <div class="order-show__card-head">
        <h5><i class="fa fa-map-marker" aria-hidden="true"></i> {{ trans('order::orders.order_tracking') }}</h5>
    </div>

    <form action="{{ route('admin.orders.update', $order->id) }}" method="POST" class="order-show__tracking-form">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="tracking_reference">{{ trans('order::orders.tracking_reference') }}</label>
            <input
                type="text"
                name="tracking_reference"
                id="tracking_reference"
                class="form-control @error('tracking_reference') is-invalid @enderror"
                value="{{ old('tracking_reference', $order->tracking_reference) }}"
                placeholder="{{ trans('order::orders.tracking_reference_placeholder') }}"
            >
            @error('tracking_reference')
                <span class="help-block text-red">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            <i class="fa fa-check" aria-hidden="true"></i>
            {{ trans('admin::admin.buttons.save') }}
        </button>
    </form>
</div>
