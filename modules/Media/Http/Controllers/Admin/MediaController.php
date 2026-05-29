<?php

namespace Modules\Media\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Modules\Media\Entities\File;
use Illuminate\Support\Facades\Storage;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Media\Http\Requests\UploadMediaRequest;
use Modules\Media\Services\ImageOptimizationService;

class MediaController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = File::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'media::media.media';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'media::admin.media';


    /**
     * Store a newly created media in storage.
     *
     * @param UploadMediaRequest $request
     *
     * @return Response
     */
    public function store(UploadMediaRequest $request)
    {
        $file = $request->file('file');
        $diskName = config('filesystems.default');
        $disk = Storage::disk($diskName);
        $path = $disk->putFile('media', $file);

        if ($path === false) {
            abort(500, 'Failed to store the uploaded file. Check storage directory permissions.');
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
        ]);
    }


    /**
     * Remove the specified resources from storage.
     *
     * @param string $ids
     *
     * @return Response
     */
    public function destroy(string $ids)
    {
        $this->deleteFilesByIds(explode(',', $ids));
    }


    public function bulkDestroy(Request $request): JsonResponse
    {
        $ids = $request->input('ids', []);

        if (! is_array($ids)) {
            $ids = explode(',', (string) $ids);
        }

        $deleted = $this->deleteFilesByIds($ids);

        return response()->json([
            'message' => trans('media::messages.deleted_count', ['count' => $deleted]),
            'deleted' => $deleted,
        ]);
    }


    /**
     * @param array<int|string> $ids
     */
    private function deleteFilesByIds(array $ids): int
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if ($ids === []) {
            return 0;
        }

        $files = File::whereIn('id', $ids)->get();

        foreach ($files as $file) {
            $file->delete();
        }

        return $files->count();
    }


    /**
     * Get paginated media files for the grid view.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function grid(Request $request): JsonResponse
    {
        $query = File::query()->latest();

        if ($request->filled('query')) {
            $query->where('filename', 'LIKE', '%' . $request->get('query') . '%');
        }

        if ($request->filled('type') && $request->get('type') !== 'null') {
            $query->where('mime', 'LIKE', $request->get('type') . '/%');
        }

        $filterUnlinkedProducts = $request->has('unlinked_products')
            && $request->boolean('unlinked_products');

        if ($filterUnlinkedProducts) {
            $query->unlinkedFromProducts();
        }

        $files = $query->paginate((int) $request->get('per_page', 20));
        $fileIds = $files->getCollection()->pluck('id')->all();
        $usedLookup = File::usedInSystemLookup($fileIds);
        $orphanLookup = File::orphanedCleanupLookup($fileIds);

        return response()->json([
            'data' => $files->through(function (File $file) use ($usedLookup, $orphanLookup) {
                return [
                    'id' => $file->id,
                    'filename' => $file->filename,
                    'path' => $file->path,
                    'url' => $file->path,
                    'is_image' => $file->isImage(),
                    'icon' => $file->icon(),
                    'type' => strtok($file->mime, '/'),
                    'size' => $file->formattedSize(),
                    'dimensions' => $file->imageDimensions(),
                    'created' => $file->created_at->diffForHumans(),
                    'used_in_system' => isset($usedLookup[$file->id]),
                    'orphaned_for_cleanup' => isset($orphanLookup[$file->id]),
                    'linked_to_product' => isset($usedLookup[$file->id]),
                ];
            })->items(),
            'total' => $files->total(),
            'per_page' => $files->perPage(),
            'current_page' => $files->currentPage(),
            'last_page' => $files->lastPage(),
            'meta' => [
                'unlinked_products_count' => File::countUnlinkedFromProducts(),
                'filter_unlinked_products' => $filterUnlinkedProducts,
            ],
        ]);
    }
}
