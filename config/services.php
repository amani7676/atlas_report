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
        'api_key' => env('MELIPAYAMAK_API_KEY', '7fd3d795-9a79-4b1b-a90f-8d675dd082ea'), // APIKey برای rest.payamak-panel.com
        'console_api_key' => env('MELIPAYAMAK_CONSOLE_API_KEY'), // APIKey برای console.melipayamak.com (ارسال الگویی)
        'from' => env('MELIPAYAMAK_FROM', '50002710051938'), // شماره فرستنده برای پیامک‌های ساده
        'pattern_from' => env('MELIPAYAMAK_PATTERN_FROM', '90001429'), // شماره فرستنده برای پیامک‌های الگویی
    ],

];
