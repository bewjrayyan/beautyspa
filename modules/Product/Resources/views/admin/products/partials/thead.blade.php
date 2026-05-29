<tr>
    @include('admin::partials.table.select_all')

    <th>{{ trans('product::products.table.thumbnail') }}</th>
    <th>{{ trans('product::products.table.name') }}</th>
    <th>{{ trans('product::products.table.price') }}</th>
    <th>{{ trans('product::products.table.stock') }}</th>
    <th data-sort>{{ trans('admin::admin.table.updated') }}</th>
    <th class="text-center">{{ trans('product::products.table.actions') }}</th>
</tr>
