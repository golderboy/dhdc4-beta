<?php

use yii\helpers\Html;

$yearSql = "SELECT t.yearprocess+543 'byear' FROM pk_byear t limit 1";
$yearRow = \Yii::$app->db->createCommand($yearSql)->queryOne();
$byear = isset($yearRow['byear']) ? $yearRow['byear'] : '-';

$this->params['breadcrumbs'][] = 'ระบบรายงาน HDC ปีงบประมาณ ' . $byear;

$categorySql = "SELECT t.* from sys_reportcategory_dhdc t";
$categories = \Yii::$app->db->createCommand($categorySql)->queryAll();
$categoryCount = count($categories);
$reportCount = (int)\Yii::$app->db
    ->createCommand("SELECT COUNT(*) FROM sys_report_dhdc WHERE id not in (select id from sys_report_drop)")
    ->queryScalar();
?>

<div class="dhdc-page-header">
    <div>
        <h1 class="dhdc-page-title">ระบบรายงาน HDC</h1>
        <div class="dhdc-page-subtitle">เลือกกลุ่มรายงานเพื่อดูรายการตัวชี้วัด ปีงบประมาณ <?= Html::encode($byear) ?></div>
    </div>
    <div class="dhdc-toolbar">
        <span class="dhdc-status-pill dhdc-status-running">ข้อมูลจริงจากระบบเดิม</span>
    </div>
</div>

<div class="dhdc-stat-grid">
    <div class="dhdc-stat-card" color="primary">
        <div class="dhdc-stat-label">ปีงบประมาณ</div>
        <div class="dhdc-stat-value"><?= Html::encode($byear) ?></div>
        <div class="dhdc-stat-note">อ้างอิงจาก pk_byear</div>
    </div>
    <div class="dhdc-stat-card" color="secondary">
        <div class="dhdc-stat-label">กลุ่มรายงาน</div>
        <div class="dhdc-stat-value"><?= number_format($categoryCount) ?></div>
        <div class="dhdc-stat-note">sys_reportcategory_dhdc</div>
    </div>
    <div class="dhdc-stat-card" color="success">
        <div class="dhdc-stat-label">รายงานที่ใช้งาน</div>
        <div class="dhdc-stat-value"><?= number_format($reportCount) ?></div>
        <div class="dhdc-stat-note">ไม่นับรายการใน sys_report_drop</div>
    </div>
    <div class="dhdc-stat-card" color="info">
        <div class="dhdc-stat-label">Route เดิม</div>
        <div class="dhdc-stat-value">HDC</div>
        <div class="dhdc-stat-note">ยังใช้ Controller และ URL เดิม</div>
    </div>
</div>

<div class="dhdc-panel">
    <div class="dhdc-section-title">กลุ่มรายงาน</div>
    <?php if (empty($categories)): ?>
        <div class="dhdc-empty-state">ไม่พบกลุ่มรายงานในระบบ</div>
    <?php else: ?>
        <div class="dhdc-link-grid">
            <?php foreach ($categories as $itm):
                $linkLabel = $itm['category_name'];
                $catId = $itm['cat_id'];
                ?>
                <div class="dhdc-link-card">
                    <div class="dhdc-link-icon"><i class="glyphicon glyphicon-list-alt"></i></div>
                    <div class="dhdc-link-content">
                        <?= Html::a(Html::encode($linkLabel), ['/hdc/default/report-group', 'cat_id' => $catId, 'cat_name' => $linkLabel], ['class' => 'dhdc-link-title']) ?>
                        <div class="dhdc-list-meta">Category ID: <?= Html::encode($catId) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
