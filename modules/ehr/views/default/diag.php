<?php

use kartik\grid\GridView;
use yii\helpers\Html;
?>

<div class="ehr-clinical-summary">
    <div class="ehr-clinical-item">
        <span class="ehr-clinical-label">วันที่รับบริการ</span>
        <strong><?= Html::encode($dateserv) ?></strong>
        <span><?= Html::encode($timeserv) ?></span>
    </div>
    <div class="ehr-clinical-item">
        <span class="ehr-clinical-label">อาการสำคัญ</span>
        <strong><?= Html::encode($cc) ?></strong>
    </div>
    <div class="ehr-clinical-item">
        <span class="ehr-clinical-label">สัญญาณชีพ</span>
        <strong>BP <?= Html::encode($sbp . ':' . $dbp) ?></strong>
        <span>T <?= Html::encode($btemp) ?>, P <?= Html::encode($pr) ?>, R <?= Html::encode($rr) ?></span>
    </div>
    <div class="ehr-clinical-item">
        <span class="ehr-clinical-label">สถานที่รับบริการ</span>
        <strong><?= Html::encode($hospcode . ' ' . $hospname) ?></strong>
    </div>
</div>

<div class="dhdc-grid-shell">
<?php
$gridColumns = [
    [
        'attribute' => 'diagcode',
        'label' => 'รหัสโรค'
    ],
    [
        'attribute' => 'diagename',
        'label' => 'ชื่อโรค'
    ],
    [
        'attribute' => 'diagtype',
        'label' => 'ประเภทวินิจฉัย'
    ],
];

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'autoXlFormat' => true,
    'export' => [
        'fontAwesome' => true,
        'showConfirmAlert' => false,
        'target' => GridView::TARGET_BLANK
    ],
    'columns' => $gridColumns,
    'resizableColumns' => true,
]);
?>
</div>
