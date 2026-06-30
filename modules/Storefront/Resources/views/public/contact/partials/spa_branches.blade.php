@if ($spaBranches->isNotEmpty())
    <section class="contact-branches" aria-labelledby="contact-branches-title">
        <div class="contact-branches__header">
            <span class="contact-branches__eyebrow">{{ trans('storefront::contact.branches_eyebrow') }}</span>
            <h2 class="contact-branches__title" id="contact-branches-title">{{ trans('storefront::contact.our_branches') }}</h2>
            <p class="contact-branches__lead">{{ trans('storefront::contact.branches_section_lead') }}</p>
        </div>

        <div class="contact-branches__grid">
            @foreach ($spaBranches as $branch)
                <article class="contact-branch-card">
                    <div class="contact-branch-card__hero">
                        <div class="contact-branch-card__icon" aria-hidden="true">
                            <i class="las la-store-alt"></i>
                        </div>

                        <div class="contact-branch-card__titles">
                            <h3 class="contact-branch-card__name">{{ $branch->name }}</h3>

                            @if (filled($branch->code))
                                <span class="contact-branch-card__code">{{ $branch->code }}</span>
                            @endif
                        </div>
                    </div>

                    <ul class="contact-branch-card__details">
                        @if (filled($branch->phone))
                            <li>
                                <span class="contact-branch-card__detail-icon" aria-hidden="true">
                                    <i class="las la-phone"></i>
                                </span>
                                <span class="contact-branch-card__detail-body">
                                    <span class="contact-branch-card__detail-label">{{ trans('storefront::contact.phone') }}</span>
                                    <a href="tel:{{ preg_replace('/\s+/', '', $branch->phone) }}">{{ $branch->phone }}</a>
                                </span>
                            </li>
                        @endif

                        @if (filled($branch->email))
                            <li>
                                <span class="contact-branch-card__detail-icon" aria-hidden="true">
                                    <i class="las la-envelope"></i>
                                </span>
                                <span class="contact-branch-card__detail-body">
                                    <span class="contact-branch-card__detail-label">{{ trans('storefront::contact.email') }}</span>
                                    <a href="mailto:{{ $branch->email }}">{{ $branch->email }}</a>
                                </span>
                            </li>
                        @endif

                        @if (filled($branch->address))
                            <li>
                                <span class="contact-branch-card__detail-icon" aria-hidden="true">
                                    <i class="las la-map-marker-alt"></i>
                                </span>
                                <span class="contact-branch-card__detail-body">
                                    <span class="contact-branch-card__detail-label">{{ trans('storefront::contact.address') }}</span>
                                    <span class="contact-branch-card__address">{{ $branch->address }}</span>
                                </span>
                            </li>
                        @endif
                    </ul>

                    <div class="contact-branch-card__actions">
                        @if (filled($branch->phone))
                            <a
                                href="tel:{{ preg_replace('/\s+/', '', $branch->phone) }}"
                                class="contact-branch-card__action contact-branch-card__action--primary"
                            >
                                <i class="las la-phone" aria-hidden="true"></i>
                                {{ trans('storefront::contact.call') }}
                            </a>
                        @endif

                        @if (filled($branch->email))
                            <a
                                href="mailto:{{ $branch->email }}"
                                class="contact-branch-card__action"
                            >
                                <i class="las la-envelope" aria-hidden="true"></i>
                                {{ trans('storefront::contact.email_us') }}
                            </a>
                        @endif

                        @if (filled($branch->address))
                            <a
                                href="https://maps.google.com/maps?q={{ urlencode($branch->address) }}"
                                class="contact-branch-card__action"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <i class="las la-directions" aria-hidden="true"></i>
                                {{ trans('storefront::contact.get_directions') }}
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif
