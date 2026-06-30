@if ($spaBranches->isNotEmpty())
    <section class="imma-about-section imma-about-branches" aria-labelledby="imma-about-branches-title">
        <div class="imma-about-section__head">
            <span class="imma-about-kicker">{{ trans('storefront::about.locations_kicker') }}</span>
            <h2 id="imma-about-branches-title">{{ trans('storefront::about.locations_title') }}</h2>
            <p>{{ trans('storefront::about.locations_lead') }}</p>
        </div>

        <div class="imma-about-branches__grid">
            @foreach ($spaBranches as $branch)
                <article class="imma-about-branch-card">
                    <header class="imma-about-branch-card__header">
                        <h3 class="imma-about-branch-card__name">{{ $branch->name }}</h3>

                        @if (filled($branch->code))
                            <span class="imma-about-branch-card__code">{{ $branch->code }}</span>
                        @endif
                    </header>

                    <ul class="imma-about-branch-card__details">
                        @if (filled($branch->phone))
                            <li>
                                <i class="las la-phone" aria-hidden="true"></i>
                                <a href="tel:{{ preg_replace('/\s+/', '', $branch->phone) }}">{{ $branch->phone }}</a>
                            </li>
                        @endif

                        @if (filled($branch->email))
                            <li>
                                <i class="las la-envelope" aria-hidden="true"></i>
                                <a href="mailto:{{ $branch->email }}">{{ $branch->email }}</a>
                            </li>
                        @endif

                        @if (filled($branch->address))
                            <li>
                                <i class="las la-map-marker-alt" aria-hidden="true"></i>
                                <span>{{ $branch->address }}</span>
                            </li>
                        @endif
                    </ul>

                    <div class="imma-about-branch-card__actions">
                        @if (filled($branch->address))
                            <a
                                href="https://maps.google.com/maps?q={{ urlencode($branch->address) }}"
                                class="imma-about-branch-card__action imma-about-branch-card__action--primary"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                {{ trans('storefront::about.get_directions') }}
                            </a>
                        @endif

                        <a href="{{ route('contact.create') }}" class="imma-about-branch-card__action">
                            {{ trans('storefront::about.contact_branch') }}
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif
