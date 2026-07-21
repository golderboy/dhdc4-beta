<?php

$dbDsn = getenv('DHDC_DB_DSN');
$dbUser = getenv('DHDC_DB_USER');
$dbPassword = getenv('DHDC_DB_PASSWORD');
$mailerDsn = getenv('DHDC_MAILER_DSN');

if (!$dbDsn || !$dbUser || $dbPassword === false || $dbPassword === '' || !$mailerDsn) {
    throw new RuntimeException('DHDC_DB_DSN, DHDC_DB_USER, DHDC_DB_PASSWORD and DHDC_MAILER_DSN are required.');
}

return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => $dbDsn,
            'username' => $dbUser,
            'password' => $dbPassword,
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => 'yii\symfonymailer\Mailer',
            'viewPath' => '@common/mail',
            'transport' => [
                'dsn' => $mailerDsn,
            ],
            'useFileTransport' => false,
        ],
    ],
];
