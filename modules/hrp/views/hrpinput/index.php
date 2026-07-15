<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel modules\hrp\models\HrpinputSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'ทะเบียนหญิงตั้งครรถ์';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hrpinput-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
<div class="well">
    <?= Html::a('แผนที่', ['/hrp/map/map'],['class' => 'btn btn-primary','target'=>'_blank']) ?>
    </div>
<?php Pjax::begin(); ?> 
<?php
$getDetailValue = function($model, $key) {
    $detail = $model->detail;
    if (is_array($detail) && array_key_exists($key, $detail)) {
        return $detail[$key];
    }
    return null;
};
?>

<?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'options' => [ 'style' => 'table-layout: fixed; width: 100%' ],
		'panel' => [ 'befor' => 'ทะเบียนหญิงตั้งครรถ์'],
		'responsiveWrap' => false,
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'columns' => [
            //['class' => 'yii\grid\SerialColumn'],
            [
                'class' => 'yii\grid\ActionColumn',
                'header'=>'Action',
                'template'=>'{update},{view}',
                'contentOptions'=>[
                    'noWrap' => true,
                    'style'=>'min-width: 110px;'
                  ],
                  'buttons'=>[
                    //'class' => 'btn btn-primary btn-block',  
                    'update' => function($url,$model,$key){
                        return Html::a('<button class = "btn btn-info"> 
                                        <i class="glyphicon glyphicon-import"></i>
                                        </button>',['update', 
                                            'HOSPCODE' => base64_encode($model->HOSPCODE), 
                                            'PID' => base64_encode($model->PID), 
                                            'GRAVIDA' => base64_encode($model->GRAVIDA)
                                    ]);
                      },
                    'view' => function($url,$model,$key){
                        return Html::a('<button class = "btn btn-success"> 
                                        <i class="glyphicon glyphicon-eye-open"></i>
                                        </button>',['view', 
                                            'HOSPCODE' => base64_encode($model->HOSPCODE), 
                                            'PID' => base64_encode($model->PID), 
                                            'GRAVIDA' => base64_encode($model->GRAVIDA)
                                    ]);
                      }
                    ]
            ],
            [
                'attribute' =>'HOSPCODE',
                'contentOptions'=>['style'=>'min-width: 100px;'],
            ],
            [
                'attribute' =>'PID',
                'filter' => FALSE,
                'contentOptions'=>['style'=>'min-width: 50px;'],
            ],
            ['attribute' => 'PID',
				'label' => 'ชื่อ-สกุล',
				//'filter' => FALSE,
				'contentOptions'=>['style'=>'min-width: 200px;max-width: 320px;'],
				'value' => function($model){return  $model->fullname ;},
			],
            [
                'attribute' => 'GRAVIDA',
                'contentOptions'=>['style'=>'min-width: 70px;'],
				'filter' => FALSE,
			],
            //'RISK1',
            //'RISK2',
            //'RISK3',
            [
                'attribute' => 'RISK',
                'contentOptions'=>['style'=>'min-width: 150px;'],
                'filter' => array(''=>'ไม่ระบุ','1' => 'Risk1', '2' => 'Risk2','3'=>'Risk3'),
                'value' => function($model){
                    switch ($model->RISK) {
                        case '1':   return "RISK1"; break;
                        case '2':   return "RISK2"; break;
                        case '3':   return "RISK3"; break;
                        default :   return "-";
                    }
                },
            ],
            ///
            [
                'label' => 'EDC',
                'contentOptions'=>['style'=>'min-width: 110px;'],
				'filter' => FALSE,
                'value' => function($model) use ($getDetailValue) { return $getDetailValue($model, 'EDC'); },
                'format' => 'date',
            ],
            [
                'label' => 'LMP',
                'filter' => FALSE,
                'contentOptions'=>['style'=>'min-width: 110px;'],
                'value' => function($model) use ($getDetailValue) { return $getDetailValue($model, 'LMP'); },
                'format' => 'date',
            ],
            [
                'attribute' => 'LABOR',
                'label' => 'LABOR',
                //'filter' => FALSE,
                
                'contentOptions'=>['style'=>'min-width: 80px;'],
                'value' => function($model) use ($getDetailValue) { 
                    $data = $getDetailValue($model, 'LABOR');
                    if ($data === null || $data === '') {
                        return "-";
                    }
                        if($data = "Y"){
                            return  "คลอดแล้ว";
                         }else{
                            return  "ยังไม่คลอด";
                         } ;
                 },
            ],
            [
                'label' => 'วันคลอด',
                'filter' => FALSE,
                'contentOptions'=>['style'=>'min-width: 110px;'],
                'value' => function($model) use ($getDetailValue) { return $getDetailValue($model, 'BDATE'); },
                'format' => 'date',
            ],
            [
                'label' => 'ANC<=12W',
                'contentOptions'=>['style'=>'min-width: 80px;'],
				'filter' => FALSE,
                'value' => function($model) use ($getDetailValue) { 
                    $data = $getDetailValue($model, 'ANC12W');
                    if ($data === null || $data === '') {
                        return "-";
                    }
                        if($data = "Y"){
                            return  "ครบ";
                         }else{
                            return  "ไม่ครบ";
                         } ;
                 },
            ],
            [
                'label' => 'ANC5ครั้ง',
                'filter' => FALSE,
                'contentOptions'=>['style'=>'min-width: 80px;'],
                'value' => function($model) use ($getDetailValue) { 
                    $data = $getDetailValue($model, 'ANC5');
                    if ($data === null || $data === '') {
                        return "-";
                    }
                        if($data = "Y"){
                            return  "ครบ";
                         }else{
                            return  "ไม่ครบ";
                         } ;
                },
            ],
            ///
            [
                'attribute' => 'PLAN',
                'contentOptions'=>['style'=>'min-width: 110px;'],
				'filter' => array(''=>'ไม่ระบุ','คลอดโรงพยาบาล','คลอดอนามัย','คลอดที่บ้าน','ไม่ระบุ'),
			],
            //'OSM',
            //'INFO',
            //'STATUS',
            [
                'attribute' => 'STATUS',
                'contentOptions'=>['style'=>'min-width: 110px;'],
                'filter' => array(''=>'ไม่ระบุ','Y' => 'อยู่ระหว่างตั้งครรถ์', 'N' => 'จำหน่าย'),
                'value' => function($model){
                    switch ($model->STATUS) {
                    case 'Y':   return "ตั้งครรถ์"; break;
                    case 'N':   return "จำหน่าย"; break;
                };},
			],

        ],
    ]); ?>

<?php Pjax::end(); ?>


</div>
