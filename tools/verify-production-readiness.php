<?php

$root = dirname(__DIR__);
$failures = [];
$warnings = [];
$strictRelease = in_array('--strict-release', $argv, true);

function readFileContents(string $root, string $relative): string
{
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (!is_file($path)) {
        throw new RuntimeException("Missing file: $relative");
    }

    return file_get_contents($path);
}

function addFailure(array &$failures, string $message): void
{
    $failures[] = $message;
}

function addWarning(array &$warnings, string $message): void
{
    $warnings[] = $message;
}

function runCheck(string $label, string $command, array &$failures, array &$warnings, bool $warningOnly = false): void
{
    $output = [];
    $status = 0;
    exec($command . ' 2>&1', $output, $status);

    if ($status !== 0) {
        $message = $label . ' failed: ' . trim(implode(' ', $output));
        if ($warningOnly) {
            addWarning($warnings, $message);
            return;
        }
        addFailure($failures, $message);
        return;
    }

    echo "[OK] $label\n";
}

function assertContains(string $label, string $contents, string $needle, array &$failures): void
{
    if (strpos($contents, $needle) === false) {
        addFailure($failures, "$label: expected '$needle'");
    }
}

function assertNotContains(string $label, string $contents, string $needle, array &$failures): void
{
    if (strpos($contents, $needle) !== false) {
        addFailure($failures, "$label: found forbidden '$needle'");
    }
}

try {
    $frontendIndex = readFileContents($root, 'frontend/web/index.php');
    $backendIndex = readFileContents($root, 'backend/web/index.php');
    $frontendMain = readFileContents($root, 'frontend/config/main.php');
    $backendMain = readFileContents($root, 'backend/config/main.php');
    $frontendMainLocal = readFileContents($root, 'frontend/config/main-local.php');
    $backendMainLocal = readFileContents($root, 'backend/config/main-local.php');
    $commonMain = readFileContents($root, 'common/config/main.php');
    $commonMainLocal = readFileContents($root, 'common/config/main-local.php');
    $envProdCommonMainLocal = readFileContents($root, 'environments/prod/common/config/main-local.php');
    $envDevCommonMainLocal = readFileContents($root, 'environments/dev/common/config/main-local.php');
    $hdcsqlController = readFileContents($root, 'backend/modules/hdcreportsetup/controllers/HdcsqlController.php');
    $hdcsqlForm = readFileContents($root, 'backend/modules/hdcreportsetup/views/hdcsql/_form.php');
    $importAjax = readFileContents($root, 'frontend/modules/import/controllers/AjaxController.php');
    $import2Ajax = readFileContents($root, 'frontend/modules/import2/controllers/AjaxController.php');
    $hdcexController = readFileContents($root, 'frontend/modules/hdcex/controllers/DefaultController.php');
    $gisJsonController = readFileContents($root, 'modules/gis/controllers/JsonController.php');
    $gisDefaultController = readFileContents($root, 'modules/gis/controllers/DefaultController.php');
    $hrpJsonController = readFileContents($root, 'modules/hrp/controllers/JsonController.php');
    $hrpMapController = readFileContents($root, 'modules/hrp/controllers/MapController.php');
    $tbmapsJsonController = readFileContents($root, 'modules/Tbmaps/controllers/JsonController.php');
    $tbmapsMapController = readFileContents($root, 'modules/Tbmaps/controllers/MapController.php');
    $importDetailView = readFileContents($root, 'frontend/modules/import/views/upload/detail.php');
    $import2DetailView = readFileContents($root, 'frontend/modules/import2/views/upload/detail.php');
    $execDefaultController = readFileContents($root, 'backend/modules/exec/controllers/DefaultController.php');
    $execTransformController = readFileContents($root, 'backend/modules/exec/controllers/TransformController.php');
    $execQcController = readFileContents($root, 'backend/modules/exec/controllers/QcController.php');
    $gateController = readFileContents($root, 'backend/modules/gate/controllers/DefaultController.php');
    $composerJsonRaw = readFileContents($root, 'composer.json');
    $packageJsonRaw = readFileContents($root, 'package.json');
} catch (RuntimeException $exception) {
    addFailure($failures, $exception->getMessage());
    $frontendIndex = $backendIndex = $frontendMain = $backendMain = $frontendMainLocal = $backendMainLocal = '';
    $commonMain = $commonMainLocal = $envProdCommonMainLocal = $envDevCommonMainLocal = '';
    $hdcsqlController = $hdcsqlForm = '';
    $importAjax = $import2Ajax = $gisJsonController = $hrpJsonController = $tbmapsJsonController = '';
    $hdcexController = '';
    $gisDefaultController = $hrpMapController = $tbmapsMapController = $importDetailView = $import2DetailView = '';
    $execDefaultController = $execTransformController = $execQcController = $gateController = '';
    $composerJsonRaw = $packageJsonRaw = '{}';
}

