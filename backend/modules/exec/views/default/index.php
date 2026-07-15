<?php

use yii\helpers\Url;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Html;

$this->params['breadcrumbs'][] = 'การประมวลผล';

$isRunning = $sys_process !== 'end';
$processBadgeClass = $isRunning ? 'dhdc-status-running' : 'dhdc-status-ok';
?>
<div class="dhdc-page-header">
    <div>
        <h1 class="dhdc-page-title">Process Dashboard</h1>
        <div class="dhdc-page-subtitle">ควบคุม Transform, Data QC และติดตาม process จากฐานข้อมูลจริง</div>
    </div>
    <div class="dhdc-toolbar">
        <button class="btn btn-brown btn-transform"><i class="glyphicon glyphicon-flash"></i> 1-TransForm</button>
        <button class="btn btn-deep-purple btn-qc"><i class="glyphicon glyphicon-flash"></i> 2-Data QC</button>
        <button class="btn btn-deep-orange btn-truncate"><i class="glyphicon glyphicon-trash"></i> 3-Truncate</button>
    </div>
</div>

<div class="dhdc-stat-grid">
    <div class="dhdc-stat-card" color="info">
        <div class="dhdc-stat-label">System Process</div>
        <div class="dhdc-stat-value">
            <?= Html::tag('span', Html::encode($sys_process), ['class' => 'dhdc-status-pill ' . $processBadgeClass]) ?>
        </div>
        <div class="dhdc-stat-note">สถานะล่าสุดจาก sys_check_process</div>
    </div>
    <div class="dhdc-stat-card" color="primary">
        <div class="dhdc-stat-label">Transform Process</div>
        <div class="dhdc-stat-value">
            <?= Html::a(Html::encode($current_process), ['check-process', 'p' => $current_process], ['target' => '_blank']) ?>
        </div>
        <div class="dhdc-stat-note">process ล่าสุดใน hdc_log</div>
    </div>
    <div class="dhdc-stat-card" color="inherit">
        <div class="dhdc-stat-label">Updated</div>
        <div class="dhdc-stat-value"><?= Html::encode($time_process ?: '-') ?></div>
        <div class="dhdc-stat-note">เวลาจาก sys_check_process</div>
    </div>
    <div class="dhdc-stat-card" color="secondary">
        <div class="dhdc-stat-label">Auto Refresh</div>
        <div class="dhdc-stat-value">5s</div>
        <div class="dhdc-stat-note">รีเฟรช process list อัตโนมัติ</div>
    </div>
</div>

<?php Pjax::begin(); ?>
<?= Html::a('Refresh', ['index'], ['class' => 'refresh', 'style' => 'display:none']) ?>

<div class="dhdc-panel">
    <?=
    GridView::widget([
        'responsiveWrap' => false,
        'dataProvider' => $dataProvider,
    ]);
    ?>
</div>

<div class="alert <?= $isRunning ? 'alert-info' : 'alert-success' ?>">
    <p>Transform Process : <?= Html::a(Html::encode($current_process), ['check-process', 'p' => $current_process], ['target' => '_blank']) ?></p>
    <p>System Process : <span style="color: orangered"><?= Html::encode($sys_process) ?></span></p>
    <p>Start Time: <?= Html::encode($time_process) ?></p>
</div>
<?php Pjax::end(); ?>

<?php
$route_transform_exec = Url::to(['/exec/transform/exec']);
$route_qc_exec = Url::to(['/exec/qc/exec']);
$route_truncate_exec = Url::to(['/exec/qc/truncate']);
$js = <<<JS
   $(document).ready(function() {
        setInterval(function(){ $('.refresh').click(); }, 5000);
    });

    $('.btn-transform').click(function(){
        yii.confirm('Run Transform now?',function(){
            $('.btn-transform').toggle();
            $.ajax({
                url: '$route_transform_exec',
                success: function(data) {
                    $('.btn-transform').toggle();
                    alert(data);
                }
            });
        });
    });

    $('.btn-qc').click(function(){
        yii.confirm('Run Data QC now?',function(){
            $('.btn-qc').toggle();
            $.ajax({
                url: '$route_qc_exec',
                success: function(data) {
                    $('.btn-qc').toggle();
                    alert(data);
                }
            });
        });
    });

    $('.btn-truncate').click(function(){
        yii.confirm('ล้างข้อมูลทั้งหมด?',function(){
            $('.btn-truncate').toggle();
            $.ajax({
                url: '$route_truncate_exec',
                success: function(data) {
                    $('.btn-truncate').toggle();
                    alert(data);
                }
            });
        });
    });
JS;
$this->registerJs($js);
