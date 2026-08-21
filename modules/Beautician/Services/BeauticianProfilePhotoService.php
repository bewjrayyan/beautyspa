<?php

namespace Modules\Beautician\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Beautician\Entities\Beautician;
use Modules\Media\Entities\File;
use Modules\Media\Services\ImageOptimizationService;

class BeauticianProfilePhotoService
{
    private const MAX_EDGE = 800;

    private const MAX_BYTES = 2_097_152; // 2 MB

    public function __construct(
        private readonly ImageOptimizationService $optimizer,
    ) {
    }


    public function attachUpload(Beautician $beautician, UploadedFile $upload, ?int $userId = null): File
    {
        $this->assertSafeImage($upload);

        $diskName = (string) config('filesystems.default');
        $disk = Storage::disk($diskName);
        $path = $disk->putFile('media/beauticians', $upload);

        if ($path === false) {
            throw ValidationException::withMessages([
                'profile_image' => [trans('beautician::beauticians.self_registration.validation.profile_image_store_failed')],
            ]);
        }

        $absolute = $disk->path($path);

        if (! is_file($absolute) || @getimagesize($absolute) === false) {
            $disk->delete($path);

            throw ValidationException::withMessages([
                'profile_image' => [trans('beautician::beauticians.self_registration.validation.profile_image_invalid')],
            ]);
        }

        $optimized = $this->optimizer->processUploadedFile($upload, $path, $diskName, self::MAX_EDGE);

        $file = File::create([
            'user_id' => $userId,
            'disk' => $diskName,
            'filename' => substr((string) $upload->getClientOriginalName(), 0, 255),
            'path' => $optimized['path'],
            'extension' => $optimized['extension'],
            'mime' => $optimized['mime'],
            'size' => $optimized['size'],
            'responsive_paths' => $optimized['responsive_paths'] ?: null,
        ]);

        $beautician->syncFiles([
            'profile' => $file->id,
        ]);

        return $file;
    }


    private function assertSafeImage(UploadedFile $upload): void
    {
        if (! $upload->isValid()) {
            throw ValidationException::withMessages([
                'profile_image' => [trans('beautician::beauticians.self_registration.validation.profile_image_invalid')],
            ]);
        }

        if ($upload->getSize() > self::MAX_BYTES) {
            throw ValidationException::withMessages([
                'profile_image' => [trans('beautician::beauticians.self_registration.validation.profile_image_too_large')],
            ]);
        }

        $mime = (string) ($upload->getMimeType() ?: $upload->getClientMimeType());

        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw ValidationException::withMessages([
                'profile_image' => [trans('beautician::beauticians.self_registration.validation.profile_image_type')],
            ]);
        }
    }
}