assertContains('frontend index must default to prod env', $frontendIndex, "getenv('YII_ENV') ?: 'prod'", $failures);
assertContains('frontend index must read YII_DEBUG from env', $frontendIndex, "getenv('YII_DEBUG')", $failures);
assertNotContains('frontend index must not hard-code debug true', $frontendIndex, "define('YII_DEBUG', true)", $failures);
assertNotContains('frontend index must not hard-code dev env', $frontendIndex, "define('YII_ENV', 'dev')", $failures);
assertContains('backend index must default to prod env', $backendIndex, "getenv('YII_ENV') ?: 'prod'", $failures);
assertContains('backend index must read YII_DEBUG from env', $backendIndex, "getenv('YII_DEBUG')", $failures);
assertNotContains('backend index must not hard-code debug true', $backendIndex, "define('YII_DEBUG', true)", $failures);
assertNotContains('backend index must not hard-code dev env', $backendIndex, "define('YII_ENV', 'dev')", $failures);

assertContains('debug/gii must be dev-only', $frontendMainLocal, 'YII_ENV_DEV && !YII_ENV_TEST', $failures);
assertContains('debug/gii must restrict localhost access', $frontendMainLocal, "'allowedIPs' => ['127.0.0.1', '::1']", $failures);
assertContains('backend debug/gii must be dev-only', $backendMainLocal, 'YII_ENV_DEV && !YII_ENV_TEST', $failures);
assertContains('backend debug/gii must restrict localhost access', $backendMainLocal, "'allowedIPs' => ['127.0.0.1', '::1']", $failures);

foreach (['info.php', 'frontend/web/index-test.php', 'backend/web/index-test.php'] as $publicDevFile) {
    if (is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $publicDevFile))) {
        addFailure($failures, "$publicDevFile must not exist in a production checkout");
    }
}

assertContains('frontend must send CSP', $frontendMain, 'Content-Security-Policy', $failures);
assertContains('frontend must send X-Content-Type-Options', $frontendMain, 'X-Content-Type-Options', $failures);
assertContains('frontend session cookie must be HttpOnly', $frontendMain, "'httpOnly' => true", $failures);
assertContains('frontend session cookie must set SameSite', $frontendMain, 'SAME_SITE_LAX', $failures);
assertContains('backend must send CSP', $backendMain, 'Content-Security-Policy', $failures);
assertContains('backend must send X-Content-Type-Options', $backendMain, 'X-Content-Type-Options', $failures);
assertContains('backend session cookie must be HttpOnly', $backendMain, "'httpOnly' => true", $failures);
assertContains('backend session cookie must set SameSite', $backendMain, 'SAME_SITE_LAX', $failures);
assertContains('common identity cookie must be HttpOnly', $commonMain, "'httpOnly' => true", $failures);
assertContains('common identity cookie must set SameSite', $commonMain, 'SAME_SITE_LAX', $failures);

assertNotContains('HDC report setup must keep CSRF enabled', $hdcsqlController, 'enableCsrfValidation = false', $failures);
assertContains('HDC report setup create/update must use bound params', $hdcsqlController, ':report_name', $failures);
assertContains('HDC report setup delete must use bound params', $hdcsqlController, 'DELETE FROM sys_report_dhdc WHERE id = :id', $failures);
assertContains('HDC report setup export must stream content', $hdcsqlController, 'sendContentAsFile', $failures);
assertContains('HDC report setup form lookup must use bound params', $hdcsqlForm, "':id' => \$model->rpt_id", $failures);
assertContains('Import error logging must use bound params', $importAjax, ':err', $failures);
assertContains('Import2 error logging must use bound params', $import2Ajax, ':err', $failures);
assertContains('Import detail ZIP lookup must use bound params', $importDetailView, ':zipname', $failures);
assertContains('Import2 detail ZIP lookup must use bound params', $import2DetailView, ':zipname', $failures);
assertContains('HDC data exchange report-list must be access controlled', $hdcexController, "'report-list'", $failures);
assertContains('HDC data exchange report-all must be access controlled', $hdcexController, "'report-all'", $failures);
assertContains('GIS map village lookup must use bound params', $gisDefaultController, ':dolacode', $failures);
assertContains('HRP map village lookup must use bound params', $hrpMapController, ':dolacode', $failures);
assertContains('TB maps village lookup must use bound params', $tbmapsMapController, ':dolacode', $failures);

foreach ([
    'modules/gis/controllers/JsonController.php' => $gisJsonController,
    'modules/hrp/controllers/JsonController.php' => $hrpJsonController,
    'modules/Tbmaps/controllers/JsonController.php' => $tbmapsJsonController,
] as $file => $contents) {
    assertContains("$file must require Admin access", $contents, 'AccessControl::className()', $failures);
    assertContains("$file must require POST for read", $contents, "'read' => ['post']", $failures);
    assertContains("$file must bind GIS insert params", $contents, ':coord', $failures);
    assertContains("$file must validate JSON file path", $contents, 'resolveJsonFile', $failures);
}

