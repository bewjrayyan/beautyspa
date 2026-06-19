<?php

namespace Modules\Loyalty\Services;

use Modules\Category\Entities\Category;
use Modules\Loyalty\Entities\LoyaltyStampProgram;
use Modules\Order\Entities\Order;
use Modules\Order\Entities\OrderProduct;
use Modules\Product\Entities\Product;

class StampProgramEligibleProductService
{
    public function __construct(
        private StampProgramProductCatalogService $catalog
    ) {}


    /**
     * @return array{category_ids: array<int>, products: array<int, array<string, mixed>>}
     */
    public function serializeForAdmin(LoyaltyStampProgram $program): array
    {
        $rules = $this->normalizeStored($program->product_ids);

        if ($rules === []) {
            return [
                'category_ids' => [],
                'products' => [],
            ];
        }

        $categoryIds = collect($rules)
            ->pluck('category_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $productRules = collect($rules)->filter(fn (array $rule) => ! empty($rule['product_id']));

        $products = Product::query()
            ->withoutGlobalScope('active')
            ->withName()
            ->whereIn('id', $productRules->pluck('product_id'))
            ->get()
            ->keyBy('id');

        return [
            'category_ids' => $categoryIds,
            'products' => $productRules->map(function (array $rule) use ($products) {
                $product = $products->get($rule['product_id']);

                return [
                    'id' => (int) $rule['product_id'],
                    'name' => $product?->name ?? '#'.$rule['product_id'],
                ];
            })->values()->all(),
        ];
    }


    /**
     * @param array<int, mixed>|null $stored
     * @return array<int, array<string, mixed>>
     */
    public function normalizeStored(?array $stored): array
    {
        if ($stored === null || $stored === []) {
            return [];
        }

        $rules = [];

        foreach ($stored as $item) {
            if (is_numeric($item)) {
                $rules[] = [
                    'product_id' => (int) $item,
                    'variations' => [],
                    'options' => [],
                ];

                continue;
            }

            if (! is_array($item)) {
                continue;
            }

            if (! empty($item['category_id'])) {
                $rules[] = [
                    'category_id' => (int) $item['category_id'],
                ];

                continue;
            }

            $productId = (int) ($item['product_id'] ?? 0);

            if ($productId <= 0) {
                continue;
            }

            $rules[] = [
                'product_id' => $productId,
                'variations' => [],
                'options' => [],
            ];
        }

        return $rules;
    }


    public function normalizeForStorage(?array $categoryIds, ?array $productIds): ?array
    {
        $rules = [];

        foreach (array_values(array_filter(array_map('intval', $categoryIds ?? []))) as $categoryId) {
            if ($categoryId > 0) {
                $rules[] = ['category_id' => $categoryId];
            }
        }

        foreach (array_values(array_filter(array_map('intval', $productIds ?? []))) as $productId) {
            if ($productId > 0) {
                $rules[] = [
                    'product_id' => $productId,
                    'variations' => [],
                    'options' => [],
                ];
            }
        }

        return $rules === [] ? null : $rules;
    }


    public function orderQualifies(Order $order, LoyaltyStampProgram $program): bool
    {
        $rules = $this->normalizeStored($program->product_ids);

        if ($rules !== []) {
            return $order->products->contains(
                fn (OrderProduct $line) => collect($rules)->contains(
                    fn (array $rule) => $this->lineMatchesRule($line, $rule)
                )
            );
        }

        if ($program->virtual_treatments_only) {
            return $order->products->contains(fn (OrderProduct $line) => (bool) $line->product?->is_virtual);
        }

        return $order->products->isNotEmpty();
    }


    /**
     * @param array<string, mixed> $rule
     */
    public function lineMatchesRule(OrderProduct $line, array $rule): bool
    {
        if (! empty($rule['category_id'])) {
            $line->loadMissing('product.categories');

            return $line->product
                && $line->product->categories->contains('id', (int) $rule['category_id']);
        }

        return (int) $line->product_id === (int) ($rule['product_id'] ?? 0);
    }


    /**
     * @return array<int|string, string>
     */
    public function categoryOptions(): array
    {
        return Category::treeList();
    }
}
