<?php

namespace Tests\Unit\Product;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ProductVariantSingleImageContractTest extends TestCase
{
    #[Test]
    public function variant_media_is_single_select_across_editor_and_bulk_edit(): void
    {
        $root = dirname(__DIR__, 3);
        $variants = file_get_contents($root.'/modules/Product/Resources/assets/admin/js/components/Variants.vue');
        $bulkEdit = file_get_contents($root.'/modules/Product/Resources/assets/admin/js/components/BulkEditVariants.vue');
        $variantState = file_get_contents($root.'/modules/Product/Resources/assets/admin/js/composables/useVariants.js');
        $bulkState = file_get_contents($root.'/modules/Product/Resources/assets/admin/js/composables/useBulkEditVariants.js');

        $this->assertStringContainsString('new MediaPicker({ type: "image" })', $variants);
        $this->assertStringNotContainsString('new MediaPicker({ type: "image", multiple: true })', $variants);
        $this->assertStringContainsString('form.variants[index].media = [{ id: +id, path }]', $variants);
        $this->assertStringContainsString('new MediaPicker({ type: "image" })', $bulkEdit);
        $this->assertStringContainsString('bulkEditVariants.media = [{ id: +id, path }]', $bulkEdit);
        $this->assertStringContainsString('.slice(0, 1)', $variantState);
        $this->assertStringContainsString('value.map((item) => ({ ...item }))', $bulkState);
    }

    #[Test]
    public function variant_media_is_limited_and_normalized_on_the_server(): void
    {
        $root = dirname(__DIR__, 3);
        $request = file_get_contents($root.'/modules/Product/Http/Requests/SaveProductRequest.php');
        $variant = file_get_contents($root.'/modules/Product/Entities/ProductVariant.php');
        $listener = file_get_contents($root.'/modules/Product/Listeners/SaveProductVariants.php');
        $resource = file_get_contents($root.'/modules/Product/Transformers/ProductVariantResource.php');

        $this->assertStringContainsString("'variants.*.media' => 'nullable|array|max:1'", $request);
        $this->assertStringContainsString("'variants.*.media.*' => 'integer|exists:files,id'", $request);
        $this->assertStringContainsString("'additional_images' => []", $variant);
        $this->assertStringContainsString("'additional_images' => []", $listener);
        $this->assertStringContainsString("->where('pivot.zone', 'base_image')", $resource);
    }
}
