@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('media::media.media'))

    <li class="active">{{ trans('media::media.media') }}</li>
@endcomponent

@section('content')
    <div class="box m-b-0">
        <div class="box-body">
            @include('media::admin.media.partials.uploader')
            @include('media::admin.media.partials.grid')
        </div>
    </div>
@endsection

@push('shortcuts')
    <dl class="dl-horizontal">
        <dt><code>u</code></dt>
        <dd>{{ trans('media::media.upload_new_file') }}</dd>
    </dl>
@endpush

@push('globals')
    @vite([
        'modules/Media/Resources/assets/admin/sass/main.scss',
        'modules/Media/Resources/assets/admin/js/main.js'
    ])
@endpush

@push('scripts')
    <script type="module">
        Mousetrap.bind('u', function() {
            $('.dropzone').trigger('click');
        });

        Mousetrap.bind('del', function () {
            $('#media-grid .btn-delete').trigger('click');
        });
    </script>
@endpush
