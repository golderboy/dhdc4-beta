<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\dgis\models\Home */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="home-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'HOSPCODE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HOUSE_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HOUSETYPE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ROOMNO')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'CONDO')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HOUSE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'SOISUB')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'SOIMAIN')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ROAD')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'VILLANAME')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'VILLAGE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'TAMBON')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'AMPUR')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'CHANGWAT')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'TELEPHONE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'LATITUDE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'LONGITUDE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'NFAMILY')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'LOCATYPE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'VHVID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HEADID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'TOILET')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'WATER')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'WATERTYPE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'GARBAGE')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'HOUSING')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'DURABILITY')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'CLEANLINESS')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'VENTILATION')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'LIGHT')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'WATERTM')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'MFOOD')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'BCONTROL')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ACONTROL')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'CHEMICAL')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'OUTDATE')->textInput() ?>

    <?= $form->field($model, 'D_UPDATE')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
