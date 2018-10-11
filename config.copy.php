<?php
define('ENV', 'DEVELOPMENT');//DEVELOPMENT, PRODUCTION, TESTING

switch (ENV) {
    case 'PRODUCTION':
        $_ENV =array(
            'DB'=>[
                'HOST' => '127.0.0.1',
                'NAME' => '',
                'USER' => '',
                'PASSWORD' => '',
            ],

            'SNS'=>[
                'KEY' => '',
                'SECRET' => '',
                'VERSION' => '',
                'REGION' => '',
                'SCHEME' => 'http'
            ]
        );
        break;

    default: //is development
        $_ENV =array(
            'DB'=>[
                'HOST' => '',
                'NAME' => '',
                'USER' => '',
                'PASSWORD' => '',
            ],

            'SNS'=>[
                'KEY' => '',
                'SECRET' => '',
                'VERSION' => '',
                'REGION' => '',
                'SCHEME' => 'http'
            ]

        );
}
