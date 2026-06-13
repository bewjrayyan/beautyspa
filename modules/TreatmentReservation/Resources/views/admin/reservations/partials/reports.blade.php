<div class="tr-reports-panel">
    <form method="GET" action="{{ route('admin.treatment_reservations.index') }}" class="tr-filters tr-filters--reports box">
        <input type="hidden" name="view" value="reports">

        <div class="tr-filters__header">
            <div>
                <h4 class="tr-filters__title">
                    <i class="fa fa-bar-chart" aria-hidden="true"></i>
                    {{ trans('treatmentreservation::admin.reports.title') }}
                </h4>
                <p class="tr-filters__subtitle">{{ trans('treatmentreservation::admin.reports.subtitle') }}</p>
            </div>
        </div>

        <div class="tr-filters__grid tr-filters__grid--reports">
            <div class="tr-filters__field">
                <label for="tr-from">{{ trans('treatmentreservation::admin.reports.from') }}</label>
                <input type="date" name="from" id="tr-from" class="form-control" value="{{ $filters['from'] }}">
            </div>

            <div class="tr-filters__field">
                <label for="tr-to">{{ trans('treatmentreservation::admin.reports.to') }}</label>
                <input type="date" name="to" id="tr-to" class="form-control" value="{{ $filters['to'] }}">
            </div>

            <div class="tr-filters__field">
                <label for="tr-beautician">{{ trans('treatmentreservation::admin.filters.beautician') }}</label>
                <select name="beautician_id" id="tr-beautician" class="custom-select-black">
                    <option value="">{{ trans('treatmentreservation::admin.filters.all_beauticians') }}</option>
                    @foreach ($beauticians as $beautician)
                        <option value="{{ $beautician->id }}" {{ (int) $filters['beautician_id'] === $beautician->id ? 'selected' : '' }}>
                            {{ $beautician->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="tr-filters__field">
                <label for="tr-source">{{ trans('treatmentreservation::admin.reports.source') }}</label>
                <select name="source" id="tr-source" class="custom-select-black">
                    <option value="">{{ trans('treatmentreservation::admin.reports.source_all') }}</option>
                    <option value="checkout" {{ ($filters['source'] ?? null) === 'checkout' ? 'selected' : '' }}>
                        {{ trans('treatmentreservation::admin.reports.source_checkout') }}
                    </option>
                    <option value="manual" {{ ($filters['source'] ?? null) === 'manual' ? 'selected' : '' }}>
                        {{ trans('treatmentreservation::admin.reports.source_manual') }}
                    </option>
                </select>
            </div>

            <div class="tr-filters__field tr-filters__field--action">
                <label class="tr-filters__action-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary tr-filters__submit">
                    <i class="fa fa-filter" aria-hidden="true"></i> {{ trans('treatmentreservation::admin.filters.apply') }}
                </button>
            </div>
        </div>

        @if ($reportSummary)
            <div class="fc-saas-stats fc-saas-stats--4 tr-reports-panel__stats">
                @include('admin::partials.fc_saas_stat', [
                    'variant' => 'tr-total',
                    'icon' => 'fa-calendar-check-o',
                    'label' => trans('treatmentreservation::admin.reports.total'),
                    'value' => number_format($reportSummary['total']),
                ])
                @include('admin::partials.fc_saas_stat', [
                    'variant' => 'tr-completed',
                    'icon' => 'fa-check-circle',
                    'label' => trans('treatmentreservation::admin.kanban.completed'),
                    'value' => number_format($reportSummary['completed']),
                ])
                @include('admin::partials.fc_saas_stat', [
                    'variant' => 'tr-pending',
                    'icon' => 'fa-ban',
                    'label' => trans('treatmentreservation::admin.reports.canceled'),
                    'value' => number_format($reportSummary['canceled']),
                ])
                @include('admin::partials.fc_saas_stat', [
                    'variant' => 'tr-revenue',
                    'icon' => 'fa-money',
                    'label' => trans('treatmentreservation::admin.reports.revenue'),
                    'value' => $reportSummary['revenue']->format(),
                ])
            </div>

            <div class="tr-reports-panel__exports">
                <span class="tr-reports-panel__exports-label">{{ trans('treatmentreservation::admin.reports.export_label') }}</span>
                <a
                    href="{{ route('admin.treatment_reservations.export', request()->only(['from', 'to', 'beautician_id', 'treatment_category_id', 'source'])) }}"
                    class="btn btn-default"
                >
                    <i class="fa fa-download" aria-hidden="true"></i> {{ trans('treatmentreservation::admin.reports.export_csv') }}
                </a>
                <a
                    href="{{ route('admin.treatment_reservations.export_pdf', request()->only(['from', 'to', 'beautician_id', 'treatment_category_id', 'source'])) }}"
                    class="btn btn-default"
                    target="_blank"
                    rel="noopener"
                >
                    <i class="fa fa-file-pdf-o" aria-hidden="true"></i> {{ trans('treatmentreservation::admin.reports.export_pdf') }}
                </a>
            </div>
        @endif
    </form>
</div>
