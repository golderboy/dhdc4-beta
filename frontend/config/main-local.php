<?php

$cookieValidationKey = getenv('DHDC_FRONTEND_COOKIE_VALIDATION_KEY');
if ($cookieValidationKey === false || $cookieValidationKey === '') {
    $cookieKeyFile = __DIR__ . '/cookie-validation-key.php';
    if (!is_file($cookieKeyFile)) {
        throw new RuntimeException(
            'DHDC_FRONTEND_COOKIE_VALIDATION_KEY is required. Run php tools/create-cookie-key-file.php --app=frontend for local development.'
        );
    }
    $cookieValidationKey = require $cookieKeyFile;
}
if (!is_string($cookieValidationKey) || strlen($cookieValidationKey) < 32) {
    throw new RuntimeException('Frontend cookie validation key must contain at least 32 characters.');
}

$config = [
    'components' => [
        'request' => [
            'cookieValidationKey' => $cookieValidationKey,
        ],
    ],
];

if (YII_ENV_DEV && !YII_ENV_TEST) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
