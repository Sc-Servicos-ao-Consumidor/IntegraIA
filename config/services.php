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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'prism_api' => [
        'url' => env('PRISM_API_URL', 'https://openapi.test/api/ai'),
        'token' => env('PRISM_API_TOKEN'),
        'embedding_provider' => env('PRISM_EMBEDDING_PROVIDER', 'openai'),
        'embedding_model' => env('PRISM_EMBEDDING_MODEL', 'text-embedding-3-small'),
        'chat_provider' => env('PRISM_CHAT_PROVIDER', 'openai'),
        'chat_model' => env('PRISM_CHAT_MODEL', 'gpt-4'),
    ],

];
