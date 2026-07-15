<?php

use yii\helpers\Html;

$this->params['breadcrumbs'][] = 'ระบบ HDC DATA-Exchange';
$db = \Yii::$app->db;

$sql = "SELECT DISTINCT t.cat_id,c.category_name from sys_data_exchange t
LEFT JOIN sys_reportcategory  c on c.cat_id = t.cat_id";
$raw = $db->createCommand($sql)->queryAll();
?>

<div class="dhdc-page-header">
    <div>
        <h1 class="dhdc-page-title">HDC DATA-Exchange</h1>
        <div class="dhdc-page-subtitle">เลือกหมวดข้อมูลสำหรับส่งออก โดยใช้ query และ route เดิมของระบบ</div>
    </div>
    <div class="dhdc-toolbar">
        <span class="dhdc-status-pill dhdc-status-running">Live data</span>
    </div>
</div>

<div class="dhdc-stat-grid">
    <div class="dhdc-stat-card" color="primary">
        <div class="dhdc-stat-label">หมวดข้อมูล</div>
        <div class="dhdc-stat-value"><?= number_format(count($raw)) ?></div>
        <div class="dhdc-stat-note">sys_data_exchange</div>
    </div>
    <div class="dhdc-stat-card" color="info">
        <div class="dhdc-stat-label">Workflow</div>
        <div class="dhdc-stat-value">เดิม</div>
        <div class="dhdc-stat-note">index -> report-list -> report-id</div>
    </div>
</div>

<div class="dhdc-panel">
    <div class="dhdc-section-title">หมวดข้อมูล</div>
    <?php if (empty($raw)): ?>
        <div class="dhdc-empty-state">ไม่พบหมวดข้อมูล Data-Exchange ที่ใช้งานได้</div>
    <?php else: ?>
        <div class="dhdc-link-grid">
            <?php foreach ($raw as $value): ?>
                <?php
                $link = $value['category_name'];
                $cat_id = $value['cat_id'];
                ?>
                <div class="dhdc-link-card">
                    <div class="dhdc-link-icon"><i class="glyphicon glyphicon-transfer"></i></div>
                    <div class="dhdc-link-content">
                        <?= Html::a(Html::encode($link), ['/hdcex/default/report-list', 'cat_id' => $cat_id, 'cat_name' => $link], ['class' => 'dhdc-link-title']) ?>
                        <div class="dhdc-list-meta">Category ID: <?= Html::encode($cat_id) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
