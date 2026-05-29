<?php

namespace Modules\Report\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Product\Entities\Product;

class SalesReportProductController
{
    public function index(Request $request): JsonResponse
    {
        $categoryId = $request->get('category_id');
        $query = trim((string) $request->get('query', ''));
        $limit = min(max((int) $request->get('limit', 500), 1), 1000);

        if ($query === '') {
            return response()->json([]);
        }

        $term = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $query) . '%';

        $products = Product::withoutGlobalScope('active')
            ->select('products.id', 'products.sku', 'products.is_virtual')
            ->withName()
            ->when($categoryId !== null && $categoryId !== '', function ($productQuery) use ($categoryId) {
                $productQuery->whereHas('categories', function ($categoryQuery) use ($categoryId) {
                    $categoryQuery->where('categories.id', $categoryId);
                });
            })
            ->where(function ($productQuery) use ($term) {
                $productQuery
                    ->whereHas('translations', function ($translationQuery) use ($term) {
                        $translationQuery
                            ->where('locale', locale())
                            ->where('name', 'like', $term);
                    })
                    ->orWhere('products.sku', 'like', $term);
            })
            ->orderBy('products.id')
            ->limit($limit)
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'is_virtual' => (bool) $product->is_virtual,
            ])
            ->values();

        return response()->json([
            'products' => $products,
            'total' => $products->count(),
        ]);
    }


    public function options(Request $request): JsonResponse
    {
        $productId = $request->get('product_id');

        if ($productId === null || $productId === '') {
            return response()->json(['groups' => []]);
        }

        $product = Product::withoutGlobalScope('active')
            ->with([
                'options' => function ($query) {
                    $query->with('values');
                },
                'variations' => function ($query) {
                    $query->with('values');
                },
            ])
            ->find($productId);

        if (!$product) {
            return response()->json(['groups' => []]);
        }

        $groups = [];

        foreach ($product->options as $option) {
            $values = $option->values
                ->map(fn ($value) => [
                    'id' => $value->id,
                    'label' => $value->label,
                ])
                ->values();

            if ($values->isEmpty()) {
                continue;
            }

            $groups[] = [
                'id' => $option->id,
                'name' => $option->name,
                'type' => 'option',
                'values' => $values,
            ];
        }

        foreach ($product->variations as $variation) {
            $values = $variation->values
                ->map(fn ($value) => [
                    'id' => $value->id,
                    'label' => $value->label,
                ])
                ->values();

            if ($values->isEmpty()) {
                continue;
            }

            $groups[] = [
                'id' => $variation->id,
                'name' => $variation->name,
                'type' => 'variation',
                'values' => $values,
            ];
        }

        return response()->json([
            'is_virtual' => (bool) $product->is_virtual,
            'groups' => $groups,
        ]);
    }
}
