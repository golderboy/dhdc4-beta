<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model modules\student\models\Student */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="student-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'HOSPCODE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'PID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'SCHOOLCODE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'EDUCATIONYEAR')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'CLASS')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'D_UPDATE')->textInput() ?>

    <?= $form->field($model, 'GRUDATE_DATE')->textInput() ?>

    <?= $form->field($model, 'id')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
