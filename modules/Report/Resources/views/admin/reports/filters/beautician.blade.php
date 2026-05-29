@if (isset($beauticians) && $beauticians->isNotEmpty())
    <div class="form-group report-field">
        <label class="report-field__label" for="beautician-id">{{ trans('report::admin.filters.beautician') }}</label>

        <select name="beautician_id" id="beautician-id" class="custom-select-black">
            <option value="">{{ trans('report::admin.filters.all_beauticians') }}</option>

            @foreach ($beauticians as $id => $name)
                <option value="{{ $id }}" {{ (string) $request->beautician_id === (string) $id ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach
        </select>
    </div>
@endif
