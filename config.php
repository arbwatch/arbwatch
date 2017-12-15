<?php

$iof = 0.0638;
$spread = 0.04;
$brlInvestment = 1000;
$estimatedBtcMiningFee = 0.000678; 
// TODO https://bitcoinfees.21.co/api 
$bcbUsdBrlApi = 'http://api.promasters.net.br/cotacao/v1/valores';
$usdBrl = json_decode(file_get_contents($bcbUsdBrlApi));

$apis = [
    'CEX' => 'https://cex.io/api/ticker/BTC/USD',
    'MBC' => 'https://www.mercadobitcoin.net/api/BTC/ticker/',
    'FOX' => 'https://api.blinktrade.com/api/v1/BRL/ticker?crypto_currency=BTC',
    'B2U' => 'https://www.bitcointoyou.com/api/ticker.aspx'
];

$ticker = ['CEX' => null, 'MBC' => null, 'FOX' => null, 'B2U' => null];

foreach ($apis as $key => $url)
    $ticker[$key] = json_decode(file_get_contents($apis[$key]));  

$exchanges = [
    'CEX' => [
        'name' => 'CEX.IO',
        'last' => $ticker['CEX']->last,
        'pair' => 'BTCUSD',
        'fee' => [
            'deposit' => [
                'usd' => [
                     'fixed' => 0.25,
                     'percentage' => 0.035
                ]
            ],
            'withdraw' => [
                'btc' => [
                     'fixed' => 0.001,
                     'percentage' => null
                ]
            ],
            'order' => [
                'maker' => null
            ]
        ]
    ],
    'MBC' => [
        'name' => 'MercadoBitcoin',
        'last' => $ticker['MBC']->ticker->last,
        'pair' => 'BTCBRL',
        'fee' => [
            'deposit' => [
                'btc' => [
                     'fixed' => null,
                     'percentage' => null
                ]
            ],
            'withdraw' => [
                'brl' => [
                     'fixed' => 2.99,
                     'percentage' => 0.0199
                ]
            ],
            'order' => [
                'maker' => 0.003
            ]
        ]
    ],
    'FOX' => [
        'name' => 'FoxBit',
        'last' => $ticker['FOX']->last,
        'pair' => 'BTCBRL',
        'fee' => [
            'deposit' => [
                'btc' => [
                     'fixed' => null,
                     'percentage' => null
                ]
            ],
            'withdraw' => [
                'brl' => [
                     'fixed' => 0,
                     'percentage' => 0.0139
                ]
            ],
            'order' => [
                'maker' => 0.0015 // percentage
            ]
        ]
    ],
    'B2U' => [
        'name' => 'BitcoinToYou',
        'last' => $ticker['B2U']->ticker->last,
        'pair' => 'BTCBRL',
        'fee' => [
            'deposit' => [
                'btc' => [
                     'fixed' => null,
                     'percentage' => null
                ]
            ],
            'withdraw' => [
                'brl' => [
                     'fixed' => 0,
                     'percentage' => 0.0189
                ]
            ],
            'order' => [
                'maker' => 0.0025 // percentage
            ]
        ]
    ],
];

?>