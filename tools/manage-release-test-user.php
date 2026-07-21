<?php

declare(strict_types=1);

$options = getopt('', ['action:', 'username:', 'role::', 'confirm:']);
$action = strtolower(trim((string) ($options['action'] ?? '')));
$username = trim((string) ($options['username'] ?? ''));
$role = trim((string) ($options['role'] ?? ''));

if (($options['confirm'] ?? '') !== 'MANAGE-RELEASE-TEST-USER'
    || !in_array($action, ['create', 'delete'], true)
    || !preg_match('/^dhdc_release_[a-z0-9_]{8,40}$/', $username)
    || ($action === 'create' && !in_array($role, ['User', 'Admin'], true))) {
    fwrite(
        STDERR,
        "Usage: php tools/manage-release-test-user.php --action=create|delete "
        . "--username=dhdc_release_<suffix> [--role=User|Admin] "
        . "--confirm=MANAGE-RELEASE-TEST-USER\n"
    );
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

try {
    $config = require dirname(__DIR__) . '/common/config/connect_database.php';
    unset($config);
    if (!isset($db_host, $db_port, $db_name, $db_user, $db_pass)) {
        throw new RuntimeException('Local database configuration is incomplete.');
    }

    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $db_host, $db_port, $db_name),
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $pdo->beginTransaction();
    $find = $pdo->prepare('SELECT id FROM user WHERE username = :username FOR UPDATE');
    $find->execute([':username' => $username]);
    $userId = $find->fetchColumn();

    if ($action === 'delete') {
        if ($userId === false) {
            throw new RuntimeException('Release test user was not found.');
        }
        $pdo->prepare('DELETE FROM auth_assignment WHERE user_id = :user_id')
            ->execute([':user_id' => (string) $userId]);
        $pdo->prepare('DELETE FROM profile WHERE user_id = :user_id')
            ->execute([':user_id' => (int) $userId]);
        $pdo->prepare('DELETE FROM user WHERE id = :user_id')
            ->execute([':user_id' => (int) $userId]);
        $pdo->commit();
        echo "Release test user deleted.\n";
        exit(0);
    }

    if ($userId !== false) {
        throw new RuntimeException('Release test username already exists.');
    }
    $password = requiredEnvironment('DHDC_RELEASE_TEST_PASSWORD');
    if (strlen($password) < 32) {
        throw new RuntimeException('DHDC_RELEASE_TEST_PASSWORD must contain at least 32 characters.');
    }
    $roleExists = $pdo->prepare('SELECT COUNT(*) FROM auth_item WHERE name = :role AND type = 1');
    $roleExists->execute([':role' => $role]);
    if ((int) $roleExists->fetchColumn() !== 1) {
        throw new RuntimeException('Requested RBAC role does not exist.');
    }

    $now = time();
    $email = $username . '@example.invalid';
    $insert = $pdo->prepare(
        'INSERT INTO user '
        . '(username, email, password_hash, auth_key, confirmed_at, registration_ip, created_at, updated_at, flags, status) '
        . 'VALUES '
        . '(:username, :email, :password_hash, :auth_key, :confirmed_at, :registration_ip, :created_at, :updated_at, 0, NULL)'
    );
    $insert->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]),
        ':auth_key' => bin2hex(random_bytes(16)),
        ':confirmed_at' => $now,
        ':registration_ip' => '127.0.0.1',
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);
    $userId = (int) $pdo->lastInsertId();
    $pdo->prepare('INSERT INTO profile (user_id, name) VALUES (:user_id, :name)')
        ->execute([
            ':user_id' => $userId,
            ':name' => 'Release verification ' . $role,
        ]);
    $pdo->prepare(
        'INSERT INTO auth_assignment (item_name, user_id, created_at) '
        . 'VALUES (:role, :user_id, :created_at)'
    )->execute([
        ':role' => $role,
        ':user_id' => (string) $userId,
        ':created_at' => $now,
    ]);
    $pdo->commit();

    echo "Release test user created with role $role.\n";
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fwrite(STDERR, "Release test user operation failed: {$exception->getMessage()}\n");
    exit(1);
}
