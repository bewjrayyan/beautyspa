<?php

namespace Modules\Setting\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Modules\Setting\Services\CatalogSyncService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CatalogSyncController
{
    public function __construct(
        private CatalogSyncService $catalogSync
    ) {
    }

    public function export(): BinaryFileResponse
    {
        $bundle = $this->catalogSync->createBundle();

        return response()->download(
            $bundle['path'],
            config('setting.catalog_sync.bundle_filename', 'catalog-bundle.zip'),
            ['Content-Type' => 'application/zip']
        )->deleteFileAfterSend(false);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'catalog_bundle' => 'required|file|mimes:zip|max:512000',
        ]);

        $uploaded = $request->file('catalog_bundle');
        $tempPath = storage_path('app/catalog-upload-' . uniqid() . '.zip');

        File::put($tempPath, file_get_contents($uploaded->getRealPath()));

        try {
            $counts = $this->catalogSync->importBundle($tempPath);

            return redirect()
                ->to(route('admin.settings.edit') . '?tab=system')
                ->with('success', trans('setting::messages.catalog_sync_import_success', $counts));
        } catch (\Throwable $exception) {
            return redirect()
                ->to(route('admin.settings.edit') . '?tab=system')
                ->with('error', $exception->getMessage());
        } finally {
            File::delete($tempPath);
        }
    }

    public function pull(Request $request): RedirectResponse
    {
        $request->validate([
            'catalog_sync_source_url' => 'nullable|url|max:500',
        ]);

        if ($request->filled('catalog_sync_source_url')) {
            setting(['catalog_sync_source_url' => $request->input('catalog_sync_source_url')]);
        }

        try {
            $counts = $this->catalogSync->pullFromSource($request->input('catalog_sync_source_url'));

            return redirect()
                ->to(route('admin.settings.edit') . '?tab=system')
                ->with('success', trans('setting::messages.catalog_sync_pull_success', $counts));
        } catch (\Throwable $exception) {
            return redirect()
                ->to(route('admin.settings.edit') . '?tab=system')
                ->with('error', $exception->getMessage());
        }
    }

    public function importStored(): RedirectResponse
    {
        try {
            $counts = $this->catalogSync->importBundle($this->catalogSync->bundlePath());

            return redirect()
                ->to(route('admin.settings.edit') . '?tab=system')
                ->with('success', trans('setting::messages.catalog_sync_import_success', $counts));
        } catch (\Throwable $exception) {
            return redirect()
                ->to(route('admin.settings.edit') . '?tab=system')
                ->with('error', $exception->getMessage());
        }
    }
}
