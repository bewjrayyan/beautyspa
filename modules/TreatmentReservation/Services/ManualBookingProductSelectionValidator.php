<?php

namespace Modules\TreatmentReservation\Services;

use Darryldecode\Cart\ItemCollection;
use Illuminate\Validation\ValidationException;
use Modules\Cart\CartItem;
use Modules\Option\Entities\Option;
use Modules\Order\Entities\Order;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductVariant;
use Modules\Product\Services\ChosenProductOptions;
use Modules\Product\Services\ChosenProductVariations;

class ManualBookingProductSelectionValidator
{
    /**
     * @param array<string, mixed> $data
     * @return array{
     *     product: Product,
     *     variant: ProductVariant|null,
     *     options: array<int|string, mixed>,
     *     variations: array<string, string>,
     *     total: float
     * }
     */
    public function validateAndResolve(array $data): array
    {
        $product = Product::query()
            ->where('is_virtual', true)
            ->where('is_active', true)
            ->with(['options.values', 'variations.values', 'variants'])
            ->findOrFail($data['product_id']);

        $options = $this->normalizeOptions($data['options'] ?? []);
        $variations = $this->normalizeVariations($data['variations'] ?? []);

        $this->validateOptions($product, $options);
        $this->validateVariations($product, $variations);

        $variant = $this->resolveVariant($product, $variations);

        if ($product->variants->isNotEmpty() && $variant === null) {
            throw ValidationException::withMessages([
                'variations' => trans('treatmentreservation::admin.manual_booking.invalid_variant'),
            ]);
        }

        $total = $this->calculateTotal($product, $variant, $options);

        return [
            'product' => $product,
            'variant' => $variant,
            'options' => $options,
            'variations' => $variations,
            'total' => $total,
        ];
    }


    /**
     * @param array<int|string, mixed> $options
     * @return array<int|string, mixed>
     */
    private function normalizeOptions(array $options): array
    {
        return array_filter($options, fn ($value) => $value !== null && $value !== '');
    }


    /**
     * @param array<int|string, mixed> $variations
     * @return array<string, string>
     */
    private function normalizeVariations(array $variations): array
    {
        $normalized = [];

        foreach ($variations as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $normalized[(string) $key] = (string) $value;
        }

        return $normalized;
    }


    /**
     * @param array<int|string, mixed> $options
     */
    private function validateOptions(Product $product, array $options): void
    {
        foreach ($product->options as $option) {
            $value = $options[$option->id] ?? null;

            if ($option->is_required && ($value === null || $value === '')) {
                throw ValidationException::withMessages([
                    "options.{$option->id}" => trans('cart::validation.this_field_is_required'),
                ]);
            }

            if ($value === null || $value === '') {
                continue;
            }

            if (in_array($option->type, ['dropdown', 'radio', 'radio_custom'], true)) {
                $allowed = $option->values->pluck('id')->map(fn ($id) => (string) $id)->all();

                if (! in_array((string) $value, $allowed, true)) {
                    throw ValidationException::withMessages([
                        "options.{$option->id}" => trans('cart::validation.the_selected_option_is_invalid'),
                    ]);
                }
            }

            if (in_array($option->type, ['checkbox', 'checkbox_custom', 'multiple_select'], true)) {
                $selected = is_array($value) ? $value : [$value];
                $allowed = $option->values->pluck('id')->map(fn ($id) => (string) $id)->all();

                foreach ($selected as $selectedValue) {
                    if (! in_array((string) $selectedValue, $allowed, true)) {
                        throw ValidationException::withMessages([
                            "options.{$option->id}" => trans('cart::validation.the_selected_option_is_invalid'),
                        ]);
                    }
                }
            }
        }
    }


    /**
     * @param array<string, string> $variations
     */
    private function validateVariations(Product $product, array $variations): void
    {
        if ($product->variations->isEmpty()) {
            return;
        }

        foreach ($product->variations as $variation) {
            $selectedUid = $variations[$variation->uid] ?? null;

            if (! $selectedUid) {
                throw ValidationException::withMessages([
                    "variations.{$variation->uid}" => trans('cart::validation.this_field_is_required'),
                ]);
            }

            $allowed = $variation->values->pluck('uid')->all();

            if (! in_array($selectedUid, $allowed, true)) {
                throw ValidationException::withMessages([
                    "variations.{$variation->uid}" => trans('cart::validation.the_selected_option_is_invalid'),
                ]);
            }
        }
    }


    /**
     * @param array<string, string> $variations
     */
    private function resolveVariant(Product $product, array $variations): ?ProductVariant
    {
        if ($product->variants->isEmpty()) {
            return null;
        }

        $selectedUids = implode('.', array_values($variations));

        return $product->variants->first(function (ProductVariant $variant) use ($selectedUids) {
            if (! $variant->uids) {
                return false;
            }

            if ($variant->uids === $selectedUids) {
                return true;
            }

            return in_array($selectedUids, explode('.', $variant->uids), true);
        });
    }


    /**
     * @param array<int|string, mixed> $options
     */
    private function calculateTotal(Product $product, ?ProductVariant $variant, array $options): float
    {
        $item = $variant ?? $product;
        $chosenOptions = new ChosenProductOptions($product, $options);

        $cartItem = new CartItem(new ItemCollection([
            'id' => $product->id,
            'quantity' => 1,
            'attributes' => [
                'product' => $product,
                'variant' => $variant,
                'item' => $item,
                'options' => $chosenOptions->getEntities(),
                'variations' => collect(),
            ],
        ]));

        return $cartItem->totalPrice()->convertToCurrentCurrency()->amount();
    }


    /**
     * @return array<int, string>
     */
    public static function paymentStatusOptions(): array
    {
        return Order::paymentStatuses();
    }
}
