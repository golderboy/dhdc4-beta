<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model frontend\models\UploadFortythree */

$this->title = $model->file_name;
$this->params['breadcrumbs'][] = ['label' => 'รายการไฟล์ 43 แฟ้ม', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$isPending = $model->note2 === 'รอนำเข้า' || $model->note2 === 'เธฃเธญเธเธณเน€เธเนเธฒ';
$isRunning = strpos((string) $model->note2, 'กำลัง') !== false || strpos((string) $model->note2, 'เธเธณเธฅเธฑเธ') !== false;
$statusClass = $model->note2 === 'OK' ? 'dhdc-status-ok' : 'dhdc-status-pending';
if ($isRunning) {
    $statusClass = 'dhdc-status-running';
} elseif (strpos((string) $model->note2, 'ผิดพลาด') !== false || stripos((string) $model->note2, 'error') !== false) {
    $statusClass = 'dhdc-status-error';
}
?>
<div class="upload-fortythree-view">

    <div class="dhdc-page-header">
        <div>
            <h1 class="dhdc-page-title"><?= Html::encode($model->file_name) ?></h1>
            <div class="dhdc-page-subtitle">
                สถานะปัจจุบัน:
                <?= Html::tag('span', Html::encode($model->note2 ?: '-'), ['class' => 'dhdc-status-pill ' . $statusClass]) ?>
            </div>
        </div>
        <div class="dhdc-toolbar">
            <?= Html::a('<span class="glyphicon glyphicon-upload"></span> Upload', ['create'], ['class' => 'btn btn-success']) ?>
            <?= Html::a('กลับรายการไฟล์', ['index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>

    <div class="dhdc-stat-grid">
        <div class="dhdc-stat-card" color="inherit">
            <div class="dhdc-stat-label">Hospcode</div>
            <div class="dhdc-stat-value"><?= Html::encode($model->hospcode ?: '-') ?></div>
            <div class="dhdc-stat-note">หน่วยบริการจากชื่อไฟล์</div>
        </div>
        <div class="dhdc-stat-card" color="info">
            <div class="dhdc-stat-label">File Size</div>
            <div class="dhdc-stat-value"><?= Html::encode($model->file_size ?: '-') ?></div>
            <div class="dhdc-stat-note">MB</div>
        </div>
        <div class="dhdc-stat-card" color="primary">
            <div class="dhdc-stat-label">Upload Date</div>
            <div class="dhdc-stat-value"><?= Html::encode($model->upload_date ?: '-') ?></div>
            <div class="dhdc-stat-note"><?= Html::encode($model->upload_time ?: '') ?></div>
        </div>
        <div class="dhdc-stat-card" color="secondary">
            <div class="dhdc-stat-label">Current File</div>
            <div class="dhdc-stat-value"><?= Html::encode($model->note3 ?: '-') ?></div>
            <div class="dhdc-stat-note">ไฟล์ย่อยล่าสุดใน workflow</div>
        </div>
    </div>

    <div class="dhdc-panel">
        <?=
        DetailView::widget([
            'model' => $model,
            'formatter' => [
                'class' => 'yii\i18n\Formatter',
                'nullDisplay' => '-',
            ],
            'attributes' => [
                'file_name',
                'file_size',
                'upload_date',
                'upload_time',
                'note1',
            ],
        ])
        ?>
    </div>

    <?php if ($isPending): ?>
        <div class="dhdc-panel">
            <button class="btn btn-danger btn-lg" id="btn_import">
                <span class="glyphicon glyphicon-play"></span>
                นำเข้าไฟล์นี้
            </button>
            <span class="dhdc-page-subtitle" style="margin-left:10px">ระบบจะนำเข้าข้อมูลจริงจาก ZIP และอัปเดตสถานะอัตโนมัติ</span>
        </div>
    <?php else: ?>
        <div class="alert <?= $model->note2 === 'OK' ? 'alert-success' : 'alert-danger' ?>">
            <?php
            if ($isRunning) {
                echo 'กำลังนำเข้า';
            } else {
                if ($model->note3 === 'import all') {
                    echo Html::a('รายละเอียด', ['detail2', 'filename' => $model->file_name]);
                } else {
                    echo Html::a('รายละเอียด', ['detail', 'filename' => $model->file_name]);
                }
            }
            ?>
        </div>
    <?php endif; ?>

    <div id="info" class="alert alert-info" style="display: none">
        ระบบกำลังนำเข้าข้อมูล ท่านสามารถเปิดหน้าจอนี้ค้างไว้เพื่อติดตามผล
    </div>

    <div id="res" class="dhdc-panel" style="display: none">
        <span class="glyphicon glyphicon-refresh glyphicon-spin"></span>
        กำลังประมวลผลไฟล์ 43 แฟ้ม...
    </div>

    <?php
    $action_route = Url::to(['/import/ajax/import']);
    $csrfParam = \yii\helpers\Json::htmlEncode(Yii::$app->request->csrfParam);
    $csrfToken = \yii\helpers\Json::htmlEncode(Yii::$app->request->csrfToken);

    $script = <<< JS
$('#btn_import').on('click', function(e) {
    $("html, body").animate({ scrollTop: $(document).height() }, "slow");
    $('#res').toggle();
    $('#info').toggle();
    $('#btn_import').hide();
    var data = {fortythree:"$model->file_name",upload_date:"$model->upload_date",upload_time:"$model->upload_time",id:"$model->id"};
    data[$csrfParam] = $csrfToken;

    $.ajax({
       url: "$action_route",
       method: "POST",
       data: data,
       success: function(data) {
            $('#res').toggle();
            $('#info').toggle();
            alert(data);
            window.location.reload();
       }
    });
});
JS;
    $this->registerJs($script);
    ?>

    <div class="alert alert-warning">
        &copy; สงวนลิขสิทธิ์ source code ส่วนการทำงานนำเข้าไฟล์ 43 แฟ้ม
    </div>

</div>
