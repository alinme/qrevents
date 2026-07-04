<?php

return [
    'base_currency' => 'EUR',

    'supported_checkout_currencies' => ['EUR', 'RON', 'GBP'],

    // Business wallet is a WHOLESALE model: a business prepays a euro amount and
    // receives credits at a discounted per-credit price. 1 credit spends like €1
    // on an event, so a lower per-credit price = the business's margin. The more
    // they prepay, the cheaper each credit (volume discount). Floor is 50% off.
    'credit_min_topup_eur' => 100,
    'credit_topup_step_eur' => 50,

    // Highest tier whose `min_eur` <= the prepaid amount applies.
    'credit_tiers' => [
        ['min_eur' => 100, 'price_per_credit_cents' => 50, 'discount_percent' => 50],
        ['min_eur' => 300, 'price_per_credit_cents' => 45, 'discount_percent' => 55],
        ['min_eur' => 1000, 'price_per_credit_cents' => 40, 'discount_percent' => 60],
        ['min_eur' => 3000, 'price_per_credit_cents' => 35, 'discount_percent' => 65],
    ],

    // Preset amounts surfaced as tier cards in the dashboard.
    'credit_presets_eur' => [100, 300, 1000, 3000],
];
