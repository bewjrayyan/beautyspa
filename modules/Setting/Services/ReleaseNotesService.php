<?php

namespace Modules\Setting\Services;

class ReleaseNotesService
{
    private ?array $notes = null;

    /**
     * @return array<string, array{en: array{summary: string, changes: string[]}, ms: array{summary: string, changes: string[]}}>
     */
    public function all(): array
    {
        return $this->load();
    }

    /**
     * @return array{version: string, summary: string, changes: string[]}|null
     */
    public function forVersion(string $version): ?array
    {
        $version = $this->normalizeVersion($version);
        $entry = $this->load()[$version] ?? null;

        if ($entry === null) {
            return null;
        }

        $locale = $this->resolveLocale($entry);

        return [
            'version' => $version,
            'summary' => $locale['summary'] ?? '',
            'changes' => $locale['changes'] ?? [],
        ];
    }

    /**
     * @return array<int, array{version: string, summary: string, changes: string[]}>
     */
    public function recent(int $limit = 5): array
    {
        $notes = $this->load();
        $versions = array_keys($notes);

        usort($versions, function (string $a, string $b): int {
            return version_compare($b, $a);
        });

        $result = [];

        foreach (array_slice($versions, 0, max(1, $limit)) as $version) {
            $entry = $this->forVersion($version);

            if ($entry !== null) {
                $result[] = $entry;
            }
        }

        return $result;
    }

    public function messageWithNotes(string $messageKey, array $replace, string $version): string
    {
        $message = trans($messageKey, $replace);
        $suffix = $this->flashSuffix($version);

        return $suffix === '' ? $message : $message.' '.$suffix;
    }

    public function flashSuffix(string $version): string
    {
        $entry = $this->forVersion($version);

        if ($entry === null) {
            return '';
        }

        $lines = [];

        if ($entry['summary'] !== '') {
            $lines[] = $entry['summary'];
        }

        foreach ($entry['changes'] as $change) {
            $lines[] = '• '.$change;
        }

        if ($lines === []) {
            return '';
        }

        return "\n\n".trans('setting::settings.form.app_version_whats_new', ['version' => $entry['version']])."\n".implode("\n", $lines);
    }

    private function load(): array
    {
        if ($this->notes !== null) {
            return $this->notes;
        }

        $path = base_path('app/release-notes.php');

        if (! is_readable($path)) {
            return $this->notes = [];
        }

        $notes = require $path;

        return $this->notes = is_array($notes) ? $notes : [];
    }

    /**
     * @param array{en?: array{summary?: string, changes?: string[]}, ms?: array{summary?: string, changes?: string[]}} $entry
     * @return array{summary: string, changes: string[]}
     */
    private function resolveLocale(array $entry): array
    {
        $locale = app()->getLocale();
        $localized = $entry[$locale] ?? $entry['en'] ?? reset($entry);

        if (! is_array($localized)) {
            return ['summary' => '', 'changes' => []];
        }

        return [
            'summary' => (string) ($localized['summary'] ?? ''),
            'changes' => array_values(array_filter(
                (array) ($localized['changes'] ?? []),
                fn ($line) => is_string($line) && trim($line) !== ''
            )),
        ];
    }

    private function normalizeVersion(string $version): string
    {
        return ltrim(trim($version), 'vV');
    }
}
