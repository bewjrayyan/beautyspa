<div class="form-group report-field">
    <label class="report-field__label" for="from">{{ trans('report::admin.filters.date_start') }}</label>
    <div class="report-date-control">
        <i class="fa fa-calendar-o" aria-hidden="true"></i>
        <input type="text" name="from" class="form-control datetime-picker" id="from" data-default-date="{{ $request->from }}" placeholder="{{ trans('report::admin.filters.select_date') }}" autocomplete="off">
    </div>
</div>
