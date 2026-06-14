<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Support\Collection;
use Modules\Attribute\Database\Seeders\ImmaSeriLarisAttributeSetsSeeder;
use Modules\Attribute\Entities\Attribute;
use Modules\Attribute\Entities\AttributeValue;
use Modules\Attribute\Entities\ProductAttributeValue;
use Modules\Product\Entities\Product;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

class TreatmentProductDurationService
{
    private const ALLOWED_MINUTES = [30, 45, 60, 90, 120];

    /** @var array<string, int> */
    private const SLUG_OVERRIDES = [
        'aura-seriputeh-body-bleaching' => 90,
        'body-bleaching-ala-carte' => 90,
        'pakej-seri-pengantin-baru-pakej-rahmah' => 120,
        'manicure' => 30,
        'pedicure' => 30,
        'remove-nail-polish' => 30,
        'brow-lamination' => 30,
        'brow-lamination-tint' => 30,
        'waxing-treatment' => 30,
        'dimple' => 30,
    ];

    /**
     * Most specific category wins (spa is last — generic fallback only).
     *
     * @var list<array{slug: string, minutes: int}>
     */
    private const CATEGORY_RULES = [
        ['slug' => 'bridal-package', 'minutes' => 120],
        ['slug' => 'manicure-pedicure', 'minutes' => 30],
        ['slug' => 'waxing', 'minutes' => 30],
        ['slug' => 'eyelash-brow', 'minutes' => 30],
        ['slug' => 'body-care', 'minutes' => 90],
        ['slug' => 'massage', 'minutes' => 90],
        ['slug' => 'laser', 'minutes' => 45],
        ['slug' => 'facial', 'minutes' => 60],
        ['slug' => 'drip', 'minutes' => 60],
        ['slug' => 'benang', 'minutes' => 45],
        ['slug' => 'botox', 'minutes' => 30],
        ['slug' => 'filler', 'minutes' => 45],
        ['slug' => 'dimple', 'minutes' => 30],
        ['slug' => 'skin-booster', 'minutes' => 60],
        ['slug' => 'whitening-booster', 'minutes' => 60],
        ['slug' => 'injection', 'minutes' => 45],
        ['slug' => 'lipo', 'minutes' => 60],
        ['slug' => 'aesthetic-estetik', 'minutes' => 60],
        ['slug' => 'skincare', 'minutes' => 45],
        ['slug' => 'surgery', 'minutes' => 120],
        ['slug' => 'spa', 'minutes' => 60],
    ];


    public function ensureDurationAttribute(): Attribute
    {
        $attribute = $this->findDurationAttribute();

        if ($attribute) {
            return $attribute;
        }

        (new ImmaSeriLarisAttributeSetsSeeder())->run();

        return $this->findDurationAttribute() ?? throw new \RuntimeException('Duration attribute could not be created.');
    }


    private function findDurationAttribute(): ?Attribute
    {
        return Attribute::query()
            ->whereIn('slug', ['spa-duration', 'duration'])
            ->orderByRaw("FIELD(slug, 'spa-duration', 'duration')")
            ->first();
    }


    /**
     * @return array{synced: int, skipped: int, parsed: int, defaulted: int, details: list<string>}
     */
    public function syncAllVirtualProducts(bool $dryRun = false, bool $force = false): array
    {
        $attribute = $this->ensureDurationAttribute();
        $valueMap = $this->durationValueMap($attribute);

        $stats = [
            'synced' => 0,
            'skipped' => 0,
            'parsed' => 0,
            'defaulted' => 0,
            'details' => [],
        ];

        Product::query()
            ->withoutGlobalScope('active')
            ->where('is_virtual', true)
            ->with(['translations', 'categories.translations', 'attributes.values.attributeValue'])
            ->orderBy('id')
            ->chunkById(50, function (Collection $products) use ($attribute, $valueMap, $dryRun, $force, &$stats) {
                foreach ($products as $product) {
                    $result = $this->syncProduct($product, $attribute, $valueMap, $dryRun, $force);

                    $stats[$result['status']]++;
                    $stats['details'][] = sprintf(
                        '#%d %s → %d min (%s)',
                        $product->id,
                        $product->name,
                        $result['minutes'],
                        $result['source']
                    );
                }
            });

        return $stats;
    }


    /**
     * @param  array<int, AttributeValue>  $valueMap
     * @return array{status: 'synced'|'skipped', minutes: int, source: string}
     */
    public function syncProduct(
        Product $product,
        ?Attribute $attribute = null,
        ?array $valueMap = null,
        bool $dryRun = false,
        bool $force = false
    ): array {
        $attribute ??= $this->ensureDurationAttribute();
        $valueMap ??= $this->durationValueMap($attribute);

        [$minutes, $source] = $this->resolveMinutesForProduct($product);
        $minutes = $this->snapToAllowedDuration($minutes);

        $existing = $product->attributes
            ->first(fn ($row) => (int) $row->attribute_id === (int) $attribute->id);

        if ($existing && ! $force) {
            $currentMinutes = $this->minutesFromValueLabel($existing->values->first()?->value ?? '');

            if ($currentMinutes === $minutes) {
                return [
                    'status' => 'skipped',
                    'minutes' => $minutes,
                    'source' => 'unchanged',
                ];
            }
        }

        if (! $dryRun) {
            $this->assignDuration($product, $attribute, $valueMap[$minutes]);
        }

        return [
            'status' => 'synced',
            'minutes' => $minutes,
            'source' => $source,
        ];
    }


