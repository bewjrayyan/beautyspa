@if (app('modules')->isEnabled('Loyalty') && ! empty($stampCards))
    <section class="account-profile-show__section account-profile-stamps" id="stamp-cards">
        <h2 class="account-profile-show__section-title d-none d-lg-flex">
            <i class="las la-ticket-alt"></i>
            {{ trans('loyalty::order_rewards.title') }} 🎉
        </h2>
        <p class="account-profile-show__section-label">{{ trans('loyalty::order_rewards.stamp_cards') }}</p>
        <p class="account-profile-stamps__lead">{{ trans('loyalty::account.stamp_cards_lead') }}</p>

        <div class="account-profile-stamps__cards">
            @include('loyalty::public.partials.stamp-cards', [
                'stampCards' => $stampCards,
                'showRedeemActions' => true,
            ])
        </div>
    </section>
@endif
