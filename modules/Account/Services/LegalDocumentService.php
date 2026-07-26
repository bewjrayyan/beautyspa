<?php

namespace Modules\Account\Services;

use Illuminate\Support\Collection;
use Modules\Account\Exceptions\LegalDocumentUnavailableException;
use Modules\Page\Entities\Page;

class LegalDocumentService
{
    public const SLUGS = ['terms-conditions', 'privacy-policy'];

    public function documents(): Collection
    {
        return Page::query()
            ->whereIn('slug', self::SLUGS)
            ->get()
            ->sortBy(fn (Page $page): int => array_search($page->slug, self::SLUGS, true))
            ->values();
    }

    public function snapshot(): array
    {
        return $this->documentsOrFail()->map(fn (Page $page): array => [
            'page_id' => $page->id,
            'slug' => $page->slug,
            'locale' => locale(),
            'title' => $page->name,
            'body' => $page->body,
            'content_hash' => hash('sha256', (string) $page->body),
            'version' => $page->updated_at?->format('YmdHis'),
            'accepted_url' => localized_url(locale(), $page->slug),
        ])->all();
    }

    public function documentsOrFail(): Collection
    {
        $documents = $this->documents();

        if ($documents->count() !== count(self::SLUGS)) {
            throw new LegalDocumentUnavailableException(
                'Terms and privacy policy pages must be active before consultation submission.'
            );
        }

        return $documents;
    }
}
