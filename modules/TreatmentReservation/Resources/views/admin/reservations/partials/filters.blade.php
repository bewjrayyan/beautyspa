<form method="GET" action="{{ route('admin.treatment_reservations.index') }}" class="tr-filters tr-filters--kanban box">
    <input type="hidden" name="view" value="{{ $activeView }}">

    <div class="tr-filters__toolbar">
        <div class="tr-filters__field">
            <label for="tr-beautician">{{ trans('treatmentreservation::admin.filters.beautician') }}</label>
            <select name="beautician_id" id="tr-beautician" class="custom-select-black">
                <option value="">{{ trans('treatmentreservation::admin.filters.all_beauticians') }}</option>
                @foreach ($beauticians as $beautician)
                    <option value="{{ $beautician->id }}" {{ (int) $filters['beautician_id'] === $beautician->id ? 'selected' : '' }}>
                        {{ $beautician->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="tr-filters__field tr-filters__field--action">
            <button type="submit" class="btn btn-primary btn-sm tr-filters__submit">
                <i class="fa fa-filter" aria-hidden="true"></i> {{ trans('treatmentreservation::admin.filters.apply') }}
            </button>
        </div>
    </div>
</form>
