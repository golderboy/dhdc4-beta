<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$options = getopt('', ['username::', 'role::', 'app::']);
$username = isset($options['username']) ? trim((string) $options['username']) : '';
$role = isset($options['role']) ? trim((string) $options['role']) : 'User';
$appName = isset($options['app']) ? trim((string) $options['app']) : 'frontend';

if (!in_array($appName, ['frontend', 'backend'], true)) {
    fwrite(STDERR, "Invalid --app value. Use frontend or backend.\n");
    exit(1);
}

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

$appRoot = $root . '/' . $appName;
$_SERVER['SCRIPT_FILENAME'] = $appRoot . '/web/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_NAME'] = '127.0.0.1';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['HTTP_HOST'] = '127.0.0.1';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

require $root . '/vendor/autoload.php';
require $root . '/vendor/yiisoft/yii2/Yii.php';
require $root . '/common/config/bootstrap.php';
require $appRoot . '/config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require $root . '/common/config/main.php',
    require $root . '/common/config/main-local.php',
    require $appRoot . '/config/main.php',
    require $appRoot . '/config/main-local.php'
);

$app = new yii\web\Application($config);

if ($username === '') {
    $username = (string) Yii::$app->db->createCommand(
        "SELECT u.username
         FROM user u
         INNER JOIN auth_assignment a ON a.user_id = u.id
         WHERE a.item_name = :role
           AND u.blocked_at IS NULL
           AND u.confirmed_at IS NOT NULL
         ORDER BY u.id
         LIMIT 1",
        [':role' => $role]
    )->queryScalar();
}

if ($username === '') {
    fwrite(STDERR, "No test user with RBAC role $role was found.\n");
    exit(1);
}

$identityClass = Yii::$app->user->identityClass;
$identity = $identityClass::find()->where(['username' => $username])->one();

if ($identity === null) {
    fwrite(STDERR, "User not found: $username\n");
    exit(1);
}

Yii::$app->user->login($identity, 0);
$session = Yii::$app->session;
$sessionId = $session->id;
$sessionName = $session->name;
$userId = Yii::$app->user->id;
$canAccess = Yii::$app->user->can($role);
$session->close();

echo json_encode([
    'sessionName' => $sessionName,
    'sessionId' => $sessionId,
    'userId' => $userId,
    'username' => $username,
    'role' => $role,
    'app' => $appName,
    'canAccessRole' => $canAccess,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . PHP_EOL;

exit(0);
