@php
    use Modules\Beautician\Support\JobTitleOptions;

    $displayTitle = old('job_title', $beautician->job_title ?? '');
    $jobTitleOptions = $jobTitleOptions ?? JobTitleOptions::forSelect($displayTitle ?: null);
@endphp

<div class="bp-field-stack bp-field-stack--job-title {{ $errors->has('job_title') ? 'has-error' : '' }}">
    {{ Form::select('job_title', trans('beautician::attributes.job_title'), $errors, $jobTitleOptions, $beautician, [
        'class' => 'form-control bp-input',
    ]) }}
    <p class="bp-field-hint">
        {{ trans('beautician::beauticians.form.job_title_help') }}
        @hasAccess('admin.beautician_job_titles.index')
            <a href="{{ route('admin.beautician_job_titles.index') }}" target="_blank" rel="noopener noreferrer">
                {{ trans('beautician::job_titles.job_titles') }}
            </a>
        @endHasAccess
    </p>
    {!! $errors->first('job_title', '<span class="help-block text-red">:message</span>') !!}
</div>
