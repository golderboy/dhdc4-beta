<?php

use yii\helpers\Html;
use components\MyHelper;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\UploadFortythreeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'รายการไฟล์ 43 แฟ้ม';
$this->params['breadcrumbs'][] = $this->title;

$summary = \Yii::$app->db->createCommand("
    SELECT
        COUNT(*) AS total_uploads,
        SUM(CASE WHEN note2 = 'OK' THEN 1 ELSE 0 END) AS ok_uploads,
        SUM(CASE WHEN note2 LIKE '%ผิดพลาด%' THEN 1 ELSE 0 END) AS error_uploads,
        SUM(CASE WHEN note2 LIKE '%รอนำเข้า%' OR note2 LIKE '%เธฃเธญเธเธณเน€เธเนเธฒ%' THEN 1 ELSE 0 END) AS pending_uploads
    FROM sys_upload_fortythree
")->queryOne();

$statusBadge = function ($status) {
    $status = (string) $status;
    $class = 'dhdc-status-pending';
    if ($status === 'OK') {
        $class = 'dhdc-status-ok';
    } elseif (strpos($status, 'ผิดพลาด') !== false || stripos($status, 'error') !== false) {
        $class = 'dhdc-status-error';
    } elseif (strpos($status, 'กำลัง') !== false || strpos($status, 'เธเธณเธฅเธฑเธ') !== false) {
        $class = 'dhdc-status-running';
    }

    return Html::tag('span', Html::encode($status !== '' ? $status : '-'), [
        'class' => 'dhdc-status-pill ' . $class,
    ]);
};
?>
<div class="upload-fortythree-index">

    <div class="dhdc-page-header">
        <div>
            <h1 class="dhdc-page-title">นำเข้า 43 แฟ้ม</h1>
            <div class="dhdc-page-subtitle">ติดตามไฟล์ ZIP, สถานะนำเข้า และผลรวมข้อมูลล่าสุดจากระบบจริง</div>
        </div>
        <div class="dhdc-toolbar">
            <?= Html::a('<i class="glyphicon glyphicon-dashboard"></i> Dashboard', ['/import/default/dashboard'], ['class' => 'btn btn-default']) ?>
            <?= Html::a('<i class="glyphicon glyphicon-open"></i> Upload 43 แฟ้ม', ['create'], ['class' => 'btn btn-blue']) ?>
            <?= Html::a('ปริมาณข้อมูล', ['count-file/index'], ['class' => 'btn btn-orange']) ?>
            <?php if (MyHelper::user_can('Admin')): ?>
                <?= Html::a('<i class="glyphicon glyphicon-info-sign"></i> ข้อผิดพลาด', ['/import/import-error/index'], ['class' => 'btn btn-red']) ?>
                <?= Html::a("รอนำเข้า $zip", ['/import/upload/importall'], ['class' => 'btn btn-green']) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="dhdc-stat-grid">
        <div class="dhdc-stat-card" color="primary">
            <div class="dhdc-stat-label">Uploads</div>
            <div class="dhdc-stat-value"><?= number_format((int) $summary['total_uploads']) ?></div>
            <div class="dhdc-stat-note">รายการไฟล์ทั้งหมด</div>
        </div>
        <div class="dhdc-stat-card" color="success">
            <div class="dhdc-stat-label">Completed</div>
            <div class="dhdc-stat-value"><?= number_format((int) $summary['ok_uploads']) ?></div>
            <div class="dhdc-stat-note">นำเข้าสำเร็จ</div>
        </div>
        <div class="dhdc-stat-card" color="warning">
            <div class="dhdc-stat-label">Pending</div>
            <div class="dhdc-stat-value"><?= number_format((int) $summary['pending_uploads']) ?></div>
            <div class="dhdc-stat-note">รอนำเข้า</div>
        </div>
        <div class="dhdc-stat-card" color="error">
            <div class="dhdc-stat-label">Errors</div>
            <div class="dhdc-stat-value"><?= number_format((int) $summary['error_uploads']) ?></div>
            <div class="dhdc-stat-note">ต้องตรวจสอบ</div>
        </div>
    </div>

    <?=
    kartik\grid\GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'hover' => true,
        'responsiveWrap' => false,
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'nullDisplay' => '-',
        ],
        'pjax' => true,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'file_name',
                'format' => 'raw',
                'value' => function ($data) {
                    if ($data->note3 === 'import all') {
                        return Html::a($data->file_name, ['detail2', 'filename' => $data->file_name]);
                    }
                    return Html::a($data->file_name, ['view', 'id' => $data->id]);
                },
            ],
            'file_size',
            'upload_date',
            'upload_time',
            [
                'attribute' => 'note2',
                'label' => 'status',
                'format' => 'raw',
                'value' => function ($data) use ($statusBadge) {
                    return $statusBadge($data->note2);
                },
            ],
            [
                'attribute' => 'note3',
                'label' => 'note',
                'value' => function ($data) {
                    return $data->note3;
                },
            ],
        ],
    ]);
    ?>

</div>
