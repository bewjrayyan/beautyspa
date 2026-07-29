<?php

namespace Modules\Order\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Modules\Media\Entities\File;

class SecureOrderDocumentsCommand extends Command
{
    protected $signature = 'orders:secure-documents
                            {--apply : Copy legacy payment proofs to private storage and update file records}
                            {--purge-public : Delete legacy public order-whatsapp copies after verification}';

    protected $description = 'Audit or secure legacy public order payment proofs and generated WhatsApp documents.';

    public function handle(): int
    {
        $legacyProofs = File::query()
            ->whereIn('id', function ($query): void {
                $query->select('payment_proof_file_id')
                    ->from('orders')
                    ->whereNotNull('payment_proof_file_id');
            })
            ->where(function ($query): void {
                $query->whereNull('disk')->orWhere('disk', '!=', 'private');
            });

        $count = (clone $legacyProofs)->count();
        $this->info("Legacy public payment proof records: {$count}");

        if (! $this->option('apply')) {
            $this->warn('Dry run only. Re-run with --apply after reviewing storage backups.');

            return self::SUCCESS;
        }

        $migrated = 0;
        $legacyProofs->orderBy('id')->chunkById(50, function ($files) use (&$migrated): void {
            foreach ($files as $file) {
                $source = Storage::disk($file->disk);
                $private = Storage::disk('private');
                $paths = array_values(array_filter(array_merge(
                    [(string) $file->getRawOriginal('path')],
                    (array) ($file->responsive_paths ?? [])
                )));

                foreach ($paths as $path) {
                    if (! $source->exists($path)) {
                        throw new \RuntimeException("Missing source file for media #{$file->id}: {$path}");
                    }

                    if (! $private->put($path, $source->get($path)) || ! $private->exists($path)) {
                        throw new \RuntimeException("Unable to verify private copy for media #{$file->id}: {$path}");
                    }
                }

                $file->forceFill(['disk' => 'private'])->save();
                $migrated++;
            }
        });

        $this->info("Private payment proof records updated: {$migrated}");

        if ($this->option('purge-public')) {
            Storage::disk('public')->deleteDirectory('order-whatsapp');
            $this->warn('Legacy public order-whatsapp copies were deleted.');
        } else {
            $this->warn('Public source copies were retained for rollback. Purge them separately after verification.');
        }

        return self::SUCCESS;
    }
}
