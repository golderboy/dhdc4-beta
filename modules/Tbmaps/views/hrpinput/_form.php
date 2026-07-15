<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\datecontrol\DateControl;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model modules\hrp\models\Hrpinput */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="hrpinput-form">

    <?php $form = ActiveForm::begin([
            'action' => ['update',
            'HOSPCODE' => base64_encode($model->HOSPCODE), 
            'PID' => base64_encode($model->PID), 
            'GRAVIDA' => base64_encode($model->GRAVIDA)
        ],
        //'method' => 'POST'
        ]); 
    ?>
<div class="row">
	<div class="col-md-12">
        <?= $form->field($model, 'RISK1')->textInput(['maxlength' => true]) ?>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
        <?= $form->field($model, 'RISK2')->textInput(['maxlength' => true]) ?>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
        <?= $form->field($model, 'RISK3')->textInput(['maxlength' => true]) ?>
	</div>
</div>
<div class="row">
	<div class="col-md-4">
        <?= $form->field($model, 'RISK')->widget(Select2::classname(), [
                        'data' => ['0' => 'ไม่ระบุ','1' => 'Risk1', '2' => 'Risk2','3'=>'Risk3'],
                        'options' => ['placeholder' => 'ระดับครรถ์เสี่ยง ...'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]); 
        ?>
	</div>
	<div class="col-md-4">
        <?= $form->field($model, 'PLAN')->widget(Select2::classname(), [
                    'data' => [ 'คลอดโรงพยาบาล',
                                'คลอดอนามัย',
                                'คลอดที่บ้าน',
                                'ไม่ระบุ',
                            ],
                    'options' => ['placeholder' => 'แผนการคลอด ...'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]);  
        ?>
	</div>


	<div class="col-md-4">
        <?= $form->field($model, 'OSM')->textInput(['maxlength' => true]) ?>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
        <?= $form->field($model, 'INFO')->textInput(['maxlength' => true]) ?>
	</div>
</div>


    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'บันทึก', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
