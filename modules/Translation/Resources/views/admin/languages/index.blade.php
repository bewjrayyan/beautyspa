@extends('admin::layout')

@section('title', trans('translation::languages.languages'))

@component('admin::components.page.header')
    @slot('title', trans('translation::languages.languages'))

    <li class="active">{{ trans('translation::languages.languages') }}</li>
@endcomponent

@section('content')
    <div class="row">
        <div class="btn-group pull-right">
            <a href="{{ route("admin.languages.add") }}" class="btn btn-primary btn-actions btn-create">
                {{ trans("translation::languages.add_language") }}
            </a>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-body">
            <div id="app"></div>
        </div>
    </div>
@endsection

@push('globals')
    <script>
        AestheticCart.data['languages'] = @json($languages);
        AestheticCart.langs['translation::languages.table.name'] = '{{ trans('translation::languages.table.name') }}';
        AestheticCart.langs['translation::languages.table.default'] = '{{ trans('translation::languages.table.default') }}';
        AestheticCart.langs['translation::languages.table.actions'] = '{{ trans('translation::languages.table.actions') }}';
        AestheticCart.langs['translation::languages.table.translations'] = '{{ trans('translation::languages.table.translations') }}';
        AestheticCart.langs['translation::languages.table.delete'] = '{{ trans('translation::languages.table.delete') }}';
        AestheticCart.langs['translation::languages.default_language_updated'] = '{{ trans('translation::languages.default_language_updated') }}';
    </script>

    @vite([
        'modules/Translation/Resources/assets/admin/languages/js/main.js',
        'modules/Translation/Resources/assets/admin/languages/sass/main.scss',
    ])
@endpush
