@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('beautician::job_titles.job_titles'))

    <li class="active">{{ trans('beautician::job_titles.job_titles') }}</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('buttons', ['create'])
    @slot('resource', 'beautician_job_titles')
    @slot('name', trans('beautician::job_titles.job_title'))

    @component('admin::components.table')
        @slot('thead')
            <tr>
                @include('admin::partials.table.select_all')

                <th>{{ trans('admin::admin.table.id') }}</th>
                <th>{{ trans('beautician::job_titles.table.name') }}</th>
                <th>{{ trans('beautician::job_titles.table.position') }}</th>
                <th>{{ trans('beautician::job_titles.table.status') }}</th>
                <th data-sort>{{ trans('admin::admin.table.created') }}</th>
            </tr>
        @endslot
    @endcomponent
@endcomponent

@push('scripts')
    <script type="module">
        new DataTable('#beautician_job_titles-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'name', name: 'name', defaultContent: '' },
                { data: 'position', name: 'position', width: '10%' },
                { data: 'status', name: 'is_active', searchable: false, width: '12%' },
                { data: 'created', name: 'created_at', width: '20%' },
            ],
        });
    </script>
@endpush
