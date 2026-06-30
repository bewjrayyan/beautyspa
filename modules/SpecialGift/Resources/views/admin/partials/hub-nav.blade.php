@php
    $activeTab = $activeTab ?? 'submissions';
@endphp

<div class="gv-hub">
  <div class="gv-hub__header">
    <div class="gv-hub__intro">
      <div class="gv-hub__title-row">
        <span class="gv-hub__icon" aria-hidden="true"><i class="fa fa-gift"></i></span>
        <h4 class="gv-hub__title">{{ trans('specialgift::admin.hub_title') }}</h4>
      </div>
      <p class="gv-hub__lead">{{ trans('specialgift::admin.hub_lead') }}</p>
    </div>

    @if (! empty($sendGiftUrl))
      <a
        href="{{ $sendGiftUrl }}"
        class="btn btn-default btn-sm gv-hub__preview"
        target="_blank"
        rel="noopener noreferrer"
      >
        <i class="fa fa-external-link" aria-hidden="true"></i>
        {{ trans('specialgift::admin.view_public_page') }}
      </a>
    @endif
  </div>

  <ul class="nav nav-tabs gv-hub__tabs" role="tablist">
    <li role="presentation" @class(['active' => $activeTab === 'submissions'])>
      <a href="{{ route('admin.gift_voucher_submissions.index') }}">
        <i class="fa fa-list" aria-hidden="true"></i>
        <span>{{ trans('specialgift::admin.tab_submissions') }}</span>
      </a>
    </li>

    @if (auth()->user()?->hasAccess('admin.gift_voucher_submissions.settings'))
      <li role="presentation" @class(['active' => $activeTab === 'content'])>
        <a href="{{ route('admin.gift_voucher_submissions.content') }}">
          <i class="fa fa-file-text-o" aria-hidden="true"></i>
          <span>{{ trans('specialgift::admin.tab_content') }}</span>
        </a>
      </li>

      <li role="presentation" @class(['active' => $activeTab === 'design'])>
        <a href="{{ route('admin.gift_voucher_submissions.design') }}">
          <i class="fa fa-paint-brush" aria-hidden="true"></i>
          <span>{{ trans('specialgift::admin.tab_design') }}</span>
        </a>
      </li>

      <li role="presentation" @class(['active' => $activeTab === 'settings'])>
        <a href="{{ route('admin.gift_voucher_submissions.settings') }}">
          <i class="fa fa-cog" aria-hidden="true"></i>
          <span>{{ trans('specialgift::admin.tab_settings') }}</span>
        </a>
      </li>
    @endif
  </ul>
</div>
