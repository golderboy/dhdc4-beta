<?php
use yii\helpers\Html;
//use backend\models\Sysconfigmain;
use miloschuman\highcharts\HighchartsAsset;
use modules\pyramid\models\ChospitalAmp;
use modules\pyramid\models\Sysconfigmain;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

$this->params['breadcrumbs'][] = ['label' => 'ข้อมูลพื้นฐาน', 'url' => ['/pyramid/default/index']];
$this->params['breadcrumbs'][] = 'ประชากร';

HighchartsAsset::register($this)->withScripts([
    'highcharts-more',
    'themes/grid'
]);

$bdg = Sysconfigmain::find()->one();
?>

<div class="well">
<?php 
	$form = ActiveForm::begin([
				'method'=>'Post',
				'action'=>Url::to(['/pyramid/default/pyramid']),
			]);
?>
    <div class="row">
        <div class="col-sm-3">
            <?php
            $list = yii\helpers\ArrayHelper::map(ChospitalAmp::find()->all(), 'hoscode', 'hosname');
            echo yii\helpers\Html::dropDownList('hospcode',$hospcode, $list, [
                'prompt' => 'เลือกสถานบริการ',
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


<?php
if(count($rawData) < 21){
    echo "<div class='alert alert-info'>ไม่มีข้อมูล</div>";
    return;
}
use miloschuman\highcharts\Highcharts;

$male = Json_encode([
    $rawData[0]['male'] * (-1), $rawData[1]['male'] * (-1), $rawData[2]['male'] * (-1)
    , $rawData[3]['male'] * (-1), $rawData[4]['male'] * (-1), $rawData[5]['male'] * (-1)
    , $rawData[6]['male'] * (-1), $rawData[7]['male'] * (-1), $rawData[8]['male'] * (-1)
    , $rawData[9]['male'] * (-1), $rawData[10]['male'] * (-1), $rawData[11]['male'] * (-1)
    , $rawData[12]['male'] * (-1), $rawData[13]['male'] * (-1), $rawData[14]['male'] * (-1)
    , $rawData[15]['male'] * (-1), $rawData[16]['male'] * (-1), $rawData[17]['male'] * (-1)
    , $rawData[18]['male'] * (-1), $rawData[19]['male'] * (-1), $rawData[20]['male'] * (-1)
]);
$js_male = implode(',',Json_decode($male));

$female = Json_encode([
    $rawData[0]['female']*1, $rawData[1]['female']*1, $rawData[2]['female']*1
    , $rawData[3]['female']*1, $rawData[4]['female']*1, $rawData[5]['female']*1
    , $rawData[6]['female']*1, $rawData[7]['female']*1, $rawData[8]['female']*1
    , $rawData[9]['female']*1, $rawData[10]['female']*1, $rawData[11]['female']*1
    , $rawData[12]['female']*1, $rawData[13]['female']*1, $rawData[14]['female']*1
    , $rawData[15]['female']*1, $rawData[16]['female']*1, $rawData[17]['female']*1
    , $rawData[18]['female']*1, $rawData[19]['female']*1, $rawData[20]['female']*1
]);

$js_female = implode(',',Json_decode($female));


//คำนวณค่า max , min 
$max_female = max(Json_decode($female));
$max_male = abs(min(Json_decode($male)));
$max = $max_female > $max_male ? $max_female : $max_male;
?>

<div id="container"></div>
<?php
$js=<<<JS
var categories = ['0-4', '5-9', '10-14', '15-19',
                    '20-24', '25-29', '30-34', '35-39', '40-44',
                    '45-49', '50-54', '55-59', '60-64', '65-69',
                    '70-74', '75-79', '80-84', '85-89', '90-94',
                    '95-99', '100 + '];
$(document).ready(function () {
    Highcharts.chart('container', {
        chart: {
            type: 'bar',
            plotBackgroundImage: '/frontend/web/images/bg_pop.png',
            height:520
        },
        title: {
            text: 'ปิรามิดประชากร $hosname'
        },
        subtitle: {
            text: ' คำนวณอายุจากปีเกิด ณ วันที่ $bdg->note2 จากแฟ้ม person'
        },
        xAxis: [{
            categories: categories,
            reversed: false,
            labels: {
                step: 1
            }
        }, { // mirror axis on right side
            opposite: true,
            reversed: false,
            categories: categories,
            linkedTo: 0,
            labels: {
                step: 1
            }
        }],
        yAxis: {
            title: {
                text: null
            },
            labels: {
                formatter: function () {
                    return Math.abs(this.value) + '%';
                }
            }
        },

        plotOptions: {
            series: {
                stacking: 'normal',
                //colorByPoint: true,
                pointPadding: 0,
                groupPadding: 0,
                borderWidth: 0,
                //shadow: true
                borderColor: '#FFFFFF',
                borderWidth: 3,
            }
        },

        tooltip: {
                formatter: function () {
                    return '<b>' + this.series.name + ', อายุ ' + this.point.category + '</b><br/>' +
                        'ประชากร: ' + Highcharts.numberFormat(Math.abs(this.point.y), 0);
                }
            },

        series: [{
            name: 'ชาย',
            data: $male
        }, {
            name: 'หญิง',
            data: $female
        }]
    });
});
JS;
$this->registerJs($js);
?>

<?php
if (isset($dataProvider))
    $dev = \yii\helpers\Html::a('คุณอุเทน จาดยางโทน', 'https://fb.com/tehnnn', ['target' => '_blank']);
    $dev2 = Html::a('คุณศรศักดิ์ สีหะวงษ์', 'https://fb.com/sosplk', ['target' => '_blank']);
    $dev3 = Html::a('คุณประเทือง สุภายูรณ์', 'https://fb.com/red9love', ['target' => '_blank']);

//echo yii\grid\GridView::widget([
echo \kartik\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'responsive' => TRUE,
    'hover' => true,
    'floatHeader' => true,
    'panel' => [
        'before' => '',
        'type' => \kartik\grid\GridView::TYPE_SUCCESS,
        'after' => 'โดย ' . $dev ." & ".$dev2." & ".$dev3
    ],
    'columns' => [

        [
            'attribute' => 'age',
            'label' => 'ช่วงอายุ (ปี)'
        ],
        [
            'attribute' => 'male',
            'label' => 'เพศชาย (คน)'
        ],
        [
            'attribute' => 'female',
            'label' => 'เพศหญิง (คน)'
        ],
        [
            'class' => '\kartik\grid\FormulaColumn',
            'label' => 'รวม (คน)',
            'value' => function ($model, $key, $index, $widget) {
                $p = compact('model', 'key', 'index');
                // เขียนสูตร

                return $widget->col(1, $p) + $widget->col(2, $p);
            }
        ]
    ]
]);
?>





