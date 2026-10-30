<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', 'https://soundheritage.duckdns.org/auth/google/callback'),
    ],

    'mercadopago' => [
        'web_public_key'   => env('MP_WEB_PUBLIC_KEY'),
        'web_access_token' => env('MP_WEB_ACCESS_TOKEN'),
        'qr_access_token'  => env('MP_QR_ACCESS_TOKEN'),
        'user_id'          => env('MP_USER_ID', '3791683102'),
        'pos_id'           => env('MP_POS_ID', 'CAJA_1'),
        'store_id'         => env('MP_EXTERNAL_STORE_ID', 'SUCURSAL_CENTRAL'),
        'webhook_secret'   => env('MP_WEBHOOK_SECRET'),
        'sandbox'          => env('MP_SANDBOX', true),
    ],

];

