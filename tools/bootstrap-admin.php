<?php

declare(strict_types=1);

/**
 * Create the first Admin account in a clean DHDC Master database.
 *
 * Required environment variables:
 *   DHDC_BOOTSTRAP_ADMIN_EMAIL
 *
 * For a custom account, also set:
 *   DHDC_BOOTSTRAP_ADMIN_USERNAME
 *   DHDC_BOOTSTRAP_ADMIN_PASSWORD
 *
 * Optional environment variable:
 *   DHDC_BOOTSTRAP_ADMIN_NAME
 *
 * Execute:
 *   php tools/bootstrap-admin.php --confirm=CREATE-INITIAL-ADMIN
 *
 * Execute with the documented one-time initial credentials:
 *   php tools/bootstrap-admin.php --use-default-credentials --confirm=CREATE-INITIAL-ADMIN
 *
 * Add --dry-run to test the insert and RBAC assignment, then roll it back.
 */

$options = getopt('', ['confirm:', 'use-default-credentials', 'dry-run']);
if (($options['confirm'] ?? '') !== 'CREATE-INITIAL-ADMIN') {
    fwrite(STDERR, "Use --confirm=CREATE-INITIAL-ADMIN\n");
    exit(2);
}

$useDefaultCredentials = array_key_exists('use-default-credentials', $options);
$dryRun = array_key_exists('dry-run', $options);

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

    $email = requiredEnvironment('DHDC_BOOTSTRAP_ADMIN_EMAIL');
    if ($useDefaultCredentials) {
        $username = 'admin';
        $password = 'P@ssw0rd';
    } else {
        $username = requiredEnvironment('DHDC_BOOTSTRAP_ADMIN_USERNAME');
        $password = requiredEnvironment('DHDC_BOOTSTRAP_ADMIN_PASSWORD');
    }
    $name = getenv('DHDC_BOOTSTRAP_ADMIN_NAME');
    $name = $name === false || trim($name) === '' ? 'System Administrator' : trim($name);

    if (!preg_match('/^[A-Za-z0-9._-]{3,64}$/', $username)) {
        throw new RuntimeException('Admin username must be 3-64 characters using letters, numbers, dot, underscore, or hyphen.');
    }
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        throw new RuntimeException('DHDC_BOOTSTRAP_ADMIN_EMAIL is not a valid email address.');
    }
    if (!$useDefaultCredentials && strlen($password) < 20) {
        throw new RuntimeException('DHDC_BOOTSTRAP_ADMIN_PASSWORD must contain at least 20 characters.');
    }
    if (!$useDefaultCredentials && stripos($password, $username) !== false) {
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

    $now = time();
    $passwordHash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
    if (!is_string($passwordHash) || !password_verify($password, $passwordHash)) {
        throw new RuntimeException('Unable to create and verify the Admin password hash.');
    }

    if ($dryRun) {
        if ((int) $pdo->query('SELECT COUNT(*) FROM user')->fetchColumn() !== 0) {
            throw new RuntimeException('Initial Admin can only be created when the user table is empty.');
        }
        $role = $pdo->query("SELECT COUNT(*) FROM auth_item WHERE name='Admin' AND type=1");
        if ((int) $role->fetchColumn() !== 1) {
            throw new RuntimeException('The Admin RBAC role definition is missing.');
        }
        echo "Initial Admin dry-run passed. No account was created.\n";
        exit(0);
    }

    // These legacy account tables are MyISAM in the Master schema, so a PDO
    // transaction cannot make the three inserts atomic. Hold write locks and
    // explicitly remove partial rows if any insert or verification fails.
    $pdo->exec('LOCK TABLES user WRITE, profile WRITE, auth_assignment WRITE, auth_item READ');
    $tablesLocked = true;

    if ((int) $pdo->query('SELECT COUNT(*) FROM user')->fetchColumn() !== 0) {
        throw new RuntimeException('Initial Admin can only be created when the user table is empty.');
    }
    $role = $pdo->query("SELECT COUNT(*) FROM auth_item WHERE name='Admin' AND type=1");
    if ((int) $role->fetchColumn() !== 1) {
        throw new RuntimeException('The Admin RBAC role definition is missing.');
    }

    $insert = $pdo->prepare(
        'INSERT INTO user '
        . '(username, email, password_hash, auth_key, confirmed_at, registration_ip, created_at, updated_at, flags, status) '
        . 'VALUES '
        . '(:username, :email, :password_hash, :auth_key, :confirmed_at, NULL, :created_at, :updated_at, 0, NULL)'
    );
    $insert->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => $passwordHash,
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

    $verifyUser = $pdo->prepare('SELECT COUNT(*) FROM user WHERE id=:user_id AND username=:username');
    $verifyUser->execute([':user_id' => $userId, ':username' => $username]);
    $verifyProfile = $pdo->prepare('SELECT COUNT(*) FROM profile WHERE user_id=:user_id');
    $verifyProfile->execute([':user_id' => $userId]);
    $verifyRole = $pdo->prepare(
        "SELECT COUNT(*) FROM auth_assignment WHERE user_id=:user_id AND item_name='Admin'"
    );
    $verifyRole->execute([':user_id' => (string) $userId]);
    if ((int) $verifyUser->fetchColumn() !== 1
        || (int) $verifyProfile->fetchColumn() !== 1
        || (int) $verifyRole->fetchColumn() !== 1) {
        throw new RuntimeException('Initial Admin verification failed.');
    }

    $pdo->exec('UNLOCK TABLES');
    $tablesLocked = false;
    echo "Initial Admin account created successfully.\n";
    if ($useDefaultCredentials) {
        fwrite(STDERR, "SECURITY WARNING: Sign in as admin and change the default password immediately.\n");
    }
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && ($tablesLocked ?? false)) {
        try {
            if (isset($userId) && $userId > 0) {
                $pdo->prepare('DELETE FROM auth_assignment WHERE user_id=:user_id')
                    ->execute([':user_id' => (string) $userId]);
                $pdo->prepare('DELETE FROM profile WHERE user_id=:user_id')
                    ->execute([':user_id' => $userId]);
                $pdo->prepare('DELETE FROM user WHERE id=:user_id')
                    ->execute([':user_id' => $userId]);
            }
            $pdo->exec('UNLOCK TABLES');
        } catch (Throwable $cleanupException) {
            fwrite(STDERR, "Initial Admin cleanup also failed: {$cleanupException->getMessage()}\n");
        }
    }
    fwrite(STDERR, "Initial Admin creation failed: {$exception->getMessage()}\n");
    exit(1);
}
