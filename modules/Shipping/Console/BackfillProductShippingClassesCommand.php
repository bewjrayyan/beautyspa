<?php

namespace Modules\Shipping\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Modules\Product\Entities\Product;
use Modules\Shipping\Entities\ShippingClass;

class BackfillProductShippingClassesCommand extends Command
{
    protected $signature = 'shipping:backfill-product-classes
                            {--dry-run : Preview changes without writing to the database}
                            {--class= : Shipping class ID to assign (defaults to first active class)}';

    protected $description = 'Assign a shipping class to physical products that are missing one';

    public function handle(): int
    {
        if (! Schema::hasColumn('products', 'shipping_class_id')) {
            $this->error('Column products.shipping_class_id does not exist. Run migrations first.');

            return self::FAILURE;
        }

        $classId = $this->resolveClassId();

        if ($classId === null) {
            $this->error('No shipping class found. Create one first or pass --class=ID.');

            return self::FAILURE;
        }

        $class = ShippingClass::withoutGlobalScope('active')->find($classId);
        $dryRun = (bool) $this->option('dry-run');
        $slugs = Product::PHYSICAL_PRODUCT_SLUGS;

        $query = Product::withoutGlobalScopes()
            ->whereNull('shipping_class_id')
            ->where(function ($q) use ($slugs) {
                $q->where('is_virtual', false)->orWhereIn('slug', $slugs);
            });

        $count = (clone $query)->count();

        if ($count === 0) {
            $this->info('All physical products already have a shipping class.');

            return self::SUCCESS;
        }

        if (! $dryRun) {
            $query->update(['shipping_class_id' => $classId]);
        }

        $label = $class?->name ?? "#{$classId}";
        $this->info(($dryRun ? '[dry-run] Would assign' : 'Assigned') . " shipping class \"{$label}\" (#{$classId}) to {$count} product(s).");

        return self::SUCCESS;
    }


    private function resolveClassId(): ?int
    {
        $option = $this->option('class');

        if ($option !== null && $option !== '') {
            return (int) $option;
        }

        $id = ShippingClass::query()->orderBy('id')->value('id')
            ?? ShippingClass::withoutGlobalScope('active')->orderBy('id')->value('id');

        return $id !== null ? (int) $id : null;
    }
}
