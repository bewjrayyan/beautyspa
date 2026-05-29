<div class="form-group report-field report-filter-field--half">
    <label class="report-field__label" for="category_id">{{ trans('report::admin.filters.category') }}</label>

    <select name="category_id" id="category_id" class="custom-select-black">
        <option value="">{{ trans('report::admin.filters.please_select') }}</option>

        @foreach ($categories ?? [] as $id => $name)
            <option value="{{ $id }}" {{ (string) $request->category_id === (string) $id ? 'selected' : '' }}>
                {{ $name }}
            </option>
        @endforeach
    </select>
</div>
