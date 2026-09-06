<?php

namespace Modules\TreatmentReservation\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Media\Entities\File;
use Modules\Media\Services\ImageOptimizationService;
use Modules\TreatmentReservation\Entities\TreatmentBooking;

class ManualBookingPaymentReceiptService
{
    public function store(?UploadedFile $file, ?int $existingFileId = null): ?int
    {
        if (! $file) {
            return $existingFileId;
        }

        $diskName = "private";
        $disk = Storage::disk($diskName);
        $path = $disk->putFile("media/manual-booking-receipts", $file);

        if ($path === false) {
            throw new \RuntimeException("Failed to store payment receipt.");
        }

        $optimized = null;
        try {
            $optimized = app(ImageOptimizationService::class)->processUploadedFile($file, $path, $diskName);
            $stored = File::create([
                "user_id" => auth()->id(),
                "disk" => $diskName,
                "filename" => substr($file->getClientOriginalName(), 0, 255),
                "path" => $optimized["path"],
                "extension" => $optimized["extension"],
                "mime" => $optimized["mime"],
                "size" => $optimized["size"],
                "responsive_paths" => $optimized["responsive_paths"] ?: null,
            ]);
        } catch (\Throwable $exception) {
            $this->deletePaths($diskName, $optimized["path"] ?? $path, $optimized["responsive_paths"] ?? []);
            throw $exception;
        }

        if (DB::transactionLevel() > 0) {
            $mainPath = $optimized["path"];
            $responsivePaths = $optimized["responsive_paths"] ?? [];
            DB::afterRollBack(fn () => $this->deletePaths($diskName, $mainPath, $responsivePaths));
        }

        return $stored->id;
    }

    public function deleteIfUnused(?int $fileId): void
    {
        if (! $fileId || TreatmentBooking::query()->where("payment_receipt_file_id", $fileId)->exists()) {
            return;
        }

        File::query()->find($fileId)?->delete();
    }

    private function deletePaths(string $diskName, string $path, array $responsivePaths): void
    {
        $disk = Storage::disk($diskName);
        $disk->delete($path);
        $disk->delete(array_values(array_filter($responsivePaths, "is_string")));
    }
}
