<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model modules\student\models\School */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="school-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'HOSPCODE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'VID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'SCHOOLCODE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'SCHOOLID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'SCHOOLNAME')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'SCHOOLOWNER')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'SCHOOLTYPE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'CLOSEDDATE')->textInput() ?>

    <?= $form->field($model, 'D_UPDATE')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
