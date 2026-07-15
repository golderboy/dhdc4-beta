<?php

use yii\helpers\Html;
use miloschuman\highcharts\HighchartsAsset;
use kartik\grid\GridView;

HighchartsAsset::register($this)->withScripts([
    'highcharts-more',
    'themes/grid'
]);
$this->params['breadcrumbs'][] = ['label' => 'ข้อมูลประชากร', 'url' => ['index']];
$this->params['breadcrumbs'][] = "แบ่งตาม TYPEAREA";
?>

<div class="dhdc-page-header">
    <div>
        <h1 class="dhdc-page-title">Population TYPEAREA</h1>
        <div class="dhdc-page-subtitle">สรุปประชากรที่ยังมีชีวิตตาม TYPEAREA แยกหน่วยบริการ</div>
    </div>
    <div class="dhdc-toolbar">
        <?= Html::a('<i class="glyphicon glyphicon-chevron-left"></i> กลับ Population', ['index'], ['class' => 'btn btn-default']) ?>
    </div>
</div>

<div class="dhdc-panel dhdc-grid-shell">
<?php
echo GridView::widget([
    'dataProvider' => $dataProvider,
    'responsiveWrap' => false,
    'panel' => ['before' => 'ข้อมูลประชากรที่ยังมีชีวิตอยู่แบ่งตาม TYPEAREA'],
]);
?>
</div>
