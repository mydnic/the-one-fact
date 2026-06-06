<?php

namespace App\Jobs;

use App\Ai\Agents\FactExtractor;
use App\Models\Fact;
use App\Services\TolkienGateway;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateDailyFact implements ShouldQueue
{
    use Queueable;

    /**
     * Fetch a random Tolkien Gateway article, extract a fact via AI, and store
     * it as today's fact. Re-running on the same day refreshes that entry.
     */
    public function handle(TolkienGateway $gateway): Fact
    {
        $page = $gateway->fetchRandomPage();

        $provider = config('thefact.ai.provider');
        $model = config('thefact.ai.model');

        $response = (new FactExtractor)->prompt(
            "Article title: {$page['title']}\n\nArticle text:\n{$page['content']}",
            provider: $provider,
            model: $model,
        );

        return Fact::updateOrCreate(
            ['fact_date' => today()],
            [
                'title' => $response['title'],
                'content' => $response['fact'],
                'tags' => $response['tags'],
                'source_url' => $page['url'],
                'source_title' => $page['title'],
                'metadata' => [
                    'provider' => $provider ?? config('ai.default'),
                    'model' => $model,
                ],
            ],
        );
    }
}
