<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Fetches a random Tolkien Gateway article. The site is protected by
 * Cloudflare's JavaScript challenge, so requests are proxied through a
 * FlareSolverr (headless Chrome) sidecar that solves the challenge for us.
 */
class TolkienGateway
{
    /**
     * Maximum number of characters of article text handed to the AI. Articles
     * can be very long; trimming keeps token usage sane.
     */
    private const MAX_CONTENT_LENGTH = 8000;

    /**
     * Fetch a random Tolkien Gateway article and return its readable content.
     *
     * @return array{url: string, title: string, content: string}
     */
    public function fetchRandomPage(): array
    {
        $solution = $this->solve((string) config('thefact.source_url'));

        return $this->extractContent($solution['html'], $solution['url']);
    }

    /**
     * Ask FlareSolverr to fetch a URL, returning the resolved HTML and URL.
     *
     * @return array{html: string, url: string}
     */
    private function solve(string $url): array
    {
        $endpoint = (string) config('thefact.flaresolverr_url');
        $timeout = (int) config('thefact.request_timeout');

        $response = Http::asJson()
            ->acceptJson()
            ->timeout($timeout)
            ->post($endpoint, [
                'cmd' => 'request.get',
                'url' => $url,
                'maxTimeout' => max(15, $timeout - 15) * 1000,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException("FlareSolverr request failed (HTTP {$response->status()}).");
        }

        $data = $response->json();

        if (($data['status'] ?? null) !== 'ok' || empty($data['solution']['response'])) {
            throw new RuntimeException('FlareSolverr could not fetch the page: '.($data['message'] ?? 'unknown error'));
        }

        return [
            'html' => $data['solution']['response'],
            'url' => $data['solution']['url'] ?? $url,
        ];
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
        $noise = './/script | .//style | .//table'
            .' | .//*[contains(@class, "toc")]'
            .' | .//*[contains(@class, "navbox")]'
            .' | .//*[contains(@class, "mw-editsection")]'
            .' | .//*[contains(@class, "reference")]';

        foreach (iterator_to_array($xpath->query($noise, $bodyNode)) as $node) {
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
