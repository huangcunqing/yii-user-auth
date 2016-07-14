<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'modules' => [
        'regist' => [
            'class' => 'huangcunqing\resit\Module'
        ],
        "oauth2"=>[
            'class' => 'huangcunqing\oauth2\Module'
        ]
    ],

    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
    ],


];
