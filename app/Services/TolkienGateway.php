<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Fetches a random Tolkien Gateway article through the MediaWiki API. A single
 * `generator=random` query returns a main-namespace article together with its
 * plain-text extract and canonical URL, so no HTML scraping is required.
 *
 * The descriptive User-Agent identifies the project (per MediaWiki API
 * etiquette) and is the string Tolkien Gateway whitelists so the request is
 * never served a Cloudflare challenge in the first place.
 *
 * @see https://www.mediawiki.org/wiki/API:Random
 */
class TolkienGateway
{
    /**
     * Maximum number of characters of article text handed to the AI. Articles
     * can be very long; trimming keeps token usage sane.
     */
    private const MAX_CONTENT_LENGTH = 8000;

    /**
     * How many random articles to try before giving up. Some pages (stubs,
     * redirects) carry an empty extract, so we skip those and roll again.
     */
    private const MAX_ATTEMPTS = 5;

    private const USER_AGENT = 'TheOneFact/1.0 (+https://github.com/mydnic/the-one-fact)';

    /**
     * Fetch a random Tolkien Gateway article and return its readable content.
     *
     * @return array{url: string, title: string, content: string}
     */
    public function fetchRandomPage(): array
    {
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $page = $this->queryRandomArticle();
            $content = $this->normalizeContent((string) ($page['extract'] ?? ''));

            if ($content !== '') {
                $title = (string) ($page['title'] ?? 'Unknown');

                return [
                    'url' => (string) ($page['fullurl'] ?? $this->articleUrl($title)),
                    'title' => $title,
                    'content' => $content,
                ];
            }
        }

        throw new RuntimeException('Could not fetch a Tolkien Gateway article with readable content.');
    }

    /**
     * Query the MediaWiki API for a single random main-namespace article,
     * returning its raw page record (title, extract, fullurl, ...).
     *
     * @return array<string, mixed>
     */
    private function queryRandomArticle(): array
    {
        $response = Http::withUserAgent(self::USER_AGENT)
            ->acceptJson()
            ->timeout((int) config('thefact.request_timeout'))
            ->get((string) config('thefact.api_url'), [
                'action' => 'query',
                'format' => 'json',
                'generator' => 'random',
                'grnnamespace' => 0,
                'grnlimit' => 1,
                'prop' => 'extracts|info',
                'explaintext' => 1,
                'exsectionformat' => 'plain',
                'inprop' => 'url',
            ]);

        if (! $response->successful()) {
            throw new RuntimeException("Tolkien Gateway API request failed (HTTP {$response->status()}).");
        }

        $pages = $response->json('query.pages');

        if (! is_array($pages) || $pages === []) {
            throw new RuntimeException('Tolkien Gateway API returned no random article.');
        }

        return (array) reset($pages);
    }

    /**
     * Collapse whitespace and trim a plain-text extract down to the AI budget.
     */
    private function normalizeContent(string $extract): string
    {
        return Str::of($extract)
            ->replaceMatches('/\s+/u', ' ')
            ->trim()
            ->limit(self::MAX_CONTENT_LENGTH, '')
            ->value();
    }

    /**
     * Build a wiki URL from a title as a fallback when the API omits `fullurl`.
     */
    private function articleUrl(string $title): string
    {
        return 'https://tolkiengateway.net/wiki/'.str_replace('%2F', '/', rawurlencode(str_replace(' ', '_', $title)));
    }
}
