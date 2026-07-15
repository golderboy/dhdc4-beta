<?php

$root = dirname(__DIR__);
$failures = [];

$checks = [
    'frontend/web/index.php must not hard-code YII_DEBUG=true' => [
        'file' => 'frontend/web/index.php',
        'absent' => "define('YII_DEBUG', true)",
    ],
    'frontend debug/gii must be gated by YII_ENV_DEV' => [
        'file' => 'frontend/config/main-local.php',
        'present' => 'YII_ENV_DEV',
    ],
    'backend/web/index.php must not hard-code YII_DEBUG=true' => [
        'file' => 'backend/web/index.php',
        'absent' => "define('YII_DEBUG', true)",
    ],
    'backend debug/gii must be gated by YII_ENV_DEV' => [
        'file' => 'backend/config/main-local.php',
        'present' => 'YII_ENV_DEV',
    ],
    'SQL runner must not create temporary procedures' => [
        'file' => 'modules/sqlquery/controllers/RunqueryController.php',
        'absent' => 'tmp_store_proc',
    ],
    'SQL runner must be admin-only' => [
        'file' => 'modules/sqlquery/controllers/RunqueryController.php',
        'present' => "'roles' => ['Admin']",
    ],
    'SQL runner security logging must be present' => [
        'file' => 'modules/sqlquery/controllers/RunqueryController.php',
        'present' => 'security.sqlquery',
    ],
    'Frontend response headers must be configured' => [
        'file' => 'frontend/config/main.php',
        'present' => 'Content-Security-Policy',
    ],
    'Backend response headers must be configured' => [
        'file' => 'backend/config/main.php',
        'present' => 'Content-Security-Policy',
    ],
    'Session SameSite must be configured' => [
        'file' => 'frontend/config/main.php',
        'present' => 'SAME_SITE_LAX',
    ],
    'Backend session SameSite must be configured' => [
        'file' => 'backend/config/main.php',
        'present' => 'SAME_SITE_LAX',
    ],
    'Import zip extraction must use safe entry list' => [
        'file' => 'frontend/modules/import/controllers/AjaxController.php',
        'present' => 'extractZipToSafeDir',
    ],
    'Import ajax must enforce HTTP verbs' => [
        'file' => 'frontend/modules/import/controllers/AjaxController.php',
        'present' => 'VerbFilter::className()',
    ],
    'Import security logging must be present' => [
        'file' => 'frontend/modules/import/controllers/AjaxController.php',
        'present' => 'security.import',
    ],
    'Import error logging must use bound params' => [
        'file' => 'frontend/modules/import/controllers/AjaxController.php',
        'present' => ':err',
    ],
    'Import2 zip extraction must use safe entry list' => [
        'file' => 'frontend/modules/import2/controllers/AjaxController.php',
        'present' => 'extractZipToSafeDir',
    ],
    'Import2 ajax must enforce HTTP verbs' => [
        'file' => 'frontend/modules/import2/controllers/AjaxController.php',
        'present' => 'VerbFilter::className()',
    ],
    'Import2 security logging must be present' => [
        'file' => 'frontend/modules/import2/controllers/AjaxController.php',
        'present' => 'security.import2',
    ],
    'Import2 error logging must use bound params' => [
        'file' => 'frontend/modules/import2/controllers/AjaxController.php',
        'present' => ':err',
    ],
    'EHR controller must keep CSRF enabled' => [
        'file' => 'modules/ehr/controllers/DefaultController.php',
        'absent' => 'enableCsrfValidation = false',
    ],
    'HDC report setup must keep CSRF enabled' => [
        'file' => 'backend/modules/hdcreportsetup/controllers/HdcsqlController.php',
        'absent' => 'enableCsrfValidation = false',
    ],
    'HDC report setup must use bound params' => [
        'file' => 'backend/modules/hdcreportsetup/controllers/HdcsqlController.php',
        'present' => ':report_name',
    ],
    'HDC data exchange report list must require access control' => [
        'file' => 'frontend/modules/hdcex/controllers/DefaultController.php',
        'present' => "'report-list'",
    ],
    'GIS JSON importer must require access control' => [
        'file' => 'modules/gis/controllers/JsonController.php',
        'present' => 'AccessControl::className()',
    ],
    'HRP JSON importer must require access control' => [
        'file' => 'modules/hrp/controllers/JsonController.php',
        'present' => 'AccessControl::className()',
    ],
    'TB maps JSON importer must require access control' => [
        'file' => 'modules/Tbmaps/controllers/JsonController.php',
        'present' => 'AccessControl::className()',
    ],
    'GIS map village lookup must use bound params' => [
        'file' => 'modules/gis/controllers/DefaultController.php',
        'present' => ':dolacode',
    ],
    'HRP map village lookup must use bound params' => [
        'file' => 'modules/hrp/controllers/MapController.php',
        'present' => ':dolacode',
    ],
    'TB maps village lookup must use bound params' => [
        'file' => 'modules/Tbmaps/controllers/MapController.php',
        'present' => ':dolacode',
    ],
    'Import detail ZIP lookup must use bound params' => [
        'file' => 'frontend/modules/import/views/upload/detail.php',
        'present' => ':zipname',
    ],
    'Import2 detail ZIP lookup must use bound params' => [
        'file' => 'frontend/modules/import2/views/upload/detail.php',
        'present' => ':zipname',
    ],
    'Backend transform runner must require access control' => [
        'file' => 'backend/modules/exec/controllers/TransformController.php',
        'present' => 'AccessControl::className()',
    ],
    'Backend transform runner must validate procedure names' => [
        'file' => 'backend/modules/exec/controllers/TransformController.php',
        'present' => 'ข้อมูลกระบวนการไม่ถูกต้อง',
    ],
    'Backend QC runner must require access control' => [
        'file' => 'backend/modules/exec/controllers/QcController.php',
        'present' => 'AccessControl::className()',
    ],
    'Backend gate must require access control' => [
        'file' => 'backend/modules/gate/controllers/DefaultController.php',
        'present' => 'AccessControl::className()',
    ],
];

