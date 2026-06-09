<?php

namespace AestheticCart\Install;

class AdminAccountLegacyPermissions
{
    /**
     * @return array<string, bool>
     */
    public function all(): array
    {
        $keys = [
            'admin.users.index', 'admin.users.create', 'admin.users.edit', 'admin.users.destroy',
            'admin.roles.index', 'admin.roles.create', 'admin.roles.edit', 'admin.roles.destroy',
            'admin.products.index', 'admin.products.create', 'admin.products.edit', 'admin.products.destroy',
            'admin.brands.index', 'admin.brands.create', 'admin.brands.edit', 'admin.brands.destroy',
            'admin.attributes.index', 'admin.attributes.create', 'admin.attributes.edit', 'admin.attributes.destroy',
            'admin.attribute_sets.index', 'admin.attribute_sets.create', 'admin.attribute_sets.edit', 'admin.attribute_sets.destroy',
            'admin.variations.index', 'admin.variations.create', 'admin.variations.edit', 'admin.variations.destroy',
            'admin.options.index', 'admin.options.create', 'admin.options.edit', 'admin.options.destroy',
            'admin.filters.index', 'admin.filters.create', 'admin.filters.edit', 'admin.filters.destroy',
            'admin.reviews.index', 'admin.reviews.create', 'admin.reviews.edit', 'admin.reviews.destroy',
            'admin.categories.index', 'admin.categories.create', 'admin.categories.edit', 'admin.categories.destroy',
            'admin.tags.index', 'admin.tags.create', 'admin.tags.edit', 'admin.tags.destroy',
            'admin.orders.index', 'admin.orders.show', 'admin.orders.edit', 'admin.orders.destroy',
            'admin.flash_sales.index', 'admin.flash_sales.create', 'admin.flash_sales.edit', 'admin.flash_sales.destroy',
            'admin.transactions.index',
            'admin.coupons.index', 'admin.coupons.create', 'admin.coupons.edit', 'admin.coupons.destroy',
            'admin.menus.index', 'admin.menus.create', 'admin.menus.edit', 'admin.menus.destroy',
            'admin.menu_items.index', 'admin.menu_items.create', 'admin.menu_items.edit', 'admin.menu_items.destroy',
            'admin.blog_posts.index', 'admin.blog_posts.create', 'admin.blog_posts.edit', 'admin.blog_posts.destroy',
            'admin.blog_categories.index', 'admin.blog_categories.create', 'admin.blog_categories.edit', 'admin.blog_categories.destroy',
            'admin.blog_tags.index', 'admin.blog_tags.create', 'admin.blog_tags.edit', 'admin.blog_tags.destroy',
            'admin.importer.import',
            'admin.media.index', 'admin.media.create', 'admin.media.destroy',
            'admin.pages.index', 'admin.pages.create', 'admin.pages.edit', 'admin.pages.destroy',
            'admin.currency_rates.index', 'admin.currency_rates.edit',
            'admin.taxes.index', 'admin.taxes.create', 'admin.taxes.edit', 'admin.taxes.destroy',
            'admin.languages.index', 'admin.languages.add',
            'admin.translations.index', 'admin.translations.edit',
            'admin.sliders.index', 'admin.sliders.create', 'admin.sliders.edit', 'admin.sliders.destroy',
            'admin.reports.index',
            'admin.settings.edit',
            'admin.storefront.edit',
        ];

        return array_fill_keys($keys, true);
    }
}
