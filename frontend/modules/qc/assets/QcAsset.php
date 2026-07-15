<?php

namespace frontend\modules\qc\assets;

use yii\web\AssetBundle;

class QcAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/qc-index.css',
    ];
    public $depends = [
        'frontend\assets\MuiWebAsset',
    ];
}
