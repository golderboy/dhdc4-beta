<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model modules\Qof\models\Dhdcqofreport */

$this->title = 'แก้ไขรายงาน: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'รวมรายงาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="dhdcqofreport-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
