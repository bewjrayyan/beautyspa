@extends('storefront::public.account.layout')

@section('title', trans('account::consultation.pages.title'))

@section('account_breadcrumb')
    <li class="active">{{ trans('account::consultation.pages.title') }}</li>
@endsection

@section('panel')
    <div class="consultation-page">
        <header class="consultation-page__hero">
            <div class="consultation-page__hero-icon"><i class="las la-notes-medical"></i></div>
            <div>
                <h1>{{ trans('account::consultation.pages.title') }}</h1>
                <p>{{ trans('account::consultation.intro') }}</p>
            </div>
        </header>

        <section class="consultation-section">
            <div class="consultation-section__heading">
                <h2>{{ trans('account::consultation.pending_title') }}</h2>
                @if ($pendingForms->isNotEmpty())<span>{{ $pendingForms->count() }}</span>@endif
            </div>

            <div class="consultation-card-list">
                @forelse ($pendingForms as $consultation)
                    <article class="consultation-card consultation-card--pending">
                        <div class="consultation-card__mark"><i class="las la-clipboard-list"></i></div>
                        <div class="consultation-card__body">
                            <span class="consultation-status is-pending">{{ trans('account::consultation.pending') }}</span>
                            <h3>{{ $consultation->form_title }}</h3>
                            @include('storefront::public.account.consultations.partials.card-details', [
                                'consultation' => $consultation,
                            ])
                        </div>
                        <a class="btn btn-primary" href="{{ route('account.consultations.form', $consultation) }}">
                            {{ trans('account::consultation.complete_now') }}
                        </a>
                    </article>
                @empty
                    <div class="consultation-empty-state">
                        <i class="las la-check-circle"></i>
                        <p>{{ trans('account::consultation.all_complete') }}</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="consultation-section">
            <div class="consultation-section__heading"><h2>{{ trans('account::consultation.history_title') }}</h2></div>

            <div class="consultation-card-list">
                @forelse ($submissions as $submission)
                    <article class="consultation-card">
                        <div class="consultation-card__mark is-complete"><i class="las la-file-signature"></i></div>
                        <div class="consultation-card__body">
                            <span class="consultation-status is-complete">{{ trans('account::consultation.complete') }}</span>
                            <h3>{{ $submission->form_title }}</h3>
                            @include('storefront::public.account.consultations.partials.card-details', [
                                'consultation' => $submission,
                                'showSubmitted' => true,
                            ])
                        </div>
                        <div class="consultation-card__actions">
                            <a class="btn btn-default" href="{{ route('account.consultations.show_submission', $submission) }}">{{ trans('account::consultation.view_record') }}</a>
                            <a class="btn btn-default" href="{{ route('account.consultations.download', $submission) }}"><i class="las la-file-pdf"></i> {{ trans('account::consultation.download_pdf') }}</a>
                        </div>
                    </article>
                @empty
                    <div class="consultation-empty-state"><p>{{ trans('account::consultation.no_history') }}</p></div>
                @endforelse
            </div>

            @if ($submissions->hasPages())
                <div class="pagination-wrapper">{{ $submissions->links() }}</div>
            @endif
        </section>
    </div>
@endsection
