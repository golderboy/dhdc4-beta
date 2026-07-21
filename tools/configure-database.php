<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$target = $root . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'config'
    . DIRECTORY_SEPARATOR . 'connect_database.php';

function requiredEnvironment(string $name): string
{
    $value = getenv($name);
    if ($value === false || $value === '') {
        throw new RuntimeException("$name is required.");
    }

    return $value;
}

try {
    $host = requiredEnvironment('DHDC_DB_HOST');
    $port = requiredEnvironment('DHDC_DB_PORT');
    $database = requiredEnvironment('DHDC_DB_NAME');
    $username = requiredEnvironment('DHDC_DB_USER');
    $password = requiredEnvironment('DHDC_DB_PASSWORD');

    if (!ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) {
        throw new RuntimeException('DHDC_DB_PORT must be a valid TCP port.');
    }
    if (!preg_match('/^[a-zA-Z0-9_.:-]+$/', $host)) {
        throw new RuntimeException('DHDC_DB_HOST contains unsupported characters.');
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
        throw new RuntimeException('DHDC_DB_NAME contains unsupported characters.');
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        throw new RuntimeException('DHDC_DB_USER contains unsupported characters.');
    }
    if (strlen($password) < 32) {
        throw new RuntimeException('DHDC_DB_PASSWORD must contain at least 32 characters.');
    }

    $contents = "<?php\n\n"
        . '$db_host = ' . var_export($host, true) . ";\n"
        . '$db_port = ' . (int) $port . ";\n"
        . '$db_name = ' . var_export($database, true) . ";\n"
        . '$db_user = ' . var_export($username, true) . ";\n"
        . '$db_pass = ' . var_export($password, true) . ";\n";

    $temporary = tempnam(dirname($target), 'dhdc-db-');
    if ($temporary === false) {
        throw new RuntimeException('Cannot create a temporary database config file.');
    }
    if (file_put_contents($temporary, $contents, LOCK_EX) === false) {
        @unlink($temporary);
        throw new RuntimeException('Cannot write the temporary database config file.');
    }
    @chmod($temporary, 0600);
    if (!rename($temporary, $target)) {
        @unlink($temporary);
        throw new RuntimeException('Cannot replace the local database config file.');
    }

    echo "Local database configuration updated for user $username.\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "Local database configuration failed: {$exception->getMessage()}\n");
    exit(1);
}
