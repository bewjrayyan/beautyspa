<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  @if ($favicon)
    <link rel="icon" href="{{ $favicon }}" type="{{ $faviconMime }}" sizes="32x32">
    <link rel="shortcut icon" href="{{ $favicon }}" type="{{ $faviconMime }}">
    <link rel="apple-touch-icon" href="{{ $faviconTouch ?: $favicon }}">
  @endif
  <title>{{ trans('lead::central.page_title') }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('modules/lead/central/styles.css') }}?v={{ @filemtime(public_path('modules/lead/central/styles.css')) ?: time() }}" />
  <script src="https://cdn.jsdelivr.net/npm/echarts@5.5.1/dist/echarts.min.js"></script>
  <style>
    :root {
      --sidebar-bg: {{ $sidebarTheme['sidebar_bg'] }};
      --sidebar-bg-2: {{ $sidebarTheme['sidebar_bg_2'] }};
      --sidebar-accent: {{ $sidebarTheme['sidebar_accent'] }};
      --sidebar-accent-2: {{ $sidebarTheme['sidebar_accent_2'] }};
    }
  </style>
</head>
<body>
  <div class="app-shell">
    <aside class="sidebar" id="sidebar">
      <a class="brand brand--logo" href="{{ $adminHomeUrl }}" title="{{ trans('lead::central.brand_subtitle') }}">
        @if ($adminLogoUrl)
          <img src="{{ $adminLogoUrl }}" alt="{{ trans('lead::central.brand_subtitle') }}" class="brand-logo">
        @else
          <img src="{{ asset('build/assets/sidebar-logo-' . (is_rtl() ? 'rtl' : 'ltr') . '.svg') }}" alt="{{ trans('lead::central.brand_subtitle') }}" class="brand-logo">
        @endif
      </a>

      <nav class="nav">
        <div class="nav-label">{{ trans('lead::central.nav_groups.overview') }}</div>
        <a href="{{ route('admin.leads.central') }}" class="nav-item{{ $initialView === 'overview' ? ' active' : '' }}" data-view="overview"><span>⌂</span>{{ trans('lead::central.nav.dashboard') }}</a>
        <a href="{{ $adminHomeUrl }}" class="nav-item nav-item--link" id="backToMainDashboard"><span>←</span>{{ trans('lead::central.nav.main_dashboard') }}</a>

        <div class="nav-label">{{ trans('lead::central.nav_groups.lead_management') }}</div>
        <a href="{{ route('admin.leads.central', ['view' => 'leads']) }}" class="nav-item{{ $initialView === 'leads' ? ' active' : '' }}" data-view="leads"><span>♙</span>{{ trans('lead::central.nav.leads') }}</a>
        <a href="{{ route('admin.leads.central', ['view' => 'import']) }}" class="nav-item{{ $initialView === 'import' ? ' active' : '' }}" data-view="import"><span>⇧</span>{{ trans('lead::central.nav.import') }}</a>
        <a href="{{ route('admin.leads.central', ['view' => 'imports']) }}" class="nav-item{{ $initialView === 'imports' ? ' active' : '' }}" data-view="imports"><span>◴</span>{{ trans('lead::central.nav.imports') }}</a>
        <a href="{{ route('admin.leads.central', ['view' => 'followup']) }}" class="nav-item{{ $initialView === 'followup' ? ' active' : '' }}" data-view="followup"><span>↻</span>{{ trans('lead::central.nav.followup') }}</a>

        <div class="nav-label">{{ trans('lead::central.nav_groups.sales_payment') }}</div>
        <a href="{{ route('admin.leads.central', ['view' => 'sales']) }}" class="nav-item{{ $initialView === 'sales' ? ' active' : '' }}" data-view="sales"><span>◫</span>{{ trans('lead::central.nav.sales') }}</a>
        <a href="{{ route('admin.leads.central', ['view' => 'payments']) }}" class="nav-item{{ $initialView === 'payments' ? ' active' : '' }}" data-view="payments"><span>▣</span>{{ trans('lead::central.nav.payments') }}</a>

        <div class="nav-label">{{ trans('lead::central.nav_groups.customer_treatment') }}</div>
        <a href="{{ route('admin.leads.central', ['view' => 'customers']) }}" class="nav-item{{ $initialView === 'customers' ? ' active' : '' }}" data-view="customers"><span>♧</span>{{ trans('lead::central.nav.customers') }}</a>
        <a href="{{ route('admin.leads.central', ['view' => 'wallet']) }}" class="nav-item{{ $initialView === 'wallet' ? ' active' : '' }}" data-view="wallet"><span>◉</span>{{ trans('lead::central.nav.wallet') }}</a>
        <a href="{{ route('admin.leads.central', ['view' => 'checkin']) }}" class="nav-item{{ $initialView === 'checkin' ? ' active' : '' }}" data-view="checkin"><span>◎</span>{{ trans('lead::central.nav.checkin') }}</a>
        <a href="{{ route('admin.leads.central', ['view' => 'clearance']) }}" class="nav-item{{ $initialView === 'clearance' ? ' active' : '' }}" data-view="clearance"><span>◇</span>{{ trans('lead::central.nav.clearance') }}</a>

        <div class="nav-label">{{ trans('lead::central.nav_groups.reporting') }}</div>
        <a href="{{ route('admin.leads.central', ['view' => 'beauticians']) }}" class="nav-item{{ $initialView === 'beauticians' ? ' active' : '' }}" data-view="beauticians"><span>♚</span>{{ trans('lead::central.nav.beauticians') }}</a>
        <a href="{{ route('admin.leads.central', ['view' => 'branches']) }}" class="nav-item{{ $initialView === 'branches' ? ' active' : '' }}" data-view="branches"><span>▦</span>{{ trans('lead::central.nav.branches') }}</a>
        <a href="{{ route('admin.leads.central', ['view' => 'audit']) }}" class="nav-item{{ $initialView === 'audit' ? ' active' : '' }}" data-view="audit"><span>▤</span>{{ trans('lead::central.nav.audit') }}</a>
      </nav>

      <div class="sidebar-footer">
        <div class="quote">{!! trans('lead::central.footer.quote') !!}</div>
        <div class="tiny">{{ trans('lead::central.footer.tagline') }}</div>
      </div>
    </aside>

    <main class="main">
      <header class="topbar">
        <div class="topbar-left">
          <button type="button" class="icon-btn" id="menuToggle" aria-label="Menu">☰</button>
          <div class="breadcrumb"><span>{{ trans('lead::central.nav.dashboard') }}</span><span>›</span><strong id="crumbCurrent">{{ $initialView === 'overview' ? trans('lead::central.common.overview_crumb') : trans('lead::central.nav.'.$initialView) }}</strong></div>
        </div>
        <div class="topbar-right">
          <div class="global-search-wrap">
            <span>⌕</span>
            <input id="globalSearch" type="search" placeholder="{{ trans('lead::central.search_placeholder') }}" />
          </div>
          <select id="branchScope" class="control compact">
            <option value="all" @selected($selectedBranch === null)>{{ trans('lead::central.common.all_branches') }}</option>
            @foreach ($branches as $branch)
              <option value="{{ $branch['id'] }}" @selected((int) $selectedBranch === (int) $branch['id'])>{{ $branch['name'] }}</option>
            @endforeach
          </select>
          <select id="periodScope" class="control compact">
            @foreach ($periodOptions as $period)
              <option value="{{ $period['value'] }}" @selected($selectedPeriod === $period['value'])>{{ $period['label'] }}</option>
            @endforeach
          </select>
          <button type="button" class="btn central-export" id="exportCentralPdf" title="{{ trans('lead::central.export_pdf.hint') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3v12m-4-4 4 4 4-4M5 16v4h14v-4" /></svg>
            <span>{{ trans('lead::central.export_pdf.button') }}</span>
          </button>
          <button type="button" class="icon-btn notification" aria-label="Notifications">♢<span class="notif-dot">3</span></button>
        </div>
      </header>
      <div class="trade-ticker" id="tradeTicker" aria-live="off"></div>

      <section class="central-print-header" id="centralPrintHeader" aria-hidden="true"></section>
      <div class="page" id="viewRoot"></div>
    </main>
  </div>

  <div class="drawer-backdrop" id="drawerBackdrop"></div>
  <aside class="drawer" id="leadDrawer" role="dialog" aria-modal="true" aria-labelledby="drawerName" aria-hidden="true" inert>
    <div class="drawer-head">
      <div>
        <span class="eyebrow">Lead Journey</span>
        <h2 id="drawerName">Ina</h2>
      </div>
      <button type="button" class="icon-btn" id="drawerClose" aria-label="{{ trans('lead::central.wallet.close') }}">✕</button>
    </div>
    <div class="drawer-body" id="drawerBody"></div>
  </aside>

  <div class="drawer-backdrop" id="actionDrawerBackdrop"></div>
  <div class="drawer action-drawer" id="actionDrawer" role="dialog" aria-modal="true" aria-labelledby="actionDrawerTitle" aria-hidden="true" inert>
    <div class="drawer-head">
      <div>
        <span class="eyebrow" id="actionDrawerEyebrow">Action</span>
        <h3 id="actionDrawerTitle">Confirm Action</h3>
      </div>
      <button type="button" class="icon-btn" id="actionDrawerClose">✕</button>
    </div>
    <div class="drawer-body" id="actionDrawerBody"></div>
    <div class="action-drawer__footer" id="actionDrawerFoot"></div>
  </div>

  <div class="toast" id="toast"></div>

  <script>
    window.IMMA_CENTRAL = {
      adminHome: @json($adminHomeUrl),
      basePath: @json(rtrim(parse_url($centralBaseUrl, PHP_URL_PATH) ?: '/admin/leads/central', '/')),
      initialView: @json($initialView),
      locale: @json(app()->getLocale()),
      i18n: @json(trans('lead::central')),
      reportingUrl: @json($reportingUrl),
      metricsUrl: @json($metricsUrl),
      leadsUrl: @json($leadsUrl),
      followUpsUrl: @json($followUpsUrl),
      paymentsUrl: @json($paymentsUrl),
      customersUrl: @json($customersUrl),
      today: @json(now()->toDateString()),
      walletUrl: @json($walletUrl),
      checkinUrl: @json($checkinUrl),
      clearanceUrl: @json($clearanceUrl),
      ordersIndexUrl: @json($ordersIndexUrl),
      orderShowUrlTemplate: @json($orderShowUrlTemplate),
      orderPaymentStatusUrlTemplate: @json($orderPaymentStatusUrlTemplate),
      userEditUrlTemplate: @json($userEditUrlTemplate),
      loyaltyMembersUrl: @json($loyaltyMembersUrl),
      loyaltyMemberShowUrlTemplate: @json($loyaltyMemberShowUrlTemplate),
      treatmentReservationsUrl: @json($treatmentReservationsUrl),
      leadStoreUrl: @json($leadStoreUrl),
      leadShowUrlTemplate: @json($leadShowUrlTemplate),
      leadStatusUrlTemplate: @json($leadStatusUrlTemplate),
      leadUpdateUrlTemplate: @json($leadUpdateUrlTemplate),
      leadDestroyUrlTemplate: @json($leadDestroyUrlTemplate),
      leadFollowUpUrlTemplate: @json($leadFollowUpUrlTemplate),
      importHistoryUrl: @json($importHistoryUrl),
      importPreviewUrl: @json($importPreviewUrl),
      importConfirmUrl: @json($importConfirmUrl),
      canCreateLead: @json($canCreateLead),
      canEditLead: @json($canEditLead),
      canDeleteLead: @json($canDeleteLead),
      canViewOrder: @json($canViewOrder),
      canEditOrder: @json($canEditOrder),
      canViewUser: @json($canViewUser),
      canShowLoyaltyMember: @json($canShowLoyaltyMember),
      canViewLoyalty: @json($canViewLoyalty),
      canViewTreatments: @json($canViewTreatments),
      metrics: @json($metrics),
      branches: @json($branches),
      csrf: @json(csrf_token()),
    };
  </script>
  <script src="{{ asset('modules/lead/central/trade-charts.js') }}"></script>
  <script src="{{ asset('modules/lead/central/qrcode.js') }}?v={{ @filemtime(public_path('modules/lead/central/qrcode.js')) ?: time() }}"></script>
  <script src="{{ asset('modules/lead/central/app.js') }}?v={{ @filemtime(public_path('modules/lead/central/app.js')) ?: time() }}"></script>
</body>
</html>
