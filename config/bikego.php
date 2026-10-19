<?php

/**
 * BikeGo application settings.
 */
return [

    // Title shown in the <title> tag of every page ("Full name – Candidate number")
    'exam_title' => env('EXAM_TITLE', 'Giáp Chí Thanh – 2001240441'),

    // Application time zone (Laravel 8 does not read it from .env)
    'timezone' => env('BIKEGO_TIMEZONE', 'Asia/Ho_Chi_Minh'),

    // Default rental price (VND per minute) used when a bike is created
    'default_price_per_minute' => (int) env('BIKEGO_PRICE_PER_MINUTE', 500),

    // Source of the ward list (JSON, see exam description)
    'wards_url' => env('BIKEGO_WARDS_URL', 'https://production.cas.so/address-kit/2025-07-01/provinces/79/communes'),

    // Admin account that is created by the seeder (login with the username below)
    'admin_username' => 'admin',
    'admin_password' => '12345',
];