foreach ($checks as $label => $check) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $check['file']);
    if (!is_file($path)) {
        $failures[] = "$label: missing {$check['file']}";
        continue;
    }
    $contents = file_get_contents($path);
    if (isset($check['present']) && strpos($contents, $check['present']) === false) {
        $failures[] = "$label: expected '{$check['present']}'";
    }
    if (isset($check['absent']) && strpos($contents, $check['absent']) !== false) {
        $failures[] = "$label: found forbidden '{$check['absent']}'";
    }
}

$deletedFiles = [
    'info.php',
    'frontend/web/index-test.php',
    'backend/web/index-test.php',
    'modules/ehr/controllers/DefaultController_.php',
    'frontend/modules/import/views/upload/importall_1.php',
    'frontend/modules/import/views/upload/view_1.php',
    'frontend/modules/import2/views/upload/importall_1.php',
    'frontend/modules/import2/views/upload/view_1.php',
];

foreach ($deletedFiles as $file) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    if (file_exists($path)) {
        $failures[] = "legacy file must be removed: $file";
    }
}

$activePhpFiles = [
    'frontend/modules/import/controllers/AjaxController.php',
    'frontend/modules/import2/controllers/AjaxController.php',
    'modules/sqlquery/controllers/RunqueryController.php',
    'modules/ehr/controllers/DefaultController.php',
    'backend/modules/hdcreportsetup/controllers/HdcsqlController.php',
    'backend/modules/exec/controllers/DefaultController.php',
    'backend/modules/exec/controllers/TransformController.php',
    'backend/modules/exec/controllers/QcController.php',
    'backend/modules/gate/controllers/DefaultController.php',
    'modules/gis/controllers/JsonController.php',
    'modules/gis/controllers/DefaultController.php',
    'modules/hrp/controllers/JsonController.php',
    'modules/hrp/controllers/MapController.php',
    'modules/Tbmaps/controllers/JsonController.php',
    'modules/Tbmaps/controllers/MapController.php',
    'frontend/modules/import/views/upload/detail.php',
    'frontend/modules/import2/views/upload/detail.php',
    'backend/config/main.php',
    'backend/config/main-local.php',
    'backend/web/index.php',
];

foreach ($activePhpFiles as $file) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
    $output = [];
    $status = 0;
    exec('php -l ' . escapeshellarg($path) . ' 2>&1', $output, $status);
    if ($status !== 0) {
        $failures[] = "$file: " . implode(' ', $output);
    }
}

if (!empty($failures)) {
    echo "OWASP regression check failed:\n";
    foreach ($failures as $failure) {
        echo "- $failure\n";
    }
    exit(1);
}

echo "OWASP regression check passed.\n";
