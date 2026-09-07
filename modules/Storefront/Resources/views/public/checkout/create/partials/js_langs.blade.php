@php
    $checkoutJsLangKeys = [
        'storefront::checkout.payment_for_order',
        'storefront::checkout.remember_about_your_order',
        'storefront::checkout.select_spa_branch_first',
        'storefront::checkout.select_spa_branch_before_schedule',
        'storefront::checkout.select_spa_branch_before_date',
        'storefront::checkout.select_beautician',
        'storefront::checkout.select_beautician_before_date',
        'storefront::checkout.select_beautician_before_schedule',
        'storefront::checkout.select_date_first',
        'storefront::checkout.select_schedule_mode',
        'storefront::checkout.schedule_later_tba',
        'storefront::checkout.summary_schedule_pending',
        'storefront::checkout.summary_date_time_pending',
        'storefront::checkout.summary_beautician_pending',
        'storefront::checkout.summary_incomplete',
        'storefront::checkout.loading_appointment_schedule',
        'storefront::checkout.complete_treatment_schedule',
        'storefront::checkout.appointment_time_conflicts_sibling',
        'storefront::checkout.appointment_time_not_in_schedule',
        'storefront::checkout.please_login_to_continue',
        'storefront::checkout.payment_method_required',
        'storefront::checkout.payment_proof_required',
        'storefront::checkout.payment_proof_invalid_type',
        'storefront::checkout.payment_proof_too_large',
        'storefront::checkout.temporarily_unavailable',
        'storefront::checkout.shipping_method_free_desc',
        'storefront::checkout.shipping_method_pickup_desc',
        'storefront::checkout.shipping_method_flat_desc',
        'storefront::checkout.shipping_method_default_desc',
        'storefront::checkout.logged_in_successfully',
        'treatmentreservation::public.slot_not_in_schedule',
        'treatmentreservation::public.slot_unavailable',
        'treatmentreservation::public.slot_beautician_unavailable',
        'loyalty::checkout.applied',
        'loyalty::checkout.load_max_failed',
        'loyalty::checkout.apply_failed',
    ];
@endphp

@foreach ($checkoutJsLangKeys as $langKey)
    AestheticCart.langs[@json($langKey)] = @json(trans($langKey));
@endforeach
