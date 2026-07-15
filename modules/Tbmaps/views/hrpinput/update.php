<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model modules\hrp\models\Hrpinput */

$this->title = 'ข้อมูล: ' . $model->fullname;
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนหญิงตั้งครรถ์', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->fullname,];
$this->params['breadcrumbs'][] = 'บันทึกข้อมูล';
?>
<div class="hrpinput-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
