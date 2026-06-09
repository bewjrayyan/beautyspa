<?php

namespace Modules\Setting\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GitHubVersionService
{
    public function sessionKey(): string
    {
        return 'app_version_github_check';
    }

    /**
     * @return array<string, mixed>|null
     */
    public function cachedCheck(): ?array
    {
        $cached = session($this->sessionKey());

        return is_array($cached) && ! empty($cached['available']) ? $cached : null;
    }

    /**
     * @return array{
     *     available: bool,
     *     version: string,
     *     commit: string,
     *     repo: string,
     *     branch: string,
     *     checked_at: string,
     *     update_available: bool,
     *     repository_url: string
     * }
     */
    public function checkLatest(string $installedVersion): array
    {
        [$owner, $repo] = $this->resolveRepository();
        $branch = $this->resolveBranch();
        $versionFile = (string) config('setting.app_version.version_file', 'app/AestheticCart.php');

        $commitResponse = $this->githubRequest(
            "https://api.github.com/repos/{$owner}/{$repo}/commits/{$branch}"
        );

        if ($commitResponse->status() === 404 && ! $this->hasToken()) {
            throw new RuntimeException(trans('setting::settings.form.app_version_github_private_repo'));
        }

        if (! $commitResponse->successful()) {
            throw new RuntimeException(trans('setting::settings.form.app_version_github_check_failed', [
                'status' => $commitResponse->status(),
            ]));
        }

        $sha = (string) $commitResponse->json('sha', '');

        if ($sha === '') {
            throw new RuntimeException(trans('setting::settings.form.app_version_github_check_failed', [
                'status' => $commitResponse->status(),
            ]));
        }

        $version = $this->fetchVersionFromGitHub($owner, $repo, $branch, $versionFile);

        if ($version === null) {
            throw new RuntimeException(trans('setting::settings.form.app_version_github_version_missing'));
        }

        return [
            'available' => true,
            'version' => $version,
            'commit' => substr($sha, 0, 7),
            'repo' => "{$owner}/{$repo}",
            'branch' => $branch,
            'checked_at' => now()->toIso8601String(),
            'update_available' => version_compare($installedVersion, $version, '<'),
            'repository_url' => "https://github.com/{$owner}/{$repo}",
        ];
    }

    /**
     * Download the latest code zip from GitHub and overwrite app files in place.
     * Designed for shared hosting where git/shell_exec are unavailable.
     *
     * @return array{version: string, repo: string, branch: string}
     */
    public function downloadAndApply(): array
    {
        if (! class_exists(\ZipArchive::class)) {
            throw new RuntimeException(trans('setting::settings.form.app_version_github_zip_unavailable'));
        }

        [$owner, $repo] = $this->resolveRepository();
        $branch = $this->resolveBranch();

        $workDir = storage_path('app/app-update');
        $this->resetDirectory($workDir);

        $zipPath = $workDir.'/update.zip';

        $request = Http::timeout(180)
            ->withHeaders($this->requestHeaders())
            ->sink($zipPath);

        if ($this->hasToken()) {
            $request = $request->withToken((string) config('setting.app_version.github_token'));
        }

        $response = $request->get("https://api.github.com/repos/{$owner}/{$repo}/zipball/{$branch}");

        if ($response->status() === 404 && ! $this->hasToken()) {
            throw new RuntimeException(trans('setting::settings.form.app_version_github_private_repo'));
        }

        if (! $response->successful() || ! is_file($zipPath) || filesize($zipPath) === 0) {
            throw new RuntimeException(trans('setting::settings.form.app_version_github_download_failed', [
                'status' => $response->status(),
            ]));
        }

        $extractDir = $workDir.'/extracted';
        @mkdir($extractDir, 0775, true);

        $zip = new \ZipArchive();

        if ($zip->open($zipPath) !== true || ! $zip->extractTo($extractDir)) {
            $zip->close();
            throw new RuntimeException(trans('setting::settings.form.app_version_github_extract_failed'));
        }

        $zip->close();

        $sourceRoot = $this->firstSubdirectory($extractDir);

        if ($sourceRoot === null) {
            throw new RuntimeException(trans('setting::settings.form.app_version_github_extract_failed'));
        }

        $this->copyTreeOverApp($sourceRoot, base_path());

        $this->resetDirectory($workDir, false);

        $version = $this->parseVersionFromContent(
            (string) @file_get_contents(base_path((string) config('setting.app_version.version_file', 'app/AestheticCart.php')))
        );

        if ($version === null) {
            throw new RuntimeException(trans('setting::settings.form.app_version_github_version_missing'));
        }

        setting(['app_version' => $version]);

        return [
            'version' => $version,
            'repo' => "{$owner}/{$repo}",
            'branch' => $branch,
        ];
    }

