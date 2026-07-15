<?php

$modules = [
    'rbac' => [
        'class' => 'johnitvn\rbacplus\Module',
        'userModelClassName' => null,
        'userModelIdField' => 'id',
        'userModelLoginField' => 'username',
        'userModelLoginFieldLabel' => null,
        'userModelExtraDataColumls' => null,
        'beforeCreateController' => null,
        'beforeAction' => null
    ],
    'user' => [
        'class' => 'dektrium\user\Module',
        'enableUnconfirmedLogin' => true,
        'confirmWithin' => 21600,
        'cost' => 12,
        'admins' => ['admin']
    ],
    'gridview' => [
        'class' => '\kartik\grid\Module'
    ],
    'test' => [
        'class' => 'frontend\modules\test\Test'
    ],
    'import' => [
        'class' => 'frontend\modules\import\Import'
    ],
    'import2' => [
        'class' => 'frontend\modules\import2\Import2'
    ],
    'qc' => [
        'class' => 'frontend\modules\qc\Qc',
    ],
    
    'hdc' => [
        'class' => 'frontend\modules\hdc\Hdc',
    ],
    'setup' => [
        'class' => 'backend\modules\setup\Setup',
    ],
    'exec' => [
        'class' => 'backend\modules\exec\Exec',
    ],
    'gate' => [
        'class' => 'backend\modules\gate\Gate',
    ],
    'hdcreportsetup' => [
        'class' => 'backend\modules\hdcreportsetup\HdcReportSetup',
    ],
    'hdcex' => [
        'class' => 'frontend\modules\hdcex\Hdcex'
    ],
    
    'pluginsetup' => [
        'class' => 'backend\modules\pluginsetup\PluginSetup',
    ],
    'plugin' => [
        'class' => 'frontend\modules\plugin\Plugin',
    ],
    
];

return array_merge($modules, require(dirname(dirname(__DIR__)).'/modules/add_modules.php'));

