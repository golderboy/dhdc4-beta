<?php

use yii\helpers\Html;
use components\ReportSqlHelper;

$this->params['breadcrumbs'][] = ['label' => 'ระบบ HDC DATA-Exchange', 'url' => ['/hdcex/default/index']];
$this->params['breadcrumbs'][] = "$cat_name";
$cat_id = ReportSqlHelper::safeIdentifierSuffix($cat_id, 'category id');

$db = \Yii::$app->db;
$sql = "SELECT * from sys_data_exchange t WHERE t.active = 1 and t.cat_id = :cat_id";
$raw = $db->createCommand($sql, [':cat_id' => $cat_id])->queryAll();
?>

<div class="dhdc-page-header">
    <div>
        <h1 class="dhdc-page-title"><?= Html::encode($cat_name) ?></h1>
        <div class="dhdc-page-subtitle">รายการส่งออกข้อมูลที่เปิดใช้งานในหมวดนี้</div>
    </div>
    <div class="dhdc-toolbar">
        <?= Html::a('<i class="glyphicon glyphicon-chevron-left"></i> กลับหมวดข้อมูล', ['/hdcex/default/index'], ['class' => 'btn btn-default']) ?>
    </div>
</div>

<div class="dhdc-stat-grid">
    <div class="dhdc-stat-card" color="inherit">
        <div class="dhdc-stat-label">Category ID</div>
        <div class="dhdc-stat-value"><?= Html::encode($cat_id) ?></div>
        <div class="dhdc-stat-note">route parameter เดิม</div>
    </div>
    <div class="dhdc-stat-card" color="primary">
        <div class="dhdc-stat-label">รายการส่งออก</div>
        <div class="dhdc-stat-value"><?= number_format(count($raw)) ?></div>
        <div class="dhdc-stat-note">active=1</div>
    </div>
</div>

<div class="dhdc-panel">
    <div class="dhdc-section-title">รายการข้อมูล</div>
    <?php if (empty($raw)): ?>
        <div class="dhdc-empty-state">ไม่พบรายการ Data-Exchange ในหมวดนี้</div>
    <?php else: ?>
        <div class="dhdc-report-list">
            <?php $i = 1; ?>
            <?php foreach ($raw as $value): ?>
                <?php
                $ex_id = $value['ex_id'];
                $title = $value['title'];
                ?>
                <div class="dhdc-report-row">
                    <div class="dhdc-report-number"><?= $i++ ?></div>
                    <div class="dhdc-report-main">
                        <?= Html::a(Html::encode($title), ['/hdcex/default/report-id', 'ex_id' => $ex_id, 'title' => $title], ['target' => '_blank', 'class' => 'dhdc-link-title']) ?>
                        <div class="dhdc-list-meta">Exchange ID: <?= Html::encode($ex_id) ?></div>
                    </div>
                    <div class="dhdc-report-action">
                        <?= Html::a('เปิดรายการ', ['/hdcex/default/report-id', 'ex_id' => $ex_id, 'title' => $title], ['target' => '_blank', 'class' => 'btn btn-primary btn-sm']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
