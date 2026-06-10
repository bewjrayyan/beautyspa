@php
    $accountUser = $accountUser ?? ($user ?? $profileUser ?? $currentUser);
    $accountUser->loadMissing(['roles', 'files']);
    $avatarUrl = $accountUser->avatarUrl();
    $dobValue = old('date_of_birth', $accountUser->date_of_birth?->format('Y-m-d'));
    $addressPrefix = $addressPrefix ?? 'profile';
    $showAdminFields = $showAdminFields ?? false;
    $addressModel = ($profileAddress ?? null) ?? new \Modules\Address\Entities\Address([
        'country' => setting('default_country', 'MY'),
    ]);
    $addressStateValue = old('state', $addressModel->state);
@endphp

<div class="admin-profile-account">
    <div class="admin-profile-card">
        <div class="admin-profile-card__head">
            <h2 class="admin-profile-card__title">
                <i class="fa fa-camera" aria-hidden="true"></i>
                {{ trans('user::users.profile_page.photo_title') }}
            </h2>
            <p class="admin-profile-card__lead">{{ trans('user::users.profile_page.photo_lead') }}</p>
        </div>

        <div
            class="admin-profile-photo"
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
                <label class="btn btn-default btn-sm">
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
                        class="btn btn-default btn-sm"
                        data-admin-profile-photo-remove
                    >
                        <i class="fa fa-trash-o" aria-hidden="true"></i>
                        {{ trans('user::users.profile_page.remove_photo') }}
                    </button>
                @endif
            </div>

            <input type="hidden" name="remove_avatar" value="0" data-admin-profile-photo-remove-flag>

            @error('avatar')
                <p class="help-block text-red">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="admin-profile-card">
        <div class="admin-profile-card__head">
            <h2 class="admin-profile-card__title">
                <i class="fa fa-user" aria-hidden="true"></i>
                {{ trans('user::users.profile_page.personal_title') }}
            </h2>
            <p class="admin-profile-card__lead">{{ trans('user::users.profile_page.personal_lead') }}</p>
        </div>

        <div class="admin-profile-card__grid admin-profile-form">
            <div class="admin-profile-card__field">
                {{ Form::text('first_name', trans('user::attributes.users.first_name'), $errors, $accountUser, ['required' => true]) }}
            </div>
            <div class="admin-profile-card__field">
                {{ Form::text('last_name', trans('user::attributes.users.last_name'), $errors, $accountUser, ['required' => true]) }}
            </div>
            <div class="admin-profile-card__field">
                {{ Form::text('identity_number', trans('user::attributes.users.identity_number'), $errors, $accountUser, [
                    'placeholder' => trans('user::users.profile_page.identity_placeholder'),
                ]) }}
                <p class="help-block">{{ trans('user::users.profile_page.identity_help') }}</p>
            </div>
            <div class="admin-profile-card__field">
                <div class="form-group">
                    <label for="{{ $addressPrefix }}_date_of_birth" class="control-label text-left">
                        {{ trans('user::attributes.users.date_of_birth') }}
                    </label>
                    <div class="admin-profile-datepicker-wrap">
                        <i class="fa fa-calendar" aria-hidden="true"></i>
                        <input
                            type="text"
                            name="date_of_birth"
                            id="{{ $addressPrefix }}_date_of_birth"
                            class="form-control profile-date-picker"
                            value="{{ $dobValue }}"
                            data-default-date="{{ $dobValue }}"
                            data-max-date="{{ now()->subDay()->format('Y-m-d') }}"
                            placeholder="{{ trans('user::attributes.users.date_of_birth') }}"
                            autocomplete="bday"
                        >
                    </div>
                    @if (app('modules')->isEnabled('Loyalty'))
                        <p class="help-block">{{ trans('loyalty::account.date_of_birth_help') }}</p>
                    @endif
                    @error('date_of_birth')
                        <span class="help-block text-red">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="admin-profile-card">
        <div class="admin-profile-card__head">
            <h2 class="admin-profile-card__title">
                <i class="fa fa-envelope" aria-hidden="true"></i>
                {{ trans('user::users.profile_page.contact_title') }}
            </h2>
            <p class="admin-profile-card__lead">{{ trans('user::users.profile_page.contact_lead') }}</p>
        </div>

        <div class="admin-profile-card__grid admin-profile-form">
            <div class="admin-profile-card__field">
                {{ Form::email('email', trans('user::attributes.users.email'), $errors, $accountUser, ['required' => true]) }}
            </div>
            <div class="admin-profile-card__field">
                {{ Form::phone('phone', trans('user::attributes.users.phone'), $errors, $accountUser, ['required' => true]) }}
            </div>
        </div>
    </div>

    @if ($showAdminFields)
        <div class="admin-profile-card">
            <div class="admin-profile-card__head">
                <h2 class="admin-profile-card__title">
                    <i class="fa fa-shield" aria-hidden="true"></i>
                    {{ trans('user::users.profile_page.admin_access_title') }}
                </h2>
                <p class="admin-profile-card__lead">{{ trans('user::users.profile_page.admin_access_lead') }}</p>
            </div>

            <div class="admin-profile-card__grid admin-profile-card__grid--access admin-profile-form">
                <div class="admin-profile-card__field admin-profile-card__field--roles">
                    {{ Form::select('roles', trans('user::attributes.users.roles'), $errors, $roles ?? [], $accountUser, ['multiple' => true, 'required' => true, 'class' => 'selectize prevent-creation']) }}
                </div>
                <div class="admin-profile-card__field admin-profile-card__field--status">
                    {{ Form::checkbox('activated', trans('user::attributes.users.activated'), trans('user::users.form.activated'), $errors, $accountUser, [
                        'disabled' => $accountUser->id === $currentUser->id,
                        'checked' => old('activated', $accountUser->isActivated()),
                    ]) }}
                </div>
            </div>
        </div>
    @endif

    <div class="admin-profile-card admin-profile-address" data-admin-profile-address>
        <div class="admin-profile-card__head">
            <h2 class="admin-profile-card__title">
                <i class="fa fa-map-marker" aria-hidden="true"></i>
                {{ trans('user::users.profile_page.address_title') }}
            </h2>
            <p class="admin-profile-card__lead">{{ trans('user::users.profile_page.address_lead') }}</p>
        </div>

        <div class="admin-profile-card__grid admin-profile-form">
            <div class="admin-profile-card__field admin-profile-card__field--full">
                {{ Form::text('address_1', trans('user::attributes.users.address_1'), $errors, $addressModel) }}
            </div>
            <div class="admin-profile-card__field admin-profile-card__field--full">
                {{ Form::text('address_2', trans('user::attributes.users.address_2'), $errors, $addressModel) }}
            </div>
            <div class="admin-profile-card__field">
                {{ Form::text('city', trans('user::attributes.users.city'), $errors, $addressModel) }}
            </div>
            <div class="admin-profile-card__field">
                {{ Form::text('zip', trans('user::attributes.users.zip'), $errors, $addressModel) }}
            </div>
            <div class="admin-profile-card__field">
                {{ Form::select('country', trans('user::attributes.users.country'), $errors, $countries ?? [], $addressModel, [
                    'id' => $addressPrefix . '_address_country',
                    'class' => 'custom-select-black',
                    'data-profile-address-country' => true,
                ]) }}
            </div>
            <div class="admin-profile-card__field">
                <div class="admin-profile-address-state admin-profile-address-state--input">
                    {{ Form::text('state', trans('user::attributes.users.state'), $errors, $addressModel, [
                        'id' => $addressPrefix . '_address_state_text',
                        'value' => $addressStateValue,
                        'data-profile-address-state-text' => true,
                    ]) }}
                </div>
                <div class="admin-profile-address-state admin-profile-address-state--select hide">
                    <div class="form-group">
                        <label for="{{ $addressPrefix }}_address_state_select" class="control-label text-left">
                            {{ trans('user::attributes.users.state') }}
                        </label>
                        <select
                            id="{{ $addressPrefix }}_address_state_select"
                            class="form-control custom-select-black"
                            disabled
                            data-profile-address-state-select
                        ></select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
