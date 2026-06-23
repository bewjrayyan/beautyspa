<?php

namespace Modules\Storefront\Admin;

use Modules\Storefront\Admin\StorefrontTab;
use Modules\Admin\Ui\Tabs;
use Modules\Tag\Entities\Tag;
use Modules\Storefront\Banner;
use Modules\Menu\Entities\Menu;
use Modules\Page\Entities\Page;
use Modules\Media\Entities\File;
use Modules\Brand\Entities\Brand;
use Modules\Slider\Entities\Slider;
use Illuminate\Support\Facades\Cache;
use Modules\FlashSale\Entities\FlashSale;
use Modules\Product\Repositories\ProductRepository;

class StorefrontTabs extends Tabs
{
    /**
     * Make new tabs with groups.
     *
     * @return void
     */
    public function make()
    {
        $this->group('general_settings', trans('storefront::storefront.tabs.group.general_settings'))
            ->active()
            ->add($this->general())
            ->add($this->logo())
            ->add($this->menus())
            ->add($this->footer())
            ->add($this->newsletter())
            ->add($this->features())
            ->add($this->productPage())
            ->add($this->socialLinks());


        $this->group('home_page_sections', trans('storefront::storefront.tabs.group.home_page_sections'))
            ->add($this->sliderBanners())
            ->add($this->threeColumnFullWidthBanners())
            ->add($this->featuredCategories())
            ->add($this->productTabsOne())
            ->add($this->topBrands())
            ->add($this->flashSaleAndVerticalProducts())
            ->add($this->twoColumnBanners())
            ->add($this->productGrid())
            ->add($this->threeColumnBanners())
            ->add($this->productTabsTwo())
            ->add($this->oneColumnBanner())
            ->add($this->googleReviews())
            ->add($this->blogs());

        $this->group('home_page_sections_mobile', trans('storefront::storefront.tabs.group.home_page_sections_mobile'))
            ->add($this->mobileHomePromo());
    }


    private function general()
    {
        return tap(new StorefrontTab('general', trans('storefront::storefront.tabs.general')), function (StorefrontTab $tab) {
            $tab->active();
            $tab->weight(5);
            $tab->fields([
                'storefront_slider',
                'storefront_copyright_text',
                'storefront_products_listing_title',
                'storefront_welcome_text',
                'storefront_display_font',
                'storefront_theme_color',
                'storefront_custom_theme_color',
                'storefront_mail_theme_color',
                'storefront_custom_mail_theme_color',
                'storefront_terms_page',
                'storefront_privacy_page',
                'storefront_address',
                'storefront_most_searched_keywords_enabled',
            ]);
            $tab->view('storefront::admin.storefront.tabs.general', [
                'display_fonts' => [
                    'Poppins' => 'Poppins',
                    'Rubik' => 'Rubik',
                    'Roboto' => 'Roboto',
                    'Open Sans' => 'Open Sans',
                    'Montserrat' => 'Montserrat',
                    'Nunito' => 'Nunito',
                    'Raleway' => 'Raleway',
                    'Oswald' => 'Oswald',
                    'Quicksand' => 'Quicksand',
                    'Hind' => 'Hind',
                    'Fira Sans' => 'Fira Sans',
                    'Mukta' => 'Mukta',
                    'Karla' => 'Karla',
                    'Barlow' => 'Barlow',
                    'Source Sans 3' => 'Source Sans 3',
                    'IBM Plex Sans' => 'IBM Plex Sans',
                    'Work Sans' => 'Work Sans',
                ],
                'pages' => $this->getPages(),
                'sliders' => $this->getSliders(),
            ]);
        });
    }


    private function getPages()
    {
        return Page::all()->pluck('name', 'id')
            ->prepend(trans('storefront::storefront.form.please_select'), '');
    }


    private function getSliders()
    {
        return Slider::all()->sortBy('name')->pluck('name', 'id')
            ->prepend(trans('storefront::storefront.form.please_select'), '');
    }


    private function logo()
    {
        return tap(new StorefrontTab('logo', trans('storefront::storefront.tabs.logo')), function (StorefrontTab $tab) {
            $tab->weight(10);
            $tab->view('storefront::admin.storefront.tabs.logo', [
                'favicon' => $this->getMedia(setting('storefront_favicon')),
                'headerLogo' => $this->getMedia(setting('storefront_header_logo')),
                'footerLogo' => $this->getMedia(setting('storefront_footer_logo')),
                'mailLogo' => $this->getMedia(setting('storefront_mail_logo')),
            ]);
        });
    }


    private function getMedia($fileId)
    {
        return Cache::rememberForever(md5("files.{$fileId}"), function () use ($fileId) {
            return File::findOrNew($fileId);
        });
    }


