<?php

use yii\helpers\Html;
use yii\widgets\LinkPager;
use miloschuman\highcharts\HighchartsAsset;
use frontend\modules\qc\assets\QcAsset;

QcAsset::register($this);
HighchartsAsset::register($this)->withScripts(['highcharts-more', 'modules/solid-gauge', 'modules/accessibility']);
$this->registerJsFile('@web/js/chart-donut.js', ['depends' => [\yii\web\JqueryAsset::className()]]);

$this->title = 'Data QC';
$this->params['breadcrumbs'][] = $this->title;

$summary = \Yii::$app->db->createCommand("
    SELECT
        COUNT(*) AS total_files,
        SUM(CASE WHEN qc >= 95 THEN 1 ELSE 0 END) AS good_files,
        SUM(CASE WHEN qc < 80 THEN 1 ELSE 0 END) AS risk_files,
        ROUND(AVG(qc), 2) AS avg_qc
    FROM sys_files
    WHERE note1 = 'y'
")->queryOne();

$lastErrCheck = \Yii::$app->db->createCommand("SELECT last_time FROM last_err_check LIMIT 1")->queryScalar();
?>

<div class="qc-default-index">
    <div class="dhdc-page-header qc-page-header">
        <div>
            <h1 class="dhdc-page-title">Data QC</h1>
            <div class="dhdc-page-subtitle">ติดตามคุณภาพข้อมูลรายแฟ้มจากผล QC ล่าสุด</div>
        </div>
        <div class="dhdc-toolbar qc-page-actions">
            <?= Html::a('<span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span> รายหน่วย', ['/qc/default/hos-sum-error'], ['class' => 'btn btn-small btn-blue']) ?>
            <?= Html::a('<span class="glyphicon glyphicon-dashboard" aria-hidden="true"></span> Import Dashboard', ['/import/default/dashboard'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>

    <div class="dhdc-stat-grid qc-summary-grid">
        <div class="dhdc-stat-card qc-summary-card" color="info">
            <span class="glyphicon glyphicon-folder-open qc-summary-icon" aria-hidden="true"></span>
            <div class="dhdc-stat-label">Files</div>
            <div class="dhdc-stat-value"><?= number_format((int) $summary['total_files']) ?></div>
            <div class="dhdc-stat-note">แฟ้มที่ตรวจ QC</div>
        </div>
        <div class="dhdc-stat-card qc-summary-card" color="primary">
            <span class="glyphicon glyphicon-stats qc-summary-icon" aria-hidden="true"></span>
            <div class="dhdc-stat-label">Average QC</div>
            <div class="dhdc-stat-value"><?= number_format((float) $summary['avg_qc'], 2) ?></div>
            <div class="dhdc-stat-note">คะแนนเฉลี่ย</div>
        </div>
        <div class="dhdc-stat-card qc-summary-card" color="success">
            <span class="glyphicon glyphicon-ok qc-summary-icon" aria-hidden="true"></span>
            <div class="dhdc-stat-label">Good</div>
            <div class="dhdc-stat-value"><?= number_format((int) $summary['good_files']) ?></div>
            <div class="dhdc-stat-note">QC ตั้งแต่ 95</div>
        </div>
        <div class="dhdc-stat-card qc-summary-card" color="error">
            <span class="glyphicon glyphicon-warning-sign qc-summary-icon" aria-hidden="true"></span>
            <div class="dhdc-stat-label">Risk</div>
            <div class="dhdc-stat-value"><?= number_format((int) $summary['risk_files']) ?></div>
            <div class="dhdc-stat-note">QC ต่ำกว่า 80</div>
        </div>
    </div>

    <section class="dhdc-panel qc-workspace" aria-labelledby="qc-by-file-title">
        <div class="qc-workspace-header">
            <div>
                <h2 id="qc-by-file-title" class="dhdc-page-title qc-workspace-title">QC by File</h2>
                <div class="dhdc-page-subtitle">ประมวลผลล่าสุด: <?= Html::encode($lastErrCheck ?: '-') ?></div>
            </div>
        </div>

        <div class="qc-chart-grid">
            <?php foreach ($models as $model) : ?>
                <?php
                $f = strtoupper($model->file_name);
                $q = $model->qc;
                $this->registerJs("
                    var obj_div=$('#$f');
                    gen_donut(obj_div,'',$q);
                ");
                ?>
                <?= Html::a(
                    '<span class="qc-chart-heading"><span>' . Html::encode($f) . '</span><span class="glyphicon glyphicon-chevron-right qc-chart-action" aria-hidden="true"></span></span>'
                    . '<span id="' . Html::encode($f) . '" class="qc-chart" aria-hidden="true"></span>'
                    . '<span class="sr-only">QC ' . Html::encode($q) . '</span>',
                    ['data-error', 'filename' => $f],
                    [
                        'class' => 'qc-chart-card',
                        'aria-label' => $f . ' QC ' . $q,
                        'title' => $f . ' QC ' . $q,
                    ]
                ) ?>
            <?php endforeach; ?>

            <?php if (empty($models)) : ?>
                <div class="dhdc-empty-state qc-empty-state" role="status">ไม่พบข้อมูลผลตรวจ QC</div>
            <?php endif; ?>
        </div>

        <nav class="qc-pagination" aria-label="QC pagination">
            <?=
            LinkPager::widget([
                'pagination' => $pages,
            ]);
            ?>
        </nav>
    </section>
</div>