    /**
     * Copy every top-level entry from the extracted source into the app root,
     * skipping paths that must never be overwritten from a release zip.
     */
    private function copyTreeOverApp(string $source, string $destination): void
    {
        $protected = ['.git', '.github', '.env', 'storage', 'vendor', 'node_modules'];

        foreach (scandir($source) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..' || in_array($entry, $protected, true)) {
                continue;
            }

            $this->recursiveCopy($source.'/'.$entry, $destination.'/'.$entry);
        }
    }

    private function recursiveCopy(string $source, string $destination): void
    {
        if (is_dir($source)) {
            if (! is_dir($destination) && ! @mkdir($destination, 0775, true) && ! is_dir($destination)) {
                throw new RuntimeException(trans('setting::settings.form.app_version_github_write_failed', [
                    'path' => $destination,
                ]));
            }

            foreach (scandir($source) ?: [] as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $this->recursiveCopy($source.'/'.$entry, $destination.'/'.$entry);
            }

            return;
        }

        if (! @copy($source, $destination)) {
            throw new RuntimeException(trans('setting::settings.form.app_version_github_write_failed', [
                'path' => $destination,
            ]));
        }
    }

    private function firstSubdirectory(string $path): ?string
    {
        foreach (scandir($path) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $full = $path.'/'.$entry;

            if (is_dir($full)) {
                return $full;
            }
        }

        return null;
    }

    private function resetDirectory(string $path, bool $recreate = true): void
    {
        if (is_dir($path)) {
            $this->deleteDirectory($path);
        }

        if ($recreate && ! @mkdir($path, 0775, true) && ! is_dir($path)) {
            throw new RuntimeException(trans('setting::settings.form.app_version_github_write_failed', [
                'path' => $path,
            ]));
        }
    }

    private function deleteDirectory(string $path): void
    {
        foreach (scandir($path) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $full = $path.'/'.$entry;

            is_dir($full) ? $this->deleteDirectory($full) : @unlink($full);
        }

        @rmdir($path);
    }

    private function fetchVersionFromGitHub(string $owner, string $repo, string $branch, string $versionFile): ?string
    {
        if (! $this->hasToken()) {
            $rawResponse = Http::timeout(20)
                ->withHeaders(['User-Agent' => $this->userAgent()])
                ->get("https://raw.githubusercontent.com/{$owner}/{$repo}/{$branch}/{$versionFile}");

            if ($rawResponse->successful()) {
                return $this->parseVersionFromContent($rawResponse->body());
            }
        }

        $contentsResponse = $this->githubRequest(
            "https://api.github.com/repos/{$owner}/{$repo}/contents/{$versionFile}",
            ['ref' => $branch]
        );

        if (! $contentsResponse->successful()) {
            throw new RuntimeException(trans('setting::settings.form.app_version_github_file_failed'));
        }

        $encoded = (string) $contentsResponse->json('content', '');
        $decoded = base64_decode(str_replace(["\n", "\r"], '', $encoded), true);

        if (! is_string($decoded) || $decoded === '') {
            throw new RuntimeException(trans('setting::settings.form.app_version_github_file_failed'));
        }

        return $this->parseVersionFromContent($decoded);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveRepository(): array
    {
        $configured = trim((string) config('setting.app_version.github_repo', ''));

        if ($configured !== '' && str_contains($configured, '/')) {
            [$owner, $repo] = explode('/', $configured, 2);

            return [$owner, rtrim($repo, '.git')];
        }

        $remote = $this->gitRemoteUrl();

        if ($remote !== null) {
            $parsed = $this->parseGitHubRepository($remote);

            if ($parsed !== null) {
                return $parsed;
            }
        }

        throw new RuntimeException(trans('setting::settings.form.app_version_github_repo_missing'));
    }

    private function resolveBranch(): string
    {
        $configured = trim((string) config('setting.app_version.github_branch', ''));

        if ($configured !== '') {
            return $configured;
        }

        if (is_dir(base_path('.git')) && function_exists('shell_exec')) {
            $branch = trim((string) \shell_exec(
                'cd '.escapeshellarg(base_path()).' && git branch --show-current 2>/dev/null'
            ));

            if ($branch !== '') {
                return $branch;
            }
        }

        return 'main';
    }

    private function gitRemoteUrl(): ?string
    {
        if (! is_dir(base_path('.git')) || ! function_exists('shell_exec')) {
            return null;
        }

        $url = trim((string) \shell_exec(
            'cd '.escapeshellarg(base_path()).' && git remote get-url origin 2>/dev/null'
        ));

        return $url !== '' ? $url : null;
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    private function parseGitHubRepository(string $url): ?array
    {
        if (preg_match('#github\.com[:/]([^/]+)/([^/]+?)(?:\.git)?$#i', $url, $matches)) {
            return [$matches[1], rtrim($matches[2], '.git')];
        }

        return null;
    }

    private function parseVersionFromContent(string $content): ?string
    {
        if (preg_match("/const VERSION = '([^']+)';/", $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @param array<string, mixed> $query
     */
    private function githubRequest(string $url, array $query = []): Response
    {
        $request = Http::timeout(20)->withHeaders($this->requestHeaders());

        if ($this->hasToken()) {
            $request = $request->withToken((string) config('setting.app_version.github_token'));
        }

        return $request->get($url, $query);
    }

    private function hasToken(): bool
    {
        $token = config('setting.app_version.github_token');

        return is_string($token) && trim($token) !== '';
    }

    /**
     * @return array<string, string>
     */
    private function requestHeaders(): array
    {
        return [
            'Accept' => 'application/vnd.github+json',
            'User-Agent' => $this->userAgent(),
        ];
    }

    private function userAgent(): string
    {
        return 'AestheticCart-Admin-Version-Check';
    }
}
