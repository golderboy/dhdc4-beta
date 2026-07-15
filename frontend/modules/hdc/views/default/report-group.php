<?php

use yii\helpers\Html;

$yearSql = "SELECT t.yearprocess+543 'byear' FROM pk_byear t limit 1";
$yearRow = \Yii::$app->db->createCommand($yearSql)->queryOne();
$byear = isset($yearRow['byear']) ? $yearRow['byear'] : '-';

$this->params['breadcrumbs'][]= ['label' => 'ระบบรายงาน HDC ปีงบประมาณ '.$byear, 'url' => ['/hdc/default/index']];
$this->params['breadcrumbs'][] = "$cat_name";

$reportSql = " SELECT t.* from sys_report_dhdc  t ";
$reportSql.= " WHERE t.cat_id = :cat_id and t.id not in (select id from sys_report_drop) ";
$reportSql.= "group by t.id";
$reports = \Yii::$app->db->createCommand($reportSql, [':cat_id' => $cat_id])->queryAll();
?>

<div class="dhdc-page-header">
    <div>
        <h1 class="dhdc-page-title"><?= Html::encode($cat_name) ?></h1>
        <div class="dhdc-page-subtitle">รายการรายงาน HDC ปีงบประมาณ <?= Html::encode($byear) ?></div>
    </div>
    <div class="dhdc-toolbar">
        <?= Html::a('<i class="glyphicon glyphicon-chevron-left"></i> กลับหน้ากลุ่มรายงาน', ['/hdc/default/index'], ['class' => 'btn btn-default']) ?>
    </div>
</div>

<div class="dhdc-stat-grid">
    <div class="dhdc-stat-card" color="inherit">
        <div class="dhdc-stat-label">Category ID</div>
        <div class="dhdc-stat-value"><?= Html::encode($cat_id) ?></div>
        <div class="dhdc-stat-note">ค่าจาก route เดิม</div>
    </div>
    <div class="dhdc-stat-card" color="primary">
        <div class="dhdc-stat-label">จำนวนรายงาน</div>
        <div class="dhdc-stat-value"><?= number_format(count($reports)) ?></div>
        <div class="dhdc-stat-note">ไม่นับรายการใน sys_report_drop</div>
    </div>
    <div class="dhdc-stat-card" color="info">
        <div class="dhdc-stat-label">การเปิดรายงาน</div>
        <div class="dhdc-stat-value">Tab</div>
        <div class="dhdc-stat-note">ยังเปิดหน้า report-id แบบ target เดิม</div>
    </div>
    <div class="dhdc-stat-card" color="success">
        <div class="dhdc-stat-label">ข้อมูล</div>
        <div class="dhdc-stat-value">Live</div>
        <div class="dhdc-stat-note">อ่านจากฐานข้อมูลจริง</div>
    </div>
</div>

<div class="dhdc-panel dhdc-alert-note">
    ประมวลผลรายงานโดยใช้หลักการเดียวกับ HDC กระทรวงสาธารณสุข และยังใช้ Controller, Action, RBAC และ URL เดิม
</div>

<div class="dhdc-panel">
    <div class="dhdc-section-title">รายงานในกลุ่มนี้</div>
    <?php if (empty($reports)): ?>
        <div class="dhdc-empty-state">ไม่พบรายงานในกลุ่มนี้</div>
    <?php else: ?>
        <div class="dhdc-report-list">
            <?php
            $i = 1;
            foreach ($reports as $itm):
                $linkLabel = $itm['report_name'];
                $id = $itm['id'];
                ?>
                <div class="dhdc-report-row">
                    <div class="dhdc-report-number"><?= $i++ ?></div>
                    <div class="dhdc-report-main">
                        <?= Html::a(Html::encode($linkLabel), ['/hdc/default/report-id', 'id' => $id, 'rpt' => $linkLabel], ['target' => '_blank', 'class' => 'dhdc-link-title']) ?>
                        <div class="dhdc-list-meta">Report ID: <?= Html::encode($id) ?></div>
                    </div>
                    <div class="dhdc-report-action">
                        <?= Html::a('เปิดรายงาน', ['/hdc/default/report-id', 'id' => $id, 'rpt' => $linkLabel], ['target' => '_blank', 'class' => 'btn btn-primary btn-sm']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
