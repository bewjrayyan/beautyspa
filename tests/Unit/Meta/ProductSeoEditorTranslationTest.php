<?php

namespace Tests\Unit\Meta;

use Tests\TestCase;

class ProductSeoEditorTranslationTest extends TestCase
{
    public function test_every_product_seo_translation_is_exported_to_vue(): void
    {
        $component = file_get_contents(base_path('modules/Product/Resources/assets/admin/js/components/Seo.vue'));
        $scripts = file_get_contents(base_path('modules/Product/Resources/views/admin/products/partials/scripts.blade.php'));

        preg_match_all(
            '~trans\(([\'"])(meta::attributes\.[^\'"]+)\1\)~',
            $component,
            $matches,
        );

        $translationKeys = array_unique($matches[2]);

        $this->assertNotEmpty($translationKeys);

        foreach ($translationKeys as $translationKey) {
            $this->assertStringContainsString("AestheticCart.langs['{$translationKey}']", $scripts);
            $this->assertNotSame($translationKey, trans($translationKey));
        }
    }
}
