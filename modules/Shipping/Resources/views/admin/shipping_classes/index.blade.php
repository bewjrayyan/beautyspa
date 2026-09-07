@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('shipping::shipping_classes.shipping_classes'))

    <li class="active">{{ trans('shipping::shipping_classes.shipping_classes') }}</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('buttons', ['create'])
    @slot('resource', 'shipping_classes')
    @slot('name', trans('shipping::shipping_classes.shipping_class'))

    @component('admin::components.table')
        @slot('thead')
            <tr>
                @include('admin::partials.table.select_all')

                <th>{{ trans('admin::admin.table.id') }}</th>
                <th>{{ trans('shipping::shipping_classes.table.name') }}</th>
                <th>{{ trans('shipping::shipping_classes.table.cost') }}</th>
                <th>{{ trans('admin::admin.table.status') }}</th>
                <th data-sort>{{ trans('admin::admin.table.created') }}</th>
            </tr>
        @endslot
    @endcomponent
@endcomponent

@push('scripts')
    <script type="module">
        new DataTable('#shipping_classes-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'name', name: 'translations.name', class: 'name', orderable: false, defaultContent: '' },
                { data: 'cost', name: 'cost', searchable: false },
                { data: 'status', name: 'is_active', searchable: false },
                { data: 'created', name: 'created_at' },
            ],
        });
    </script>
@endpush
