<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Modules\Product\Services\SearchTermService;

class SearchTermController
{
    public function __construct(private SearchTermService $searchTermService)
    {
    }

    public function destroy(): RedirectResponse
    {
        if (! auth()->user()->hasAnyAccess(['admin.storefront.edit', 'admin.reports.index'])) {
            abort(403);
        }

        $this->searchTermService->reset();

        return back()->withSuccess(trans('product::search_terms.reset_success'));
    }
}
