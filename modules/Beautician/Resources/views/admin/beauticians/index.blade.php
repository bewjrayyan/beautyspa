@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('beautician::beauticians.beauticians'))

    <li class="active">{{ trans('beautician::beauticians.beauticians') }}</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('buttons', ['create'])
    @slot('resource', 'beauticians')
    @slot('name', trans('beautician::beauticians.beautician'))

    @component('admin::components.table')
        @slot('thead')
            <tr>
                @include('admin::partials.table.select_all')

                <th>{{ trans('admin::admin.table.id') }}</th>
                <th>{{ trans('beautician::beauticians.table.profile') }}</th>
                <th>{{ trans('beautician::beauticians.table.name') }}</th>
                <th>{{ trans('beautician::beauticians.table.job_title') }}</th>
                <th>{{ trans('beautician::beauticians.table.phone') }}</th>
                @if (is_module_enabled('SpaBranch'))
                    <th>{{ trans('beautician::beauticians.table.branches') }}</th>
                @endif
                <th>{{ trans('beautician::beauticians.table.status') }}</th>
                <th data-sort>{{ trans('admin::admin.table.created') }}</th>
            </tr>
        @endslot
    @endcomponent
@endcomponent

@push('styles')
    <style>
        .beautician-table-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            color: #fff;
            font-weight: 700;
            font-size: 13px;
        }
    </style>
@endpush

@push('scripts')
    <script type="module">
        new DataTable('#beauticians-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'profile', orderable: false, searchable: false, width: '5%' },
                { data: 'name', name: 'name' },
                { data: 'job_title', name: 'job_title', defaultContent: '' },
                { data: 'phone', name: 'phone', defaultContent: '' },
                @if (is_module_enabled('SpaBranch'))
                { data: 'branches', name: 'branches', orderable: false, searchable: false, defaultContent: '' },
                @endif
                { data: 'status', name: 'is_active', orderable: false, searchable: false },
                { data: 'created', name: 'created_at', width: '20%' },
            ],
        });
    </script>
@endpush