foreach ([
    'backend/modules/exec/controllers/DefaultController.php' => $execDefaultController,
    'backend/modules/exec/controllers/TransformController.php' => $execTransformController,
    'backend/modules/exec/controllers/QcController.php' => $execQcController,
    'backend/modules/gate/controllers/DefaultController.php' => $gateController,
] as $file => $contents) {
    assertContains("$file must require Admin access", $contents, 'AccessControl::className()', $failures);
}
assertContains('Backend exec check-process must require POST', $execDefaultController, "'check-process' => ['post']", $failures);
assertContains('Backend transform setup must require POST', $execTransformController, "'setup' => ['post']", $failures);
assertContains('Backend transform runner must validate procedure names', $execTransformController, 'ข้อมูลกระบวนการไม่ถูกต้อง', $failures);
assertContains('Backend QC truncate must require POST', $execQcController, "'truncate' => ['post']", $failures);

foreach ([
    'common/config/main-local.php' => $commonMainLocal,
    'environments/prod/common/config/main-local.php' => $envProdCommonMainLocal,
    'environments/dev/common/config/main-local.php' => $envDevCommonMainLocal,
] as $file => $contents) {
    assertContains("$file must use SymfonyMailer", $contents, 'yii\symfonymailer\Mailer', $failures);
    assertNotContains("$file must not use SwiftMailer as active app mailer", $contents, 'yii\swiftmailer\Mailer', $failures);
}

$composerJson = json_decode($composerJsonRaw, true);
if (!is_array($composerJson)) {
    addFailure($failures, 'composer.json is not valid JSON');
} else {
    $require = $composerJson['require'] ?? [];
    if (isset($require['yiisoft/yii2-swiftmailer'])) {
        addFailure($failures, 'composer.json must not require yiisoft/yii2-swiftmailer directly');
    }
    if (($require['yiisoft/yii2-symfonymailer'] ?? null) !== '^2.0.4') {
        addFailure($failures, 'composer.json must require reviewed yiisoft/yii2-symfonymailer major line ^2.0.4');
    }
}

$packageJson = json_decode($packageJsonRaw, true);
if (!is_array($packageJson)) {
    addFailure($failures, 'package.json is not valid JSON');
} elseif (!isset($packageJson['scripts']['verify:production-readiness'])) {
    addFailure($failures, 'package.json must expose verify:production-readiness');
}

if (filter_var(getenv('YII_DEBUG'), FILTER_VALIDATE_BOOLEAN)) {
    addFailure($failures, 'YII_DEBUG is true in the current environment');
}

if (filter_var(getenv('DHDC_ALLOW_PHPINFO'), FILTER_VALIDATE_BOOLEAN)) {
    addFailure($failures, 'DHDC_ALLOW_PHPINFO is true in the current environment');
}

runCheck('OWASP regression verifier', 'php ' . escapeshellarg($root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'verify-owasp-security.php'), $failures, $warnings);
runCheck('Composer vulnerability audit', 'composer audit --abandoned=ignore --format=plain', $failures, $warnings);
runCheck('NPM vulnerability audit', 'npm audit --audit-level=moderate', $failures, $warnings);

$whySwiftmailer = [];
$whySwiftmailerStatus = 0;
exec('composer why swiftmailer/swiftmailer 2>&1', $whySwiftmailer, $whySwiftmailerStatus);
if ($whySwiftmailerStatus === 0 && !empty($whySwiftmailer)) {
    $whyYiiSwiftmailer = [];
    exec('composer why yiisoft/yii2-swiftmailer 2>&1', $whyYiiSwiftmailer);

    addWarning(
        $warnings,
        'swiftmailer/swiftmailer is still installed transitively, but the active Yii app mailer is SymfonyMailer. Current chain: '
        . trim(implode(' ', $whySwiftmailer))
        . (!empty($whyYiiSwiftmailer) ? ' / ' . trim(implode(' ', $whyYiiSwiftmailer)) : '')
    );
}

$gitStatus = [];
$gitStatusCode = 0;
exec('git status --porcelain 2>&1', $gitStatus, $gitStatusCode);
if ($gitStatusCode !== 0) {
    addWarning($warnings, 'Cannot read git status: ' . trim(implode(' ', $gitStatus)));
} elseif (!empty($gitStatus)) {
    $message = 'Working tree is dirty; commit/review before production release.';
    if ($strictRelease) {
        addFailure($failures, $message);
    } else {
        addWarning($warnings, $message);
    }
}

if (!empty($warnings)) {
    echo "\nWarnings:\n";
    foreach ($warnings as $warning) {
        echo "- $warning\n";
    }
}

if (!empty($failures)) {
    echo "\nProduction readiness check failed:\n";
    foreach ($failures as $failure) {
        echo "- $failure\n";
    }
    exit(1);
}

echo "\nProduction readiness check passed";
if (!$strictRelease) {
    echo " (non-strict release mode)";
}
echo ".\n";
