<div class="form-group report-field">
    <label class="report-field__label" for="to">{{ trans('report::admin.filters.date_end') }}</label>
    <div class="report-date-control">
        <i class="fa fa-calendar-o" aria-hidden="true"></i>
        <input type="text" name="to" class="form-control datetime-picker" id="to" data-default-date="{{ $request->to }}" placeholder="{{ trans('report::admin.filters.select_date') }}" autocomplete="off">
    </div>
</div>
