<form method="GET" action="{{ route('admin.treatment_reservations.index') }}" class="tr-filters tr-filters--calendar box">
    <input type="hidden" name="view" value="{{ $activeView }}">

    <div class="tr-filters__header">
        <div>
            <h4 class="tr-filters__title">
                <i class="fa fa-sliders"></i>
                {{ trans('treatmentreservation::admin.filters.title') }}
            </h4>
            <p class="tr-filters__subtitle">{{ trans('treatmentreservation::admin.filters.calendar_help') }}</p>
        </div>
    </div>

    <div class="tr-filters__grid">
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

        <div class="tr-filters__field">
            <label for="tr-category">{{ trans('treatmentreservation::admin.filters.category') }}</label>
            <select name="treatment_category_id" id="tr-category" class="custom-select-black">
                <option value="">{{ trans('treatmentreservation::admin.filters.all_categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ (int) $filters['treatment_category_id'] === $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="tr-filters__field">
            <label for="tr-month">{{ trans('treatmentreservation::admin.filters.month') }}</label>
            <input type="month" name="month" id="tr-month" class="form-control" value="{{ $filters['month'] }}">
        </div>

        <div class="tr-filters__field tr-filters__field--action">
            <label class="tr-filters__action-label">&nbsp;</label>
            <button type="submit" class="btn btn-primary tr-filters__submit">
                <i class="fa fa-filter"></i> {{ trans('treatmentreservation::admin.filters.apply') }}
            </button>
        </div>
    </div>
</form>