    private function menus()
    {
        return tap(new StorefrontTab('menus', trans('storefront::storefront.tabs.menus')), function (StorefrontTab $tab) {
            $tab->weight(15);

            $tab->fields([
                'storefront_primary_menu',
                'storefront_category_menu',
                'storefront_footer_menu',
                'storefront_footer_menu_title',
            ]);

            $tab->view('storefront::admin.storefront.tabs.menus', [
                'menus' => $this->getMenus(),
            ]);
        });
    }


    private function getMenus()
    {
        return Menu::all()->pluck('name', 'id')
            ->prepend(trans('storefront::storefront.form.please_select'), '');
    }


    private function footer()
    {
        return tap(new StorefrontTab('footer', trans('storefront::storefront.tabs.footer')), function (StorefrontTab $tab) {
            $tab->weight(17);
            $tab->view('storefront::admin.storefront.tabs.footer', [
                'tags' => Tag::list(),
                'acceptedPaymentMethodsImage' => $this->getMedia(setting('storefront_accepted_payment_methods_image')),
            ]);
        });
    }


    private function newsletter()
    {
        if (!setting('newsletter_enabled')) {
            return;
        }

        return tap(new StorefrontTab('newsletter', trans('storefront::storefront.tabs.newsletter')), function (StorefrontTab $tab) {
            $tab->weight(18);
            $tab->view('storefront::admin.storefront.tabs.newsletter', [
                'newsletterBgImage' => $this->getMedia(setting('storefront_newsletter_bg_image')),
            ]);
        });
    }


    private function features()
    {
        return tap(new StorefrontTab('features', trans('storefront::storefront.tabs.features')), function (StorefrontTab $tab) {
            $tab->weight(20);
            $tab->view('storefront::admin.storefront.tabs.features');
        });
    }


    private function productPage()
    {
        return tap(new StorefrontTab('product_page', trans('storefront::storefront.tabs.product_page')), function (StorefrontTab $tab) {
            $tab->weight(22);
            $tab->fields([
                'storefront_product_share_whatsapp_enabled',
                'storefront_product_share_whatsapp_message',
            ]);
            $tab->view('storefront::admin.storefront.tabs.product_page', [
                'banner' => Banner::getProductPageBanner(),
            ]);
        });
    }


    private function socialLinks()
    {
        return tap(new StorefrontTab('social_links', trans('storefront::storefront.tabs.social_links')), function (StorefrontTab $tab) {
            $tab->weight(25);

            $tab->fields([
                'storefront_fb_link',
                'storefront_twitter_link',
                'storefront_instagram_link',
                'storefront_linkedin_link',
                'storefront_pinterest_link',
                'storefront_gplus_link',
                'storefront_youtube_link',
            ]);

            $tab->view('storefront::admin.storefront.tabs.social_links');
        });
    }


    private function sliderBanners()
    {
        return tap(new StorefrontTab('slider_banners', trans('storefront::storefront.tabs.slider_banners')), function (StorefrontTab $tab) {
            $tab->weight(30);
            $tab->fields(['storefront_slider_banners_enabled']);
            $tab->view('storefront::admin.storefront.tabs.slider_banners', [
                'banners' => Banner::getSliderBanners(),
            ]);
        });
    }


    private function threeColumnFullWidthBanners()
    {
        return tap(new StorefrontTab('three_column_full_width_banners', trans('storefront::storefront.tabs.three_column_full_width_banners')), function (StorefrontTab $tab) {
            $tab->weight(35);
            $tab->view('storefront::admin.storefront.tabs.three_column_full_width_banners', [
                'banners' => Banner::getThreeColumnFullWidthBanners(),
            ]);
        });
    }


    private function featuredCategories()
    {
        return tap(new StorefrontTab('featured_categories', trans('storefront::storefront.tabs.featured_categories')), function (StorefrontTab $tab) {
            $tab->weight(40);
            $tab->view('storefront::admin.storefront.tabs.featured_categories', [
                'categoryOneProducts' => $this->getProductListFromSetting('storefront_featured_categories_section_category_1_products'),
                'categoryTwoProducts' => $this->getProductListFromSetting('storefront_featured_categories_section_category_2_products'),
                'categoryThreeProducts' => $this->getProductListFromSetting('storefront_featured_categories_section_category_3_products'),
                'categoryFourProducts' => $this->getProductListFromSetting('storefront_featured_categories_section_category_4_products'),
                'categoryFiveProducts' => $this->getProductListFromSetting('storefront_featured_categories_section_category_5_products'),
                'categorySixProducts' => $this->getProductListFromSetting('storefront_featured_categories_section_category_6_products'),
            ]);
        });
    }


    private function getProductListFromSetting($key)
    {
        return ProductRepository::list(setting($key) ?? []);
    }


