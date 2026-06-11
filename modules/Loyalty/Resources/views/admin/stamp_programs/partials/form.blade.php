@php
    $productIdsText = old('product_ids_text');

    if ($productIdsText === null && is_array($program->product_ids)) {
        $productIdsText = implode(', ', $program->product_ids);
    }
@endphp

<div class="box box-primary">
    <div class="box-body">
        <div class="form-group">
            <label for="name" class="col-sm-3 control-label text-left">
                {{ trans('loyalty::stamp_programs.form.name') }}
                <span class="text-red">*</span>
            </label>
            <div class="col-sm-9">
                <input
                    type="text"
                    name="name"
                    id="name"
                    class="form-control"
                    value="{{ old('name', $program->name) }}"
                    required
                >
            </div>
        </div>

        <div class="form-group">
            <label for="reward_description" class="col-sm-3 control-label text-left">
                {{ trans('loyalty::stamp_programs.form.reward_description') }}
            </label>
            <div class="col-sm-9">
                <textarea
                    name="reward_description"
                    id="reward_description"
                    class="form-control"
                    rows="2"
                >{{ old('reward_description', $program->reward_description) }}</textarea>
                <p class="help-block">{{ trans('loyalty::stamp_programs.form.reward_description_help') }}</p>
            </div>
        </div>

        <div class="form-group">
            <label for="stamps_required" class="col-sm-3 control-label text-left">
                {{ trans('loyalty::stamp_programs.form.stamps_required') }}
                <span class="text-red">*</span>
            </label>
            <div class="col-sm-9">
                <input
                    type="number"
                    name="stamps_required"
                    id="stamps_required"
                    class="form-control"
                    min="2"
                    max="30"
                    value="{{ old('stamps_required', $program->stamps_required ?? 7) }}"
                    required
                >
            </div>
        </div>

        <div class="form-group">
            <label for="validity_days" class="col-sm-3 control-label text-left">
                {{ trans('loyalty::stamp_programs.form.validity_days') }}
                <span class="text-red">*</span>
            </label>
            <div class="col-sm-9">
                <input
                    type="number"
                    name="validity_days"
                    id="validity_days"
                    class="form-control"
                    min="1"
                    max="365"
                    value="{{ old('validity_days', $program->validity_days ?? 30) }}"
                    required
                >
                <p class="help-block">{{ trans('loyalty::stamp_programs.form.validity_days_help') }}</p>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label text-left">
                {{ trans('loyalty::stamp_programs.form.eligibility') }}
            </label>
            <div class="col-sm-9">
                <div class="checkbox">
                    <label>
                        <input
                            type="hidden"
                            name="virtual_treatments_only"
                            value="0"
                        >
                        <input
                            type="checkbox"
                            name="virtual_treatments_only"
                            value="1"
                            {{ old('virtual_treatments_only', $program->virtual_treatments_only ?? true) ? 'checked' : '' }}
                        >
                        {{ trans('loyalty::stamp_programs.form.virtual_treatments_only') }}
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="product_ids_text" class="col-sm-3 control-label text-left">
                {{ trans('loyalty::stamp_programs.form.product_ids') }}
            </label>
            <div class="col-sm-9">
                <input
                    type="text"
                    name="product_ids_text"
                    id="product_ids_text"
                    class="form-control"
                    value="{{ $productIdsText }}"
                    placeholder="12, 34, 56"
                >
                <p class="help-block">{{ trans('loyalty::stamp_programs.form.product_ids_help') }}</p>
            </div>
        </div>

        <div class="form-group">
            <label for="sort_order" class="col-sm-3 control-label text-left">
                {{ trans('loyalty::stamp_programs.form.sort_order') }}
            </label>
            <div class="col-sm-9">
                <input
                    type="number"
                    name="sort_order"
                    id="sort_order"
                    class="form-control"
                    min="0"
                    value="{{ old('sort_order', $program->sort_order ?? 0) }}"
                >
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label text-left">
                {{ trans('admin::admin.table.status') }}
            </label>
            <div class="col-sm-9">
                <select name="is_active" class="form-control custom-select-black">
                    <option value="1" {{ old('is_active', $program->is_active ?? true) ? 'selected' : '' }}>
                        {{ trans('admin::admin.table.active') }}
                    </option>
                    <option value="0" {{ ! old('is_active', $program->is_active ?? true) ? 'selected' : '' }}>
                        {{ trans('admin::admin.table.inactive') }}
                    </option>
                </select>
            </div>
        </div>
    </div>
</div>
