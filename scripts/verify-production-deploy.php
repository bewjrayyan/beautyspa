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

if ($errors === []) {
    echo "OK: production deploy checks passed.\n";
    exit(0);
}

echo "Deploy verification failed:\n";

foreach ($errors as $error) {
    echo " - {$error}\n";
}

echo "\nFix: cd to project root, run git pull origin main && composer dump-autoload -o\n";

exit(1);
