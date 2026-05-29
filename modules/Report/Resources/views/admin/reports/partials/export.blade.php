@php
    $exportQuery = request()->query();
    unset($exportQuery['page']);
@endphp

<div class="report-export">
    <div class="btn-group report-export__dropdown">
        <button
            type="button"
            class="btn btn-default dropdown-toggle report-export__toggle"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
        >
            <i class="fa fa-download" aria-hidden="true"></i>
            {{ trans('report::admin.export') }}
            <span class="caret"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-right report-export__menu">
            <li class="dropdown-header">{{ trans('report::admin.export_as') }}</li>
            <li>
                <a href="{{ route('admin.reports.export', array_merge($exportQuery, ['format' => 'csv'])) }}">
                    <i class="fa fa-file-text-o" aria-hidden="true"></i>
                    {{ trans('report::admin.export_csv') }}
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reports.export', array_merge($exportQuery, ['format' => 'xlsx'])) }}">
                    <i class="fa fa-file-excel-o" aria-hidden="true"></i>
                    {{ trans('report::admin.export_excel') }}
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reports.export', array_merge($exportQuery, ['format' => 'pdf'])) }}">
                    <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                    {{ trans('report::admin.export_pdf') }}
                </a>
            </li>
        </ul>
    </div>
</div>
