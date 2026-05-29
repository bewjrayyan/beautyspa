<?php

namespace Modules\Media\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Modules\Media\Admin\MediaTable;
use Modules\Media\IconResolver;
use Modules\Media\Support\FileUsage;
use Modules\Support\Eloquent\Model;
use Modules\User\Entities\User;

class File extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be visible in serialization.
     *
     * @var array
     */
    protected $visible = ['id', 'filename', 'path', 'srcset'];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'responsive_paths' => 'array',
    ];


    /**
     * Perform any actions required after the model boots.
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleting(function ($file) {
            $disk = Storage::disk($file->disk);
            $disk->delete($file->getRawOriginal('path'));

            foreach ($file->responsive_paths ?? [] as $variantPath) {
                if (is_string($variantPath) && $variantPath !== '') {
                    $disk->delete($variantPath);
                }
            }
        });
    }


    /**
     * Get the user that uploaded the file.
     *
     * @return void
     */
    public function uploader()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Get the file's path.
     *
     * @param string $path
     *
     * @return string|null
     */
    public function getPathAttribute($path)
    {
        if (!is_null($path)) {
            return cdn_url(Storage::disk($this->disk)->url($path), 'media');
        }
    }


    /**
     * Responsive srcset for storefront images.
     */
    public function getSrcsetAttribute(): string
    {
        if (! $this->id) {
            return '';
        }

        $entries = [];
        $disk = Storage::disk($this->disk);

        foreach ($this->normalizedResponsivePaths() as $width => $variantPath) {
            if (is_string($variantPath) && $variantPath !== '') {
                $entries[] = cdn_url($disk->url($variantPath), 'media') . ' ' . $width . 'w';
            }
        }

        $mainPath = $this->attributes['path'] ?? null;

        if (is_string($mainPath) && $mainPath !== '') {
            $entries[] = cdn_url($disk->url($mainPath), 'media') . ' ' . $this->responsiveMaxWidth() . 'w';
        }

        return implode(', ', array_unique($entries));
    }


    /**
     * @return array<int, string>
     */
    private function normalizedResponsivePaths(): array
    {
        $paths = $this->responsive_paths ?? [];

        if (is_string($paths)) {
            $paths = json_decode($paths, true) ?? [];
        }

        ksort($paths, SORT_NUMERIC);

        return $paths;
    }


    private function responsiveMaxWidth(): int
    {
        $paths = $this->normalizedResponsivePaths();

        if ($paths !== []) {
            return (int) max(array_keys($paths));
        }

        $dimensions = $this->imageDimensions();

        if ($dimensions && preg_match('/^(\d+)/', $dimensions, $matches)) {
            return (int) $matches[1];
        }

        return 1920;
    }


    /**
     * Get file's real path.
     *
     * @return void
     */
    public function realPath()
    {
        if (!is_null($this->attributes['path'])) {
            return Storage::disk($this->disk)->path($this->attributes['path']);
        }
    }


    /**
     * Determine if the file type is image.
     *
     * @return bool
     */
    public function isImage()
    {
        return strtok($this->mime, '/') === 'image';
    }


    /**
     * Get human-readable file size.
     *
     * @return string
     */
    public function formattedSize()
    {
        $bytes = (int) $this->size;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return $bytes . ' B';
    }


    /**
     * Get image dimensions as "width × height", or null.
     *
     * @return string|null
     */
    public function imageDimensions()
    {
        if (!$this->isImage()) {
            return null;
        }

        $path = $this->realPath();

        if (!$path || !is_file($path)) {
            return null;
        }

        $info = @getimagesize($path);

        if (!$info) {
            return null;
        }

        return $info[0] . ' × ' . $info[1];
    }


    /**
     * Get the file's icon.
     *
     * @return string
     */
    public function icon()
    {
        return IconResolver::resolve($this->mime);
    }


    /**
     * Media attached only to soft-deleted products/variants (cleanup candidates).
     */
    public function scopeUnlinkedFromProducts(Builder $query): Builder
    {
        $orphanIds = FileUsage::orphanedCleanupFileIds();

        if ($orphanIds === []) {
            return $query->whereRaw('0 = 1');
        }

        return $query->whereIn('files.id', $orphanIds);
    }


    public static function countUnlinkedFromProducts(): int
    {
        return count(FileUsage::orphanedCleanupFileIds());
    }


    /**
     * @param list<int> $fileIds
     *
     * @return array<int, true>
     */
    public static function usedInSystemLookup(array $fileIds): array
    {
        return FileUsage::activelyUsedLookup($fileIds);
    }


    /**
     * @param list<int> $fileIds
     *
     * @return array<int, true>
     */
    public static function orphanedCleanupLookup(array $fileIds): array
    {
        return FileUsage::orphanedCleanupLookup($fileIds);
    }


    public static function isUsedInSystem(int $fileId): bool
    {
        return isset(static::usedInSystemLookup([$fileId])[$fileId]);
    }


    /**
     * Get table data for the resource
     *
     * @return JsonResponse
     */
    public function table($request)
    {
        $query = $this->newQuery()
            ->when(!is_null($request->type) && $request->type !== 'null', function ($query) use ($request) {
                $query->where('mime', 'LIKE', "{$request->type}/%");
            });

        return new MediaTable($query);
    }
}
