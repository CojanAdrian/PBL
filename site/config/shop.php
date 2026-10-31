<?php

/*
 * Big Ed's Jerky shop settings. Meat types, pack types, products, prices and
 * ingredients are managed in the admin (Shop); this file holds the things
 * that rarely change.
 */
return [

    // Saved with every order (how it is written on the site is in Text blocks: shop_currency).
    'currency' => 'lei',

    // Custom pack sizes are switched off for now. Switched on, customers can pick any
    // weight for a one-flavour product, priced from its own weight (250 g of a 100 g
    // product at 130 = 325).
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
