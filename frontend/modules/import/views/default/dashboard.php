<?php

use kartik\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Import Dashboard';
$this->params['breadcrumbs'][] = ['label' => 'รายการไฟล์ 43 แฟ้ม', 'url' => ['/import/upload/index']];
$this->params['breadcrumbs'][] = $this->title;

$isRunning = !empty($processStatus['is_running']) && $processStatus['is_running'] === 'true';
$processClass = $isRunning ? 'dhdc-status-running' : 'dhdc-status-ok';
$latestStatus = !empty($latestUpload['note2']) ? $latestUpload['note2'] : '-';
$latestStatusClass = $latestStatus === 'OK' ? 'dhdc-status-ok' : 'dhdc-status-pending';
if (strpos($latestStatus, 'ผิดพลาด') !== false || stripos($latestStatus, 'error') !== false) {
    $latestStatusClass = 'dhdc-status-error';
} elseif (strpos($latestStatus, 'กำลัง') !== false || strpos($latestStatus, 'เธเธณเธฅเธฑเธ') !== false) {
    $latestStatusClass = 'dhdc-status-running';
}
?>

<div class="import-dashboard">
    <div class="dhdc-page-header">
        <div>
            <h1 class="dhdc-page-title">Import Dashboard</h1>
            <div class="dhdc-page-subtitle">ภาพรวมการนำเข้า 43 แฟ้ม, Transform และ QC แบบอ่านอย่างเดียว</div>
        </div>
        <div class="dhdc-toolbar">
            <?= Html::a('รายการไฟล์', ['/import/upload/index'], ['class' => 'btn btn-default']) ?>
            <?= Html::a('ปริมาณข้อมูล', ['/import/count-file/index'], ['class' => 'btn btn-orange']) ?>
            <?= Html::a('QC', ['/qc/default/index'], ['class' => 'btn btn-blue']) ?>
            <?= Html::a('HDC Reports', ['/hdc/default/index'], ['class' => 'btn btn-green']) ?>
        </div>
    </div>

    <div class="dhdc-stat-grid">
        <div class="dhdc-stat-card" color="primary">
            <div class="dhdc-stat-label">Uploads</div>
            <div class="dhdc-stat-value"><?= number_format((int) $uploadSummary['total_uploads']) ?></div>
            <div class="dhdc-stat-note"><?= number_format((int) $uploadSummary['ok_uploads']) ?> completed</div>
        </div>
        <div class="dhdc-stat-card" color="secondary">
            <div class="dhdc-stat-label">Imported Files</div>
            <div class="dhdc-stat-value"><?= number_format((int) $countSummary['imported_files']) ?></div>
            <div class="dhdc-stat-note"><?= number_format((int) $countSummary['imported_records']) ?> records</div>
        </div>
        <div class="dhdc-stat-card" color="success">
            <div class="dhdc-stat-label">Process</div>
            <div class="dhdc-stat-value">
                <?= Html::tag('span', Html::encode($processStatus['is_running'] ?: '-'), ['class' => 'dhdc-status-pill ' . $processClass]) ?>
            </div>
            <div class="dhdc-stat-note"><?= Html::encode($processStatus['fnc_name'] ?: '-') ?></div>
        </div>
        <div class="dhdc-stat-card" color="success">
            <div class="dhdc-stat-label">Latest Upload</div>
            <div class="dhdc-stat-value">
                <?= Html::tag('span', Html::encode($latestStatus), ['class' => 'dhdc-status-pill ' . $latestStatusClass]) ?>
            </div>
            <div class="dhdc-stat-note"><?= Html::encode($latestUpload['file_name'] ?? '-') ?></div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="dhdc-panel">
                <h3 class="dhdc-page-title" style="font-size:18px">Workflow Status</h3>
                <table class="table table-striped">
                    <tbody>
                    <tr>
                        <th>Latest import</th>
                        <td><?= Html::encode($countSummary['latest_import_date'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <th>Last transform</th>
                        <td><?= Html::encode($processStatus['last_transform'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <th>Last QC</th>
                        <td><?= Html::encode($processStatus['last_err_check'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <th>Process time</th>
                        <td><?= Html::encode($processStatus['process_time'] ?: '-') ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-6">
            <div class="dhdc-panel">
                <h3 class="dhdc-page-title" style="font-size:18px">Latest Upload Detail</h3>
                <table class="table table-striped">
                    <tbody>
                    <tr>
                        <th>Hospcode</th>
                        <td><?= Html::encode($latestUpload['hospcode'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>File size</th>
                        <td><?= Html::encode($latestUpload['file_size'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <th>Upload date</th>
                        <td><?= Html::encode(($latestUpload['upload_date'] ?? '-') . ' ' . ($latestUpload['upload_time'] ?? '')) ?></td>
                    </tr>
                    <tr>
                        <th>Current note</th>
                        <td><?= Html::encode($latestUpload['note3'] ?? '-') ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="dhdc-panel">
                <h3 class="dhdc-page-title" style="font-size:18px">Top Imported Files</h3>
                <?=
                GridView::widget([
                    'dataProvider' => $fileCountProvider,
                    'responsiveWrap' => false,
                    'summary' => false,
                    'columns' => [
                        ['attribute' => 'FILE_NAME', 'label' => 'File'],
                        [
                            'attribute' => 'TOTAL_RECORD',
                            'label' => 'Records',
                            'format' => ['decimal', 0],
                            'contentOptions' => ['class' => 'text-right'],
                        ],
                        ['attribute' => 'IMPORT_DATE', 'label' => 'Import Date'],
                    ],
                ])
                ?>
            </div>
        </div>
        <div class="col-md-5">
            <div class="dhdc-panel">
                <h3 class="dhdc-page-title" style="font-size:18px">Lowest QC Scores</h3>
                <?=
                GridView::widget([
                    'dataProvider' => $qcProvider,
                    'responsiveWrap' => false,
                    'summary' => false,
                    'columns' => [
                        ['attribute' => 'file_name', 'label' => 'File'],
                        [
                            'attribute' => 'qc',
                            'label' => 'QC',
                            'format' => ['decimal', 2],
                            'contentOptions' => ['class' => 'text-right'],
                        ],
                    ],
                ])
                ?>
            </div>
        </div>
    </div>
</div>
