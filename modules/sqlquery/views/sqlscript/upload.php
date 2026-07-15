<?php

use yii\widgets\ActiveForm;

$this->title = 'นำเข้า Script';
$this->params['breadcrumbs'][] = ['label' => 'คลัง Script', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="dhdc-page-header">
    <div>
        <h1 class="dhdc-page-title">นำเข้า Script</h1>
        <div class="dhdc-page-subtitle">รองรับไฟล์ .txt และ .sql ตาม workflow เดิม</div>
    </div>
</div>

<div class="sqlscript-create dhdc-panel">
    <div class="alert alert-success">
        อนุญาตเฉพาะไฟล์นามสกุล .txt หรือ .sql เท่านั้น
    </div>

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'file[]')->fileInput(['multiple' => true]) ?>

    <button class="btn btn-success"><i class="glyphicon glyphicon-upload"></i> ตกลง</button>

    <?php ActiveForm::end(); ?>
</div>
