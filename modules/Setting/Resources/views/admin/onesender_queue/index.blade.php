@extends('admin::layout')

@section('title', trans('setting::settings.onesender_queue.title'))

@push('globals')
    @vite(['modules/Setting/Resources/assets/admin/sass/main.scss'])
@endpush

@section('content_header')
    <h3>{{ trans('setting::settings.onesender_queue.title') }}</h3>

    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard.index') }}">{{ trans('admin::dashboard.dashboard') }}</a></li>
        <li><a href="{{ route('admin.settings.edit') }}?tab=sms">{{ trans('setting::settings.settings') }}</a></li>
        <li class="active">{{ trans('setting::settings.onesender_queue.title') }}</li>
    </ol>
@endsection

@section('content')
    @php
        $activeStatus = request('status');
        $showingFrom = $messages->count() > 0 ? $messages->firstItem() : 0;
        $showingTo = $messages->count() > 0 ? $messages->lastItem() : 0;
    @endphp

    <div class="osq-page">
        <section class="osq-hero">
            <div class="osq-hero__copy">
                <h4 class="osq-hero__title">{{ trans('setting::settings.onesender_queue.title') }}</h4>
                <p class="osq-hero__intro">{{ trans('setting::settings.onesender_queue.intro') }}</p>

                @if ($pendingCount > 0)
                    <div class="osq-hero__alert">
                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                        <div>
                            <p>{{ trans('setting::settings.onesender_queue.pending_count', ['count' => $pendingCount]) }}</p>
                            <p>{{ trans('setting::settings.onesender_queue.scheduler_hint') }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="osq-hero__actions">
                <a href="{{ route('admin.onesender_logs.index') }}" class="btn btn-default btn-sm">
                    <i class="fa fa-list-alt" aria-hidden="true"></i>
                    {{ trans('setting::settings.form.view_onesender_logs') }}
                </a>

                @if ($pendingCount > 0)
                    <form method="POST" action="{{ route('admin.onesender_queue.process_due') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa fa-play" aria-hidden="true"></i>
                            {{ trans('setting::settings.onesender_queue.process_due') }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.onesender_queue.cancel_all') }}" style="display: inline;"
                        onsubmit="return confirm(@js(trans('setting::settings.onesender_queue.cancel_all_confirm')));">
                        @csrf
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="fa fa-stop" aria-hidden="true"></i>
                            {{ trans('setting::settings.onesender_queue.cancel_all') }}
                        </button>
                    </form>
                @endif
            </div>
        </section>

        <section class="osq-stats" aria-label="{{ trans('setting::settings.onesender_queue.stats_label') }}">
            <a href="{{ route('admin.onesender_queue.index') }}"
                class="osq-stats__card {{ $activeStatus === null || $activeStatus === '' ? 'osq-stats__card--active' : '' }}">
                <span class="osq-stats__label">{{ trans('setting::settings.onesender_queue.all_statuses') }}</span>
                <strong class="osq-stats__value">{{ number_format(array_sum($statusCounts)) }}</strong>
            </a>

            @foreach ($statuses as $statusOption)
                <a href="{{ route('admin.onesender_queue.index', array_filter([
                    'status' => $statusOption,
                    'recipient' => request('recipient'),
                    'source' => request('source'),
                ])) }}"
                    class="osq-stats__card osq-stats__card--{{ $statusOption }} {{ $activeStatus === $statusOption ? 'osq-stats__card--active' : '' }}">
                    <span class="osq-stats__label">{{ trans('setting::settings.onesender_queue.statuses.' . $statusOption) }}</span>
                    <strong class="osq-stats__value">{{ number_format($statusCounts[$statusOption] ?? 0) }}</strong>
                </a>
            @endforeach
        </section>

        <section class="osq-toolbar">
            <form method="GET" action="{{ route('admin.onesender_queue.index') }}" class="osq-toolbar__filters">
                <div class="osq-toolbar__field">
                    <label for="osq-filter-status">{{ trans('setting::settings.onesender_queue.status') }}</label>
                    <select name="status" id="osq-filter-status" class="form-control">
                        <option value="">{{ trans('setting::settings.onesender_queue.all_statuses') }}</option>
                        @foreach ($statuses as $statusOption)
                            <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>
                                {{ trans('setting::settings.onesender_queue.statuses.' . $statusOption) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="osq-toolbar__field">
                    <label for="osq-filter-recipient">{{ trans('setting::settings.onesender_queue.recipient') }}</label>
                    <input type="text" name="recipient" id="osq-filter-recipient" class="form-control"
                        placeholder="{{ trans('setting::settings.onesender_queue.recipient') }}"
                        value="{{ request('recipient') }}">
                </div>

                <div class="osq-toolbar__field">
                    <label for="osq-filter-source">{{ trans('setting::settings.onesender_queue.source') }}</label>
                    <input type="text" name="source" id="osq-filter-source" class="form-control"
                        placeholder="{{ trans('setting::settings.onesender_queue.source') }}"
                        value="{{ request('source') }}">
                </div>

                <div class="osq-toolbar__actions">
                    <button type="submit" class="btn btn-default">{{ trans('setting::settings.onesender_queue.filter') }}</button>
                    @if ($hasActiveFilters)
                        <a href="{{ route('admin.onesender_queue.index') }}" class="btn btn-link">{{ trans('setting::settings.onesender_queue.reset') }}</a>
                    @endif
                </div>
            </form>

            <p class="osq-toolbar__summary">
                {{ trans('setting::settings.onesender_queue.results_summary', [
                    'from' => $showingFrom,
                    'to' => $showingTo,
                    'total' => $filteredCount,
                ]) }}
                @if ($hasActiveFilters)
                    · {{ trans('setting::settings.onesender_queue.results_filtered') }}
                @endif
            </p>
        </section>

        <div class="osq-table-wrap">
            @if ($deletableTotalCount > 0)
                <div class="osq-bulk">
                    <label class="osq-bulk__label" for="osq-bulk-action">{{ trans('setting::settings.onesender_queue.bulk_actions') }}</label>
                    <select id="osq-bulk-action" class="form-control osq-bulk__select">
                        <option value="">{{ trans('setting::settings.onesender_queue.bulk_choose_action') }}</option>
                        @if ($deletableFilteredCount > 0)
                            <option value="delete_filtered">
                                {{ trans('setting::settings.onesender_queue.delete_filtered') }} ({{ number_format($deletableFilteredCount) }})
                            </option>
                        @endif
                        <option value="delete_all">
                            {{ trans('setting::settings.onesender_queue.delete_all') }} ({{ number_format($deletableTotalCount) }})
                        </option>
                    </select>
                    <button type="button" class="btn btn-default btn-sm osq-bulk__apply" id="osq-bulk-apply">
                        {{ trans('setting::settings.onesender_queue.bulk_apply') }}
                    </button>

                    <form id="osq-bulk-form-delete-filtered" method="POST" action="{{ route('admin.onesender_queue.destroy_filtered') }}" hidden>
                        @csrf
                        <input type="hidden" name="status" value="{{ request('status') }}">
                        <input type="hidden" name="recipient" value="{{ request('recipient') }}">
                        <input type="hidden" name="source" value="{{ request('source') }}">
                    </form>

                    <form id="osq-bulk-form-delete-all" method="POST" action="{{ route('admin.onesender_queue.destroy_all') }}" hidden>
                        @csrf
                    </form>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table osq-table">
                    <thead>
                        <tr>
                            <th>{{ trans('setting::settings.onesender_queue.schedule') }}</th>
                            <th>{{ trans('setting::settings.onesender_queue.status') }}</th>
                            <th>{{ trans('setting::settings.onesender_queue.recipient') }}</th>
                            <th>{{ trans('setting::settings.onesender_queue.source') }}</th>
                            <th>{{ trans('setting::settings.onesender_queue.preview') }}</th>
                            <th class="text-right">{{ trans('setting::settings.onesender_queue.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($messages as $message)
                            <tr>
                                <td class="osq-table__schedule">
                                    <span class="osq-table__schedule-line">{{ $message->created_at?->format('Y-m-d H:i') }}</span>
                                    <span class="osq-table__schedule-line osq-table__schedule-line--muted">
                                        {{ trans('setting::settings.onesender_queue.send_at_short', ['time' => $message->scheduled_at?->format('Y-m-d H:i')]) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="osq-status osq-status--{{ $message->status }}">
                                        {{ trans('setting::settings.onesender_queue.statuses.' . $message->status) }}
                                    </span>
                                    @if ($message->error_message)
                                        <span class="osq-error" title="{{ $message->error_message }}">
                                            {{ \Illuminate\Support\Str::limit($message->error_message, 120) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="osq-table__recipient">
                                    <code>{{ $message->recipient }}</code>
                                    @if ($message->message_type)
                                        <span class="osq-table__type">{{ $message->message_type }}</span>
                                    @endif
                                </td>
                                <td class="osq-table__source">{{ $message->source ?: '—' }}</td>
                                <td class="osq-table__preview">
                                    <p class="osq-table__preview-text">{{ $message->message_preview }}</p>
                                    @if ($message->dedupe_key)
                                        <div class="osq-table__meta">
                                            <strong>{{ trans('setting::settings.onesender_queue.dedupe_key') }}:</strong>
                                            {{ $message->dedupe_key }}
                                        </div>
                                    @endif
                                </td>
                                <td class="osq-table__actions">
                                    <div class="osq-row-actions">
                                        @if ($message->canBeCancelled())
                                            <form method="POST" action="{{ route('admin.onesender_queue.cancel', $message) }}" style="display: inline;"
                                                onsubmit="return confirm(@js(trans('setting::settings.onesender_queue.cancel_confirm')));">
                                                @csrf
                                                <button type="submit" class="btn btn-warning btn-xs" title="{{ trans('setting::settings.onesender_queue.cancel') }}">
                                                    <i class="fa fa-stop"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if ($message->canBeDeleted())
                                            <form method="POST" action="{{ route('admin.onesender_queue.destroy', $message) }}" style="display: inline;"
                                                onsubmit="return confirm(@js(trans('setting::settings.onesender_queue.delete_confirm')));">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs" title="{{ trans('setting::settings.onesender_queue.delete') }}">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="osq-table__empty">
                                    <p>{{ trans('setting::settings.onesender_queue.empty') }}</p>
                                    <p>{{ trans('setting::settings.onesender_queue.empty_hint') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($messages->hasPages())
                <div class="osq-pagination">
                    {{ $messages->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const select = document.getElementById('osq-bulk-action');
            const applyBtn = document.getElementById('osq-bulk-apply');

            if (!select || !applyBtn) {
                return;
            }

            const confirms = {
                delete_filtered: @json(trans('setting::settings.onesender_queue.delete_filtered_confirm', ['count' => $deletableFilteredCount ?? 0])),
                delete_all: @json(trans('setting::settings.onesender_queue.delete_all_confirm')),
            };

            const forms = {
                delete_filtered: document.getElementById('osq-bulk-form-delete-filtered'),
                delete_all: document.getElementById('osq-bulk-form-delete-all'),
            };

            applyBtn.addEventListener('click', function () {
                const action = select.value;

                if (!action || !forms[action]) {
                    return;
                }

                if (!window.confirm(confirms[action])) {
                    return;
                }

                forms[action].submit();
            });
        })();
    </script>
@endpush
