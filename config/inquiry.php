<?php

return [

    'default' => 'finnotech',

    'drivers' => [
        'finnotech' => [
            'url' => 'https://sandboxapi.finnotech.ir',
            'client_id' => 'example',
            'token' => 'example',
            'refresh_token' => 'example',
        ],
    ],

    'map' => [
        'zarinpal' => \App\Lib\Inquiry\Drivers\Finnotech\Finnotech::class,
    ],

];
