<?php
$params = array_merge(
	require(__DIR__ . '/../../common/config/params.php'),
	require(__DIR__ . '/../../common/config/params-local.php'),
	require(__DIR__ . '/params.php'),
	require(__DIR__ . '/params-local.php')
);

return  [
	'version' => "0.0.1",
    'basePath' => dirname(__DIR__),
	'timeZone' => 'Asia/Chongqing',
	'bootstrap' => ['log'],
    'modules' => [
        'gii' => [ //for development only
            'class' => 'yii\gii\Module',
        ],
		'oauth2' => [
			'class' => 'filsh\yii2\oauth2server\Module',
			'tokenParamName' => 'accessToken',
			'tokenAccessLifetime' => 3600 * 24,
//			'options' => [
//				'token_param_name' => 'access_token',
//				'access_lifetime' => 3600 * 24
//			],
			'storageMap' => [
				'user_credentials' => 'api\models\User'
			],
			'grantTypes' => [
				'client_credentials' => [
					'class' => 'OAuth2\GrantType\ClientCredentials',
					'allow_public_clients' => false
				],
				'user_credentials' => [
					'class' => 'OAuth2\GrantType\UserCredentials'
				],
				'refresh_token' => [
					'class' => 'OAuth2\GrantType\RefreshToken',
					'always_issue_new_refresh_token' => true
				]
			],
		],
		'v1' => [
			'class' => 'api\versions\v1\Module',
		],

	],
    'components' => [
		'authManager' => [
			'class' => 'yii\rbac\DbManager',
		],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['trace', 'info', 'error', 'warning'],
                ],
            ],
        ],
    ],
    'params' => $params,
];