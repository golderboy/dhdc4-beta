<?php

use yii\helpers\Html;

$this->title = 'แก้ไข script: ' . ' ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'คลัง Script', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'script ที่ ' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>

<div class="dhdc-page-header">
    <div>
        <h1 class="dhdc-page-title"><?= Html::encode($this->title) ?></h1>
        <div class="dhdc-page-subtitle">แก้ไข SQL script โดยใช้ model/form เดิม</div>
    </div>
</div>

<div class="sqlscript-update dhdc-panel">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
