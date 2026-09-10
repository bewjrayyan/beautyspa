<?php

declare(strict_types=1);

namespace Modules\Lead\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Lead\Entities\LeadImport;
use Modules\Lead\Http\Requests\Admin\ConfirmLeadImportRequest;
use Modules\Lead\Http\Requests\Admin\PreviewLeadImportRequest;
use Modules\Lead\Services\LeadImportService;

final class LeadImportController
{
    public function __construct(
        private readonly LeadImportService $imports,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->imports->paginate((int) $request->query('per_page', 25));
        $data = collect($paginator->items())
            ->map(fn (LeadImport $batch) => $this->imports->toArray($batch))
            ->values()
            ->all();

        return response()->json([
            'data' => $data,
            'meta' => [
                'summary' => $this->imports->historySummary(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function preview(PreviewLeadImportRequest $request): JsonResponse
    {
        $preview = $this->imports->preview($request->payload());

        return response()->json([
            'data' => $preview,
        ]);
    }

    public function confirm(ConfirmLeadImportRequest $request): JsonResponse
    {
        $result = $this->imports->confirm($request->payload());

        return response()->json([
            'message' => trans('lead::central.import.imported', [
                'count' => $result['imported'],
            ]),
            'data' => $result,
        ], 201);
    }
}
