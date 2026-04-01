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

];
