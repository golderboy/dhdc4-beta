<?php

declare(strict_types=1);

$options = getopt('', ['confirm:']);
if (($options['confirm'] ?? '') !== 'ROTATE-DATABASE-ADMIN') {
    fwrite(STDERR, "Usage: php tools/rotate-database-admin.php --confirm=ROTATE-DATABASE-ADMIN\n");
    exit(2);
}

function requiredEnvironment(string $name): string
{
    $value = getenv($name);
    if ($value === false || $value === '') {
        throw new RuntimeException("$name is required.");
    }

    return $value;
}

function sqlString(string $value): string
{
    return "'" . str_replace("'", "''", $value) . "'";
}

try {
    $host = requiredEnvironment('DHDC_DB_ADMIN_HOST');
    $port = requiredEnvironment('DHDC_DB_ADMIN_PORT');
    $username = requiredEnvironment('DHDC_DB_ADMIN_USER');
    $currentPassword = requiredEnvironment('DHDC_DB_ADMIN_CURRENT_PASSWORD');
    $secretFile = requiredEnvironment('DHDC_DB_ADMIN_SECRET_FILE');

    if (!ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) {
        throw new RuntimeException('DHDC_DB_ADMIN_PORT must be a valid TCP port.');
    }
    if (!preg_match('/^[a-zA-Z0-9_.:-]+$/', $host)) {
        throw new RuntimeException('DHDC_DB_ADMIN_HOST contains unsupported characters.');
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        throw new RuntimeException('DHDC_DB_ADMIN_USER contains unsupported characters.');
    }
    if (is_file($secretFile)) {
        throw new RuntimeException('DHDC_DB_ADMIN_SECRET_FILE already exists.');
    }
    $secretDirectory = dirname($secretFile);
    if (!is_dir($secretDirectory) || !is_writable($secretDirectory)) {
        throw new RuntimeException('DHDC_DB_ADMIN_SECRET_FILE directory must exist and be writable.');
    }

    $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $host, (int) $port);
    $pdo = new PDO($dsn, $username, $currentPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $statement = $pdo->prepare(
        "SELECT Host
         FROM mysql.user
         WHERE User = :username
           AND authentication_string = PASSWORD(:current_password)
         ORDER BY Host"
    );
    $statement->execute([
        ':username' => $username,
        ':current_password' => $currentPassword,
    ]);
    $hosts = array_map(static fn(array $row): string => (string) $row['Host'], $statement->fetchAll());
    if ($hosts === []) {
        throw new RuntimeException('No database administrator accounts use the supplied current credential.');
    }
    $orphanAccounts = 0;
    foreach ($hosts as $accountHost) {
        try {
            $pdo->query('SHOW GRANTS FOR ' . sqlString($username) . '@' . sqlString($accountHost))->fetchAll();
        } catch (PDOException $exception) {
            if ((int) $exception->errorInfo[1] !== 1141) {
                throw $exception;
            }
            $orphanAccounts++;
        }
    }
    if ($orphanAccounts !== 0) {
        throw new RuntimeException(
            "Database administrator metadata contains $orphanAccounts orphan account(s); repair them before rotation."
        );
    }

    $activeOthers = (int) $pdo->query(
        "SELECT COUNT(*)
         FROM information_schema.PROCESSLIST
         WHERE ID <> CONNECTION_ID()
           AND COMMAND <> 'Sleep'"
    )->fetchColumn();
    if ($activeOthers !== 0) {
        throw new RuntimeException('Active database work was detected; retry during an idle window.');
    }

    $newPassword = bin2hex(random_bytes(32));
    $secretContents = "<?php\n\n"
        . '$db_host = ' . var_export($host, true) . ";\n"
        . '$db_port = ' . (int) $port . ";\n"
        . '$db_user = ' . var_export($username, true) . ";\n"
        . '$db_pass = ' . var_export($newPassword, true) . ";\n";
    $temporary = tempnam($secretDirectory, 'dhdc-admin-');
    if ($temporary === false || file_put_contents($temporary, $secretContents, LOCK_EX) === false) {
        if (is_string($temporary)) {
            @unlink($temporary);
        }
        throw new RuntimeException('Cannot write the new administrator credential file.');
    }
    @chmod($temporary, 0600);
    if (!rename($temporary, $secretFile)) {
        @unlink($temporary);
        throw new RuntimeException('Cannot publish the new administrator credential file.');
    }

    foreach ($hosts as $accountHost) {
        $sql = 'ALTER USER ' . sqlString($username) . '@' . sqlString($accountHost)
            . ' IDENTIFIED BY ' . sqlString($newPassword);
        try {
            $pdo->exec($sql);
        } catch (PDOException $exception) {
            if ((int) $exception->errorInfo[1] !== 1396) {
                throw $exception;
            }
            $pdo->exec(
                'SET PASSWORD FOR ' . sqlString($username) . '@' . sqlString($accountHost)
                . ' = PASSWORD(' . sqlString($newPassword) . ')'
            );
        }
    }
    $pdo = null;

    $verification = new PDO($dsn, $username, $newPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $verifiedAccounts = (int) $verification->query(
        'SELECT COUNT(*) FROM mysql.user WHERE User = ' . sqlString($username)
        . ' AND authentication_string = PASSWORD(' . sqlString($newPassword) . ')'
    )->fetchColumn();
    if ($verifiedAccounts !== count($hosts)) {
        throw new RuntimeException('Post-rotation administrator account verification failed.');
    }

    echo 'Database administrator credential rotated for ' . count($hosts) . " account host(s).\n";
} catch (Throwable $exception) {
    fwrite(STDERR, "Database administrator rotation failed: {$exception->getMessage()}\n");
    exit(1);
}
