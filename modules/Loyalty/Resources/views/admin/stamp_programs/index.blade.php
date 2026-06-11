@extends('admin::layout')

@component('admin::components.page.header')
    @slot('title', trans('loyalty::stamp_programs.programs'))

    <li class="active">{{ trans('loyalty::stamp_programs.programs') }}</li>
@endcomponent

@section('content')
    <div class="loyalty-admin loyalty-stamp-programs">
        <header class="loyalty-page-hero loyalty-page-hero--tiers">
            <div class="loyalty-page-hero__main">
                <h1 class="loyalty-page-hero__title">
                    <i class="fa fa-ticket" aria-hidden="true"></i>
                    {{ trans('loyalty::stamp_programs.programs') }}
                </h1>
                <p class="loyalty-page-hero__lead">{{ trans('loyalty::stamp_programs.index.lead') }}</p>
            </div>
            <div class="loyalty-page-hero__actions">
                @hasAccess('admin.loyalty.stamp_programs.create')
                    <a href="{{ route('admin.loyalty.stamp_programs.create') }}" class="btn btn-primary loyalty-page-hero__btn">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        {{ trans('admin::resource.create', ['resource' => trans('loyalty::stamp_programs.program')]) }}
                    </a>
                @endHasAccess
            </div>
        </header>

        <div class="loyalty-page-stats loyalty-page-stats--3">
            <div class="loyalty-page-stats__stat">
                <span class="loyalty-page-stats__icon loyalty-page-stats__icon--primary">
                    <i class="fa fa-th-large" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-page-stats__label">{{ trans('loyalty::stamp_programs.index.stats_total') }}</span>
                    <strong class="loyalty-page-stats__value">{{ number_format($stats['total']) }}</strong>
                </div>
            </div>
            <div class="loyalty-page-stats__stat">
                <span class="loyalty-page-stats__icon loyalty-page-stats__icon--success">
                    <i class="fa fa-check-circle" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-page-stats__label">{{ trans('loyalty::stamp_programs.index.stats_active') }}</span>
                    <strong class="loyalty-page-stats__value">{{ number_format($stats['active']) }}</strong>
                </div>
            </div>
            <div class="loyalty-page-stats__stat">
                <span class="loyalty-page-stats__icon loyalty-page-stats__icon--info">
                    <i class="fa fa-id-card-o" aria-hidden="true"></i>
                </span>
                <div>
                    <span class="loyalty-page-stats__label">{{ trans('loyalty::stamp_programs.index.stats_active_cards') }}</span>
                    <strong class="loyalty-page-stats__value">{{ number_format($stats['active_cards']) }}</strong>
                </div>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-body index-table">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ trans('loyalty::stamp_programs.form.name') }}</th>
                            <th>{{ trans('loyalty::stamp_programs.form.stamps_required') }}</th>
                            <th>{{ trans('loyalty::stamp_programs.form.validity_days') }}</th>
                            <th>{{ trans('loyalty::stamp_programs.index.members') }}</th>
                            <th>{{ trans('admin::admin.table.status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($programs as $program)
                            <tr>
                                <td>
                                    <strong>{{ $program->name }}</strong>
                                    @if ($program->reward_description)
                                        <br><small class="text-muted">{{ $program->reward_description }}</small>
                                    @endif
                                </td>
                                <td>{{ $program->stamps_required }}</td>
                                <td>{{ trans('loyalty::stamp_programs.index.validity_days_value', ['days' => $program->validity_days]) }}</td>
                                <td>{{ number_format($program->wallets_count) }}</td>
                                <td>
                                    @if ($program->is_active)
                                        <span class="label label-success">{{ trans('admin::admin.table.active') }}</span>
                                    @else
                                        <span class="label label-default">{{ trans('admin::admin.table.inactive') }}</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @hasAccess('admin.loyalty.stamp_programs.edit')
                                        <a href="{{ route('admin.loyalty.stamp_programs.edit', $program) }}" class="btn btn-default btn-sm">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    @endHasAccess
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    {{ trans('loyalty::stamp_programs.index.empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    @vite(['modules/Loyalty/Resources/assets/admin/sass/main.scss'])
@endpush