    private function productTabsOne()
    {
        return tap(new StorefrontTab('product_tabs_one', trans('storefront::storefront.tabs.product_tabs_one')), function (StorefrontTab $tab) {
            $tab->weight(45);
            $tab->view('storefront::admin.storefront.tabs.product_tabs_one', [
                'tabOneProducts' => $this->getProductListFromSetting('storefront_product_tabs_1_section_tab_1_products'),
                'tabTwoProducts' => $this->getProductListFromSetting('storefront_product_tabs_1_section_tab_2_products'),
                'tabThreeProducts' => $this->getProductListFromSetting('storefront_product_tabs_1_section_tab_3_products'),
                'tabFourProducts' => $this->getProductListFromSetting('storefront_product_tabs_1_section_tab_4_products'),
            ]);
        });
    }


    private function topBrands()
    {
        if (!auth()->user()->hasAccess(['admin.brands.index'])) {
            return;
        }

        return tap(new StorefrontTab('top_brands', trans('storefront::storefront.tabs.top_brands')), function (StorefrontTab $tab) {
            $tab->weight(50);
            $tab->view('storefront::admin.storefront.tabs.top_brands', [
                'brands' => Brand::list(),
            ]);
        });
    }


    private function flashSaleAndVerticalProducts()
    {
        return tap(new StorefrontTab('flash_sale_and_vertical_products', trans('storefront::storefront.tabs.flash_sale_and_vertical_products')), function (StorefrontTab $tab) {
            $tab->weight(60);
            $tab->view('storefront::admin.storefront.tabs.flash_sale_and_vertical_products', [
                'flashSales' => $this->getFlashSales(),
                'verticalProductsOne' => $this->getProductListFromSetting('storefront_vertical_products_1_products'),
                'verticalProductsTwo' => $this->getProductListFromSetting('storefront_vertical_products_2_products'),
                'verticalProductsThree' => $this->getProductListFromSetting('storefront_vertical_products_3_products'),
            ]);
        });
    }


    private function getFlashSales()
    {
        return FlashSale::all()->pluck('campaign_name', 'id')
            ->prepend(trans('admin::admin.form.please_select'), '');
    }


    private function twoColumnBanners()
    {
        return tap(new StorefrontTab('two_column_banners', trans('storefront::storefront.tabs.two_column_banners')), function (StorefrontTab $tab) {
            $tab->weight(65);
            $tab->view('storefront::admin.storefront.tabs.two_column_banners', [
                'banners' => Banner::getTwoColumnBanners(),
            ]);
        });
    }


    private function productGrid()
    {
        return tap(new StorefrontTab('product_grid', trans('storefront::storefront.tabs.product_grid')), function (StorefrontTab $tab) {
            $tab->weight(70);
            $tab->view('storefront::admin.storefront.tabs.product_grid', [
                'tabOneProducts' => $this->getProductListFromSetting('storefront_product_grid_section_tab_1_products'),
                'tabTwoProducts' => $this->getProductListFromSetting('storefront_product_grid_section_tab_2_products'),
                'tabThreeProducts' => $this->getProductListFromSetting('storefront_product_grid_section_tab_3_products'),
                'tabFourProducts' => $this->getProductListFromSetting('storefront_product_grid_section_tab_4_products'),
            ]);
        });
    }


    private function threeColumnBanners()
    {
        return tap(new StorefrontTab('three_column_banners', trans('storefront::storefront.tabs.three_column_banners')), function (StorefrontTab $tab) {
            $tab->weight(75);
            $tab->view('storefront::admin.storefront.tabs.three_column_banners', [
                'banners' => Banner::getThreeColumnBanners(),
            ]);
        });
    }


    private function productTabsTwo()
    {
        return tap(new StorefrontTab('product_tabs_two', trans('storefront::storefront.tabs.product_tabs_two')), function (StorefrontTab $tab) {
            $tab->weight(80);
            $tab->view('storefront::admin.storefront.tabs.product_tabs_two', [
                'tabOneProducts' => $this->getProductListFromSetting('storefront_product_tabs_2_section_tab_1_products'),
                'tabTwoProducts' => $this->getProductListFromSetting('storefront_product_tabs_2_section_tab_2_products'),
                'tabThreeProducts' => $this->getProductListFromSetting('storefront_product_tabs_2_section_tab_3_products'),
                'tabFourProducts' => $this->getProductListFromSetting('storefront_product_tabs_2_section_tab_4_products'),
            ]);
        });
    }


    private function oneColumnBanner()
    {
        return tap(new StorefrontTab('one_column_banner', trans('storefront::storefront.tabs.one_column_banner')), function (StorefrontTab $tab) {
            $tab->weight(85);
            $tab->view('storefront::admin.storefront.tabs.one_column_banner', [
                'banner' => Banner::getOneColumnBanner(),
            ]);
        });
    }


