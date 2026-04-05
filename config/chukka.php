<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Auth Provider
    |--------------------------------------------------------------------------
    |
    | Controls how users authenticate with the sim app.
    |
    | null     — No authentication (OSS default). All routes are public.
    | passport — OAuth via id.chukka.app (production SaaS).
    |
    */

    'auth_provider' => env('AUTH_PROVIDER'),

    /*
    |--------------------------------------------------------------------------
    | Cloud API URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the chukka-cloud REST API. All simulation events
    | are pushed to cloud through this URL.
    |
    */

    'cloud_url' => env('CHUKKA_CLOUD_URL', 'http://localhost:8000'),

    /*
    |--------------------------------------------------------------------------
    | Cloud API Key
    |--------------------------------------------------------------------------
    |
    | API key for authenticating with chukka-cloud. Used for match creation.
    | Owner and scorer tokens are obtained per-match via the API.
    |
    */

    'api_key' => env('CHUKKA_API_KEY'),

];
