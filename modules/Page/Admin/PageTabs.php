<?php

namespace Modules\Page\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;

class PageTabs extends Tabs
{
    protected $buttonOffset = false;

    public function make()
    {
        $this->group('page_information', trans('page::pages.tabs.group.page_information'))
            ->active()
            ->add($this->general());
    }


    public function render($data = [])
    {
        $page = $data['page'] ?? null;

        return view('page::admin.pages.form_wrapper', [
            'contents' => $this->contents($data),
            'buttonOffset' => $this->buttonOffset,
            'page' => $page,
        ]);
    }


    private function general()
    {
        return tap(new Tab('general', trans('page::pages.tabs.general')), function (Tab $tab) {
            $tab->active();
            $tab->weight(5);
            $tab->fields([
                'name',
                'body',
                'is_active',
                'slug',
                'meta.meta_title',
                'meta.meta_description',
                'meta.og_image_id',
                'meta.meta_robots',
            ]);

            $tab->view(
                'page::admin.pages.tabs.general',
                [],
                fn () => '<div class="page-editor-main">',
                fn () => '</div>'
            );
        });
    }
}
