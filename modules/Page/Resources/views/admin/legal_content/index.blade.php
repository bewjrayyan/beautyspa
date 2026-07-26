@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('page::pages.legal.title'))
    @slot('subtitle', trans('page::pages.legal.subtitle'))
    <li class="active">{{ trans('page::pages.legal.title') }}</li>
@endcomponent

@section('content')
    <div class="legal-content-hub">
        <header class="legal-content-hub__hero">
            <span><i class="fa fa-shield" aria-hidden="true"></i></span>
            <div>
                <h2>{{ trans('page::pages.legal.heading') }}</h2>
                <p>{{ trans('page::pages.legal.help') }}</p>
            </div>
        </header>

        <div class="legal-content-hub__grid">
            @foreach ($pages as $page)
                <article class="legal-content-card">
                    <div class="legal-content-card__icon">
                        <i class="fa {{ $page->slug === 'privacy-policy' ? 'fa-lock' : 'fa-file-text-o' }}" aria-hidden="true"></i>
                    </div>
                    <div class="legal-content-card__body">
                        <span>{{ $page->is_active ? trans('page::pages.legal.published') : trans('page::pages.legal.draft') }}</span>
                        <h3>{{ $page->name }}</h3>
                        <p>{{ trans('page::pages.legal.updated', ['date' => $page->updated_at?->format('d M Y, H:i')]) }}</p>
                        <small>{{ localized_url(locale(), $page->slug) }}</small>
                    </div>
                    <div class="legal-content-card__actions">
                        <a class="btn btn-default" href="{{ localized_url(locale(), $page->slug) }}" target="_blank" rel="noopener">
                            <i class="fa fa-external-link"></i> {{ trans('page::pages.legal.preview') }}
                        </a>
                        @can('admin.pages.edit')
                            <a class="btn btn-primary" href="{{ route('admin.pages.edit', $page) }}">
                                <i class="fa fa-pencil"></i> {{ trans('page::pages.legal.edit') }}
                            </a>
                        @endcan
                    </div>
                </article>
            @endforeach
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .legal-content-hub{max-width:1180px;margin:0 auto 45px}.legal-content-hub__hero{display:flex;align-items:center;gap:18px;margin-bottom:20px;padding:25px;border:1px solid #eadfe6;border-radius:17px;background:linear-gradient(135deg,#fff4fa,#fff);box-shadow:0 10px 30px rgba(50,24,39,.05)}.legal-content-hub__hero>span{display:grid;place-items:center;flex:0 0 54px;width:54px;height:54px;border-radius:15px;color:#fff;background:linear-gradient(145deg,#b50863,#76033f);font-size:24px}.legal-content-hub__hero h2{margin:0 0 5px;font-size:23px}.legal-content-hub__hero p{margin:0;color:#766c72}.legal-content-hub__grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.legal-content-card{display:flex;align-items:center;gap:16px;padding:21px;border:1px solid #e8e1e5;border-radius:15px;background:#fff;box-shadow:0 7px 24px rgba(40,25,34,.05)}.legal-content-card__icon{display:grid;place-items:center;flex:0 0 48px;width:48px;height:48px;border-radius:13px;color:#9c0756;background:#fbe8f2;font-size:21px}.legal-content-card__body{flex:1;min-width:0}.legal-content-card__body>span{display:inline-block;margin-bottom:5px;padding:4px 7px;border-radius:999px;color:#087447;background:#dcf7ea;font-size:9px;font-weight:800;text-transform:uppercase}.legal-content-card__body h3{margin:0 0 4px;font-size:17px}.legal-content-card__body p,.legal-content-card__body small{display:block;margin:0;color:#7c7178}.legal-content-card__body small{overflow:hidden;margin-top:4px;text-overflow:ellipsis;white-space:nowrap}.legal-content-card__actions{display:flex;flex-direction:column;gap:7px}.legal-content-card__actions .btn{min-width:105px;border-radius:8px}@media(max-width:900px){.legal-content-hub__grid{grid-template-columns:1fr}}@media(max-width:600px){.legal-content-card,.legal-content-hub__hero{align-items:flex-start;flex-direction:column}.legal-content-card__actions{width:100%}.legal-content-card__actions .btn{width:100%}}
    </style>
@endpush
