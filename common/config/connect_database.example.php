<?php
// ตั้งค่าการเชื่อมต่อกับฐานข้อมูล

$db_host = getenv('DHDC_DB_HOST') ?: '127.0.0.1';
$db_name = getenv('DHDC_DB_NAME') ?: 'dhdc4';
$db_port = (int) (getenv('DHDC_DB_PORT') ?: 3306);
$db_user = getenv('DHDC_DB_USER');
$db_pass = getenv('DHDC_DB_PASSWORD');

if (!$db_user || $db_pass === false || $db_pass === '') {
    throw new RuntimeException('DHDC_DB_USER and DHDC_DB_PASSWORD are required.');
}

// จบการตั้งค่า
