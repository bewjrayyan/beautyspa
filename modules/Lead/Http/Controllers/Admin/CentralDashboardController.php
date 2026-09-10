<?php

declare(strict_types=1);

namespace Modules\Lead\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Modules\Lead\Services\CentralMetricsService;
use Modules\Lead\Support\CentralThemePalette;
use Modules\Media\Entities\File;
use Modules\SpaBranch\Entities\SpaBranch;

final class CentralDashboardController
{
    public function __construct(
        private readonly CentralMetricsService $metrics,
    ) {
    }

    /** @var list<string> */
    public const VIEWS = [
        'overview',
        'leads',
        'import',
        'imports',
        'followup',
        'sales',
        'payments',
        'customers',
        'wallet',
        'checkin',
        'clearance',
        'beauticians',
        'branches',
        'audit',
    ];

    public function __invoke(Request $request, ?string $view = null): View|RedirectResponse
    {
        if ($view !== null && ! in_array($view, self::VIEWS, true)) {
            return redirect()->route('admin.leads.central');
        }

        $initialView = $view === null || $view === 'overview' ? 'overview' : $view;

        $user = $request->user();
        $name = trim((string) ($user?->full_name ?: $user?->first_name ?: 'Admin'));
        $initial = mb_strtoupper(mb_substr($name !== '' ? $name : 'A', 0, 1));

        [$from, $to] = $this->metrics->resolvePeriod($request->query('period'));
        // Invalid / inactive branch → fall back to all-branches for the page view.
        $branchId = $this->resolveActiveBranchId($request->query('branch'), soft: true);

        return view('lead::admin.central.index', [
            'profileName' => $name !== '' ? $name : 'Admin',
            'profileInitial' => $initial,
            'profileRole' => 'HQ Admin',
            'adminHomeUrl' => route('admin.dashboard.index'),
            'adminLogoUrl' => $this->adminSidebarLogoUrl(),
            'favicon' => storefront_favicon_url(),
            'faviconMime' => storefront_favicon_mime(),
            'faviconTouch' => storefront_favicon_touch_icon_url(),
            'sidebarTheme' => CentralThemePalette::sidebarFromSettings(),
            'metrics' => $this->metrics->build($from, $to, $branchId),
            'branches' => $this->metrics->branchOptions(),
            'centralBaseUrl' => url('admin/leads/central'),
            'initialView' => $initialView,
            'reportingUrl' => route('admin.leads.reporting'),
            'metricsUrl' => route('admin.leads.central.metrics'),
            'leadsUrl' => route('admin.leads.workspace.index'),
            'followUpsUrl' => route('admin.leads.followup.index'),
            'paymentsUrl' => route('admin.leads.payments.index'),
            'customersUrl' => route('admin.leads.customers.index'),
            'walletUrl' => route('admin.leads.wallet.index'),
            'checkinUrl' => route('admin.leads.checkin.index'),
            'clearanceUrl' => route('admin.leads.clearance.index'),
            'ordersIndexUrl' => route('admin.orders.index'),
            'orderShowUrlTemplate' => url('admin/orders/__ID__'),
            'orderPaymentStatusUrlTemplate' => url('admin/orders/__ID__/payment-status'),
            'userEditUrlTemplate' => url('admin/users/__ID__/edit'),
            'loyaltyMembersUrl' => route('admin.loyalty.members.index'),
            'loyaltyMemberShowUrlTemplate' => url('admin/loyalty/members/__ID__'),
            'treatmentReservationsUrl' => route('admin.treatment_reservations.index'),
            'leadStoreUrl' => route('admin.leads.workspace.store'),
            'leadShowUrlTemplate' => url('admin/leads/workspace/__ID__'),
            'leadStatusUrlTemplate' => url('admin/leads/workspace/__ID__/status'),
            'leadUpdateUrlTemplate' => url('admin/leads/workspace/__ID__'),
            'leadDestroyUrlTemplate' => url('admin/leads/workspace/__ID__'),
            'leadFollowUpUrlTemplate' => url('admin/leads/workspace/__ID__/follow-up'),
            'importHistoryUrl' => route('admin.leads.import.index'),
            'importPreviewUrl' => route('admin.leads.import.preview'),
            'importConfirmUrl' => route('admin.leads.import.confirm'),
            'canCreateLead' => $user?->hasAccess('admin.leads.create') ?? false,
            'canEditLead' => $user?->hasAccess('admin.leads.edit') ?? false,
            'canDeleteLead' => $user?->hasAccess('admin.leads.destroy') ?? false,
            'canViewOrder' => $user?->hasAccess('admin.orders.show') ?? false,
            'canEditOrder' => $user?->hasAccess('admin.orders.edit') ?? false,
            'canViewUser' => $user?->hasAccess('admin.users.edit') ?? false,
            'canShowLoyaltyMember' => $user?->hasAccess('admin.loyalty.members.show') ?? false,
            'canViewLoyalty' => $user?->hasAccess('admin.loyalty.members.index') ?? false,
            'canViewTreatments' => $user?->hasAccess('admin.treatment_reservations.index') ?? false,
            'selectedBranch' => $branchId,
            'selectedPeriod' => $from->format('Y-m'),
            'periodOptions' => $this->periodOptions(),
        ]);
    }

    public function metrics(Request $request): JsonResponse
    {
        [$from, $to] = $this->metrics->resolvePeriod($request->query('period'));
        $resolved = $this->resolveActiveBranchId($request->query('branch'), soft: false);

        if ($resolved === false) {
            return response()->json([
                'message' => trans('lead::central.overview.invalid_branch'),
            ], 422);
        }

        $branchId = $resolved;

        return response()->json([
            'metrics' => $this->metrics->build($from, $to, $branchId),
            'branches' => $this->metrics->branchOptions(),
        ]);
    }

    /**
     * @return list<array{value:string,label:string}>
     */
    private function periodOptions(): array
    {
        $now = now();
        $options = [];
        for ($i = 0; $i < 6; $i++) {
            $m = $now->copy()->subMonthsNoOverflow($i)->startOfMonth();
            $options[] = [
                'value' => $m->format('Y-m'),
                'label' => $i === 0
                    ? trans('lead::central.common.this_month') . ' (' . $m->format('M Y') . ')'
                    : $m->format('F Y'),
            ];
        }

        return $options;
    }

    /**
     * Parse branch query and ensure it exists as an active SpaBranch.
     *
     * @return int|null|false  null = all branches; int = valid id; false = invalid (JSON only)
     */
    private function resolveActiveBranchId(mixed $raw, bool $soft = true): int|null|false
    {
        if ($raw === null || $raw === '' || $raw === 'all') {
            return null;
        }

        $id = (int) $raw;
        if ($id <= 0) {
            return $soft ? null : false;
        }

        $exists = SpaBranch::query()
            ->where('is_active', true)
            ->whereKey($id)
            ->exists();

        if (! $exists) {
            return $soft ? null : false;
        }

        return $id;
    }

    private function adminSidebarLogoUrl(): ?string
    {
        $fileId = setting('admin_sidebar_logo') ?: setting('admin_logo');

        if (! $fileId) {
            return null;
        }

        $file = Cache::rememberForever(md5("files.{$fileId}"), function () use ($fileId) {
            return File::findOrNew($fileId);
        });

        $path = $file->path ?? null;

        return $path ? (string) $path : null;
    }
}
