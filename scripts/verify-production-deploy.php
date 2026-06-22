<?php

/**
 * Run on the production server after git pull:
 *   php scripts/verify-production-deploy.php
 */

$root = dirname(__DIR__);

$requiredFiles = [
    'modules/Support/Cache/CacheHealth.php',
    'modules/Support/Cache/TaggedCache.php',
    'config/filesystems.php',
    'modules/Media/Entities/File.php',
    'modules/Core/Support/WritableStorageBootstrap.php',
];

$requiredDirs = [
    'storage/app/public',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'bootstrap/cache',
];

$errors = [];

foreach ($requiredFiles as $file) {
    if (! is_file($root.'/'.$file)) {
        $errors[] = "Missing file: {$file}";
    }
}

foreach ($requiredDirs as $dir) {
    if (! is_dir($root.'/'.$dir)) {
        $errors[] = "Missing directory: {$dir}";
    }
}

$filesystemsContents = @file_get_contents($root.'/config/filesystems.php') ?: '';

if (! str_contains($filesystemsContents, "storage_path('app/public')")
    || str_contains($filesystemsContents, "public_path('storage')")) {
    $errors[] = 'config/filesystems.php public_storage root is outdated (expected storage/app/public).';
}

$link = $root.'/public/storage';
$target = $root.'/storage/app/public';

if (! is_link($link) && ! is_dir($link)) {
    $errors[] = 'public/storage symlink is missing. Run: php artisan storage:link';
} elseif (is_link($link)) {
    $resolved = realpath($link);
    $targetResolved = realpath($target);

    if ($resolved === false || $targetResolved === false || $resolved !== $targetResolved) {
        $errors[] = 'public/storage symlink is broken. Run: rm -f public/storage && ln -s ../storage/app/public public/storage';
    }
}

$manifestPath = $root.'/public/build/manifest.json';

if (! is_file($manifestPath)) {
    $errors[] = 'Missing public/build/manifest.json — run npm run build locally and commit public/build/, or git pull the full release.';
} else {
    $manifest = json_decode(file_get_contents($manifestPath), true);

    if (! is_array($manifest)) {
        $errors[] = 'public/build/manifest.json is invalid JSON.';
    } else {
        $assetPaths = [];

        foreach ($manifest as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            if (! empty($entry['file'])) {
                $assetPaths[$entry['file']] = true;
            }

            if (! empty($entry['css']) && is_array($entry['css'])) {
                foreach ($entry['css'] as $cssPath) {
                    $assetPaths[$cssPath] = true;
                }
            }
        }

        $missingAssets = [];

        foreach (array_keys($assetPaths) as $assetPath) {
            if (! is_file($root.'/public/build/'.$assetPath)) {
                $missingAssets[] = $assetPath;
            }
        }

        if ($missingAssets !== []) {
            $preview = implode(', ', array_slice($missingAssets, 0, 5));
            $suffix = count($missingAssets) > 5 ? ' …' : '';
            $errors[] = count($missingAssets).' Vite build asset(s) missing from public/build/ (e.g. '.$preview.$suffix.'). Ensure public/build/ was deployed in full.';
        }
    }
}

if ($errors === []) {
    echo "OK: production deploy checks passed.\n";
    exit(0);
}

echo "Deploy verification failed:\n";

foreach ($errors as $error) {
    echo " - {$error}\n";
}

echo "\nFix: cd to project root, run:\n";
echo "  git pull origin main\n";
echo "  composer dump-autoload -o\n";
echo "  php artisan config:clear && php artisan cache:clear && php artisan view:clear\n";
echo "If assets are still missing, redeploy the full public/build/ directory from the release tag.\n";

exit(1);
