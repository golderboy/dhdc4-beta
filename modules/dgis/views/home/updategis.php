<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\dgis\models\Home */

$this->title = 'แก้ไขพิกัดบ้าน: ' . $model->HOUSE.' '.$model->tambon;
$this->params['breadcrumbs'][] = ['label' => 'Homes', 'url' => ['index']];
//$this->params['breadcrumbs'][] = ['label' => $model->HOSPCODE, 'url' => ['view', 'HOSPCODE' => $model->HOSPCODE, 'HID' => $model->HID]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="home-update">
    <?= $this->render('_form_gis', [
        'model' => $model,
    ]) ?>

</div>
<?php
$js=<<<JS
if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function(loc){
    $('#home-latitude').val(loc.coords.latitude);
    $('#home-longitude').val(loc.coords.longitude);
    });
}
JS;
$this->registerJs($js);
?>
