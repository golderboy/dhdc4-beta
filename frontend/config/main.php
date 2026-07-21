<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
        ],
        'response' => [
            'on beforeSend' => function ($event) {
                $headers = $event->sender->headers;
                $headers->set('X-Frame-Options', 'SAMEORIGIN');
                $headers->set('X-Content-Type-Options', 'nosniff');
                $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
                $headers->set('Content-Security-Policy', "frame-ancestors 'self'; base-uri 'self'");
                if (Yii::$app->request->isSecureConnection) {
                    $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
                }
            },
        ],
        /*'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],*/
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
            'cookieParams' => [
                'httpOnly' => true,
                'sameSite' => \yii\web\Cookie::SAME_SITE_LAX,
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    // Never persist request/session superglobals: they may contain
                    // credentials, identifiers, or protected health information.
                    'logVars' => [],
                    'maskVars' => [
                        '_SERVER.HTTP_AUTHORIZATION',
                        '_SERVER.HTTP_COOKIE',
                        '_SERVER.HTTP_X_CSRF_TOKEN',
                        '_SERVER.*TOKEN*',
                        '_SERVER.*SECRET*',
                        '_SERVER.*PASSWORD*',
                    ],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@vendor/dektrium/yii2-user/views' => '@frontend/views/user',
                ],
            ],
        ],
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        'urlManagerBackend' => [
            'class' => 'yii\web\urlManager',
            'baseUrl' => '/backend/web',
            //'scriptUrl' => '../backend/web/',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ], 
        
    ],
    'params' => $params,
];