    private function googleReviews()
    {
        return tap(new StorefrontTab('google_reviews', trans('storefront::storefront.tabs.google_reviews')), function (StorefrontTab $tab) {
            $tab->weight(86);
            $tab->fields([
                'storefront_google_reviews_section_enabled',
                'storefront_google_reviews_section_title',
                'storefront_google_reviews_rating',
                'storefront_google_reviews_review_count',
                'storefront_google_reviews_items',
                'storefront_google_reviews_metrics',
            ]);
            $tab->view('storefront::admin.storefront.tabs.google_reviews');
        });
    }


    private function blogs()
    {
        return tap(new StorefrontTab('blogs', trans('storefront::storefront.tabs.blogs')), function (StorefrontTab $tab) {
            $tab->weight(87);
            $tab->view('storefront::admin.storefront.tabs.blogs');
        });
    }


    private function mobileHomePromo()
    {
        return tap(new StorefrontTab('mobile_home_promo', trans('storefront::storefront.tabs.mobile_home_promo')), function (StorefrontTab $tab) {
            $tab->weight(5);
            $tab->fields([
                'storefront_mobile_home_promo_enabled',
                'storefront_mobile_home_promo_media_type',
                'storefront_mobile_home_promo_image_file_id',
                'storefront_mobile_home_promo_video_file_id',
                'storefront_mobile_home_promo_video_poster_file_id',
                'storefront_mobile_home_promo_call_to_action_url',
                'storefront_mobile_home_promo_open_in_new_window',
            ]);
            $tab->view('storefront::admin.storefront.tabs.mobile_home_promo', [
                'promoImage' => $this->getMedia(setting('storefront_mobile_home_promo_image_file_id')),
                'promoVideo' => $this->getMedia(setting('storefront_mobile_home_promo_video_file_id')),
                'promoVideoPoster' => $this->getMedia(setting('storefront_mobile_home_promo_video_poster_file_id')),
            ]);
        });
    }


    /**
     * @param array $data
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function render($data = [])
    {
        $this->buttonOffset = false;
        $this->activateTabFromRequest();

        return view('admin::components.settings-layout', [
            'navigation' => $this->settingsNavigation(),
            'contents' => $this->contents($data),
            'buttonOffset' => false,
            'activeTab' => $this->activeTabName(),
            'activeTabMeta' => $this->activeTabMeta(),
            'settingsFormAction' => route('admin.storefront.settings.update'),
            'settingsFormId' => 'storefront-settings-edit-form',
            'settingsSidebarBrand' => trans('storefront::storefront.storefront'),
            'settingsTabField' => 'storefront_tab',
            'settingsSearchLabel' => trans('storefront::storefront.form.search_tabs'),
            'settingsSearchPlaceholder' => trans('storefront::storefront.form.search_tabs'),
            'settingsSearchNoResults' => trans('storefront::storefront.form.search_no_results'),
            'settingsSidebarAria' => trans('storefront::storefront.storefront'),
        ]);
    }


    protected function settingsNavigation(): string
    {
        $html = '';
        $baseUrl = route('admin.storefront.settings.edit');

        foreach ($this->groups as $groupName => $options) {
            $title = $options['title'] ?? $groupName;

            $html .= '<div class="settings-nav-group is-expanded" data-settings-group>';
            $html .= '<button type="button" class="settings-nav-group__toggle" aria-expanded="true">';
            $html .= '<span class="settings-nav-group__title">'.e($title).'</span>';
            $html .= '<i class="fa fa-chevron-down settings-nav-group__chevron" aria-hidden="true"></i>';
            $html .= '</button>';
            $html .= '<ul class="settings-nav settings-nav-group__list">';

            foreach ($this->group($groupName)->getSortedTabs() as $tab) {
                $html .= $tab->getSettingsNav($baseUrl);
            }

            $html .= '</ul></div>';
        }

        return $html;
    }


    /**
     * @return array{name: string, label: string, group: string, lead: ?string, icon: ?string}
     */
    protected function activeTabMeta(): array
    {
        foreach ($this->groups as $groupName => $options) {
            foreach ($this->tabs[$groupName] ?? [] as $tab) {
                if (! $tab->active) {
                    continue;
                }

                $leadKey = "storefront::storefront.tab_leads.{$tab->name}";
                $lead = trans($leadKey);

                return [
                    'name' => $tab->name,
                    'label' => $tab->label,
                    'group' => $options['title'] ?? $groupName,
                    'lead' => $lead === $leadKey ? null : $lead,
                    'icon' => $tab->settingsNavIcon(),
                ];
            }
        }

        return [
            'name' => 'general',
            'label' => '',
            'group' => '',
            'lead' => null,
            'icon' => null,
        ];
    }
}
