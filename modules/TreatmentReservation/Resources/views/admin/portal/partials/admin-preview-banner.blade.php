@php
    $previewActive = admin_portal_preview()?->isActive();
    $profileColor = $beautician->profile_color ?? '#6366f1';
    $editProfileUrl = route('admin.beauticians.edit', $beautician);
@endphp

<div class="tr-portal-admin-preview" role="status" aria-live="polite" style="--tr-preview-accent: {{ $profileColor }};">
    <div class="tr-portal-admin-preview__glow" aria-hidden="true"></div>

    <div class="tr-portal-admin-preview__icon" aria-hidden="true">
        <span class="tr-portal-admin-preview__pulse"></span>
        <i class="fa fa-eye"></i>
    </div>

    <div class="tr-portal-admin-preview__avatar" aria-hidden="true">
        @if ($beautician->displayAvatarUrl())
            <img src="{{ $beautician->displayAvatarUrl() }}" alt="">
        @else
            <span>{{ $beautician->initials }}</span>
        @endif
    </div>

    <div class="tr-portal-admin-preview__body">
        <span class="tr-portal-admin-preview__badge">
            <i class="fa fa-shield" aria-hidden="true"></i>
            {{ trans('beautician::beauticians.form.admin_portal_preview_badge') }}
        </span>

        @if ($previewActive)
            <h4 class="tr-portal-admin-preview__title">
                {{ trans('beautician::beauticians.form.admin_portal_preview_title', ['name' => $beautician->name]) }}
            </h4>
            <p class="tr-portal-admin-preview__lead">
                {{ trans('beautician::beauticians.form.admin_portal_preview_lead') }}
            </p>
        @else
            <h4 class="tr-portal-admin-preview__title">
                {{ trans('beautician::beauticians.form.admin_portal_preview_no_user_title') }}
            </h4>
            <p class="tr-portal-admin-preview__lead">
                {{ trans('beautician::beauticians.form.admin_portal_preview_no_user_lead') }}
            </p>
        @endif
    </div>

    <div class="tr-portal-admin-preview__actions">
        @unless ($previewActive)
            <a href="{{ $editProfileUrl }}" class="btn btn-primary btn-sm tr-portal-admin-preview__btn">
                <i class="fa fa-link" aria-hidden="true"></i>
                {{ trans('beautician::beauticians.form.admin_portal_preview_link_user') }}
            </a>
        @endunless

        <a href="{{ $editProfileUrl }}" class="btn btn-default btn-sm tr-portal-admin-preview__btn">
            <i class="fa fa-pencil" aria-hidden="true"></i>
            {{ trans('beautician::beauticians.form.admin_portal_preview_edit_profile') }}
        </a>

        <a href="{{ $editProfileUrl }}" class="btn btn-default btn-sm tr-portal-admin-preview__btn tr-portal-admin-preview__btn--exit">
            <i class="fa fa-sign-out" aria-hidden="true"></i>
            {{ trans('beautician::beauticians.form.admin_portal_preview_exit') }}
        </a>
    </div>
</div>
