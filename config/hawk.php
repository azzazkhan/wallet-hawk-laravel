<?php

return [
    'opensea' => [
        'event' => [
            'types'    => [
                'created',
                'successful',
                'cancelled',
                'bid_entered',
                'bid_withdrawn',
                'transfer',
                'offer_entered',
                'approve',
                'unknown', // If no value is present
            ],
            'schema'   => ['ERC721', 'ERC1155'],
            'per_page' => (int) env('OPENSEA_RECORDS_PER_PAGE', 20)
        ],
        'network' => [
            'max_calls_sec' => (int) env('OPENSEA_MAX_API_CALLS', 20),
            'max_calls_daily' => (int) env('OPENSEA_MAX_API_CALLS_DAILY', INF)
        ],
        'limits' => [
            'default' => (int) env('OPENSEA_LOCK_DURATION', 0),
            'pagination' => (int) env('OPENSEA_PAGINATION_LOCK_DURATION', 0)
        ],
        'api_key' => env('OPENSEA_API_KEY'),
    ],
    'etherscan' => [
        'api_key' => env('ETHERSCAN_API_KEY'),
        'limits' => [
            'default' => (int) env('OPENSEA_LOCK_DURATION', 0),
            'pagination' => (int) env('OPENSEA_PAGINATION_LOCK_DURATION', 0)
        ],
        'blocks' => [
            'per_page' => (int) env('ETHERSCAN_RECORDS_PER_PAGE', 20)
        ]
    ]
];
