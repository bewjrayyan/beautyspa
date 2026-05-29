@extends('storefront::public.account.layout')

@section('title', trans('storefront::account.pages.my_profile'))

@section('account_breadcrumb')
    <li class="active">{{ trans('storefront::account.pages.my_profile') }}</li>
@endsection

@section('panel')
    <div
        class="account-profile-show"
        x-data="{
            preview: @js($account->profile_image->exists ? $account->profile_image->path : null),
            hasPhoto: @js($account->profile_image->exists),
            onFileChange(event) {
                const file = event.target.files[0];
                if (! file) return;
                this.preview = URL.createObjectURL(file);
                this.hasPhoto = true;
                this.$refs.removeAvatar.value = '0';
            },
            removePhoto() {
                this.preview = null;
                this.hasPhoto = false;
                this.$refs.removeAvatar.value = '1';
                if (this.$refs.avatarInput) this.$refs.avatarInput.value = '';
            }
        }"
    >
        <form
            method="POST"
            action="{{ route('account.profile.update') }}"
            enctype="multipart/form-data"
            class="account-profile-form"
        >
            @csrf
            @method('put')
            <input type="hidden" name="remove_avatar" value="0" x-ref="removeAvatar">

            <header class="account-profile-show__hero">
                <div class="account-profile-show__hero-main">
                    <div class="account-profile-show__avatar-block">
                    <div class="account-profile-show__avatar-wrap">
                        <template x-if="preview">
                            <img :src="preview" alt="" class="account-profile-show__avatar account-profile-show__avatar--photo">
                        </template>
                        <template x-if="! preview">
                            <span class="account-profile-show__avatar account-profile-show__avatar--initial">
                                {{ $account->initials }}
                            </span>
                        </template>

                        <label
                            class="account-profile-show__avatar-edit"
                            title="{{ trans('storefront::account.profile.upload_photo') }}"
                        >
                            <span class="sr-only">{{ trans('storefront::account.profile.upload_photo') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M4 8h2l1.5-2h7L16 8h4a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <circle cx="12" cy="13" r="3.2" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                            <input
                                type="file"
                                name="avatar"
                                accept="image/jpeg,image/png,image/webp"
                                class="account-profile-show__file-input"
                                x-ref="avatarInput"
                                @change="onFileChange($event)"
                            >
                        </label>

                        <button
                            type="button"
                            class="account-profile-show__avatar-remove"
                            title="{{ trans('storefront::account.profile.remove_photo') }}"
                            x-show="hasPhoto"
                            x-cloak
                            @click="removePhoto()"
                        >
                            <span class="sr-only">{{ trans('storefront::account.profile.remove_photo') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>

                    @error('avatar')
                        <p class="account-profile-show__avatar-error">{{ $message }}</p>
                    @enderror
                    </div>

                    <div class="account-profile-show__identity">
                    <h1 class="account-profile-show__title">{{ $account->full_name }}</h1>
                    <p class="account-profile-show__meta">
                        <i class="las la-envelope"></i>
                        {{ $account->email }}
                    </p>
                    @if ($account->phone)
                        <p class="account-profile-show__meta">
                            <i class="las la-phone"></i>
                            {{ $account->phone }}
                        </p>
                    @endif
                </div>
            </div>

            <ul class="account-profile-show__stats">
                <li>
                    <span class="account-profile-show__stat-label">{{ trans('storefront::account.profile.member_since') }}</span>
                    <span class="account-profile-show__stat-value">{{ $account->created_at?->format('d M Y') ?? '—' }}</span>
                </li>
                <li>
                    <span class="account-profile-show__stat-label">{{ trans('storefront::account.profile.last_login') }}</span>
                    <span class="account-profile-show__stat-value">
                        @if ($account->last_login)
                            {{ $account->last_login->format('d M Y · h:i A') }}
                        @else
                            —
                        @endif
                    </span>
                </li>
            </ul>
            </header>

            <div class="account-profile-show__layout">
                <main class="account-profile-show__main">
                    <section class="account-profile-show__section">
                        <h2 class="account-profile-show__section-title">
                            <i class="las la-user"></i>
                            {{ trans('storefront::account.profile.personal_info') }}
                        </h2>

                        <div class="account-profile-show__fields">
                            <div class="form-group">
                                <label for="first-name">
                                    {{ trans('storefront::account.profile.first_name') }}<span>*</span>
                                </label>
                                <input
                                    type="text"
                                    name="first_name"
                                    value="{{ old('first_name', $account->first_name) }}"
                                    id="first-name"
                                    class="form-control"
                                >
                                @error('first_name')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="last-name">
                                    {{ trans('storefront::account.profile.last_name') }}<span>*</span>
                                </label>
                                <input
                                    type="text"
                                    name="last_name"
                                    value="{{ old('last_name', $account->last_name) }}"
                                    id="last-name"
                                    class="form-control"
                                >
                                @error('last_name')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="email">
                                    {{ trans('storefront::account.profile.email') }}<span>*</span>
                                </label>
                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('email', $account->email) }}"
                                    id="email"
                                    class="form-control"
                                >
                                @error('email')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="phone">
                                    {{ trans('storefront::account.profile.phone') }}<span>*</span>
                                </label>
                                <div class="account-profile-show__phone-wrap">
                                    @include('storefront::public.partials.phone_input', [
                                        'name' => 'phone',
                                        'id' => 'phone',
                                        'value' => $account->phone,
                                        'required' => true,
                                    ])
                                </div>
                                @error('phone')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            @if (app('modules')->isEnabled('Loyalty'))
                                <div class="form-group">
                                    <label for="date-of-birth">
                                        {{ trans('loyalty::account.date_of_birth') }}
                                    </label>
                                    <div class="account-profile-show__date-wrap">
                                        <i class="las la-calendar" aria-hidden="true"></i>
                                        <input
                                            type="text"
                                            name="date_of_birth"
                                            id="date-of-birth"
                                            class="form-control modern-datepicker"
                                            value="{{ old('date_of_birth', $account->date_of_birth?->format('Y-m-d')) }}"
                                            data-max-date="{{ now()->subDay()->format('Y-m-d') }}"
                                            placeholder="{{ trans('loyalty::account.date_of_birth') }}"
                                            autocomplete="bday"
                                        >
                                    </div>
                                    <p class="help-block">{{ trans('loyalty::account.date_of_birth_help') }}</p>
                                    @error('date_of_birth')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif
                        </div>
                    </section>

                    <section class="account-profile-show__section">
                        <h2 class="account-profile-show__section-title">
                            <i class="las la-lock"></i>
                            {{ trans('storefront::account.profile.security') }}
                        </h2>

                        <div class="account-profile-show__fields">
                            <div class="form-group">
                                <label for="password">{{ trans('storefront::account.profile.new_password') }}</label>
                                <input type="password" name="password" id="password" class="form-control" autocomplete="new-password">
                                @error('password')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="confirm-password">{{ trans('storefront::account.profile.confirm_password') }}</label>
                                <input type="password" name="password_confirmation" id="confirm-password" class="form-control" autocomplete="new-password">
                                @error('password_confirmation')
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <div class="account-profile-show__actions">
                        <button type="submit" class="btn btn-lg btn-primary account-profile-show__submit" data-loading>
                            {{ trans('storefront::account.profile.save_changes') }}
                        </button>
                    </div>
                </main>

                <aside class="account-profile-show__sidebar">
                <div class="account-profile-sidebar__card">
                    <h2 class="account-profile-sidebar__title">
                        <i class="las la-info-circle"></i>
                        {{ trans('storefront::account.profile.account_details') }}
                    </h2>
                    <ul class="account-profile-sidebar__list">
                        <li>
                            <span class="account-profile-sidebar__label">{{ trans('storefront::account.profile.member_since') }}</span>
                            <span class="account-profile-sidebar__value">{{ $account->created_at?->format('d M Y') ?? '—' }}</span>
                        </li>
                        <li>
                            <span class="account-profile-sidebar__label">{{ trans('storefront::account.profile.last_login') }}</span>
                            <span class="account-profile-sidebar__value">
                                @if ($account->last_login)
                                    {{ $account->last_login->format('d M Y · h:i A') }}
                                @else
                                    —
                                @endif
                            </span>
                        </li>
                        <li>
                            <span class="account-profile-sidebar__label">{{ trans('storefront::account.profile.email') }}</span>
                            <span class="account-profile-sidebar__value">{{ $account->email }}</span>
                        </li>
                    </ul>
                </div>
                </aside>
            </div>
        </form>
    </div>
@endsection

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/account/profile/main.scss',
    ])
@endpush
