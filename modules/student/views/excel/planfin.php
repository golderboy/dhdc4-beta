<?php

use yii\widgets\ActiveForm;
use kartik\grid\GridView;
use frontend\modules\cfo\models\LogImport;

$this->title = "นำเข้า EXCEL Planfin ของประเทศ";
$this->params['breadcrumbs'][] = ['label' => 'CFO', 'url' => ['/cfo/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin() ?>

<?= $form->field($mUpload, 'dataFile')->fileInput() ?>

<button>นำเข้า</button>

<?php ActiveForm::end() ?>
<hr>
<p>รายการนำเข้าทั้งหมด</p>
<?php
$model = LogImport::find();
$dataProvider = new yii\data\ActiveDataProvider([
    'query'=>$model
]);
echo GridView::widget([
    'dataProvider'=>$dataProvider,
	'responsiveWrap' => false,
	'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
		'file_name',
		'records',
		'created_by',
		'created_at',
	]
	
]);
?>