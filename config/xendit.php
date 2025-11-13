<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Xendit API Key
    |--------------------------------------------------------------------------
    |
    | Your Xendit secret API key from the Xendit Dashboard.
    | Get it from: https://dashboard.xendit.co/settings/developers#api-keys
    |
    */
    'api_key' => env('XENDIT_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Xendit Webhook Token
    |--------------------------------------------------------------------------
    |
    | The webhook verification token to validate incoming webhooks from Xendit.
    |
    */
    'webhook_token' => env('XENDIT_WEBHOOK_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Invoice Success Redirect URL
    |--------------------------------------------------------------------------
    |
    | URL to redirect after successful payment
    |
    */
    'success_redirect_url' => env('XENDIT_SUCCESS_REDIRECT_URL', env('FRONTEND_URL') . '/payment/success'),

    /*
    |--------------------------------------------------------------------------
    | Invoice Failure Redirect URL
    |--------------------------------------------------------------------------
    |
    | URL to redirect after failed payment
    |
    */
    'failure_redirect_url' => env('XENDIT_FAILURE_REDIRECT_URL', env('FRONTEND_URL') . '/payment/failed'),
];
