<?php

use yii\helpers\Html;

$this->title = 'เพิ่ม script';
$this->params['breadcrumbs'][] = ['label' => 'คลัง Script', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="dhdc-page-header">
    <div>
        <h1 class="dhdc-page-title"><?= Html::encode($this->title) ?></h1>
        <div class="dhdc-page-subtitle">สร้าง SQL script ด้วย model/form เดิม</div>
    </div>
</div>

<div class="sqlscript-create dhdc-panel">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
