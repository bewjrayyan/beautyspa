<?php

namespace Modules\Product\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class ProductIndexQueryFilter
{
    private const LOW_STOCK_THRESHOLD = 5;

    public function apply(Builder $query, Request $request): void
    {
        $payload = Validator::make($request->only([
            'is_active', 'type', 'stock', 'category_id', 'brand_id', 'tag_id',
            'price_from', 'price_to', 'on_sale', 'sku', 'updated_from', 'updated_to',
            'month', 'search', 'sort',
        ]), [
            'is_active' => ['nullable', 'in:0,1'],
            'type' => ['nullable', 'in:physical,virtual,variable'],
            'stock' => ['nullable', 'in:in_stock,out_of_stock,low_stock'],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'brand_id' => ['nullable', 'integer', 'min:1'],
            'tag_id' => ['nullable', 'integer', 'min:1'],
            'price_from' => ['nullable', 'numeric', 'min:0'],
            'price_to' => ['nullable', 'numeric', 'min:0'],
            'on_sale' => ['nullable', 'in:1'],
            'sku' => ['nullable', 'string', 'max:100'],
            'updated_from' => ['nullable', 'date_format:Y-m-d'],
            'updated_to' => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'string', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
            'search' => ['nullable', 'string', 'max:100'],
            'sort' => ['nullable', 'in:latest,oldest,name_asc,name_desc,price_asc,price_desc'],
        ])->valid();

        if (isset($payload['is_active'])) {
            $query->where('is_active', (bool) (int) $payload['is_active']);
        }

        $this->applyTypeFilter($query, $payload['type'] ?? null);
        $this->applyStockFilter($query, $payload['stock'] ?? null);

        if (! empty($payload['category_id'])) {
            $categoryId = (int) $payload['category_id'];
            $query->whereHas('categories', fn (Builder $q) => $q->where('categories.id', $categoryId));
        }

        if (! empty($payload['brand_id'])) {
            $query->where('brand_id', (int) $payload['brand_id']);
        }

        if (! empty($payload['tag_id'])) {
            $tagId = (int) $payload['tag_id'];
            $query->whereHas('tags', fn (Builder $q) => $q->where('tags.id', $tagId));
        }

        $this->applyPriceFilters(
            $query,
            isset($payload['price_from']) ? (float) $payload['price_from'] : null,
            isset($payload['price_to']) ? (float) $payload['price_to'] : null,
        );

        if (($payload['on_sale'] ?? null) === '1') {
            $this->applyOnSaleFilter($query);
        }

        if (! empty($payload['sku'])) {
            $like = '%' . addcslashes(trim((string) $payload['sku']), '%_\\') . '%';
            $query->where(function (Builder $builder) use ($like): void {
                $builder->where('sku', 'like', $like)
                    ->orWhereHas('variants', fn (Builder $vq) => $vq->where('sku', 'like', $like));
            });
        }

        $this->applyUpdatedAtFilters(
            $query,
            isset($payload['month']) ? (string) $payload['month'] : null,
            isset($payload['updated_from']) ? (string) $payload['updated_from'] : null,
            isset($payload['updated_to']) ? (string) $payload['updated_to'] : null,
        );

        if (! empty($payload['search'])) {
            $this->applySearch($query, trim((string) $payload['search']));
        }

        $this->applySort($query, $payload['sort'] ?? null);
    }

    private function applyTypeFilter(Builder $query, ?string $type): void
    {
        match ($type) {
            'virtual' => $query->where('is_virtual', true),
            'variable' => $query->whereHas('variants'),
            'physical' => $query->where('is_virtual', false)->whereDoesntHave('variants'),
            default => null,
        };
    }

    private function applyStockFilter(Builder $query, ?string $stock): void
    {
        match ($stock) {
            'in_stock' => $query->where('in_stock', true),
            'out_of_stock' => $query->where('in_stock', false),
            'low_stock' => $query->where('manage_stock', true)->where('qty', '<=', self::LOW_STOCK_THRESHOLD),
            default => null,
        };
    }

    private function applyPriceFilters(Builder $query, ?float $from, ?float $to): void
    {
        if ($from === null && $to === null) {
            return;
        }

        if ($from !== null && $to !== null && $from > $to) {
            [$from, $to] = [$to, $from];
        }

        if ($from !== null) {
            $query->where(function (Builder $priceQuery) use ($from): void {
                $priceQuery->where('selling_price', '>=', $from)
                    ->orWhereHas('variants', fn (Builder $vq) => $vq->where('selling_price', '>=', $from));
            });
        }

        if ($to !== null) {
            $query->where(function (Builder $priceQuery) use ($to): void {
                $priceQuery->where('selling_price', '<=', $to)
                    ->orWhereHas('variants', fn (Builder $vq) => $vq->where('selling_price', '<=', $to));
            });
        }
    }

    private function applyOnSaleFilter(Builder $query): void
    {
        $today = now()->toDateString();

        $query->whereNotNull('special_price')->where(function (Builder $dateQuery) use ($today): void {
            $dateQuery->where(function (Builder $startQuery) use ($today): void {
                $startQuery->whereNull('special_price_start')->orWhereDate('special_price_start', '<=', $today);
            })->where(function (Builder $endQuery) use ($today): void {
                $endQuery->whereNull('special_price_end')->orWhereDate('special_price_end', '>=', $today);
            });
        });
    }

    private function applyUpdatedAtFilters(Builder $query, ?string $month, ?string $updatedFrom, ?string $updatedTo): void
    {
        $from = $this->parseDate($updatedFrom);
        $to = $this->parseDate($updatedTo);

        if ($from !== null && $to !== null && $from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        if ($from !== null || $to !== null) {
            if ($from !== null) {
                $query->where('updated_at', '>=', $from->copy()->startOfDay());
            }
            if ($to !== null) {
                $query->where('updated_at', '<=', $to->copy()->endOfDay());
            }
            return;
        }

        if ($month) {
            try {
                $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $query->whereBetween('updated_at', [$start, $start->copy()->endOfMonth()]);
            } catch (\Throwable) {
            }
        }
    }

    private function applySearch(Builder $query, string $term): void
    {
        if ($term === '') {
            return;
        }

        $like = '%' . addcslashes($term, '%_\\') . '%';

        $query->where(function (Builder $builder) use ($term, $like): void {
            if (ctype_digit($term)) {
                $builder->orWhere('products.id', (int) $term);
            }

            $builder->orWhere('sku', 'like', $like)
                ->orWhereHas('translations', fn (Builder $tq) => $tq->where('name', 'like', $like))
                ->orWhereHas('variants', fn (Builder $vq) => $vq->where('sku', 'like', $like));
        });
    }

    private function applySort(Builder $query, ?string $sort): void
    {
        match ($sort) {
            'oldest' => $query->orderBy('updated_at'),
            'name_asc' => $this->orderByName($query, 'asc'),
            'name_desc' => $this->orderByName($query, 'desc'),
            'price_asc' => $query->orderBy('selling_price'),
            'price_desc' => $query->orderByDesc('selling_price'),
            default => $query->orderByDesc('updated_at'),
        };
    }

    private function orderByName(Builder $query, string $direction): void
    {
        $query->orderBy(
            \Modules\Product\Entities\ProductTranslation::query()
                ->select('name')
                ->whereColumn('product_id', 'products.id')
                ->where('locale', locale())
                ->limit(1),
            $direction
        );
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
