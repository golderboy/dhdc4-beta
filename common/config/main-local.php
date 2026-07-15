<?php

require(__DIR__ . '/connect_database.php');
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => "mysql:host=$db_host;dbname=$db_name;port=$db_port",
            'username' => $db_user,
            'password' => $db_pass,
            'charset' => 'utf8mb4',
            'attributes' => [PDO::MYSQL_ATTR_LOCAL_INFILE => true],
            'on afterOpen' => function ($event) {
                $event->sender->createCommand(
                    "SET SESSION character_set_collations='utf8mb3=utf8mb3_general_ci'"
                )->execute();
            },
        ],
        'db_update' => require(__DIR__.'/connect_update.php'),
        'mailer' => [
            'class' => 'yii\symfonymailer\Mailer',
            'viewPath' => '@common/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
    ],
];
