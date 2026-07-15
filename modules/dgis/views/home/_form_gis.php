<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\dgis\models\Home */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="home-form">

<?php
       $form = kartik\widgets\ActiveForm::begin(
       [
           'id' => 'Home-form',
       //    'enableAjaxValidation' => true,
       ]);
	?>
    <?= $form->field($model, 'LATITUDE')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'LONGITUDE')->textInput(['maxlength' => true]) ?>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
