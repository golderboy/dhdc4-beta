<?php
use yii\helpers\Html;
//use backend\models\Sysconfigmain;
use miloschuman\highcharts\HighchartsAsset;
use modules\pyramid\models\ChospitalAmp;
use modules\pyramid\models\Sysconfigmain;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\Json;
$this->params['breadcrumbs'][] = ['label' => 'ข้อมูลพื้นฐาน', 'url' => ['/pyramid/default/index']];
$this->params['breadcrumbs'][] ='ประเภทการอยู่อาศัย';
use miloschuman\highcharts\Highcharts;
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
				'action'=>Url::to(['/pyramid/default/checktype']),
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

<div id="container"></div>
<?php
$raw = $dataProvider->getModels();

$data = [];

foreach($raw as $value){
$data[]=['name'=> 'TYPE1', 'y'=> (float)number_format(($value['type1']*100)/($value['total']*1),2)];
$data[]=['name'=> 'TYPE2', 'y'=> (float)number_format(($value['type2']*100)/($value['total']*1),2)];
$data[]=['name'=> 'TYPE3', 'y'=> (float)number_format(($value['type3']*100)/($value['total']*1),2)];
$data[]=['name'=> 'TYPE4', 'y'=> (float)number_format(($value['type4']*100)/($value['total']*1),2)];
$data[]=['name'=> 'TYPE5', 'y'=> (float)number_format(($value['type5']*100)/($value['total']*1),2)];
$data[]=['name'=> 'NULL', 'y'=> (float)number_format(($value['nottype']*100)/($value['total']*1),2)];

}

$json =  str_replace('"',"'",Json::encode($data));
//echo ($json);

?>
<?php
$js=<<<JS
// Radialize the colors
Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
    return {
        radialGradient: {
            cx: 0.5,
            cy: 0.3,
            r: 0.7
        },
        stops: [
            [0, color],
            [1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
        ]
    };
});

// Build the chart
Highcharts.chart('container', {
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        type: 'pie'
    },
    title: {
        text: 'ประเภทการอยู่อาศัยประชากร'
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
                enabled: true,
                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                style: {
                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                },
                connectorColor: 'silver'
            }
        }
    },
    series: [{
        name: 'ประเภท',
        data:  $json 
    }]
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
       'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'hover' => true,
        'floatHeader' => true,
        'panel' => [
            'before' => '',
            'type' => \kartik\grid\GridView::TYPE_SUCCESS,
            'after' => 'โดย ' . $dev ." & ".$dev2." & ".$dev3
        ],
    ]);
?>





