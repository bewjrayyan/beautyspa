@php
    $activeStatus = (string) request('is_active', '');
    if (! in_array($activeStatus, ['0', '1'], true)) {
        $activeStatus = '';
    }

    $activeType = (string) request('type', '');
    if (! in_array($activeType, ['physical', 'virtual', 'variable'], true)) {
        $activeType = '';
    }

    $activeStock = (string) request('stock', '');
    if (! in_array($activeStock, ['in_stock', 'out_of_stock', 'low_stock'], true)) {
        $activeStock = '';
    }

    $activeCategoryId = (string) request('category_id', '');
    $activeBrandId = (string) request('brand_id', '');
    $activeTagId = (string) request('tag_id', '');
    $activePriceFrom = (string) request('price_from', '');
    $activePriceTo = (string) request('price_to', '');
    $activeSku = (string) request('sku', '');
    $activeOnSale = request('on_sale') === '1';
    $activeSort = (string) request('sort', 'latest');
    $activeSearch = (string) request('search', '');

    $activeMonth = (string) request('month', '');
    if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $activeMonth)) {
        $activeMonth = '';
    }

    $activeUpdatedFrom = (string) request('updated_from', '');
    $activeUpdatedTo = (string) request('updated_to', '');

    $monthOptions = collect(range(0, 17))->map(function (int $offset) {
        $date = now()->startOfMonth()->subMonths($offset);

        return [
            'value' => $date->format('Y-m'),
            'label' => $date->translatedFormat('F Y'),
        ];
    });

    $updatedRangeDefault = '';
    if ($activeUpdatedFrom !== '' && $activeUpdatedTo !== '') {
        $updatedRangeDefault = $activeUpdatedFrom . ' to ' . $activeUpdatedTo;
    } elseif ($activeUpdatedFrom !== '') {
        $updatedRangeDefault = $activeUpdatedFrom;
    }

    $hasAdvancedFilters = $activeCategoryId !== ''
        || $activeBrandId !== ''
        || $activeTagId !== ''
        || $activePriceFrom !== ''
        || $activePriceTo !== ''
        || $activeSku !== ''
        || $activeOnSale
        || $activeMonth !== ''
        || $activeUpdatedFrom !== ''
        || $activeUpdatedTo !== ''
        || $activeSort !== 'latest'
        || $activeSearch !== '';

    $advancedFilterCount = collect([
        $activeCategoryId !== '',
        $activeBrandId !== '',
        $activeTagId !== '',
        $activePriceFrom !== '',
        $activePriceTo !== '',
        $activeSku !== '',
        $activeOnSale,
        $activeMonth !== '',
        $activeUpdatedFrom !== '',
        $activeUpdatedTo !== '',
        $activeSort !== 'latest',
        $activeSearch !== '',
    ])->filter()->count();

    $quickFilterCount = collect([
        $activeStatus !== '',
        $activeType !== '',
        $activeStock !== '',
    ])->filter()->count() + $advancedFilterCount;
@endphp

