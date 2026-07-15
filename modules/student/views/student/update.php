<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model modules\student\models\Student */

$this->title = 'Update Student: ' . $model->HOSPCODE;
$this->params['breadcrumbs'][] = ['label' => 'Students', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->HOSPCODE, 'url' => ['view', 'HOSPCODE' => $model->HOSPCODE, 'PID' => $model->PID, 'SCHOOLCODE' => $model->SCHOOLCODE, 'EDUCATIONYEAR' => $model->EDUCATIONYEAR, 'CLASS' => $model->CLASS]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="student-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
