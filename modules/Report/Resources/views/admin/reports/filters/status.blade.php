<div class="form-group report-field">
    <label class="report-field__label" for="status">{{ trans('report::admin.filters.status') }}</label>

    <select name="status" id="status" class="custom-select-black">
        <option value="">{{ trans('report::admin.filters.please_select') }}</option>

        @foreach (trans('order::statuses') as $name => $label)
            <option value="{{ $name }}" {{ $request->status === $name ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
</div>
