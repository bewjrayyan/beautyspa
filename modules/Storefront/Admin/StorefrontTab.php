<?php

namespace Modules\Storefront\Admin;

use Modules\Admin\Ui\Tab;

class StorefrontTab extends Tab
{
    /**
     * Font Awesome 4.7 icon classes.
     *
     * @var array<string, string>
     */
    private const NAV_ICONS = [
        'general' => 'fa-sliders',
        'logo' => 'fa-image',
        'menus' => 'fa-bars',
        'footer' => 'fa-columns',
        'newsletter' => 'fa-envelope-o',
        'features' => 'fa-star',
        'product_page' => 'fa-cube',
        'social_links' => 'fa-share-alt',
        'slider_banners' => 'fa-picture-o',
        'three_column_full_width_banners' => 'fa-th-large',
        'featured_categories' => 'fa-folder-open',
        'product_tabs_one' => 'fa-th-list',
        'top_brands' => 'fa-tags',
        'flash_sale_and_vertical_products' => 'fa-bolt',
        'two_column_banners' => 'fa-columns',
        'product_grid' => 'fa-th',
        'three_column_banners' => 'fa-th-large',
        'product_tabs_two' => 'fa-list-alt',
        'one_column_banner' => 'fa-window-maximize',
        'google_reviews' => 'fa-google',
        'blogs' => 'fa-newspaper-o',
        'mobile_home_promo' => 'fa-mobile',
    ];

    protected function navIcon(): ?string
    {
        return self::NAV_ICONS[$this->name] ?? 'fa-cog';
    }

    public function getMainView($data = [])
    {
        $content = parent::getMainView($data);

        if ($this->hasCustomLayoutMarkup($content)) {
            return $content;
        }

        return view('storefront::admin.storefront.partials.tab-shell', [
            'content' => $content,
            'lead' => $this->resolveLead(),
        ])->render();
    }

    protected function hasCustomLayoutMarkup(string $content): bool
    {
        return str_contains($content, 'st-tab')
            || str_contains($content, 'st-fields-grid--sections');
    }

    protected function resolveLead(): ?string
    {
        $key = "storefront::storefront.tab_leads.{$this->name}";
        $lead = trans($key);

        return $lead === $key ? null : $lead;
    }
}
