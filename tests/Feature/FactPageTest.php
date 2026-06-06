<?php

use App\Models\Fact;

beforeEach(fn () => $this->withoutVite());

it('shows the fact of the day on the home page', function () {
    Fact::factory()->create([
        'title' => 'Of Beren and Lúthien',
        'content' => 'Lúthien gave up her immortality for love of Beren.',
        'tags' => ['Beren', 'Lúthien'],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Of Beren and Lúthien')
        ->assertSee('gave up her immortality', false)
        ->assertSee('Lúthien')
        ->assertSee(config('thefact.github_url'));
});

it('shows an empty state when no fact exists', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('has not yet begun', false);
});

it('returns the latest fact as JSON', function () {
    Fact::factory()->create(['fact_date' => '2026-01-01']);

    Fact::factory()->create([
        'fact_date' => '2026-06-05',
        'title' => 'The Last Fact',
        'content' => 'A fact about the Rings.',
        'tags' => ['Rings of Power'],
        'source_url' => 'https://tolkiengateway.net/wiki/Rings',
        'source_title' => 'Rings of Power',
    ]);

    $this->getJson('/api/fact')
        ->assertOk()
        ->assertJsonPath('data.date', '2026-06-05')
        ->assertJsonPath('data.title', 'The Last Fact')
        ->assertJsonPath('data.fact', 'A fact about the Rings.')
        ->assertJsonPath('data.tags', ['Rings of Power'])
        ->assertJsonPath('data.source.url', 'https://tolkiengateway.net/wiki/Rings');
});

it('returns 404 from the API when no fact exists', function () {
    $this->getJson('/api/fact')->assertNotFound();
});
