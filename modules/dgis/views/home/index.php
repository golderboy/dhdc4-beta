<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\dgis\models\HomeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Mobie_GIS ';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="home-index">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
		'responsiveWrap' => false,
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'columns' => [
		[
			'attribute' => 'HOSPCODE',
			'contentOptions'=>['style'=>'min-width: 120px;']
		],
		[
			'attribute' => 'HOUSE',
			'contentOptions'=>['style'=>'min-width: 120px;']
		],
        [
			'attribute' => 'HID',
			'label' => 'ชื่อหมู่บ้าน',
			'filter' => FALSE,
			'value' => function($model){return  $model->tambon;},
		],
        //  'LATITUDE',
        //  'LONGITUDE',
        [
			'label' => '<center><span class="glyphicon glyphicon-search"></center>' ,
			'encodeLabel' => false,
			'format'=>'Html',
			//'contentOptions'=>['style'=>'min-width: 100px;'],
			'value' => function($model){
					return Html::a('<span class="glyphicon glyphicon-edit"> บันทึกพิกัด</span>',
						['/dgis/home/updategis',
							'HOSPCODE'=>$model->HOSPCODE,
							'HID'=>$model->HID,								
						],
						['class'=>'btn btn-info']
					);
				
				},
		],
        [
			'label' => '<center><span class="glyphicon glyphicon-edit"></center>' ,
			'encodeLabel' => false,
			'format'=>'Html',
			//'contentOptions'=>['style'=>'min-width: 100px;'],
			'value' => function($model){
					return Html::a('<span class="glyphicon glyphicon-edit"> คัดกรอง</span>',
						['#',
							'HOSPCODE'=>$model->HOSPCODE,
							'HID'=>$model->HID,								
						],
						['class'=>'btn btn-primary']
					);
				
				},
		],
        ],
    ]); ?>
</div>
