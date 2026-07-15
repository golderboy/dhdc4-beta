<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model modules\hrp\models\HrpinputSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="hrpinput-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'HOSPCODE') ?>

    <?= $form->field($model, 'PID') ?>

    <?= $form->field($model, 'GRAVIDA') ?>

    <?= $form->field($model, 'RISK1') ?>

    <?= $form->field($model, 'RISK2') ?>

    <?php // echo $form->field($model, 'RISK3') ?>

    <?php // echo $form->field($model, 'RISK') ?>

    <?php // echo $form->field($model, 'PLAN') ?>

    <?php // echo $form->field($model, 'OSM') ?>

    <?php // echo $form->field($model, 'INFO') ?>

    <?php // echo $form->field($model, 'STATUS') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
