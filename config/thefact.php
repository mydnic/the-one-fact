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
    | FlareSolverr
    |--------------------------------------------------------------------------
    |
    | Tolkien Gateway sits behind Cloudflare's JavaScript challenge, which a
    | plain HTTP client cannot pass. Requests are therefore routed through a
    | FlareSolverr sidecar (headless Chrome) that solves the challenge and
    | returns the resolved HTML. This points at that service's /v1 endpoint.
    |
    */

    'flaresolverr_url' => env('FLARESOLVERR_URL', 'http://flaresolverr:8191/v1'),

    /*
    |--------------------------------------------------------------------------
    | Request timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time (seconds) to wait for FlareSolverr to solve the challenge
    | and return the page. The browser cold-start makes the first call slow.
    |
    */

    'request_timeout' => (int) env('FACT_REQUEST_TIMEOUT', 90),

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
