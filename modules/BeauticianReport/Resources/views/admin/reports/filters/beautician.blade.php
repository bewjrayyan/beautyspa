<div>
    <label for="beautician-id">{{ trans('beauticianreport::admin.filters.beautician') }}</label>

    <select name="beautician_id" id="beautician-id" class="custom-select-black">
        <option value="">{{ trans('beauticianreport::admin.filters.all_beauticians') }}</option>

        @foreach ($beauticians as $id => $name)
            <option value="{{ $id }}" {{ (string) $request->beautician_id === (string) $id ? 'selected' : '' }}>
                {{ $name }}
            </option>
        @endforeach
    </select>
</div>
