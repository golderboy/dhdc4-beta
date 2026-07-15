<?php

use yii\helpers\Html;

$this->title = 'QOF Dashboard';
$this->params['breadcrumbs'][] = $this->title;

$reportCount = (int)\Yii::$app->db->createCommand("SELECT COUNT(*) FROM dhdc_qof_report")->queryScalar();
$activeCount = (int)\Yii::$app->db->createCommand("SELECT COUNT(*) FROM dhdc_qof_report WHERE active = 'Y'")->queryScalar();
$inactiveCount = $reportCount - $activeCount;
?>

<div class="Qof-default-index">
    <div class="dhdc-page-header">
        <div>
            <h1 class="dhdc-page-title">QOF Dashboard</h1>
            <div class="dhdc-page-subtitle">ศูนย์รวมรายงาน QOF โดยใช้ข้อมูลและ route เดิมของระบบ</div>
        </div>
        <div class="dhdc-toolbar">
            <?= Html::a('<i class="glyphicon glyphicon-list-alt"></i> เปิดรายการรายงาน', ['/Qof/qof/index'], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="glyphicon glyphicon-cog"></i> จัดการรายงาน', ['/Qof/dhdcqofreport/index'], ['class' => 'btn btn-default']) ?>
        </div>
    </div>

    <div class="dhdc-stat-grid">
        <div class="dhdc-stat-card" color="primary">
            <div class="dhdc-stat-label">รายงานทั้งหมด</div>
            <div class="dhdc-stat-value"><?= number_format($reportCount) ?></div>
            <div class="dhdc-stat-note">ตาราง dhdc_qof_report</div>
        </div>
        <div class="dhdc-stat-card" color="success">
            <div class="dhdc-stat-label">รายงานที่ใช้งาน</div>
            <div class="dhdc-stat-value"><?= number_format($activeCount) ?></div>
            <div class="dhdc-stat-note">active = Y</div>
        </div>
        <div class="dhdc-stat-card" color="warning">
            <div class="dhdc-stat-label">รายงานที่ปิดไว้</div>
            <div class="dhdc-stat-value"><?= number_format($inactiveCount) ?></div>
            <div class="dhdc-stat-note">ยังคงข้อมูลเดิมไว้</div>
        </div>
        <div class="dhdc-stat-card" color="info">
            <div class="dhdc-stat-label">แนวทาง UI</div>
            <div class="dhdc-stat-value">Native</div>
            <div class="dhdc-stat-note">พร้อมใช้งานตามสิทธิ์ของผู้ใช้</div>
        </div>
    </div>

    <div class="dhdc-panel">
        <div class="dhdc-section-title">งาน QOF</div>
        <div class="dhdc-link-grid">
            <div class="dhdc-link-card">
                <div class="dhdc-link-icon"><i class="glyphicon glyphicon-stats"></i></div>
                <div class="dhdc-link-content">
                    <?= Html::a('รายงาน QOF เขต 1', ['/Qof/qof/index'], ['class' => 'dhdc-link-title']) ?>
                    <div class="dhdc-list-meta">แสดง GridView/Pjax และลิงก์รายงานเดิม</div>
                </div>
            </div>
            <div class="dhdc-link-card">
                <div class="dhdc-link-icon"><i class="glyphicon glyphicon-wrench"></i></div>
                <div class="dhdc-link-content">
                    <?= Html::a('จัดการรายงาน QOF', ['/Qof/dhdcqofreport/index'], ['class' => 'dhdc-link-title']) ?>
                    <div class="dhdc-list-meta">ใช้ CRUD เดิมของโมดูล QOF</div>
                </div>
            </div>
            <div class="dhdc-link-card">
                <div class="dhdc-link-icon"><i class="glyphicon glyphicon-ok-circle"></i></div>
                <div class="dhdc-link-content">
                    <span class="dhdc-link-title">ข้อมูลจริง</span>
                    <div class="dhdc-list-meta">รายการข้อมูลที่พร้อมตรวจสอบ</div>
                </div>
            </div>
        </div>
    </div>
</div>
