@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('blog::admin.blog_posts.name'))

    <li class="active">{{ trans('blog::admin.blog_posts.name') }}</li>
@endcomponent

@component('admin::components.page.index_table')
    @slot('buttons', ['create'])
    @slot('resource', 'blog_posts')
    @slot('name', trans('blog::admin.blog_post.name'))

    @component('admin::components.table')
        @slot('thead')
            <tr>
                @include('admin::partials.table.select_all')

                <th>{{ trans('admin::admin.table.id') }}</th>
                <th>{{ trans('blog::admin.blog_posts.table.featured_image') }}</th>
                <th>{{ trans('blog::admin.blog_posts.table.title') }}</th>
                <th>{{ trans('blog::admin.blog_posts.table.user') }}</th>
                <th>{{ trans('blog::admin.blog_posts.table.publish_status') }}</th>
                <th data-sort>{{ trans('admin::admin.table.created') }}</th>
            </tr>
        @endslot
    @endcomponent
@endcomponent


@push('scripts')
    <script type="module">
        DataTable.set('#blog_posts-table .table', {
            routePrefix: 'blog/posts',
            routes: {
                table: 'table',
                edit: 'edit',
                destroy: 'destroy'
            }
        });

        new DataTable('#blog_posts-table .table', {
            columns: [
                { data: 'checkbox', orderable: false, searchable: false, width: '3%' },
                { data: 'id', width: '5%' },
                { data: 'featured_image', name: 'featured_image', orderable : false },
                { data: 'title', name: 'translations.title', class: 'name' },
                { data: 'user', name: 'user', orderable: false },
                { data: 'publish_status', name: 'publish_status', orderable: false, defaultContent: '' },
                { data: 'created', name: 'created_at' },
            ],
        });
    </script>
@endpush
