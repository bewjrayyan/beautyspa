@php
    $lookupCode = old('code', request('code', ''));
    $lookup = $stampLookup ?? null;
@endphp

<div class="loyalty-member-card loyalty-stamp-lookup">
    <div class="loyalty-member-card__head">
        <h3>
            <i class="fa fa-ticket" aria-hidden="true"></i>
            {{ trans('loyalty::members.stamps.lookup_title') }}
        </h3>
        <p>{{ trans('loyalty::members.stamps.lookup_lead') }}</p>
    </div>
    <div class="loyalty-member-card__body">
        <form method="POST" action="{{ route('admin.loyalty.stamp_redemptions.lookup') }}" class="loyalty-stamp-lookup__form">
            @csrf
            <div class="input-group">
                <input
                    type="text"
                    name="code"
                    class="form-control loyalty-stamp-lookup__input"
                    value="{{ $lookupCode }}"
                    placeholder="{{ trans('loyalty::members.stamps.lookup_placeholder') }}"
                    autocomplete="off"
                    spellcheck="false"
                >
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-search" aria-hidden="true"></i>
                        {{ trans('loyalty::members.stamps.lookup_submit') }}
                    </button>
                </span>
            </div>
        </form>

        @if ($lookup)
            @php
                $wallet = $lookup['wallet'] ?? null;
                $status = $lookup['status'] ?? 'not_found';
            @endphp

            <div @class([
                'loyalty-stamp-lookup__result',
                'loyalty-stamp-lookup__result--' . $status,
            ])>
                @if ($status === 'not_found')
                    <p class="loyalty-stamp-lookup__result-title">
                        <i class="fa fa-times-circle" aria-hidden="true"></i>
                        {{ trans('loyalty::members.stamps.lookup_not_found') }}
                    </p>
                    @if (! empty($lookup['code']))
                        <p class="loyalty-stamp-lookup__result-meta">
                            <code>{{ $lookup['code'] }}</code>
                        </p>
                    @endif
                @else
                    <p class="loyalty-stamp-lookup__result-title">
                        <i class="fa fa-{{ $status === 'valid' ? 'check-circle' : ($status === 'fulfilled' ? 'flag-checkered' : 'info-circle') }}" aria-hidden="true"></i>
                        {{ trans('loyalty::members.stamps.lookup_status_' . $status) }}
                    </p>

                    @if ($wallet)
                        <dl class="loyalty-stamp-lookup__details">
                            <div>
                                <dt>{{ trans('loyalty::members.stamps.lookup_code') }}</dt>
                                <dd><code>{{ $wallet->redemption_code ?: '—' }}</code></dd>
                            </div>
                            <div>
                                <dt>{{ trans('loyalty::members.table.customer') }}</dt>
                                <dd>{{ $wallet->user?->full_name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt>{{ trans('loyalty::members.stamps.program') }}</dt>
                                <dd>{{ $wallet->program?->name ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt>{{ trans('loyalty::members.stamps.reward') }}</dt>
                                <dd>{{ $wallet->program?->reward_description ?: $wallet->program?->name ?: '—' }}</dd>
                            </div>
                            @if ($wallet->redeemed_at)
                                <div>
                                    <dt>{{ trans('loyalty::members.stamps.redeemed_at') }}</dt>
                                    <dd>{{ $wallet->redeemed_at->timezone(config('app.timezone'))->format('d M Y, H:i') }}</dd>
                                </div>
                            @endif
                            @if ($wallet->fulfilled_at)
                                <div>
                                    <dt>{{ trans('loyalty::members.stamps.fulfilled_at') }}</dt>
                                    <dd>{{ $wallet->fulfilled_at->timezone(config('app.timezone'))->format('d M Y, H:i') }}</dd>
                                </div>
                            @endif
                        </dl>

                        <div class="loyalty-stamp-lookup__actions">
                            @if (! empty($lookup['loyalty_wallet']))
                                <a
                                    href="{{ route('admin.loyalty.members.show', $lookup['loyalty_wallet']) }}"
                                    class="btn btn-default btn-sm"
                                >
                                    <i class="fa fa-user" aria-hidden="true"></i>
                                    {{ trans('loyalty::members.stamps.view_member') }}
                                </a>
                            @endif

                            @if ($status === 'valid' && auth()->user()?->hasAccess('admin.loyalty.members.show'))
                                <form
                                    method="POST"
                                    action="{{ route('admin.loyalty.stamp_redemptions.fulfill', $wallet) }}"
                                    class="loyalty-stamp-lookup__fulfill-form"
                                    onsubmit="return confirm(@js(trans('loyalty::members.stamps.fulfill_confirm')));"
                                >
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fa fa-check" aria-hidden="true"></i>
                                        {{ trans('loyalty::members.stamps.fulfill_button') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        @endif
    </div>
</div>
