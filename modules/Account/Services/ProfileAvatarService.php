<?php

namespace Modules\Account\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Media\Entities\File;
use Modules\User\Entities\User;

class ProfileAvatarService
{
    public function syncFromRequest(User $user, ?UploadedFile $upload, bool $remove): void
    {
        if ($remove) {
            $this->deleteCurrent($user);
            $user->syncFiles(['profile' => []]);

            return;
        }

        if (! $upload) {
            return;
        }

        $this->deleteCurrent($user);

        $disk = Storage::disk(config('filesystems.default'));
        $path = $disk->putFile('media/profile', $upload);

        if ($path === false) {
            abort(500, 'Failed to store the uploaded file. Check storage directory permissions.');
        }

        $file = File::create([
            'user_id' => $user->id,
            'disk' => config('filesystems.default'),
            'filename' => substr($upload->getClientOriginalName(), 0, 255),
            'path' => $path,
            'extension' => $upload->guessClientExtension() ?? '',
            'mime' => $upload->getClientMimeType(),
            'size' => $upload->getSize(),
        ]);

        $user->syncFiles(['profile' => [$file->id]]);
    }


    private function deleteCurrent(User $user): void
    {
        $user->loadMissing('files');

        $current = $user->profile_image;

        if ($current->exists) {
            $current->delete();
        }
    }
}
