<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\dgis\models\HomeSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="home-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'HOSPCODE') ?>

    <?= $form->field($model, 'HID') ?>

    <?= $form->field($model, 'HOUSE_ID') ?>

    <?= $form->field($model, 'HOUSETYPE') ?>

    <?= $form->field($model, 'ROOMNO') ?>

    <?php // echo $form->field($model, 'CONDO') ?>

    <?php // echo $form->field($model, 'HOUSE') ?>

    <?php // echo $form->field($model, 'SOISUB') ?>

    <?php // echo $form->field($model, 'SOIMAIN') ?>

    <?php // echo $form->field($model, 'ROAD') ?>

    <?php // echo $form->field($model, 'VILLANAME') ?>

    <?php // echo $form->field($model, 'VILLAGE') ?>

    <?php // echo $form->field($model, 'TAMBON') ?>

    <?php // echo $form->field($model, 'AMPUR') ?>

    <?php // echo $form->field($model, 'CHANGWAT') ?>

    <?php // echo $form->field($model, 'TELEPHONE') ?>

    <?php // echo $form->field($model, 'LATITUDE') ?>

    <?php // echo $form->field($model, 'LONGITUDE') ?>

    <?php // echo $form->field($model, 'NFAMILY') ?>

    <?php // echo $form->field($model, 'LOCATYPE') ?>

    <?php // echo $form->field($model, 'VHVID') ?>

    <?php // echo $form->field($model, 'HEADID') ?>

    <?php // echo $form->field($model, 'TOILET') ?>

    <?php // echo $form->field($model, 'WATER') ?>

    <?php // echo $form->field($model, 'WATERTYPE') ?>

    <?php // echo $form->field($model, 'GARBAGE') ?>

    <?php // echo $form->field($model, 'HOUSING') ?>

    <?php // echo $form->field($model, 'DURABILITY') ?>

    <?php // echo $form->field($model, 'CLEANLINESS') ?>

    <?php // echo $form->field($model, 'VENTILATION') ?>

    <?php // echo $form->field($model, 'LIGHT') ?>

    <?php // echo $form->field($model, 'WATERTM') ?>

    <?php // echo $form->field($model, 'MFOOD') ?>

    <?php // echo $form->field($model, 'BCONTROL') ?>

    <?php // echo $form->field($model, 'ACONTROL') ?>

    <?php // echo $form->field($model, 'CHEMICAL') ?>

    <?php // echo $form->field($model, 'OUTDATE') ?>

    <?php // echo $form->field($model, 'D_UPDATE') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
