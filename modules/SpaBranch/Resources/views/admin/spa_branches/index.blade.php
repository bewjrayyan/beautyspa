@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('spabranch::spa_branches.spa_branches'))

    <li class="active">{{ trans('spabranch::spa_branches.spa_branches') }}</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('buttons', ['create'])
    @slot('resource', 'spa_branches')
    @slot('name', trans('spabranch::spa_branches.spa_branch'))

    @component('admin::components.table')
        @slot('thead')
            <tr>
                @include('admin::partials.table.select_all')

                <th>{{ trans('admin::admin.table.id') }}</th>
                <th>{{ trans('spabranch::spa_branches.table.name') }}</th>
                <th>{{ trans('spabranch::spa_branches.table.code') }}</th>
                <th>{{ trans('spabranch::spa_branches.table.phone') }}</th>
                <th>{{ trans('admin::admin.table.status') }}</th>
                <th data-sort>{{ trans('admin::admin.table.created') }}</th>
            </tr>
        @endslot
    @endcomponent
@endcomponent

@push('scripts')
    <script type="module">
        new DataTable('#spa_branches-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'name', class: 'name' },
                { data: 'code' },
                { data: 'phone' },
                { data: 'status', name: 'is_active', searchable: false },
                { data: 'created', name: 'created_at' },
            ],
        });
    </script>
@endpush
