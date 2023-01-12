<?php
return [
    'api_layer' => [
        'end_point' => "https://api.apilayer.com/exchangerates_data/",
        'api_key' => "RlWU6fsIJW998VejTRrkD0AaiLdCLJ8G",
        'actions' => [
            'convert' => [
                'method' => 'GET',
                'action'=>'convert?to={to}&from={from}&amount={amount}',
                'params' => ['to','from','amount']
            ],
            'currencies' => [
                'method' => 'GET',
                'action'=>'symbols',
                'params' => []
            ]
        ]
    ]
];