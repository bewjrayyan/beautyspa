@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('product::consultation_forms.title'))
    @slot('subtitle', trans('product::consultation_forms.subtitle'))
    <li class="active">{{ trans('product::consultation_forms.title') }}</li>
@endcomponent

@section('content')
    <div class="consultation-library">
        <section class="consultation-library__hero">
            <div>
                <span class="consultation-library__eyebrow">{{ trans('product::consultation_forms.overview') }}</span>
                <h2>{{ trans('product::consultation_forms.title') }}</h2>
                <p>{{ trans('product::consultation_forms.subtitle') }}</p>
            </div>
            <div class="consultation-library__stats">
                <article><strong>{{ $templates->sum('pending_count') }}</strong><span>{{ trans('account::consultation.admin.pending') }}</span></article>
                <article><strong>{{ $templates->sum('completed_count') }}</strong><span>{{ trans('account::consultation.admin.completed') }}</span></article>
                <article><strong>{{ $templates->where('is_active', true)->count() }}</strong><span>{{ trans('product::consultation_forms.active_forms') }}</span></article>
            </div>
        </section>

        <section class="consultation-library__panel">
            <div class="consultation-library__section-head">
                <div>
                    <h3>{{ trans('product::consultation_forms.template_title') }}</h3>
                    <p>{{ trans('product::consultation_forms.template_help') }}</p>
                </div>
            </div>

            <div class="consultation-library__list">
                @foreach ($templates as $template)
                    <article class="consultation-library__item">
                        <div class="consultation-library__identity">
                            <span class="consultation-library__icon"><i class="fa fa-file-text-o" aria-hidden="true"></i></span>
                            <div>
                                <span class="consultation-library__label">{{ trans('product::consultation_forms.template') }}</span>
                                <h3>{{ $template->name }}</h3>
                                <p>{{ $template->title }}</p>
                            </div>
                        </div>
                        <div class="consultation-library__meta">
                            <span>{{ trans_choice('product::consultation_forms.questions', count($template->questions ?: []), ['count' => count($template->questions ?: [])]) }}</span>
                            <span>{{ trans('product::consultation_forms.version', ['version' => $template->version]) }}</span>
                            <span class="consultation-library__status {{ $template->is_active ? 'is-active' : '' }}">
                                {{ trans('product::consultation_forms.'.($template->is_active ? 'enabled' : 'disabled')) }}
                            </span>
                        </div>
                        <div class="consultation-library__actions">
                            <a class="btn btn-default btn-sm" href="{{ route('admin.consultation_forms.show', $template) }}" target="_blank">
                                <i class="fa fa-eye" aria-hidden="true"></i>
                                {{ trans('product::consultation_forms.view_form') }}
                            </a>
                            <a class="btn btn-primary btn-sm" href="{{ route('admin.consultation_forms.edit', $template) }}">
                                <i class="fa fa-pencil" aria-hidden="true"></i>
                                {{ trans('product::consultation_forms.configure') }}
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="consultation-library__panel consultation-library__requests">
            <div class="consultation-library__section-head">
                <div>
                    <h3>{{ trans('product::consultation_forms.recent_requests') }}</h3>
                    <p>{{ trans('product::consultation_forms.recent_requests_help') }}</p>
                </div>
            </div>
            <div class="consultation-library__list">
                @forelse ($recentRequests as $requestItem)
                    @php $requestContext = app(\Modules\Account\Services\ConsultationContextService::class)->forDisplay($requestItem); @endphp
                    <article class="consultation-library__item consultation-library__item--request">
                        <div class="consultation-library__identity">
                            <span class="consultation-library__icon"><i class="fa fa-user" aria-hidden="true"></i></span>
                            <div>
                                <h3>{{ $requestItem->customer_name ?: $requestItem->customer_email ?: '—' }}</h3>
                                <p>
                                    {{ $requestContext['treatment_name'] ?: '—' }}
                                    @if ($requestContext['beautician_name'] ?? null) · {{ $requestContext['beautician_name'] }} @endif
                                </p>
                            </div>
                        </div>
                        <div class="consultation-library__meta">
                            <span>{{ $requestItem->sent_at?->format('d M Y, H:i') }}</span>
                            <span class="consultation-library__status {{ $requestItem->isCompleted() ? 'is-active' : '' }}">
                                {{ $requestItem->isCompleted() ? trans('account::consultation.complete') : trans('account::consultation.admin.pending') }}
                            </span>
                        </div>
                        @if ($requestItem->isCompleted())
                            <a class="btn btn-default" href="{{ route('admin.users.consultations.download', $requestItem) }}">
                                <i class="fa fa-file-pdf-o"></i> {{ trans('account::consultation.download_pdf') }}
                            </a>
                        @else
                            <span></span>
                        @endif
                    </article>
                @empty
                    <div class="consultation-library__empty">{{ trans('product::consultation_forms.no_requests') }}</div>
                @endforelse
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .consultation-library{max-width:1320px;margin:0 auto 40px}.consultation-library__hero{display:flex;align-items:center;justify-content:space-between;gap:28px;margin-bottom:22px;padding:28px;border:1px solid #eadfe6;border-radius:18px;background:linear-gradient(135deg,#fff6fb,#fff);box-shadow:0 12px 34px rgba(44,24,36,.06)}.consultation-library__eyebrow,.consultation-library__label{display:block;margin-bottom:5px;color:#a20759;font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase}.consultation-library__hero h2{margin:0 0 7px;font-size:26px}.consultation-library__hero p{max-width:620px;margin:0;color:#756d73}.consultation-library__stats{display:grid;grid-template-columns:repeat(3,minmax(110px,1fr));gap:10px}.consultation-library__stats article{padding:15px 17px;border:1px solid #eee5ea;border-radius:13px;background:#fff}.consultation-library__stats strong,.consultation-library__stats span{display:block}.consultation-library__stats strong{color:#a20759;font-size:24px}.consultation-library__stats span{margin-top:3px;color:#776f74;font-size:11px}.consultation-library__panel{overflow:hidden;margin-bottom:20px;border:1px solid #e6e1e4;border-radius:16px;background:#fff;box-shadow:0 8px 28px rgba(35,26,31,.05)}.consultation-library__section-head{padding:20px 22px;border-bottom:1px solid #eee9ec;background:#fbfafb}.consultation-library__section-head h3{margin:0 0 4px;font-size:17px}.consultation-library__section-head p{margin:0;color:#7c7379}.consultation-library__list{padding:10px 20px}.consultation-library__item{display:grid;grid-template-columns:minmax(260px,1fr) auto auto;align-items:center;gap:24px;padding:17px 4px;border-bottom:1px solid #eee9ec}.consultation-library__item:last-child{border-bottom:0}.consultation-library__identity{display:flex;align-items:center;gap:14px;min-width:0}.consultation-library__icon{display:grid;place-items:center;flex:0 0 44px;width:44px;height:44px;border-radius:12px;color:#a20759;background:#fbe7f2;font-size:17px}.consultation-library__identity h3{overflow:hidden;margin:0 0 3px;font-size:15px;text-overflow:ellipsis;white-space:nowrap}.consultation-library__identity p{margin:0;color:#80777d;font-size:12px}.consultation-library__meta{display:flex;align-items:center;gap:8px;flex-wrap:wrap}.consultation-library__meta>span{padding:6px 9px;border-radius:999px;color:#6f676c;background:#f5f3f4;font-size:11px}.consultation-library__status.is-active{color:#087447!important;background:#dcf7ea!important}.consultation-library__item .btn{display:inline-flex;align-items:center;gap:7px;border-radius:9px}.consultation-library__empty{padding:36px 20px;color:#857c82;text-align:center}@media(max-width:991px){.consultation-library__hero{align-items:stretch;flex-direction:column}.consultation-library__item{grid-template-columns:1fr}.consultation-library__item .btn{justify-self:start}}@media(max-width:600px){.consultation-library__hero{padding:20px}.consultation-library__stats{grid-template-columns:1fr}.consultation-library__list{padding:8px 16px}.consultation-library__item{gap:13px;padding:16px 0}}
    </style>
@endpush
