@if (isset($spaBranches) && $spaBranches->isNotEmpty())
    <div class="form-group report-field">
        <label class="report-field__label" for="spa-branch-id">{{ trans('report::admin.filters.spa_branch') }}</label>

        <select name="spa_branch_id" id="spa-branch-id" class="custom-select-black">
            <option value="">{{ trans('report::admin.filters.all_spa_branches') }}</option>

            @foreach ($spaBranches as $id => $name)
                <option value="{{ $id }}" {{ (string) $request->spa_branch_id === (string) $id ? 'selected' : '' }}>
                    {{ $name }}
                </option>
            @endforeach
        </select>
    </div>
@endif
