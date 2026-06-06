<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class TolkienGateway
{
    /**
     * Maximum number of characters of article text handed to the AI. Tolkien
     * Gateway articles can be very long; trimming keeps token usage sane.
     */
    private const MAX_CONTENT_LENGTH = 8000;

    /**
     * Fetch a random Tolkien Gateway article and return its readable content.
     *
     * @return array{url: string, title: string, content: string}
     */
    public function fetchRandomPage(?string $url = null): array
    {
        $url ??= (string) config('thefact.source_url');

        $response = Http::withHeaders([
            'User-Agent' => 'TheOneFact/1.0 (+https://github.com)',
        ])->timeout(30)->get($url);

        if (! $response->successful()) {
            throw new RuntimeException("Failed to fetch Tolkien Gateway page (HTTP {$response->status()}).");
        }

        $effectiveUri = $response->effectiveUri();

        return $this->extractContent($response->body(), $effectiveUri ? (string) $effectiveUri : $url);
    }

    /**
     * Extract the article title and #bodyContent text from a wiki page.
     *
     * @return array{url: string, title: string, content: string}
     */
    public function extractContent(string $html, string $url): array
    {
        $document = new DOMDocument;

        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);

        $titleNode = $xpath->query('//*[@id="firstHeading"]')->item(0);
        $title = $titleNode ? trim($titleNode->textContent) : 'Unknown';

        $bodyNode = $xpath->query('//*[@id="mw-content-text"]')->item(0)
            ?? $xpath->query('//*[@id="bodyContent"]')->item(0);

        if ($bodyNode === null) {
            throw new RuntimeException('Could not locate the article body in the fetched page.');
        }

        // Drop noise that would only waste tokens or confuse the model.
        foreach ($xpath->query('.//script | .//style | .//table | .//*[contains(@class, "toc")]', $bodyNode) as $node) {
            $node->parentNode?->removeChild($node);
        }

        $content = Str::of($bodyNode->textContent)
            ->replaceMatches('/\s+/u', ' ')
            ->trim()
            ->limit(self::MAX_CONTENT_LENGTH, '')
            ->value();

        if ($content === '') {
            throw new RuntimeException('The fetched Tolkien Gateway page had no readable content.');
        }

        return [
            'url' => $url,
            'title' => $title,
            'content' => $content,
        ];
    }
}
