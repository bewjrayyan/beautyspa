<?php

namespace Modules\Support\Services;

use Dompdf\Options;
use Illuminate\Support\Facades\File;
use RuntimeException;

class DompdfConfigurator
{
    public static function createOptions(bool $allowRemoteAssets = false): Options
    {
        $fontDir = realpath(base_path('vendor/dompdf/dompdf/lib/fonts'));

        if ($fontDir === false) {
            throw new RuntimeException('Dompdf fonts directory is missing.');
        }

        $fontCache = static::ensureDirectory(storage_path('app/dompdf-fonts'))
            ?? static::ensureDirectory(sys_get_temp_dir() . '/fleetcart-dompdf-fonts');
        $tempDir = static::ensureDirectory(storage_path('app/dompdf-tmp'))
            ?? static::ensureDirectory(sys_get_temp_dir() . '/fleetcart-dompdf-tmp');

        if ($fontCache === null || $tempDir === null) {
            throw new RuntimeException('Unable to prepare a writable Dompdf working directory.');
        }

        $options = new Options();
        $options->set('isRemoteEnabled', $allowRemoteAssets);
        $options->set('fontDir', $fontDir);
        $options->set('fontCache', $fontCache);
        $options->set('tempDir', $tempDir);
        // Built-in Helvetica avoids DejaVu subsetting (needs a writable tempDir).
        $options->set('defaultFont', 'helvetica');

        $chroot = realpath(base_path());

        if ($chroot !== false) {
            $options->set('chroot', $chroot);
        }

        return $options;
    }


    private static function ensureDirectory(string $path): ?string
    {
        if ($path === '') {
            return null;
        }

        if (! File::isDirectory($path)) {
            try {
                File::makeDirectory($path, 0755, true);
            } catch (\Throwable) {
                return null;
            }
        }

        return is_writable($path) ? $path : null;
    }
}
