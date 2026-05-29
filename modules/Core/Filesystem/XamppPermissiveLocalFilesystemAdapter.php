<?php

namespace Modules\Core\Filesystem;

use League\Flysystem\Config;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\UnixVisibility\VisibilityConverter;
use League\Flysystem\Visibility;
use League\MimeTypeDetection\MimeTypeDetector;

/**
 * On XAMPP/macOS, Apache and CLI often run as different users. chmod() then fails
 * on existing cache dirs even when they are already world-writable (775/777).
 */
class XamppPermissiveLocalFilesystemAdapter extends LocalFilesystemAdapter
{
    public function __construct(
        string $location,
        ?VisibilityConverter $visibility = null,
        int $writeFlags = LOCK_EX,
        int $linkHandling = self::DISALLOW_LINKS,
        ?MimeTypeDetector $mimeTypeDetector = null,
    ) {
        parent::__construct(
            $location,
            $visibility ?? PortableVisibilityConverter::fromArray([
                'file' => [
                    'public' => 0666,
                    'private' => 0666,
                ],
                'dir' => [
                    'public' => 0777,
                    'private' => 0777,
                ],
            ], Visibility::PUBLIC),
            $writeFlags,
            $linkHandling,
            $mimeTypeDetector,
        );
    }

    public function createDirectory(string $path, Config $config): void
    {
        try {
            parent::createDirectory($path, $config);
        } catch (UnableToSetVisibility) {
            // Existing directory owned by another user — ignore if usable.
        }
    }

    public function setVisibility(string $path, string $visibility): void
    {
        try {
            parent::setVisibility($path, $visibility);
        } catch (UnableToSetVisibility) {
            // Non-owner cannot chmod on macOS; skip when permissions are already shared.
        }
    }
}
