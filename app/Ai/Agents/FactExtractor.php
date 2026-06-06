<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

class FactExtractor implements Agent, HasStructuredOutput
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        return <<<'INSTRUCTIONS'
        You are a loremaster of J.R.R. Tolkien's Legendarium (The Hobbit, The Lord of
        the Rings, The Silmarillion, and related works).

        You are given the raw text of a single Tolkien Gateway wiki article. From it,
        extract ONE genuinely interesting, self-contained fact that a fan would enjoy.

        Rules:
        - The fact must stand on its own without the reader having seen the article.
        - Write 1-3 sentences, in an engaging encyclopaedic tone. No "Did you know" filler.
        - Only state things supported by the provided text. Never invent details.
        - Provide a short punchy title (a few words) for the fact.
        - Provide 2 to 5 lowercase-or-proper-noun tags naming the key people, places,
          or themes involved (e.g. "Gondor", "Rings of Power", "First Age").
        INSTRUCTIONS;
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()
                ->description('A short, punchy title for the fact (a few words).')
                ->required(),
            'fact' => $schema->string()
                ->description('The self-contained fact, 1-3 engaging sentences.')
                ->required(),
            'tags' => $schema->array()
                ->items($schema->string())
                ->min(2)
                ->max(5)
                ->description('Key people, places, or themes the fact involves.')
                ->required(),
        ];
    }
}
