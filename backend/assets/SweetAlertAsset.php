<?php

namespace backend\assets;

class SweetAlertAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@backend/web/js';
    public $css = [
        'swal/sweetalert.css',
    ];
    public $js = [
        'swal/sweetalert.min.js'
    ];
}

