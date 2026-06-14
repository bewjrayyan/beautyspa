<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Media\Entities\File;
use Modules\Media\Services\ImageOptimizationService;

class ManualBookingPaymentReceiptService
{
    public function store(?UploadedFile $file, ?int $existingFileId = null): ?int
    {
        if (! $file) {
            return $existingFileId;
        }

        $diskName = config('filesystems.default');
        $disk = Storage::disk($diskName);
        $path = $disk->putFile('media/manual-booking-receipts', $file);

        if ($path === false) {
            throw new \RuntimeException('Failed to store payment receipt.');
        }

        $optimized = app(ImageOptimizationService::class)->processUploadedFile($file, $path, $diskName);

        return File::create([
            'user_id' => auth()->id(),
            'disk' => $diskName,
            'filename' => substr($file->getClientOriginalName(), 0, 255),
            'path' => $optimized['path'],
            'extension' => $optimized['extension'],
            'mime' => $optimized['mime'],
            'size' => $optimized['size'],
            'responsive_paths' => $optimized['responsive_paths'] ?: null,
        ])->id;
    }
}
