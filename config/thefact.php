<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Source
    |--------------------------------------------------------------------------
    |
    | The MediaWiki API endpoint the daily job pulls from. A generator=random
    | query returns a random Legendarium article with its plain-text extract.
    |
    */

    'api_url' => env('FACT_API_URL', 'https://tolkiengateway.net/w/api.php'),

    /*
    |--------------------------------------------------------------------------
    | Project repository
    |--------------------------------------------------------------------------
    |
    | Shown in the page footer so visitors can find the source and deploy their
    | own instance. Override with GITHUB_URL if you fork the project.
    |
    */

    'github_url' => env('GITHUB_URL', 'https://github.com/mydnic/the-one-fact'),

    /*
    |--------------------------------------------------------------------------
    | Request timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time (seconds) to wait for the MediaWiki API to respond.
    |
    */

    'request_timeout' => (int) env('FACT_REQUEST_TIMEOUT', 15),

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
