<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
//use kartik\detail\DetailView;
/* @var $this yii\web\View */
/* @var $model modules\hrp\models\Hrpinput */

$this->title = $model->fullname;
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนหญิงตั้งครรถ์', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hrpinput-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('บันทึกข้อมูล', ['update', 
                                    'HOSPCODE' => base64_encode($model->HOSPCODE), 
                                    'PID' => base64_encode($model->PID), 
                                    'GRAVIDA' => base64_encode($model->GRAVIDA)
                                ],['class' => 'btn btn-primary']) ?>

    <?= DetailView::widget([
        'model' => $model,
        'formatter' => [
            'class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'
            ],
        'attributes' => [
            [
                'attribute' => 'HOSPCODE',
				'filter' => FALSE,
                'value' => function($model){ return  $model->hospital->hosname; },
            ],
            'PID',
            [   'attribute' => 'fullname',
                'label' => 'ชื่อ-นามสกุล',
				'filter' => TRUE,
                'value' => function($model){ return  $model->fullname; },
            ],
            'GRAVIDA',
            [
                'label' => 'EDC',
				'filter' => FALSE,
                'value' => function($model){ return  $model->detail['EDC']; },
                'format' => 'date',
            ],
            [
                'label' => 'LMP',
				'filter' => FALSE,
                'value' => function($model){ return  $model->detail['LMP']; },
                'format' => 'date',
            ],
            [
                'label' => 'สถานะการคลอด',
				'filter' => FALSE,
                'value' => function($model){ 
                    $data = $model->detail['LABOR'];
                        if($data = "Y"){
                            return  "คลอดแล้ว";
                         }else{
                            return  "ยังไม่คลอด";
                         } ;
                 },
            ],
            [
                'label' => 'วันที่คลอด',
				'filter' => FALSE,
                'value' => function($model){ return  $model->detail['BDATE']; },
                'format' => 'date',
            ],
            [
                'label' => 'สถานที่คลอด',
				'filter' => FALSE,
                'value' => function($model){ return  $model->bplece; },
            ],
            [
                'label' => 'ฝากครรถ์ครั้งแรกน้อยกว่า 12 W',
				'filter' => FALSE,
                'value' => function($model){ 
                    $data = $model->detail['ANC12W'];
                        if($data = "Y"){
                            return  "ครบ";
                         }else{
                            return  "ไม่ครบ";
                         } ;
                 },
            ],
            [
                'label' => 'ฝากครรถ์ครบ 5 ครั้ง',
				'filter' => FALSE,
                'value' => function($model){ 
                    $data = $model->detail['ANC5'];
                        if($data = "Y"){
                            return  "ครบ";
                         }else{
                            return  "ไม่ครบ";
                         } ;
                },
            ],             
            'RISK1',
            'RISK2',
            'RISK3',
            [
                'attribute' => 'RISK',
                'value' => function($model){ 
                    switch ($model->RISK) {
                        case '1':   return "RISK1"; break;
                        case '2':   return "RISK2"; break;
                        case '3':   return "RISK3"; break;
                        default :   return "-";
                    }
                 },
            ],
            'PLAN',
            'OSM',
            'INFO',
            [
				'attribute' => 'STATUS',
				'filter' => FALSE,
				'value' => function($model){ 
                    if($model->STATUS = "Y"){
                        return  "รักษาอยู่";
                     }else{
                        return  "จำหน่าย";
                     } ;
                    },
            ],
            [
                'label' => 'ที่อยู่',
				'filter' => FALSE,
				'value' => function($model){ return  $model->addrs; },
			],
        ],
    ]) ?>

</div>
