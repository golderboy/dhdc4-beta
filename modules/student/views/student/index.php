<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use modules\student\models\School;
use modules\student\models\Student;
use modules\student\models\Sclass;
use modules\student\models\Person;
use modules\student\models\ChospitalAmp;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Json;

$this->title = 'ข้อมูลนักเรียน';
$this->params['breadcrumbs'][] = ['label' => 'ระบบข้อมูลนักเรียน', 'url' => ['/student/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="student-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('นำเข้าข้อมูลนักเรียน', ['/student/excel/student'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'panel'=>['type'=>'primary', 'heading'=>'ข้อมูลนักเรียน','befor' => ''],
        'options' => [ 'style' => 'table-layout: fixed; width: 100%' ],
        'responsiveWrap' => FALSE,
        'toolbar' =>  [
            ['content' => 
                Html::a('<i class="glyphicon glyphicon-repeat"></i>', ['/student/student/index'],
                        ['data-pjax' => 0,
                         'class' => 'btn btn-default',
                          'title' => Yii::t('app', 'Reset Grid'),
                          ])
            ],
            '{export}',
            '{toggleData}',
        ],
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'HOSPCODE',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'HOSPCODE',
                    'data' => ArrayHelper::map(ChospitalAmp::find()->all(),'hoscode','hosname'),
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'options' => [
                        'placeholder' => 'สถานบริการ',
                    ]
                ]),
                'contentOptions'=>['style'=>'min-width: 80px;max-width: 80px;'],  
            ],
            [
                'attribute' => 'PID',
                'filter' => FALSE,
                'contentOptions'=>['style'=>'min-width: 150px;max-width: 150px;'],  
            ],
            [
                'label' => 'ชื่อนักเรียน',
                'attribute' => 'PID',
                'contentOptions'=>['style'=>'min-width: 200px;max-width: 200px;'],
                'filter' => TRUE,
                'value' => function($model){
                    $data = Person::find()
                            ->WHERE(['HOSPCODE'=>$model->HOSPCODE ,'PID'=>$model->PID]);
                    if($data->count() >0){        
                        return $model->personname->NAME." ".$model->personname->LNAME;
                    }else{ return '';}
                    },
            ],
            //'SCHOOLCODE',
            [
                'attribute' => 'SCHOOLCODE',
                'label' => 'ชื่อสถานศึกษา',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'SCHOOLCODE',
                    'data' => ArrayHelper::map(School::find()
                                ->where('SCHOOLCODE in (select SCHOOLCODE from student)')
                                ->all(),'SCHOOLCODE','SCHOOLNAME'),
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'options' => [
                        'placeholder' => 'สถานศึกษา',
                    ]
                ]),
                'contentOptions'=>['style'=>'min-width: 200px;max-width: 200px;'],
                'value' => function($model){
                    $data = School::find()
                    ->WHERE(['HOSPCODE'=>$model->HOSPCODE ,'SCHOOLCODE'=>$model->SCHOOLCODE]);
                    
                    //$count = $data->count();
                    if($data->count() >0){
                            return $model->sschool->SCHOOLNAME;
                        }else{
                            return  $model->SCHOOLCODE;
                        };
                    },
            ],
            [
                'attribute' => 'EDUCATIONYEAR',
                'contentOptions'=>['style'=>'min-width: 100px;max-width: 100px;'],
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'EDUCATIONYEAR',
                    'data' => ArrayHelper::map(Student::find()->groupBy('EDUCATIONYEAR')->all(),'EDUCATIONYEAR','EDUCATIONYEAR'),
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'options' => [
                        'placeholder' => 'ปีการศึกษา',
                    ]
                ]),
            ],
            [
                'attribute' => 'CLASS',
                'contentOptions'=>['style'=>'min-width: 100px;max-width: 100px;'],
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'CLASS',
                    'data' => ArrayHelper::map(Sclass::find()->groupBy('class_name')->all(),'class','class_name'),
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'options' => [
                        'placeholder' => 'ชั้นเรียน',
                    ]
            ]),
            //'contentOptions'=>['style'=>'min-width: 200px;max-width: 320px;'],
            'value' => function($model){return  $model->sclass->class_name ;},
            ],
            // 'D_UPDATE',
            // 'GRUDATE_DATE',
            // 'id',

           // ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
