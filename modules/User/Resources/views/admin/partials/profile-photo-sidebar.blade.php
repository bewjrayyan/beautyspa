@php
    $accountUser = $accountUser ?? ($user ?? $profileUser ?? $currentUser);
    $accountUser->loadMissing(['roles', 'files']);
    $avatarUrl = $accountUser->avatarUrl();
@endphp

<aside class="admin-profile-photo-sidebar">
    <div class="admin-profile-photo-sidebar__head">
        <h3>
            <i class="fa fa-camera" aria-hidden="true"></i>
            {{ trans('user::users.profile_page.photo_title') }}
        </h3>
        <p>{{ trans('user::users.profile_page.photo_lead') }}</p>
    </div>

    <div
        class="admin-profile-photo admin-profile-photo--sidebar"
        data-admin-profile-photo
        data-removed-hint="{{ trans('user::users.profile_page.photo_removed_hint') }}"
    >
        <div class="admin-profile-photo__preview">
            @if ($avatarUrl)
                <img
                    src="{{ $avatarUrl }}"
                    alt=""
                    class="admin-profile-photo__img"
                    data-admin-profile-photo-preview
                >
            @else
                @include('user::admin.partials.avatar', [
                    'user' => $accountUser,
                    'class' => 'admin-profile-photo__img admin-profile-photo__img--initial profile-first-letter',
                ])
            @endif
        </div>

        <div class="admin-profile-photo__actions">
            <label class="btn btn-default btn-sm btn-block">
                <i class="fa fa-upload" aria-hidden="true"></i>
                {{ trans('user::users.profile_page.upload_photo') }}
                <input
                    type="file"
                    name="avatar"
                    accept="image/jpeg,image/png,image/webp"
                    class="sr-only"
                    data-admin-profile-photo-input
                >
            </label>

            @if ($avatarUrl || $accountUser->profile_image->exists)
                <button
                    type="button"
                    class="btn btn-default btn-sm btn-block"
                    data-admin-profile-photo-remove
                >
                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                    {{ trans('user::users.profile_page.remove_photo') }}
                </button>
            @endif
        </div>

        <input type="hidden" name="remove_avatar" value="0" data-admin-profile-photo-remove-flag>

        @error('avatar')
            <p class="help-block text-red admin-profile-photo-sidebar__error">{{ $message }}</p>
        @enderror
    </div>
</aside>
