<?php

namespace Tests\Unit\Support;

use AestheticCart\Support\ReleaseFilePruner;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ReleaseFilePrunerTest extends TestCase
{
    private string $temporaryRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temporaryRoot = sys_get_temp_dir().'/aestheticcart-release-pruner-'.bin2hex(random_bytes(6));
        mkdir($this->temporaryRoot, 0775, true);
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->temporaryRoot);

        parent::tearDown();
    }

    public function test_it_quarantines_only_paths_declared_by_the_manifest(): void
    {
        $appRoot = $this->temporaryRoot.'/app-root';
        $releaseRoot = $this->temporaryRoot.'/release-root';
        $quarantineRoot = $this->temporaryRoot.'/quarantine';

        $this->writeManifest($appRoot, [
            'modules/Legacy/Gateway.php',
            'modules/LegacySdk',
        ]);
        $this->writeFile($appRoot.'/modules/Legacy/Gateway.php', 'legacy');
        $this->writeFile($appRoot.'/modules/LegacySdk/Client.php', 'legacy-sdk');
        $this->writeFile($appRoot.'/modules/Payment/Chip.php', 'active');
        mkdir($releaseRoot, 0775, true);

        $pruner = new ReleaseFilePruner();
        $result = $pruner->apply($appRoot, $quarantineRoot, $releaseRoot);

        $this->assertSame(['modules/Legacy/Gateway.php', 'modules/LegacySdk'], $result['paths']);
        $this->assertFileDoesNotExist($appRoot.'/modules/Legacy/Gateway.php');
        $this->assertDirectoryDoesNotExist($appRoot.'/modules/LegacySdk');
        $this->assertFileExists($appRoot.'/modules/Payment/Chip.php');
        $this->assertFileExists($result['quarantine'].'/modules/Legacy/Gateway.php');
        $this->assertFileExists($result['quarantine'].'/modules/LegacySdk/Client.php');
        $this->assertSame('legacy', file_get_contents($result['quarantine'].'/modules/Legacy/Gateway.php'));

        $secondRun = $pruner->apply($appRoot, $quarantineRoot, $releaseRoot);

        $this->assertSame([], $secondRun['paths']);
        $this->assertNull($secondRun['quarantine']);
    }

    public function test_it_refuses_to_remove_a_path_present_in_the_new_release(): void
    {
        $appRoot = $this->temporaryRoot.'/app-root';
        $releaseRoot = $this->temporaryRoot.'/release-root';

        $this->writeManifest($appRoot, ['modules/StillActive.php']);
        $this->writeFile($appRoot.'/modules/StillActive.php', 'old');
        $this->writeFile($releaseRoot.'/modules/StillActive.php', 'new');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('active release path');

        (new ReleaseFilePruner())->apply($appRoot, $this->temporaryRoot.'/quarantine', $releaseRoot);
    }

    #[DataProvider('unsafePaths')]
    public function test_it_rejects_unsafe_or_protected_paths(string $path): void
    {
        $appRoot = $this->temporaryRoot.'/app-root';
        $this->writeManifest($appRoot, [$path]);

        $this->expectException(RuntimeException::class);

        (new ReleaseFilePruner())->apply($appRoot, $this->temporaryRoot.'/quarantine');
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function unsafePaths(): iterable
    {
        yield 'parent traversal' => ['../.env'];
        yield 'absolute path' => ['/etc/passwd'];
        yield 'environment file' => ['.env'];
        yield 'private storage' => ['storage/app/private/document.pdf'];
        yield 'vendor directory' => ['vendor/autoload.php'];
        yield 'public uploads' => ['public/uploads/customer-document.pdf'];
    }

    public function test_repository_manifest_contains_only_retired_paths(): void
    {
        $manifest = require dirname(__DIR__, 3).'/app/release-deletions.php';
        $repositoryRoot = dirname(__DIR__, 3);

        $this->assertSame(1, $manifest['version']);
        $this->assertNotEmpty($manifest['paths']);
        $this->assertSame($manifest['paths'], array_values(array_unique($manifest['paths'])));

        foreach ($manifest['paths'] as $path) {
            $this->assertPathHasNoReleaseFiles($repositoryRoot.'/'.$path, $path);
        }
    }

    private function assertPathHasNoReleaseFiles(string $absolutePath, string $manifestPath): void
    {
        if (! is_dir($absolutePath)) {
            $this->assertFileDoesNotExist($absolutePath, "Manifest path still exists: {$manifestPath}");

            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($absolutePath, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($files as $file) {
            $this->fail("Manifest directory still contains a release file: {$manifestPath} ({$file->getPathname()})");
        }

        $this->addToAssertionCount(1);
    }

    /**
     * @param list<string> $paths
     */
    private function writeManifest(string $appRoot, array $paths): void
    {
        $manifest = "<?php\n\nreturn ".var_export([
            'version' => 1,
            'release' => 'test-release',
            'paths' => $paths,
        ], true).";\n";

        $this->writeFile($appRoot.'/'.ReleaseFilePruner::MANIFEST_PATH, $manifest);
    }

    private function writeFile(string $path, string $contents): void
    {
        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents($path, $contents);
    }

    private function deleteDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        foreach (scandir($path) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $fullPath = $path.'/'.$entry;

            if (is_dir($fullPath) && ! is_link($fullPath)) {
                $this->deleteDirectory($fullPath);
            } else {
                unlink($fullPath);
            }
        }

        rmdir($path);
    }
}
