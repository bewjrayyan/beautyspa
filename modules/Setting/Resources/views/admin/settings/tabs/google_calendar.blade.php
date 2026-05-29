<div class="row">
    <div class="col-md-8">
        <div class="box-content clearfix">
            <h4 class="section-title">{{ trans('setting::settings.form.google_calendar_settings') }}</h4>

            <p class="help-block text-muted">{{ trans('setting::settings.form.google_calendar_intro') }}</p>

            {{ Form::checkbox('google_calendar_enabled', trans('setting::attributes.google_calendar_enabled'), trans('setting::settings.form.enable_google_calendar_sync'), $errors, $settings) }}

            <div class="{{ old('google_calendar_enabled', array_get($settings, 'google_calendar_enabled')) ? '' : 'hide' }}" id="google-calendar-fields">
                {{ Form::text('google_calendar_id', trans('setting::attributes.google_calendar_id'), $errors, $settings, [
                    'placeholder' => 'xxxx@group.calendar.google.com',
                ]) }}
                <p class="help-block text-muted">{{ trans('setting::settings.form.google_calendar_id_help') }}</p>

                <p class="help-block text-muted">{{ trans('setting::settings.form.google_calendar_share_help') }}</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.querySelector('[name="google_calendar_enabled"]');
            const panel = document.getElementById('google-calendar-fields');

            if (!toggle || !panel) {
                return;
            }

            toggle.addEventListener('change', () => {
                panel.classList.toggle('hide', !toggle.checked);
            });
        });
    </script>
@endpush
