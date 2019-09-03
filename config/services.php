<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'authy' => [
        'secret' => env('AUTHY_SECRET'),
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key'    => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'stripe' => [
        'model'  => App\User::class,
        'key'    => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'facebook' => [
        'client_id' => '2278721772447009',
        'client_secret' => 'cdf55060cb55fed5aff86289e89c0424',
        'redirect' => env('FACEBOOK_REDIRECT_URL', 'https://fantasyfootball.adeogroup.co.uk/api/social/facebook/callback'),
    ],

    'google' => [
        'client_id' => '747781914318-78hpm3ekkh4e05qeu25mu63fnnsgudn0.apps.googleusercontent.com',
        'client_secret' => 'WFhlpxQmMHpyEoHgB7LdMnOz',
        'redirect' => env('GOOGLE_REDIRECT_URL', 'https://fantasyfootball.adeogroup.co.uk/api/social/google/callback'),
    ],

];
