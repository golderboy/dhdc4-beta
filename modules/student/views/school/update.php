<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model modules\student\models\School */

$this->title = 'Update School: ' . $model->HOSPCODE;
$this->params['breadcrumbs'][] = ['label' => 'Schools', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->HOSPCODE, 'url' => ['view', 'HOSPCODE' => $model->HOSPCODE, 'VID' => $model->VID, 'SCHOOLCODE' => $model->SCHOOLCODE]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="school-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
