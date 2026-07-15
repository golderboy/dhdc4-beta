<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\select2\Select2;
use modules\student\models\Cschooltype;
use modules\student\models\ChospitalAmp;
use modules\student\models\Student;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Json;
/* @var $this yii\web\View */
/* @var $searchModel modules\student\models\SchoolSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

if(isset($_POST['BYEAR'])){$BYEAR = $_POST['BYEAR']+543; }else{ $BYEAR ="2560"; }
$this->title = 'ข้อมูลสถานศึกษา '.$BYEAR ;
$this->params['breadcrumbs'][] = ['label' => 'ระบบข้อมูลนักเรียน', 'url' => ['/student/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="school-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('นำเข้าข้อมูลสถานศึกษา', ['/student/excel/school'], ['class' => 'btn btn-success']) ?>
    </p>
    <div class="well">
<?php 
	$form = ActiveForm::begin([
				'method'=>'Post',
				'action'=>Url::to(['/student/school/index']),
			]);
?>
    <div class="row">
        <div class="col-sm-3">
            <?php
           // $list = yii\helpers\ArrayHelper::map(Unitcost::find()->select('BYEAR')->groupBy('BYEAR')->all(), 'BYEAR', 'BYEAR');
            echo yii\helpers\Html::dropDownList('BYEAR',$BYEAR, ['2018'=>'2561', '2017'=>'2560','2016'=>'2559'], 
                [
                    'prompt' => 'เลือกปีการศึกษา',
                    'class' => 'form-control'
                ]);
            ?>
        </div>
        <div class="col-sm-3">
            <button class="btn btn-danger">ตกลง</button>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'panel'=>['type'=>'primary', 'heading'=>'ข้อมูลสถานศึกษา','befor' => ''],
        'options' => [ 'style' => 'table-layout: fixed; width: 100%' ],
        'responsiveWrap' => FALSE,
        'toolbar' =>  [
            ['content' => 
                Html::a('<i class="glyphicon glyphicon-repeat"></i>', ['/student/school/index'],
                        ['data-pjax' => 0,
                         'class' => 'btn btn-default',
                          'title' => Yii::t('app', 'Reset Grid'),
                          ])
            ],
            '{export}',
            '{toggleData}',
        ],
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
            //'VID',
            //'SCHOOLCODE',
            'SCHOOLID',
            [
                'attribute' => 'SCHOOLNAME',
                'encodeLabel' => false,
                'format' => 'Html',
                'value' => function ($model) {
                    if(isset($_POST['BYEAR'])){$BYEAR = $_POST['BYEAR']+543; }else{ $BYEAR ="2560"; }
                    return Html::a($model->SCHOOLNAME,
                    ['/student/student/index',
                        'SCHOOLCODE'=>$model->SCHOOLCODE,
                        'HOSPCODE' => $model->HOSPCODE,
                        'EDUCATIONYEAR' => $BYEAR,									
                    ]);
                },
            ],
            [
                //'attribute' => 'SCHOOLNAME',
                'label' => 'จำนวนนักเรียน',
                'format' => 'Raw',
                'value' => function ($model) {
                    if(isset($_POST['BYEAR'])){$BYEAR = $_POST['BYEAR']+543; }else{ $BYEAR ="2560"; }
                    return Student::find()
                    ->Where(['SCHOOLCODE'=>$model->SCHOOLCODE,
                            'HOSPCODE' => $model->HOSPCODE,
                            'EDUCATIONYEAR' => $BYEAR
                            ])
                    ->groupBy('SCHOOLCODE,HOSPCODE,PID')
                    ->count();
                },
            ],
            // 'SCHOOLOWNER',
             //'SCHOOLTYPE',
             [
                'attribute' => 'SCHOOLTYPE',
                'contentOptions'=>['style'=>'min-width: 200px;max-width: 200px;'],
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'SCHOOLTYPE',
                    'data' => ArrayHelper::map(Cschooltype::find()
                                ->where('id_schooltype in (select SCHOOLTYPE from school)')
                                ->all(),'id_schooltype','schooltype'),
                    'theme' => Select2::THEME_BOOTSTRAP,
                    'options' => [
                        'placeholder' => 'ประเภท',
                    ]
                ]),
                'value' => function($model){
                    $data = Cschooltype::find()
                            ->WHERE(['id_schooltype'=>$model->SCHOOLTYPE]);
                    if($data->count() >0){        
                        return $model->cschooltype->schooltype;
                    }else{ return $model->SCHOOLTYPE;}
                    },
            ],
            // 'CLOSEDDATE',
            // 'D_UPDATE',

            //['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
