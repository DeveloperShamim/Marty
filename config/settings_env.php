<?php

/*
 * .env values that Setting::get() falls back to when Admin has not saved the setting.
 * Read here so they survive 'php artisan config:cache' (after it, env() outside config/ returns null).
 */

return [
    'google_client_id'       => env('GOOGLE_CLIENT_ID'),
    'google_client_secret'   => env('GOOGLE_CLIENT_SECRET'),
    'mail_from_address'      => env('MAIL_FROM_ADDRESS'),
    'mail_from_name'         => env('MAIL_FROM_NAME'),
    'mail_host'              => env('MAIL_HOST'),
    'mail_mailer'            => env('MAIL_MAILER'),
    'mail_password'          => env('MAIL_PASSWORD'),
    'mail_port'              => env('MAIL_PORT'),
    'mail_username'          => env('MAIL_USERNAME'),
    'pathao_client_id'       => env('PATHAO_CLIENT_ID'),
    'pathao_client_secret'   => env('PATHAO_CLIENT_SECRET'),
    'pathao_enabled'         => env('PATHAO_ENABLED'),
    'pathao_env'             => env('PATHAO_ENV'),
    'pathao_password'        => env('PATHAO_PASSWORD'),
    'pathao_store_id'        => env('PATHAO_STORE_ID'),
    'pathao_username'        => env('PATHAO_USERNAME'),
    'redx_api_token'         => env('REDX_API_TOKEN'),
    'redx_enabled'           => env('REDX_ENABLED'),
    'redx_env'               => env('REDX_ENV'),
    'steadfast_api_key'      => env('STEADFAST_API_KEY'),
    'steadfast_enabled'      => env('STEADFAST_ENABLED'),
    'steadfast_secret_key'   => env('STEADFAST_SECRET_KEY'),
    'tracking_ga4_id'        => env('TRACKING_GA4_ID'),
    'tracking_gtm_id'        => env('TRACKING_GTM_ID'),
    'tracking_meta_pixel_id' => env('TRACKING_META_PIXEL_ID'),
];
