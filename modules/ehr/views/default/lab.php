<?php

use kartik\grid\GridView;

$gridColumns = [
    [
        'attribute' => 'labtest',
        'label' => 'รหัส'
    ],
    [
        'attribute' => 'tlname',
        'label' => 'รายการ'
    ],
    [
        'attribute' => 'labresult',
        'label' => 'Result'
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
]);
?>
</div>
