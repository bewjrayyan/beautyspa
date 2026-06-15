@php
    $profileColor = $beautician->profile_color ?? '#6366f1';
    $portalUser = $beautician->user ?? ($user ?? auth()->user());
    $avatarUrl = $beautician->displayAvatarUrl();
    $hasPhoto = filled($avatarUrl);
    $isActive = (bool) $beautician->is_active;
    $heroInsights = $heroInsights ?? [];
    $heroStats = $heroStats ?? null;
@endphp

<header class="bp-hero" style="--bp-profile-color: {{ $profileColor }};">
    <div class="bp-hero-main">
        <div class="bp-hero-avatar-block">
            <div class="bp-hero-avatar">
                @if ($hasPhoto)
                    <img src="{{ $avatarUrl }}" alt="">
                @else
                    <span class="bp-hero-initial" style="background-color: {{ $profileColor }};">
                        {{ $beautician->initials }}
                    </span>
                @endif
            </div>
        </div>

        <div class="bp-hero-identity">
            <div class="bp-hero-name-row">
                <h2 class="bp-hero-name">{{ $beautician->name }}</h2>
                <span class="bp-hero-status-badge {{ $isActive ? 'is-active' : 'is-inactive' }}">
                    {{ $isActive ? trans('beautician::beauticians.active') : trans('beautician::beauticians.inactive') }}
                </span>
            </div>

            @if ($beautician->job_title)
                <p class="bp-hero-meta">
                    <i class="fa fa-briefcase"></i>
                    <span>{{ $beautician->job_title }}</span>
                </p>
            @endif

            @if ($beautician->phone)
                <p class="bp-hero-meta">
                    <i class="fa fa-phone"></i>
                    <span>{{ $beautician->phone }}</span>
                </p>
            @elseif ($portalUser?->phone)
                <p class="bp-hero-meta">
                    <i class="fa fa-phone"></i>
                    <span>{{ $portalUser->phone }}</span>
                </p>
            @endif

            @if ($portalUser?->email)
                <p class="bp-hero-meta">
                    <i class="fa fa-envelope"></i>
                    <span>{{ $portalUser->email }}</span>
                </p>
            @endif
        </div>
    </div>

    @if ($heroInsights)
        <div class="bp-hero-insights">
            @foreach ($heroInsights as $insight)
                <article class="bp-hero-insight">
                    <div class="bp-hero-insight-icon">
                        @if (($insight['type'] ?? '') === 'swatch')
                            <span class="bp-hero-insight-swatch" style="background-color: {{ $insight['swatch'] ?? $profileColor }};"></span>
                        @else
                            <i class="fa {{ $insight['icon'] ?? 'fa-info-circle' }}"></i>
                        @endif
                    </div>
                    <div class="bp-hero-insight-body">
                        <span class="bp-hero-insight-label">{{ $insight['label'] }}</span>
                        <span class="bp-hero-insight-value {{ $insight['value_class'] ?? '' }}">{{ $insight['value'] }}</span>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    <ul class="bp-hero-stats">
        @if ($heroStats)
            @foreach ($heroStats as $stat)
                <li>
                    <span class="bp-hero-stat-label">{{ $stat['label'] }}</span>
                    <span class="bp-hero-stat-value {{ $stat['value_class'] ?? '' }}">{{ $stat['value'] }}</span>
                </li>
            @endforeach
        @else
            <li>
                <span class="bp-hero-stat-label">{{ trans('beautician::beauticians.table.status') }}</span>
                <span class="bp-hero-stat-value bp-hero-stat-status {{ $isActive ? 'is-active' : 'is-inactive' }}">
                    {{ $isActive ? trans('beautician::beauticians.active') : trans('beautician::beauticians.inactive') }}
                </span>
            </li>
            <li>
                <span class="bp-hero-stat-label">{{ trans('beautician::beauticians.beautician') }}</span>
                <span class="bp-hero-stat-value">#{{ $beautician->id }}</span>
            </li>
            <li>
                <span class="bp-hero-stat-label">{{ trans('beautician::attributes.sort_order') }}</span>
                <span class="bp-hero-stat-value">{{ $beautician->position ?? 0 }}</span>
            </li>
        @endif
    </ul>
</header>
