<?php

use yii\helpers\Html;

$this->title = 'ระบบข้อมูลนักเรียน';
$this->params['breadcrumbs'][] = $this->title;

$items = [
    [
        'title' => 'ข้อมูลสถานศึกษา',
        'note' => 'จัดการทะเบียนสถานศึกษา',
        'icon' => 'glyphicon-home',
        'url' => ['/student/school/index'],
        'class' => 'btn-info',
    ],
    [
        'title' => 'ข้อมูลนักเรียน',
        'note' => 'จัดการทะเบียนนักเรียน',
        'icon' => 'glyphicon-user',
        'url' => ['/student/student/index'],
        'class' => 'btn-primary',
    ],
    [
        'title' => 'นำเข้าข้อมูลสถานศึกษา',
        'note' => 'นำเข้าข้อมูลโรงเรียนจากไฟล์ Excel',
        'icon' => 'glyphicon-import',
        'url' => ['/student/excel/school'],
        'class' => 'btn-success',
    ],
    [
        'title' => 'นำเข้าข้อมูลนักเรียน',
        'note' => 'นำเข้าข้อมูลนักเรียนจากไฟล์ Excel',
        'icon' => 'glyphicon-cloud-upload',
        'url' => ['/student/excel/student'],
        'class' => 'btn-success',
    ],
    [
        'title' => 'ข้อมูลการชั่งน้ำหนัก',
        'note' => 'เมนูเดิมยังไม่กำหนดปลายทาง',
        'icon' => 'glyphicon-scale',
        'url' => ['#'],
        'class' => 'btn-warning',
    ],
    [
        'title' => 'ออกจากระบบ',
        'note' => 'กลับหน้าหลักตาม behavior เดิม',
        'icon' => 'glyphicon-off',
        'url' => Yii::$app->homeUrl,
        'class' => 'btn-danger',
    ],
];
?>

<div class="student-default-index">
    <div class="dhdc-page-header">
        <div>
            <h1 class="dhdc-page-title"><?= Html::encode($this->title) ?></h1>
            <div class="dhdc-page-subtitle">เมนูจัดการข้อมูลสถานศึกษา นักเรียน และการนำเข้าข้อมูล โดยใช้ route เดิมทั้งหมด</div>
        </div>
        <div class="dhdc-toolbar">
            <span class="dhdc-status-pill dhdc-status-ok">Protected by RBAC</span>
        </div>
    </div>

    <div class="dhdc-stat-grid">
        <div class="dhdc-stat-card" color="primary">
            <div class="dhdc-stat-label">เมนูหลัก</div>
            <div class="dhdc-stat-value"><?= number_format(count($items)) ?></div>
            <div class="dhdc-stat-note">คงลิงก์เดิมจากหน้าเก่า</div>
        </div>
        <div class="dhdc-stat-card" color="info">
            <div class="dhdc-stat-label">ข้อมูล</div>
            <div class="dhdc-stat-value">2</div>
            <div class="dhdc-stat-note">สถานศึกษาและนักเรียน</div>
        </div>
        <div class="dhdc-stat-card" color="secondary">
            <div class="dhdc-stat-label">นำเข้า</div>
            <div class="dhdc-stat-value">2</div>
            <div class="dhdc-stat-note">Excel school/student</div>
        </div>
        <div class="dhdc-stat-card" color="inherit">
            <div class="dhdc-stat-label">สิทธิ์</div>
            <div class="dhdc-stat-value">User</div>
            <div class="dhdc-stat-note">ใช้ AccessControl เดิม</div>
        </div>
    </div>

    <div class="dhdc-panel">
        <div class="dhdc-section-title">งานข้อมูลนักเรียน</div>
        <div class="dhdc-link-grid">
            <?php foreach ($items as $item): ?>
                <div class="dhdc-link-card">
                    <div class="dhdc-link-icon"><i class="glyphicon <?= Html::encode($item['icon']) ?>"></i></div>
                    <div class="dhdc-link-content">
                        <?= Html::a(Html::encode($item['title']), $item['url'], ['class' => 'dhdc-link-title']) ?>
                        <div class="dhdc-list-meta"><?= Html::encode($item['note']) ?></div>
                        <div style="margin-top: 10px">
                            <?= Html::a('เปิด', $item['url'], ['class' => 'btn btn-sm ' . $item['class']]) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
