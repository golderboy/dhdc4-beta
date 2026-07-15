<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Material UI-like CSS layer for Yii2 server-rendered frontend pages.
 */
class MuiWebAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/mui-web/tokens.css',
        'css/mui-web/layout.css',
        'css/mui-web/components.css',
    ];
    public $js = [
        'js/mui-web.js',
    ];
    public $depends = [
        'frontend\assets\AppAsset',
    ];
}
