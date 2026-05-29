<?php

namespace Modules\FlashSale\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Product\Entities\Product;

class FlashSaleProductController
{
    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->get('query', ''));

        if ($term === '') {
            return response()->json([]);
        }

        $limit = (int) $request->get('limit', 15);

        $products = $this->searchVirtualTreatments($term, $limit);

        return response()->json(
            $products->map(fn (Product $product) => $this->formatProductOption($product))->values()
        );
    }


    public function show(Product $product): JsonResponse
    {
        abort_unless($product->isVirtualTreatment(), 404);

        return response()->json($this->formatProductOption($product));
    }


    private function searchVirtualTreatments(string $term, int $limit)
    {
        $model = new Product();

        $baseQuery = $model->newQuery()
            ->withoutGlobalScope('active')
            ->select('products.*')
            ->where('is_active', true)
            ->where('is_virtual', true);

        $scoutResults = $model->search($term)
            ->query()
            ->withoutGlobalScope('active')
            ->mergeConstraintsFrom($baseQuery)
            ->limit($limit)
            ->get();

        if ($scoutResults->isNotEmpty()) {
            return $scoutResults;
        }

        return (clone $baseQuery)
            ->whereHas('translations', function ($translationQuery) use ($term) {
                $translationQuery->where('name', 'like', '%'.$term.'%');
            })
            ->orderByDesc('products.id')
            ->limit($limit)
            ->get();
    }


    private function formatProductOption(Product $product): array
    {
        $label = trim($product->name).' · '.trans('product::products.table.virtual_treatment');
        $catalogMoney = $product->hasSpecialPrice()
            ? $product->getSpecialPrice()
            : $product->price;
        $catalogMoney = $catalogMoney->convertToCurrentCurrency();
        $suggestedAmount = round((float) $catalogMoney->amount(), 2);

        return [
            'id' => $product->id,
            'name' => $label,
            'is_virtual' => true,
            'default_qty' => 0,
            'suggested_price' => $suggestedAmount,
            'catalog_price' => $suggestedAmount,
            'catalog_price_formatted' => $catalogMoney->format(),
            'has_variants' => $product->hasAnyVariants(),
            'has_options' => $product->options()->exists(),
        ];
    }
}