<div class="products-index__layout">
    <section class="products-index__section products-index__section--stats" aria-label="{{ trans('product::products.filters.section_stats') }}">
        <header class="products-index__section-head">
            <h3 class="products-index__section-title">{{ trans('product::products.filters.section_stats') }}</h3>
        </header>

        <div class="products-index__stats" role="list">
            <div class="products-index__stat" role="listitem">
                <span class="products-index__stat-label">{{ trans('product::products.filters.stat_total') }}</span>
                <strong class="products-index__stat-value">{{ number_format($totalProductsCount) }}</strong>
            </div>
            <div class="products-index__stat products-index__stat--active" role="listitem">
                <span class="products-index__stat-label">{{ trans('product::products.filters.active') }}</span>
                <strong class="products-index__stat-value">{{ number_format($activeProductsCount) }}</strong>
            </div>
            <div class="products-index__stat products-index__stat--inactive" role="listitem">
                <span class="products-index__stat-label">{{ trans('product::products.filters.inactive') }}</span>
                <strong class="products-index__stat-value">{{ number_format($inactiveProductsCount) }}</strong>
            </div>
            @if ($quickFilterCount > 0)
                <div class="products-index__stat products-index__stat--filtered" role="listitem">
                    <span class="products-index__stat-label">{{ trans('product::products.filters.stat_filtered') }}</span>
                    <strong class="products-index__stat-value" id="products-filtered-count">—</strong>
                </div>
            @endif
        </div>
    </section>

    <section class="products-index__section products-index__section--filters" aria-label="{{ trans('product::products.filters.section_filters') }}">
        <div class="products-index__section-accent" aria-hidden="true"></div>

        <header class="products-index__section-head">
            <h3 class="products-index__section-title">{{ trans('product::products.filters.section_filters') }}</h3>
        </header>

        <div class="products-index__command-bar">
            <label class="products-index__search" for="products-quick-search">
                <i class="fa fa-search products-index__search-icon" aria-hidden="true"></i>
                <input
                    type="search"
                    id="products-quick-search"
                    class="products-index__search-input"
                    value="{{ $activeSearch }}"
                    maxlength="100"
                    placeholder="{{ trans('product::products.filters.quick_search_placeholder') }}"
                    autocomplete="off"
                >
            </label>

            <div class="products-index__command-actions">
                <button
                    type="button"
                    class="products-index__command-btn products-index__command-btn--ghost"
                    id="products-advanced-toggle"
                    aria-expanded="{{ $hasAdvancedFilters ? 'true' : 'false' }}"
                    aria-controls="products-advanced-panel"
                >
                    <i class="fa fa-sliders" aria-hidden="true"></i>
                    <span>{{ trans('product::products.filters.advanced') }}</span>
                    @if ($advancedFilterCount > 0)
                        <span class="products-index__badge" id="products-advanced-badge">{{ $advancedFilterCount }}</span>
                    @else
                        <span class="products-index__badge" id="products-advanced-badge" hidden>0</span>
                    @endif
                    <i class="fa fa-chevron-down products-index__advanced-chevron" aria-hidden="true"></i>
                </button>

                <button type="button" id="products-filter-apply" class="products-index__command-btn products-index__command-btn--primary">
                    <i class="fa fa-check" aria-hidden="true"></i>
                    <span>{{ trans('product::products.filters.apply') }}</span>
                </button>

                <button type="button" id="products-filter-clear" class="products-index__command-btn products-index__command-btn--muted" title="{{ trans('product::products.filters.clear') }}">
                    <i class="fa fa-undo" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="products-index__filter-strip" role="group" aria-label="{{ trans('product::products.filters.quick_filters') }}">
        <div class="products-index__filter-group" aria-labelledby="products-status-label">
            <span class="products-index__filter-group-label" id="products-status-label">{{ trans('product::products.filters.status') }}</span>
            <div class="products-index__filter-group-chips" id="products-status-filters" role="group" aria-labelledby="products-status-label">
                <button type="button" class="products-index__chip {{ $activeStatus === '' ? 'is-active' : '' }}" data-status="" aria-pressed="{{ $activeStatus === '' ? 'true' : 'false' }}">
                    <span class="products-index__chip-text">{{ trans('product::products.filters.all') }}</span>
                    <span class="products-index__chip-count">{{ number_format($totalProductsCount) }}</span>
                </button>
                <button type="button" class="products-index__chip products-index__chip--active {{ $activeStatus === '1' ? 'is-active' : '' }}" data-status="1" aria-pressed="{{ $activeStatus === '1' ? 'true' : 'false' }}">
                    <span class="products-index__chip-dot" aria-hidden="true"></span>
                    <span class="products-index__chip-text">{{ trans('product::products.filters.active') }}</span>
                    <span class="products-index__chip-count">{{ number_format($activeProductsCount) }}</span>
                </button>
                <button type="button" class="products-index__chip products-index__chip--inactive {{ $activeStatus === '0' ? 'is-active' : '' }}" data-status="0" aria-pressed="{{ $activeStatus === '0' ? 'true' : 'false' }}">
                    <span class="products-index__chip-dot" aria-hidden="true"></span>
                    <span class="products-index__chip-text">{{ trans('product::products.filters.inactive') }}</span>
                    <span class="products-index__chip-count">{{ number_format($inactiveProductsCount) }}</span>
                </button>
            </div>
        </div>

        <div class="products-index__filter-group" aria-labelledby="products-type-label">
            <span class="products-index__filter-group-label" id="products-type-label">{{ trans('product::products.filters.type') }}</span>
            <div class="products-index__filter-group-chips" id="products-type-filters" role="group" aria-labelledby="products-type-label">
                <button type="button" class="products-index__chip {{ $activeType === '' ? 'is-active' : '' }}" data-type="" aria-pressed="{{ $activeType === '' ? 'true' : 'false' }}">
                    <span class="products-index__chip-text">{{ trans('product::products.filters.all') }}</span>
                </button>
                <button type="button" class="products-index__chip products-index__chip--physical {{ $activeType === 'physical' ? 'is-active' : '' }}" data-type="physical" aria-pressed="{{ $activeType === 'physical' ? 'true' : 'false' }}">
                    <span class="products-index__chip-dot" aria-hidden="true"></span>
                    <span class="products-index__chip-text">{{ trans('product::products.filters.type_physical') }}</span>
                    <span class="products-index__chip-count">{{ number_format($physicalProductsCount) }}</span>
                </button>
                <button type="button" class="products-index__chip products-index__chip--virtual {{ $activeType === 'virtual' ? 'is-active' : '' }}" data-type="virtual" aria-pressed="{{ $activeType === 'virtual' ? 'true' : 'false' }}">
                    <span class="products-index__chip-dot" aria-hidden="true"></span>
                    <span class="products-index__chip-text">{{ trans('product::products.filters.type_virtual') }}</span>
                    <span class="products-index__chip-count">{{ number_format($virtualProductsCount) }}</span>
                </button>
                <button type="button" class="products-index__chip products-index__chip--variable {{ $activeType === 'variable' ? 'is-active' : '' }}" data-type="variable" aria-pressed="{{ $activeType === 'variable' ? 'true' : 'false' }}">
                    <span class="products-index__chip-dot" aria-hidden="true"></span>
                    <span class="products-index__chip-text">{{ trans('product::products.filters.type_variable') }}</span>
                    <span class="products-index__chip-count">{{ number_format($variableProductsCount) }}</span>
                </button>
            </div>
        </div>

        <div class="products-index__filter-group" aria-labelledby="products-stock-label">
            <span class="products-index__filter-group-label" id="products-stock-label">{{ trans('product::products.filters.stock') }}</span>
            <div class="products-index__filter-group-chips" id="products-stock-filters" role="group" aria-labelledby="products-stock-label">
                <button type="button" class="products-index__chip {{ $activeStock === '' ? 'is-active' : '' }}" data-stock="" aria-pressed="{{ $activeStock === '' ? 'true' : 'false' }}">
                    <span class="products-index__chip-text">{{ trans('product::products.filters.all') }}</span>
                </button>
                <button type="button" class="products-index__chip products-index__chip--in-stock {{ $activeStock === 'in_stock' ? 'is-active' : '' }}" data-stock="in_stock" aria-pressed="{{ $activeStock === 'in_stock' ? 'true' : 'false' }}">
                    <span class="products-index__chip-dot" aria-hidden="true"></span>
                    <span class="products-index__chip-text">{{ trans('product::products.filters.stock_in') }}</span>
                    <span class="products-index__chip-count">{{ number_format($inStockCount) }}</span>
                </button>
                <button type="button" class="products-index__chip products-index__chip--out-stock {{ $activeStock === 'out_of_stock' ? 'is-active' : '' }}" data-stock="out_of_stock" aria-pressed="{{ $activeStock === 'out_of_stock' ? 'true' : 'false' }}">
                    <span class="products-index__chip-dot" aria-hidden="true"></span>
                    <span class="products-index__chip-text">{{ trans('product::products.filters.stock_out') }}</span>
                    <span class="products-index__chip-count">{{ number_format($outOfStockCount) }}</span>
                </button>
                <button type="button" class="products-index__chip products-index__chip--low-stock {{ $activeStock === 'low_stock' ? 'is-active' : '' }}" data-stock="low_stock" aria-pressed="{{ $activeStock === 'low_stock' ? 'true' : 'false' }}">
                    <span class="products-index__chip-dot" aria-hidden="true"></span>
                    <span class="products-index__chip-text">{{ trans('product::products.filters.stock_low') }}</span>
                    <span class="products-index__chip-count">{{ number_format($lowStockCount) }}</span>
                </button>
            </div>
        </div>
    </div>

    <div
        id="products-advanced-panel"
        class="products-index__advanced-panel {{ $hasAdvancedFilters ? 'is-open' : '' }}"
        @unless($hasAdvancedFilters) hidden @endunless
    >
        <div class="products-index__advanced-sections">
            <div class="products-index__advanced-section">
                <h4 class="products-index__section-title">{{ trans('product::products.filters.section_catalog') }}</h4>
                <div class="products-index__advanced-grid">
                    <label class="products-index__field">
                        <span class="products-index__field-label">{{ trans('product::products.filters.category') }}</span>
                        <select id="products-filter-category" class="products-index__control">
                            <option value="">{{ trans('product::products.filters.all') }}</option>
                            @foreach ($categories as $id => $name)
                                <option value="{{ $id }}" @selected($activeCategoryId === (string) $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="products-index__field">
                        <span class="products-index__field-label">{{ trans('product::products.filters.brand') }}</span>
                        <select id="products-filter-brand" class="products-index__control">
                            <option value="">{{ trans('product::products.filters.all') }}</option>
                            @foreach ($brands as $id => $name)
                                <option value="{{ $id }}" @selected($activeBrandId === (string) $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </label>
                    @if ($tags->isNotEmpty())
                        <label class="products-index__field">
                            <span class="products-index__field-label">{{ trans('product::products.filters.tag') }}</span>
                            <select id="products-filter-tag" class="products-index__control">
                                <option value="">{{ trans('product::products.filters.all') }}</option>
                                @foreach ($tags as $id => $name)
                                    <option value="{{ $id }}" @selected($activeTagId === (string) $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endif
                    <label class="products-index__field">
                        <span class="products-index__field-label">{{ trans('product::products.filters.sku') }}</span>
                        <input type="text" id="products-filter-sku" class="products-index__control" value="{{ $activeSku }}" maxlength="100" placeholder="{{ trans('product::products.filters.sku_placeholder') }}">
                    </label>
                </div>
            </div>

            <div class="products-index__advanced-section">
                <h4 class="products-index__section-title">{{ trans('product::products.filters.section_pricing') }}</h4>
                <div class="products-index__advanced-grid">
                    <label class="products-index__field products-index__field--price">
                        <span class="products-index__field-label">{{ trans('product::products.filters.price_range') }}</span>
                        <div class="products-index__price-range">
                            <input type="number" id="products-filter-price-from" class="products-index__control" min="0" step="0.01" value="{{ $activePriceFrom }}" placeholder="{{ trans('product::products.filters.price_from') }}">
                            <span class="products-index__price-sep">–</span>
                            <input type="number" id="products-filter-price-to" class="products-index__control" min="0" step="0.01" value="{{ $activePriceTo }}" placeholder="{{ trans('product::products.filters.price_to') }}">
                        </div>
                    </label>
                    <label class="products-index__field">
                        <span class="products-index__field-label">{{ trans('product::products.filters.sort') }}</span>
                        <select id="products-filter-sort" class="products-index__control">
                            <option value="latest" @selected($activeSort === 'latest')>{{ trans('product::products.filters.sort_latest') }}</option>
                            <option value="oldest" @selected($activeSort === 'oldest')>{{ trans('product::products.filters.sort_oldest') }}</option>
                            <option value="name_asc" @selected($activeSort === 'name_asc')>{{ trans('product::products.filters.sort_name_asc') }}</option>
                            <option value="name_desc" @selected($activeSort === 'name_desc')>{{ trans('product::products.filters.sort_name_desc') }}</option>
                            <option value="price_asc" @selected($activeSort === 'price_asc')>{{ trans('product::products.filters.sort_price_asc') }}</option>
                            <option value="price_desc" @selected($activeSort === 'price_desc')>{{ trans('product::products.filters.sort_price_desc') }}</option>
                        </select>
                    </label>
                    <label class="products-index__field products-index__field--toggle">
                        <span class="products-index__toggle-card">
                            <input type="checkbox" id="products-filter-on-sale" value="1" @checked($activeOnSale)>
                            <span class="products-index__toggle-copy">
                                <strong>{{ trans('product::products.filters.on_sale') }}</strong>
                                <small>{{ trans('product::products.filters.on_sale_hint') }}</small>
                            </span>
                        </span>
                    </label>
                </div>
            </div>

            <div class="products-index__advanced-section">
                <h4 class="products-index__section-title">{{ trans('product::products.filters.section_timeline') }}</h4>
                <div class="products-index__advanced-grid">
                    <label class="products-index__field">
                        <span class="products-index__field-label">{{ trans('product::products.filters.updated_month') }}</span>
                        <select id="products-filter-month" class="products-index__control">
                            <option value="">{{ trans('product::products.filters.all') }}</option>
                            @foreach ($monthOptions as $option)
                                <option value="{{ $option['value'] }}" @selected($activeMonth === $option['value'])>{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="products-index__field products-index__field--range">
                        <span class="products-index__field-label">{{ trans('product::products.filters.updated_range') }}</span>
                        <input
                            type="text"
                            id="products-filter-updated-range"
                            class="products-index__control products-index__control--range datetime-picker"
                            data-range
                            data-default-date="{{ $updatedRangeDefault }}"
                            value="{{ $updatedRangeDefault }}"
                            placeholder="{{ trans('product::products.filters.updated_range_placeholder') }}"
                            autocomplete="off"
                            readonly
                        >
                    </label>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" id="products-filter-search" value="{{ $activeSearch }}">

    <div id="products-active-filters" class="products-index__active-filters" hidden></div>
    </section>
</div>
