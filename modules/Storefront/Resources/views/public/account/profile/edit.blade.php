@extends('storefront::public.account.layout')

@section('account_mobile_hero', true)

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
                    <input
                        type="file"
                        name="avatar"
                        accept="image/jpeg,image/png,image/webp"
                        class="account-profile-show__file-input"
                        x-ref="avatarInput"
                        @change="onFileChange($event)"
                    >

                    <div class="account-profile-show__hero-head">
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
                                    class="account-profile-show__avatar-edit d-none d-lg-flex"
                                    title="{{ trans('storefront::account.profile.upload_photo') }}"
                                    @click.prevent="$refs.avatarInput.click()"
                                >
                                    <span class="sr-only">{{ trans('storefront::account.profile.upload_photo') }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4 8h2l1.5-2h7L16 8h4a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                        <circle cx="12" cy="13" r="3.2" stroke="currentColor" stroke-width="1.8"/>
                                    </svg>
                                </label>

                                <button
                                    type="button"
                                    class="account-profile-show__avatar-remove d-none d-lg-flex"
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

                        <div class="account-profile-show__hero-summary">
                            <h1 class="account-profile-show__title">{{ $account->full_name }}</h1>

                            <p class="account-profile-show__email d-lg-none">{{ $account->email }}</p>

                            <div class="account-profile-show__meta-row d-none d-lg-flex">
                                <span>
                                    <i class="las la-envelope"></i>
                                    {{ $account->email }}
                                </span>
                                @if ($account->phone)
                                    <span>
                                        <i class="las la-phone"></i>
                                        {{ $account->phone }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="account-profile-show__photo-actions d-lg-none">
                        <button
                            type="button"
                            class="account-profile-show__photo-btn"
                            @click="$refs.avatarInput.click()"
                        >
                            <i class="las la-camera" aria-hidden="true"></i>
                            {{ trans('storefront::account.profile.upload_photo') }}
                        </button>

                        <button
                            type="button"
                            class="account-profile-show__photo-btn account-profile-show__photo-btn--danger"
                            x-show="hasPhoto"
                            x-cloak
                            @click="removePhoto()"
                        >
                            <i class="las la-trash-alt" aria-hidden="true"></i>
                            {{ trans('storefront::account.profile.remove_photo') }}
                        </button>
                    </div>

                    <ul class="account-profile-show__stats">
                            <li>
                                <span class="account-profile-show__stat-label">
                                    <i class="las la-calendar-check" aria-hidden="true"></i>
                                    {{ trans('storefront::account.profile.member_since') }}
                                </span>
                                <span class="account-profile-show__stat-value">{{ $account->created_at?->format('d M Y') ?? '—' }}</span>
                            </li>
                            <li>
                                <span class="account-profile-show__stat-label">
                                    <i class="las la-history" aria-hidden="true"></i>
                                    {{ trans('storefront::account.profile.last_login') }}
                                </span>
                                <span class="account-profile-show__stat-value">
                                    @if ($account->last_login)
                                        {{ $account->last_login->format('d M Y · h:i A') }}
                                    @else
                                        —
                                    @endif
                                </span>
                            </li>
                            @if (app('modules')->isEnabled('Loyalty') && $loyaltyWallet)
                                <li class="account-profile-show__stat--loyalty">
                                    <a href="{{ route('account.loyalty.index') }}" class="account-profile-show__stat-link">
                                        <span class="account-profile-show__stat-label">
                                            <i class="las la-coins" aria-hidden="true"></i>
                                            {{ trans('loyalty::account.points_balance') }}
                                        </span>
                                        <span class="account-profile-show__stat-value">
                                            {{ number_format($loyaltyWallet->balance) }}
                                            <span class="account-profile-show__stat-sub">· RM {{ number_format($loyaltyBalanceRm, 2) }}</span>
                                        </span>
                                        <i class="las la-angle-right account-profile-show__stat-chevron" aria-hidden="true"></i>
                                    </a>
                                </li>
                            @endif
                        </ul>
                </div>
            </header>

            <div class="account-profile-show__layout">
                <main class="account-profile-show__main">
                    <section class="account-profile-show__section">
                        <h2 class="account-profile-show__section-title d-none d-lg-flex">
                            <i class="las la-user"></i>
                            {{ trans('storefront::account.profile.personal_info') }}
                        </h2>
                        <p class="account-profile-show__section-label">{{ trans('storefront::account.profile.personal_info') }}</p>

                        <div class="account-profile-show__fields">
                            <div class="account-profile-show__field-row account-profile-show__field-row--split">
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

                            <div class="form-group form-group--phone">
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
                                    <p class="help-block account-profile-show__field-hint">{{ trans('loyalty::account.date_of_birth_help') }}</p>
                                    @error('date_of_birth')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif
                        </div>
                    </section>

                    @include('loyalty::public.account.partials.profile-stamps', [
                        'stampCards' => $stampCards ?? [],
                    ])

                    <section class="account-profile-show__section">
                        <h2 class="account-profile-show__section-title d-none d-lg-flex">
                            <i class="las la-lock"></i>
                            {{ trans('storefront::account.profile.security') }}
                        </h2>
                        <p class="account-profile-show__section-label">{{ trans('storefront::account.profile.security') }}</p>

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

                    <div class="account-profile-show__actions d-none d-lg-block">
                        <button type="submit" class="btn btn-lg btn-primary account-profile-show__submit" data-loading>
                            {{ trans('storefront::account.profile.save_changes') }}
                        </button>
                    </div>
                </main>

                <aside class="account-profile-show__sidebar d-none d-lg-flex">
                @if (app('modules')->isEnabled('Loyalty') && $loyaltyWallet)
                    @include('loyalty::public.account.partials.profile-sidebar', [
                        'account' => $account,
                        'loyaltyWallet' => $loyaltyWallet,
                        'loyaltyBalanceRm' => $loyaltyBalanceRm,
                        'loyaltyEarnRate' => $loyaltyEarnRate,
                    ])
                @endif
                </aside>
            </div>

            <div class="account-profile-show__mobile-save d-lg-none">
                <button type="submit" class="btn btn-primary account-profile-show__mobile-save-btn" data-loading>
                    {{ trans('storefront::account.profile.save_changes') }}
                </button>
            </div>
        </form>
    </div>
@endsection

@push('globals')
    @vite([
        'modules/Storefront/Resources/assets/public/sass/pages/account/profile/main.scss',
    ])
@endpush
