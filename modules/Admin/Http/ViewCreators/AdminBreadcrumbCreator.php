<?php

namespace Modules\Admin\Http\ViewCreators;

use Illuminate\View\View;
use Modules\Admin\Sidebar\AdminBreadcrumbTrail;

class AdminBreadcrumbCreator
{
    public function __construct(protected AdminBreadcrumbTrail $trail)
    {
    }

    public function create(View $view): void
    {
        $view->with('adminBreadcrumb', $this->trail->crumbs());
    }
}
