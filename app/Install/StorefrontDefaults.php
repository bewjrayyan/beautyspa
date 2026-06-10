<?php

namespace AestheticCart\Install;

use Modules\Category\Entities\Category;
use Modules\Setting\Entities\Setting;

class StorefrontDefaults
{
    public function apply(): void
    {
        Setting::setMany($this->settings());

        $this->assignFeaturedCategories();
    }


    /**
     * @return array<string, mixed>
     */
    private function settings(): array
    {
        $settings = [
            'storefront_features_section_enabled' => true,
            'storefront_featured_categories_section_enabled' => true,
            'storefront_product_tabs_1_section_enabled' => true,
            'storefront_product_grid_section_enabled' => true,
            'storefront_blogs_section_enabled' => true,
            'storefront_recent_blogs' => 5,
            'storefront_slider_banners_enabled' => 1,
            'storefront_feature_1_icon' => 'las la-spa',
            'storefront_feature_2_icon' => 'las la-user-md',
            'storefront_feature_3_icon' => 'las la-calendar-check',
            'storefront_feature_4_icon' => 'las la-gift',
            'storefront_feature_5_icon' => 'las la-headset',
            'translatable' => [
                'storefront_feature_1_title' => 'Professional Treatments',
                'storefront_feature_1_subtitle' => 'Certified beauticians and quality products.',
                'storefront_feature_2_title' => 'Trusted Experts',
                'storefront_feature_2_subtitle' => 'Experienced team for every treatment.',
                'storefront_feature_3_title' => 'Easy Booking',
                'storefront_feature_3_subtitle' => 'Book appointments online anytime.',
                'storefront_feature_4_title' => 'Special Offers',
                'storefront_feature_4_subtitle' => 'Packages and promotions for members.',
                'storefront_feature_5_title' => 'Customer Support',
                'storefront_feature_5_subtitle' => 'We are here to help before and after your visit.',
                'storefront_featured_categories_section_title' => 'Shop by Category',
                'storefront_featured_categories_section_subtitle' => 'Browse our treatment categories.',
                'storefront_product_tabs_1_section_tab_1_title' => 'Latest Treatments',
                'storefront_product_tabs_1_section_tab_2_title' => 'Popular Treatments',
                'storefront_product_grid_section_tab_1_title' => 'Our Treatments',
                'storefront_product_grid_section_tab_2_title' => 'New Arrivals',
                'storefront_blogs_section_title' => 'From Our Blog',
            ],
        ];

        foreach ([1, 2] as $tab) {
            $settings["storefront_product_tabs_1_section_tab_{$tab}_product_type"] = 'latest_products';
            $settings["storefront_product_tabs_1_section_tab_{$tab}_products_limit"] = 12;
        }

        foreach ([1, 2] as $tab) {
            $settings["storefront_product_grid_section_tab_{$tab}_product_type"] = 'latest_products';
            $settings["storefront_product_grid_section_tab_{$tab}_products_limit"] = 12;
        }

        return $settings;
    }


    private function assignFeaturedCategories(): void
    {
        $categoryIds = Category::query()
            ->whereNull('parent_id')
            ->orderBy('id')
            ->limit(6)
            ->pluck('id')
            ->values();

        if ($categoryIds->isEmpty()) {
            return;
        }

        $featured = [];

        foreach ($categoryIds as $index => $categoryId) {
            $slot = $index + 1;

            $featured["storefront_featured_categories_section_category_{$slot}_product_type"] = 'category_products';
            $featured["storefront_featured_categories_section_category_{$slot}_category_id"] = $categoryId;
        }

        Setting::setMany($featured);
    }
}
