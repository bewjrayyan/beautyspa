<?php

namespace Modules\Loyalty\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Category\Entities\Category;
use Modules\Loyalty\Services\StampProgramProductCatalogService;
use Modules\Product\Entities\Product;

class StampProgramProductController
{
    public function __construct(
        private StampProgramProductCatalogService $catalog
    ) {}


    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->get('query', ''));
        $limit = (int) $request->get('limit', 25);
        $categoryId = $request->filled('category_id') ? (int) $request->get('category_id') : null;

        if ($term === '' && ! $categoryId) {
            return response()->json([]);
        }

        $products = $this->catalog->search($term, $limit, $categoryId);

        return response()->json(
            $products->map(fn (Product $product) => $this->catalog->formatSearchOption($product))->values()
        );
    }


    public function categoryProducts(Category $category): JsonResponse
    {
        $products = $this->catalog->forCategory((int) $category->id);

        return response()->json(
            $products->map(fn (Product $product) => $this->catalog->formatSearchOption($product))->values()
        );
    }


    public function show(Product $product): JsonResponse
    {
        abort_unless($product->is_active, 404);

        return response()->json($this->catalog->serializeProduct($product));
    }
}
