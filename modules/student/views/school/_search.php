<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model modules\student\models\SchoolSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="school-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'HOSPCODE') ?>

    <?= $form->field($model, 'VID') ?>

    <?= $form->field($model, 'SCHOOLCODE') ?>

    <?= $form->field($model, 'SCHOOLID') ?>

    <?= $form->field($model, 'SCHOOLNAME') ?>

    <?php // echo $form->field($model, 'SCHOOLOWNER') ?>

    <?php // echo $form->field($model, 'SCHOOLTYPE') ?>

    <?php // echo $form->field($model, 'CLOSEDDATE') ?>

    <?php // echo $form->field($model, 'D_UPDATE') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
