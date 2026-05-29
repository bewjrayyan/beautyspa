<div class="form-group report-field">
    <label class="report-field__label" for="from">{{ trans('report::admin.filters.date_start') }}</label>
    <input type="text" name="from" class="form-control datetime-picker" id="from" data-default-date="{{ $request->from }}">
</div>