    public function resolveMinutesForProduct(Product $product): array
    {
        if (isset(self::SLUG_OVERRIDES[$product->slug])) {
            return [self::SLUG_OVERRIDES[$product->slug], 'override'];
        }

        $fromName = $this->guessMinutesFromName($product->name);

        if ($fromName !== null) {
            return [$fromName, 'name'];
        }

        $fromCategory = $this->guessMinutesFromCategories($product);

        if ($fromCategory !== null) {
            return [$fromCategory, 'category'];
        }

        $text = implode(' ', array_filter([
            $product->short_description,
            $product->description,
        ]));

        $parsed = $this->parseMinutesFromText($text);

        if ($parsed !== null) {
            return [$this->snapToAllowedDuration($parsed), 'description'];
        }

        return [BeauticianAvailabilityService::SLOT_MINUTES, 'default'];
    }


    private function parseMinutesFromText(string $text): ?int
    {
        $plain = html_entity_decode(strip_tags($text), ENT_QUOTES, 'UTF-8');
        $candidates = [];

        if (preg_match_all('/(\d+)\s*(?:min|minit|minute|minutes)\b/i', $plain, $matches)) {
            foreach ($matches[1] as $value) {
                $minutes = (int) $value;

                if ($minutes >= 20) {
                    $candidates[] = $minutes;
                }
            }
        }

        if (preg_match_all('/(\d+)\s*(?:jam|hour|hours)\b/i', $plain, $hourMatches)) {
            foreach ($hourMatches[1] as $value) {
                $candidates[] = (int) $value * 60;
            }
        }

        if ($candidates === []) {
            return null;
        }

        return max($candidates);
    }


    private function guessMinutesFromName(string $name): ?int
    {
        $normalized = mb_strtolower(trim($name));

        if (
            str_contains($normalized, 'pakej')
            || str_contains($normalized, 'pengantin')
            || str_contains($normalized, 'bridal')
            || str_contains($normalized, 'package')
        ) {
            return 120;
        }

        if (str_contains($normalized, 'combo')) {
            return 90;
        }

        if (
            preg_match('/\b(3d|4d|extension|volume look|mega vollume|keratin lash)\b/u', $normalized)
        ) {
            return 60;
        }

        if (
            str_contains($normalized, 'booster')
            || str_contains($normalized, 'profilo')
            || str_contains($normalized, 'rejuran')
            || str_contains($normalized, 'prp')
        ) {
            return 60;
        }

        if (
            str_contains($normalized, 'full body')
            || str_contains($normalized, 'body bleaching')
            || str_contains($normalized, 'bleaching')
        ) {
            return 90;
        }

        if (str_contains($normalized, 'massage')) {
            return 90;
        }

        if (str_contains($normalized, 'drip')) {
            return 60;
        }

        if (str_contains($normalized, 'benang') || str_contains($normalized, 'filler')) {
            return 45;
        }

        if (
            str_contains($normalized, 'manicure')
            || str_contains($normalized, 'pedicure')
            || str_contains($normalized, 'nail polish')
            || str_contains($normalized, 'brow')
            || str_contains($normalized, 'lash')
            || str_contains($normalized, 'wax')
            || str_contains($normalized, 'botox')
            || str_contains($normalized, 'injection')
        ) {
            return 30;
        }

        return null;
    }


    private function guessMinutesFromCategories(Product $product): ?int
    {
        $slugs = $product->categories->pluck('slug')->all();

        foreach (self::CATEGORY_RULES as $rule) {
            if (in_array($rule['slug'], $slugs, true)) {
                return $rule['minutes'];
            }
        }

        return null;
    }


    private function snapToAllowedDuration(int $minutes): int
    {
        $closest = self::ALLOWED_MINUTES[0];
        $smallestDiff = abs($minutes - $closest);

        foreach (self::ALLOWED_MINUTES as $allowed) {
            $diff = abs($minutes - $allowed);

            if ($diff < $smallestDiff) {
                $smallestDiff = $diff;
                $closest = $allowed;
            }
        }

        return $closest;
    }


    /**
     * @return array<int, AttributeValue>
     */
    private function durationValueMap(Attribute $attribute): array
    {
        $map = [];

        foreach (AttributeValue::query()->where('attribute_id', $attribute->id)->get() as $value) {
            $minutes = $this->minutesFromValueLabel($value->value ?? '');

            if ($minutes !== null) {
                $map[$minutes] = $value;
            }
        }

        return $map;
    }


    private function minutesFromValueLabel(string $label): ?int
    {
        if (preg_match('/(\d+)/', $label, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }


    private function assignDuration(Product $product, Attribute $attribute, AttributeValue $value): void
    {
        $product->attributes()
            ->where('attribute_id', $attribute->id)
            ->get()
            ->each(function ($productAttribute) {
                ProductAttributeValue::query()
                    ->where('product_attribute_id', $productAttribute->id)
                    ->delete();

                $productAttribute->delete();
            });

        $productAttribute = $product->attributes()->create([
            'attribute_id' => $attribute->id,
        ]);

        ProductAttributeValue::query()->insert([
            'product_attribute_id' => $productAttribute->id,
            'attribute_value_id' => $value->id,
        ]);
    }


    public function verifyBookingDuration(TreatmentBooking $booking): int
    {
        $booking->loadMissing([
            'product.attributes.attribute',
            'product.attributes.values.attributeValue',
        ]);

        return $booking->resolveSlotDurationMinutes();
    }
}
