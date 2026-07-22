<?php

declare(strict_types=1);

/**
 * Create the first Admin account in a clean DHDC Master database.
 *
 * Required environment variables:
 *   DHDC_BOOTSTRAP_ADMIN_USERNAME
 *   DHDC_BOOTSTRAP_ADMIN_EMAIL
 *   DHDC_BOOTSTRAP_ADMIN_PASSWORD
 *
 * Optional environment variable:
 *   DHDC_BOOTSTRAP_ADMIN_NAME
 *
 * Execute:
 *   php tools/bootstrap-admin.php --confirm=CREATE-INITIAL-ADMIN
 */

$options = getopt('', ['confirm:']);
if (($options['confirm'] ?? '') !== 'CREATE-INITIAL-ADMIN') {
    fwrite(STDERR, "Use --confirm=CREATE-INITIAL-ADMIN\n");
    exit(2);
}

function requiredEnvironment(string $name): string
{
    $value = getenv($name);
    if ($value === false || trim($value) === '') {
        throw new RuntimeException("{$name} is required.");
    }

    return trim($value);
}

try {
    require dirname(__DIR__) . '/common/config/connect_database.php';
    if (!isset($db_host, $db_port, $db_name, $db_user, $db_pass)) {
        throw new RuntimeException('Local database configuration is incomplete.');
    }

    $username = requiredEnvironment('DHDC_BOOTSTRAP_ADMIN_USERNAME');
    $email = requiredEnvironment('DHDC_BOOTSTRAP_ADMIN_EMAIL');
    $password = requiredEnvironment('DHDC_BOOTSTRAP_ADMIN_PASSWORD');
    $name = getenv('DHDC_BOOTSTRAP_ADMIN_NAME');
    $name = $name === false || trim($name) === '' ? 'System Administrator' : trim($name);

    if (!preg_match('/^[A-Za-z0-9._-]{3,64}$/', $username)) {
        throw new RuntimeException('Admin username must be 3-64 characters using letters, numbers, dot, underscore, or hyphen.');
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        throw new RuntimeException('DHDC_BOOTSTRAP_ADMIN_EMAIL is not a valid email address.');
    }
    if (strlen($password) < 20) {
        throw new RuntimeException('DHDC_BOOTSTRAP_ADMIN_PASSWORD must contain at least 20 characters.');
    }
    if (stripos($password, $username) !== false) {
        throw new RuntimeException('Admin password must not contain the username.');
    }

    $pdo = new PDO(
        "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $pdo->beginTransaction();
    if (count($pdo->query('SELECT id FROM user FOR UPDATE')->fetchAll(PDO::FETCH_COLUMN)) !== 0) {
        throw new RuntimeException('Initial Admin can only be created when the user table is empty.');
    }

    $role = $pdo->prepare("SELECT COUNT(*) FROM auth_item WHERE name='Admin' AND type=1");
    $role->execute();
    if ((int) $role->fetchColumn() !== 1) {
        throw new RuntimeException('The Admin RBAC role definition is missing.');
    }

    $now = time();
    $insert = $pdo->prepare(
        'INSERT INTO user '
        . '(username, email, password_hash, auth_key, confirmed_at, registration_ip, created_at, updated_at, flags, status) '
        . 'VALUES '
        . '(:username, :email, :password_hash, :auth_key, :confirmed_at, NULL, :created_at, :updated_at, 0, NULL)'
    );
    $insert->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]),
        ':auth_key' => bin2hex(random_bytes(16)),
        ':confirmed_at' => $now,
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);
    $userId = (int) $pdo->lastInsertId();

    $pdo->prepare('INSERT INTO profile (user_id, name) VALUES (:user_id, :name)')
        ->execute([':user_id' => $userId, ':name' => $name]);
    $pdo->prepare(
        "INSERT INTO auth_assignment (item_name, user_id, created_at) VALUES ('Admin', :user_id, :created_at)"
    )->execute([':user_id' => (string) $userId, ':created_at' => $now]);

    $verify = $pdo->prepare(
        "SELECT COUNT(*) FROM user u
         INNER JOIN auth_assignment a ON a.user_id=CAST(u.id AS CHAR)
         WHERE u.id=:user_id AND a.item_name='Admin'"
    );
    $verify->execute([':user_id' => $userId]);
    if ((int) $verify->fetchColumn() !== 1) {
        throw new RuntimeException('Initial Admin verification failed.');
    }

    $pdo->commit();
    echo "Initial Admin account created successfully.\n";
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Initial Admin creation failed: {$exception->getMessage()}\n");
    exit(1);
}
