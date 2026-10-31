<?php

/*
 * Big Ed's Jerky shop settings. Products and prices are managed in the admin
 * (Shop -> Products); this file holds the things that rarely change.
 */
return [

    'currency' => 'lei',

    // Pack sizes on offer, in grams. Product prices are for the popular size.
    'sizes' => [100],
    'popular_size' => 100,

    // Custom pack sizes are switched off for now. Switched on, a custom pack is
    // priced from the product's price for the popular size (250 g = 2.5 x price).
    'custom' => [
        'enabled' => false,
        'min' => 30,
        'max' => 1000,
        'step' => 10,
    ],

    'limits' => [
        'max_packs_per_line' => 20,
        'max_lines' => 25,
        'max_total_grams' => 10000,
    ],

    // Meats and their pouch colours (the names are translated under Text blocks: meat_beef, meat_chicken).
    'meats' => [
        'beef' => ['name' => 'Beef', 'pouch' => ['top' => '#2a2624', 'bottom' => '#0d0b0a', 'cap' => '#0a0908']],
        'chicken' => ['name' => 'Chicken', 'pouch' => ['top' => '#7b5a3f', 'bottom' => '#4b3424', 'cap' => '#3a281b']],
    ],

    // A customer can send this many preorders an hour from one connection (only saved orders count).
    'orders_per_hour' => 15,

    'notify' => [
        // Who gets an email for every new preorder.
        'email_to' => env('ORDER_EMAIL_TO', 'kenny@igtfreight.com'),
        // Railway blocks SMTP on its cheaper plans, so emails go through an email API.
        'resend_key' => env('RESEND_API_KEY'),
        'brevo_key' => env('BREVO_API_KEY'),
        'from' => env('EMAIL_FROM'),

        'telegram_token' => env('TELEGRAM_BOT_TOKEN'),
        'telegram_chat_id' => env('TELEGRAM_CHAT_ID'),

        // Google Sheet (google-sheets/apps-script.gs published as a web app).
        'sheets_url' => env('SHEETS_WEBHOOK_URL'),
        'sheets_token' => env('SHEETS_TOKEN'),
    ],
];
