<?php

return [
    'base_currency' => 'EUR',

    'supported_checkout_currencies' => ['EUR', 'RON', 'GBP'],

    'top_up_packs' => [
        [
            'credits' => 25,
            'bonus_percent' => 0,
        ],
        [
            'credits' => 50,
            'bonus_percent' => 10,
        ],
        [
            'credits' => 100,
            'bonus_percent' => 25,
        ],
        [
            'credits' => 200,
            'bonus_percent' => 35,
        ],
        [
            'credits' => 500,
            'bonus_percent' => 50,
        ],
        [
            'credits' => 1000,
            'bonus_percent' => 75,
        ],
    ],
];
