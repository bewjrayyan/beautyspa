<?php

namespace Modules\Page\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Modules\Page\Entities\Page;

class LegalContentController
{
    private const SLUGS = ['terms-conditions', 'privacy-policy'];

    public function index(): View
    {
        $pages = Page::query()
            ->withoutGlobalScope('active')
            ->whereIn('slug', self::SLUGS)
            ->get()
            ->sortBy(fn (Page $page): int => array_search($page->slug, self::SLUGS, true))
            ->values();

        return view('page::admin.legal_content.index', compact('pages'));
    }
}
