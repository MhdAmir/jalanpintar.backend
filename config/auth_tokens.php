<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Refresh Token TTL (Time To Live)
    |--------------------------------------------------------------------------
    |
    | The number of days a refresh token is valid. After this period,
    | users must re-authenticate with their credentials.
    |
    */
    'refresh_token_ttl_days' => (int) env('REFRESH_TOKEN_TTL_DAYS', 14),
];
