@php
    $previewEarned = $previewEarned ?? 3;
@endphp

<div class="loyalty-stamp-preview" id="stamp-program-preview">
    <article class="loyalty-stamp-preview__card" data-stamp-preview-card>
        <div class="loyalty-stamp-preview__header">
            <p class="loyalty-stamp-preview__title" data-preview="name">{{ $program->name ?: trans('loyalty::stamp_programs.form.new_program_title') }}</p>
            <p class="loyalty-stamp-preview__reward" data-preview="reward">{{ $program->reward_description ?: trans('loyalty::stamp_programs.form.reward_description') }}</p>
        </div>

        <div class="loyalty-stamp-preview__stamps" data-preview="stamps" aria-hidden="true"></div>

        <div class="loyalty-stamp-preview__footer">
            <span class="loyalty-stamp-preview__progress" data-preview="progress"></span>
            <span class="loyalty-stamp-preview__expiry" data-preview="expiry">
                <i class="fa fa-clock-o" aria-hidden="true"></i>
                <span data-preview="expiry-text"></span>
            </span>
        </div>
    </article>

    <p class="loyalty-stamp-preview__note">{{ trans('loyalty::stamp_programs.form.preview_note') }}</p>
</div>
