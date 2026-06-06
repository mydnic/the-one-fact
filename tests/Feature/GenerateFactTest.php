<?php

use App\Ai\Agents\FactExtractor;
use App\Models\Fact;
use App\Services\TolkienGateway;
use Illuminate\Support\Facades\Http;

function fakeArticle(): void
{
    Http::fake([
        '*' => Http::response(
            '<html><body><h1 id="firstHeading">Glorfindel</h1>'.
            '<div id="mw-content-text"><p>Glorfindel was an Elf-lord of Gondolin who fell fighting a Balrog.</p></div>'.
            '</body></html>'
        ),
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

it('extracts the article body and title from wiki html', function () {
    $page = app(TolkienGateway::class)->extractContent(
        '<html><body><h1 id="firstHeading">Eärendil</h1>'.
        '<div id="mw-content-text"><script>ignore()</script><p>Eärendil  the   Mariner.</p></div>'.
        '</body></html>',
        'https://tolkiengateway.net/wiki/Earendil'
    );

    expect($page['title'])->toBe('Eärendil')
        ->and($page['content'])->toBe('Eärendil the Mariner.')
        ->and($page['content'])->not->toContain('ignore');
});
