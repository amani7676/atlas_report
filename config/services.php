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

    'melipayamak' => [
        'username' => env('MELIPAYAMAK_USERNAME', '9961351938'),
        'password' => env('MELIPAYAMAK_PASSWORD'), // استفاده نمی‌شود، فقط برای سازگاری
        'api_key' => env('MELIPAYAMAK_API_KEY', '2fe1db8f-2148-4701-96f9-ecfd25bc5470'), // APIKey اصلی
        'from' => env('MELIPAYAMAK_FROM', '2170006555'),
    ],

];
