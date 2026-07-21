<?php

$host = getenv('DHDC_UPDATE_DB_HOST') ?: '127.0.0.1';
$name = getenv('DHDC_UPDATE_DB_NAME') ?: 'dhdc_update';
$port = (int) (getenv('DHDC_UPDATE_DB_PORT') ?: 3306);
$user = getenv('DHDC_UPDATE_DB_USER');
$password = getenv('DHDC_UPDATE_DB_PASSWORD');

if (!$user || $password === false || $password === '') {
    throw new RuntimeException('DHDC_UPDATE_DB_USER and DHDC_UPDATE_DB_PASSWORD are required.');
}

return [

    'class' => 'yii\db\Connection',
    'dsn' => "mysql:host={$host};dbname={$name};port={$port}",
    'username' => $user,
    'password' => $password,
    'charset' => 'utf8',
];

