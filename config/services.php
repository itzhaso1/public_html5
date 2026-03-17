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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sendgrid' => [
        'key' => env('SENDGRID_API_KEY'),
        'endpoint' => env('SENDGRID_ENDPOINT', 'https://api.sendgrid.com/v3/mail/send'),
        'timeout' => env('SENDGRID_TIMEOUT', 20),
    ],

    'erp' => [
        'url' => env('ERP_API_URL', 'http://207.180.213.98:80/api/RunSql'),
        'connection_string' => env('ERP_CONNECTION_STRING', 'user id=sa;pwd=Ts@2008@;Data Source=5.189.161.154;database=demo_website;'),
    ],

    'shop2topup' => [
        'base_url' => env('SHOP2TOPUP_BASE_URL', 'https://shop2topup.com/api/shopapi/v1'),
        'api_key' => env('SHOP2TOPUP_API_KEY'),
        'timeout' => env('SHOP2TOPUP_TIMEOUT', 20),
    ],

    'wasender' => [
        'enabled' => env('WASENDER_ENABLED', false),
        'base_url' => env('WASENDER_BASE_URL', 'https://www.wasenderapi.com/api'),
        'api_key' => env('WASENDER_API_KEY'),
        'notify_to' => array_values(array_filter(array_map('trim', explode(',', (string) env('WASENDER_NOTIFY_TO', ''))))),
        'notify_customers' => env('WASENDER_NOTIFY_CUSTOMERS', true),
    ],

    // Optional email notifications (additional channel besides WhatsApp)
    'email_notify' => [
        'enabled' => env('EMAIL_NOTIFY_ENABLED', true),
        'notify_admin' => env('EMAIL_NOTIFY_ADMIN', true),
        'notify_customers' => env('EMAIL_NOTIFY_CUSTOMERS', true),
        // Comma-separated list of admin recipient emails (optional).
        // If empty, the app will try to notify all admins + main settings email.
        'admin_to' => array_values(array_filter(array_map('trim', explode(',', (string) env('EMAIL_NOTIFY_TO', ''))))),
    ],

];