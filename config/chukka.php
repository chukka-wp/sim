<?php

return [

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
    | Manager Token
    |--------------------------------------------------------------------------
    |
    | A pre-configured manager API token for creating matches and generating
    | scorer tokens in chukka-cloud.
    |
    */

    'manager_token' => env('CHUKKA_MANAGER_TOKEN'),

];
