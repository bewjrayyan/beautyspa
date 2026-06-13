@php
    use Modules\Beautician\Support\TitleCase;

    $beauticianJobTitle->name = TitleCase::format(old('name', $beauticianJobTitle->name ?? ''));
@endphp

<div class="row">
    <div class="col-md-8">
        {{ Form::text('name', trans('beautician::attributes.job_title_name'), $errors, $beauticianJobTitle, [
            'required' => true,
            'class' => 'bp-title-case-input',
        ]) }}
        {{ Form::number('position', trans('beautician::attributes.job_title_position'), $errors, $beauticianJobTitle) }}
        {{ Form::checkbox('is_active', trans('beautician::attributes.job_title_is_active'), trans('beautician::job_titles.form.enable_job_title'), $errors, $beauticianJobTitle) }}
        <p class="help-block">{{ trans('beautician::job_titles.form.help') }}</p>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toTitleCase = (value) => value
                .trim()
                .toLowerCase()
                .replace(/[^\s\/]+/g, (word) => word.charAt(0).toUpperCase() + word.slice(1));

            const applyTitleCase = (input) => {
                if (!input) {
                    return;
                }

                const formatted = toTitleCase(input.value);

                if (formatted !== input.value) {
                    input.value = formatted;
                }
            };

            document.querySelectorAll('.bp-title-case-input').forEach((input) => {
                applyTitleCase(input);

                input.addEventListener('blur', () => applyTitleCase(input));
            });
        });
    </script>
@endpush
