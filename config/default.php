<?php

    /**
     * Custom configuration for Cryptkeeper Sprinkle.
     *
     */
    return [
        'address_book' => [
            'admin' => [
                'name'  => 'Cryptkeeper'
            ]
        ],
        'session' => [
            'name'          => 'cryptkeeper'
        ],
        'site' => [
            'title'     =>      'Cryptkeeper',
            'currencies' => [
                'fiat_available' => [
                    'USD', 'CAD', 'GBP', 'AUD'
                ],
                'fiat_default_id' => 1,
                // Time, in seconds, we should wait before refreshing the market quotes
                'refresh_interval' => 60
            ]
        ]
    ];
