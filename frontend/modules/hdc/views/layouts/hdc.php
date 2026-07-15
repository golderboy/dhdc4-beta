<?php

use yii\helpers\Html;
use frontend\assets\AppAsset;
use frontend\assets\MuiWebAsset;


/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
MuiWebAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
        <link rel="icon" href="favicon.ico" type="image/x-icon">

        <title><?= Html::encode($this->title) ?></title>
        <?php $this->head() ?>
    </head>
    <body class="mui-web-scope" data-mui-web-color-scheme="light">
        <?php $this->beginBody() ?>
        <div class="mui-web-page-container mui-web-report-shell">
             
            <?= $content ?>                     

        </div>

        
            <div class="container mui-web-report-note">
                <p class="pull-right">
                    หมายเหตุ: ชุดคำสั่งประมวลผลข้อมูลในหน้าจอนี้ นำมาจากโปรแกรม HDC ของกระทรวงสาธารณสุข
                </p>
            </div>
       

        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
