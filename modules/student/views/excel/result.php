<?php

use yii\widgets\ActiveForm;
use kartik\grid\GridView;
use frontend\modules\cfo\models\LogImport;
use kartik\select2\Select2;

$this->title = "นำเข้า EXCEL";
$this->params['breadcrumbs'][] = ['label' => 'CFO', 'url' => ['/cfo/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin() ?>
<div class="row">
	<div class="col col-md-6">
		<?php 
		echo '<label class="control-label">เลือกไฟล์</label>';
		echo $form->field($mUpload, 'dataFile')->fileInput()->label(false); 
		?>
	</div>
</div>
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