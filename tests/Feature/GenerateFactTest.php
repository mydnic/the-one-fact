<?php

use App\Ai\Agents\FactExtractor;
use App\Models\Fact;
use App\Services\TolkienGateway;
use Illuminate\Support\Facades\Http;

function fakeArticle(): void
{
    Http::fake([
        '*api.php*' => Http::response([
            'batchcomplete' => '',
            'query' => [
                'pages' => [
                    '123' => [
                        'pageid' => 123,
                        'ns' => 0,
                        'title' => 'Glorfindel',
                        'fullurl' => 'https://tolkiengateway.net/wiki/Glorfindel',
                        'extract' => "Glorfindel was an Elf-lord of Gondolin\n\nwho fell   fighting a Balrog.",
                    ],
                ],
            ],
        ]),
    ]);
}

function fakeExtractor(): void
{
    FactExtractor::fake(fn () => [
        'title' => 'The Twice-Born Elf',
        'fact' => 'Glorfindel died slaying a Balrog during the Fall of Gondolin and was later re-embodied in Valinor.',
        'tags' => ['Glorfindel', 'Gondolin', 'Balrogs'],
    ]);
}

it('generates and stores the fact of the day', function () {
    fakeArticle();
    fakeExtractor();

    $this->artisan('fact:generate')->assertSuccessful();

    $fact = Fact::ofTheDay();

    expect($fact)->not->toBeNull()
        ->and($fact->title)->toBe('The Twice-Born Elf')
        ->and($fact->content)->toContain('Balrog')
        ->and($fact->tags)->toContain('Gondolin')
        ->and($fact->source_title)->toBe('Glorfindel')
        ->and($fact->fact_date->toDateString())->toBe(today()->toDateString());

    FactExtractor::assertPrompted(fn ($prompt) => str_contains($prompt->prompt, 'Glorfindel'));
});

it('keeps a single fact per day when run more than once', function () {
    fakeArticle();
    fakeExtractor();

    $this->artisan('fact:generate')->assertSuccessful();
    $this->artisan('fact:generate')->assertSuccessful();

    expect(Fact::count())->toBe(1);
});

it('returns the article title, url and normalized extract from the API', function () {
    fakeArticle();

    $page = app(TolkienGateway::class)->fetchRandomPage();

    expect($page['title'])->toBe('Glorfindel')
        ->and($page['url'])->toBe('https://tolkiengateway.net/wiki/Glorfindel')
        ->and($page['content'])->toBe('Glorfindel was an Elf-lord of Gondolin who fell fighting a Balrog.');
});

it('skips articles without readable content and rolls again', function () {
    Http::fakeSequence()
        ->push([
            'query' => ['pages' => ['1' => ['title' => 'Stub', 'fullurl' => 'https://tolkiengateway.net/wiki/Stub', 'extract' => '']]],
        ])
        ->push([
            'query' => ['pages' => ['2' => ['title' => 'Glorfindel', 'fullurl' => 'https://tolkiengateway.net/wiki/Glorfindel', 'extract' => 'Glorfindel was an Elf-lord of Gondolin.']]],
        ]);

    $page = app(TolkienGateway::class)->fetchRandomPage();

    expect($page['title'])->toBe('Glorfindel')
        ->and($page['content'])->toBe('Glorfindel was an Elf-lord of Gondolin.');
});
