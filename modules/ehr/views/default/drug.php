<?php

use kartik\grid\GridView;

$gridColumns = [
    ['class' => 'kartik\grid\SerialColumn'],
    [
        'attribute' => 'dname',
        'label' => 'รายการ',
        'pageSummary' => 'รวมทั้งหมด',
    ],
    [
        'attribute' => 'AMOUNT',
        'label' => 'จำนวน',
        'format' => ['decimal', 0],
        'hAlign' => 'right',
        'pageSummary' => true,
        'pageSummaryOptions' => ['id' => 'total_sum'],
    ],
];
?>

<div class="dhdc-grid-shell">
<?php
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
    'showPageSummary' => true,
]);
?>
</div>
