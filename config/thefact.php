<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Source
    |--------------------------------------------------------------------------
    |
    | The page the daily job pulls from. Tolkien Gateway's Special:Random
    | endpoint redirects to a random article from the Legendarium wiki.
    |
    */

    'source_url' => env('FACT_SOURCE_URL', 'https://tolkiengateway.net/wiki/Special:Random'),

    /*
    |--------------------------------------------------------------------------
    | AI provider
    |--------------------------------------------------------------------------
    |
    | Which laravel/ai provider and model to use when extracting the fact.
    | Leave AI_PROVIDER empty to fall back to the package default (openai),
    | and AI_MODEL empty to use that provider's default model. Set the
    | matching API key env (OPENAI_API_KEY, ANTHROPIC_API_KEY, ...).
    |
    */

    'ai' => [
        'provider' => env('AI_PROVIDER') ?: null,
        'model' => env('AI_MODEL') ?: null,
    ],

];
