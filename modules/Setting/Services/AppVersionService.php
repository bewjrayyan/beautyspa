<?php

namespace Modules\Setting\Services;

use RuntimeException;

class AppVersionService
{
    public function codeVersion(): string
    {
        return $this->versionFromFile(base_path('app/AestheticCart.php'));
    }

    public function publishedVersion(): string
    {
        $stored = setting('app_version');

        if (is_string($stored) && trim($stored) !== '') {
            return trim($stored);
        }

        return $this->codeVersion();
    }

    /**
     * @return array{
     *     available: bool,
     *     commit?: string|null,
     *     branch?: string|null,
     *     upstream?: string|null,
     *     remote_version?: string|null,
     *     remote_commit?: string|null,
     *     commits_behind?: int,
     *     update_available?: bool
     * }
     */
    public function gitInfo(bool $fetchRemote = false): array
    {
        $base = base_path();

        if (! is_dir($base.'/.git') || ! $this->canRunShell()) {
            return ['available' => false];
        }

        if ($fetchRemote) {
            $this->runGit('fetch origin 2>&1');
        }

        $commit = $this->runGit('rev-parse --short HEAD 2>/dev/null');
        $branch = $this->runGit('branch --show-current 2>/dev/null');
        $upstream = $this->runGit('rev-parse --abbrev-ref --symbolic-full-name @{u} 2>/dev/null');

        if ($commit === '') {
            return ['available' => false];
        }

        $info = [
            'available' => true,
            'commit' => $commit,
            'branch' => $branch !== '' ? $branch : null,
            'upstream' => $upstream !== '' ? $upstream : null,
        ];

        if ($upstream === '') {
            return $info;
        }

        $remoteCommit = $this->runGit('rev-parse --short @{u} 2>/dev/null');
        $commitsBehind = (int) $this->runGit('rev-list --count HEAD..@{u} 2>/dev/null');

        $info['remote_commit'] = $remoteCommit !== '' ? $remoteCommit : null;
        $info['commits_behind'] = max(0, $commitsBehind);
        $info['update_available'] = $commitsBehind > 0;

        try {
            $info['remote_version'] = $this->remoteVersionFromUpstream($upstream);
        } catch (\Throwable) {
            $info['remote_version'] = null;
        }

        return $info;
    }

    /**
     * Fetch from origin and pull fast-forward updates when available.
     *
     * @return array{updated: bool, version: string, git: array}
     */
    public function pullLatest(): array
    {
        $base = base_path();

        if (! is_dir($base.'/.git') || ! $this->canRunShell()) {
            throw new RuntimeException(trans('setting::settings.form.app_version_git_unavailable'));
        }

        $beforeVersion = $this->codeVersion();
        $gitBefore = $this->gitInfo(true);

        if (empty($gitBefore['upstream'])) {
            throw new RuntimeException(trans('setting::settings.form.app_version_no_upstream'));
        }

        $commitsBehind = (int) ($gitBefore['commits_behind'] ?? 0);

        if ($commitsBehind === 0) {
            $version = $this->syncPublishedVersion();

            return [
                'updated' => false,
                'version' => $version,
                'git' => $this->gitInfo(false),
            ];
        }

        $pullOutput = $this->runGit('pull --ff-only 2>&1');

        if ($this->runGit('rev-parse --short HEAD 2>/dev/null') === '') {
            throw new RuntimeException(trans('setting::settings.form.app_version_pull_failed', [
                'output' => trim($pullOutput) !== '' ? trim($pullOutput) : trans('setting::settings.form.app_version_pull_failed_unknown'),
            ]));
        }

        $this->syncPublishedFromCode($this->versionFromFile(base_path('app/AestheticCart.php')));

        return [
            'updated' => true,
            'version' => $this->codeVersion(),
            'git' => $this->gitInfo(false),
        ];
    }

    public function syncPublishedVersion(): string
    {
        $version = $this->codeVersion();
        setting(['app_version' => $version]);

        return $version;
    }

    private function syncPublishedFromCode(string $version): void
    {
        setting(['app_version' => $version]);
    }

    private function remoteVersionFromUpstream(string $upstream): ?string
    {
        if (! $this->canRunShell()) {
            return null;
        }

        $remoteFile = trim((string) \shell_exec(
            'cd '.escapeshellarg(base_path()).' && git show '.escapeshellarg($upstream).':app/AestheticCart.php 2>/dev/null'
        ));

        if ($remoteFile === '') {
            return null;
        }

        return $this->parseVersionFromContent($remoteFile);
    }

    private function versionFromFile(string $path): string
    {
        if (! is_readable($path)) {
            return \AestheticCart\AestheticCart::VERSION;
        }

        return $this->parseVersionFromContent(file_get_contents($path))
            ?? \AestheticCart\AestheticCart::VERSION;
    }

    private function parseVersionFromContent(string $content): ?string
    {
        if (preg_match("/const VERSION = '([^']+)';/", $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function runGit(string $command): string
    {
        if (! $this->canRunShell()) {
            return '';
        }

        return trim((string) \shell_exec(
            'cd '.escapeshellarg(base_path()).' && git '.$command
        ));
    }


    private function canRunShell(): bool
    {
        return function_exists('shell_exec') && ! in_array('shell_exec', $this->disabledFunctions(), true);
    }


    /**
     * @return list<string>
     */
    private function disabledFunctions(): array
    {
        $disabled = ini_get('disable_functions');

        if (! is_string($disabled) || trim($disabled) === '') {
            return [];
        }

        return array_map('trim', explode(',', $disabled));
    }
}
