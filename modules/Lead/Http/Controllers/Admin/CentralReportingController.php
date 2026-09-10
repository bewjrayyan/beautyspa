<?php

declare(strict_types=1);

namespace Modules\Lead\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Lead\Services\CentralMetricsService;
use Modules\Lead\Services\CentralReportingService;

final class CentralReportingController
{
    public function __invoke(Request $request, CentralReportingService $reports, CentralMetricsService $metrics): JsonResponse
    {
        $filters = $request->validate([
            'view' => 'required|in:beauticians,branches,audit',
            'period' => 'nullable|date_format:Y-m',
            'branch' => 'nullable|integer|min:1|exists:spa_branches,id',
            'q' => 'nullable|string|max:150',
            'page' => 'nullable|integer|min:1',
        ]);
        [$from, $to] = $metrics->resolvePeriod($filters['period'] ?? null);
        $branch = isset($filters['branch']) ? (int) $filters['branch'] : null;
        $data = $filters['view'] === 'audit'
            ? $reports->audit($from, $to, $branch, trim($filters['q'] ?? ''), (int) ($filters['page'] ?? 1))
            : $reports->performance($filters['view'], $from, $to, $branch);
        $data['meta']['period'] = $from->format('M Y');
        return response()->json($data);
    }
}
