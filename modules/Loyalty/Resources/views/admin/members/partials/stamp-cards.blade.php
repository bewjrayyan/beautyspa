@php
    $activeCards = $stampData['active_cards'] ?? collect();
    $readyToRedeem = $stampData['ready_to_redeem'] ?? collect();
    $redemptions = $stampData['redemptions'] ?? collect();
    $hasStamps = $activeCards->isNotEmpty() || $readyToRedeem->isNotEmpty() || $redemptions->isNotEmpty();
@endphp

@if ($hasStamps)
    <div class="loyalty-member-card loyalty-member-stamps">
        <div class="loyalty-member-card__head">
            <h3>
                <i class="fa fa-ticket" aria-hidden="true"></i>
                {{ trans('loyalty::members.stamps.title') }}
            </h3>
            <p>{{ trans('loyalty::members.stamps.lead') }}</p>
        </div>
        <div class="loyalty-member-card__body">
            @if ($activeCards->isNotEmpty())
                <h4 class="loyalty-member-stamps__group-title">{{ trans('loyalty::members.stamps.active_cards') }}</h4>
                <div class="loyalty-member-stamps__list">
                    @foreach ($activeCards as $card)
                        <article class="loyalty-member-stamps__item">
                            <div class="loyalty-member-stamps__item-head">
                                <strong>{{ $card->program?->name }}</strong>
                                <span class="label label-info">
                                    {{ trans('loyalty::members.stamps.progress', [
                                        'earned' => min($card->stamps_count, $card->program?->stamps_required ?? $card->stamps_count),
                                        'required' => $card->program?->stamps_required ?? '—',
                                    ]) }}
                                </span>
                            </div>
                            @if ($card->program?->reward_description)
                                <p class="loyalty-member-stamps__reward">{{ $card->program->reward_description }}</p>
                            @endif
                            @if ($card->expires_at)
                                <p class="loyalty-member-stamps__meta">
                                    {{ trans('loyalty::members.stamps.expires') }}:
                                    {{ $card->expires_at->timezone(config('app.timezone'))->format('d M Y') }}
                                </p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif

            @if ($readyToRedeem->isNotEmpty())
                <h4 class="loyalty-member-stamps__group-title">{{ trans('loyalty::members.stamps.ready_to_redeem') }}</h4>
                <div class="loyalty-member-stamps__list">
                    @foreach ($readyToRedeem as $card)
                        <article class="loyalty-member-stamps__item loyalty-member-stamps__item--ready">
                            <div class="loyalty-member-stamps__item-head">
                                <strong>{{ $card->program?->name }}</strong>
                                <span class="label label-success">{{ trans('loyalty::members.stamps.complete') }}</span>
                            </div>
                            @if ($card->program?->reward_description)
                                <p class="loyalty-member-stamps__reward">{{ $card->program->reward_description }}</p>
                            @endif
                            <p class="loyalty-member-stamps__meta">{{ trans('loyalty::members.stamps.awaiting_customer_redeem') }}</p>
                        </article>
                    @endforeach
                </div>
            @endif

            @if ($redemptions->isNotEmpty())
                <h4 class="loyalty-member-stamps__group-title">{{ trans('loyalty::members.stamps.redemptions') }}</h4>
                <div class="table-responsive">
                    <table class="table table-hover loyalty-member-stamps__table">
                        <thead>
                            <tr>
                                <th>{{ trans('loyalty::members.stamps.program') }}</th>
                                <th>{{ trans('loyalty::members.stamps.lookup_code') }}</th>
                                <th>{{ trans('loyalty::members.stamps.redeemed_at') }}</th>
                                <th>{{ trans('loyalty::members.stamps.fulfillment') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($redemptions as $card)
                                <tr>
                                    <td>
                                        <strong>{{ $card->program?->name }}</strong>
                                        @if ($card->program?->reward_description)
                                            <br><small class="text-muted">{{ $card->program->reward_description }}</small>
                                        @endif
                                    </td>
                                    <td><code>{{ $card->redemption_code }}</code></td>
                                    <td class="text-nowrap">
                                        {{ $card->redeemed_at?->timezone(config('app.timezone'))->format('d M Y, H:i') }}
                                    </td>
                                    <td>
                                        @if ($card->fulfilled_at)
                                            <span class="label label-default">
                                                {{ trans('loyalty::members.stamps.fulfilled') }}
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                {{ $card->fulfilled_at->timezone(config('app.timezone'))->format('d M Y, H:i') }}
                                            </small>
                                        @else
                                            <span class="label label-warning">
                                                {{ trans('loyalty::members.stamps.pending_fulfillment') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-right text-nowrap">
                                        @if (! $card->fulfilled_at && auth()->user()?->hasAccess('admin.loyalty.members.show'))
                                            <form
                                                method="POST"
                                                action="{{ route('admin.loyalty.stamp_redemptions.fulfill', $card) }}"
                                                class="inline"
                                                onsubmit="return confirm(@js(trans('loyalty::members.stamps.fulfill_confirm')));"
                                            >
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-xs">
                                                    {{ trans('loyalty::members.stamps.fulfill_button') }}
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endif
