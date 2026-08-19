<div class="dashboard-panel top-beauticians-panel">
    <div class="dashboard-panel__head">
        <h5><i class="fa fa-star" aria-hidden="true"></i> {{ trans('admin::dashboard.top_beauticians.title') }}</h5>
        @if (is_module_enabled('BeauticianReport'))
            <a href="{{ route('admin.beautician_reports.index') }}" class="dashboard-panel__view-all">
                {{ trans('admin::dashboard.view_all') }} <i class="fa fa-angle-right"></i>
            </a>
        @endif
    </div>
    <p class="top-beauticians-panel__subtitle">{{ trans('admin::dashboard.top_beauticians.subtitle') }}</p>

    @if (($topBeauticians ?? collect())->isEmpty())
        <div class="top-beauticians-panel__empty">
            {{ trans('admin::dashboard.top_beauticians.empty') }}
        </div>
    @else
        <div class="top-beauticians-list">
            @foreach ($topBeauticians as $index => $b)
                @php
                    $initials = $b->initials;
                    $color = $b->profile_color ?: ['#6366f1','#f59e0b','#ef4444','#10b981','#8b5cf6'][$index % 5];
                    $avatarUrl = $b->displayAvatarUrl();
                    $branch = $b->spaBranches->first()?->name;
                @endphp
                <a href="{{ route('admin.orders.index', ['query' => trim($b->first_name . ' ' . $b->last_name)]) }}" class="top-beauticians-list__item top-beauticians-list__item--link">
                    <div class="top-beauticians-list__rank">
                        @if ($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="{{ $b->name }}" class="top-beauticians-list__avatar-img">
                        @else
                            <span class="top-beauticians-list__avatar" style="background: {{ $color }}">{{ $initials }}</span>
                        @endif
                        <span class="top-beauticians-list__badge">{{ $index + 1 }}</span>
                    </div>
                    <div class="top-beauticians-list__info">
                        <strong>{{ $b->first_name }} {{ $b->last_name }}</strong>
                        @if ($b->job_title || $branch)
                            <span class="top-beauticians-list__role">
                                {{ $b->job_title }}@if ($b->job_title && $branch) &middot; @endif{{ $branch }}
                            </span>
                        @endif
                        <span class="top-beauticians-list__meta">
                            {{ number_format($b->orders_count) }} {{ trans('admin::dashboard.top_beauticians.orders') }}
                        </span>
                    </div>
                    <div class="top-beauticians-list__revenue">
                        @if ($b->revenue > 0)
                            <strong>{{ \Modules\Support\Money::inDefaultCurrency($b->revenue)->format() }}</strong>
                        @else
                            <strong class="top-beauticians-list__revenue--zero">&mdash;</strong>
                        @endif
                        <span class="top-beauticians-list__rev-label">{{ trans('admin::dashboard.top_beauticians.revenue') }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
