<?php

use yii\helpers\Html;
use backend\modules\pluginsetup\models\SysDhdcPlugin;
use yii\helpers\Url;

$this->title = 'ระบบงาน';
$this->params['breadcrumbs'][] = $this->title;

$models = SysDhdcPlugin::find()->where(['status' => 'on'])->orderBy(['type' => SORT_DESC, 'name' => SORT_ASC])->all();
$activeCount = count($models);
$moduleCount = SysDhdcPlugin::find()->where(['status' => 'on', 'type' => 'module'])->count();
$appCount = SysDhdcPlugin::find()->where(['status' => 'on', 'type' => 'app'])->count();
$offCount = SysDhdcPlugin::find()->where(['status' => 'off'])->count();
?>

<div class="plugin-default-index">
    <div class="dhdc-page-header">
        <div>
            <h1 class="dhdc-page-title">ระบบงาน</h1>
            <div class="dhdc-page-subtitle">เมนูเข้าสู่ระบบงานที่เปิดใช้งานจากตาราง sys_dhdc_plugin</div>
        </div>
        <div class="dhdc-toolbar">
            <span class="dhdc-status-pill dhdc-status-ok">Plugin ที่เปิดใช้งาน</span>
        </div>
    </div>

    <div class="dhdc-stat-grid">
        <div class="dhdc-stat-card" color="success">
            <div class="dhdc-stat-label">เปิดใช้งาน</div>
            <div class="dhdc-stat-value"><?= number_format($activeCount) ?></div>
            <div class="dhdc-stat-note">status = on</div>
        </div>
        <div class="dhdc-stat-card" color="primary">
            <div class="dhdc-stat-label">Module</div>
            <div class="dhdc-stat-value"><?= number_format($moduleCount) ?></div>
            <div class="dhdc-stat-note">เปิดในหน้าปัจจุบัน</div>
        </div>
        <div class="dhdc-stat-card" color="secondary">
            <div class="dhdc-stat-label">App</div>
            <div class="dhdc-stat-value"><?= number_format($appCount) ?></div>
            <div class="dhdc-stat-note">เปิดหน้าต่างใหม่ตาม behavior เดิม</div>
        </div>
        <div class="dhdc-stat-card" color="warning">
            <div class="dhdc-stat-label">ปิดไว้</div>
            <div class="dhdc-stat-value"><?= number_format($offCount) ?></div>
            <div class="dhdc-stat-note">ไม่แสดงในเมนูนี้</div>
        </div>
    </div>

    <div class="dhdc-panel">
        <div class="dhdc-section-title">รายการระบบงาน</div>
        <?php if (empty($models)): ?>
            <div class="dhdc-empty-state">ไม่พบระบบงานที่เปิดใช้งาน</div>
        <?php else: ?>
            <div class="dhdc-app-grid">
                <?php foreach ($models as $model) :
                    $img = '@web/images/mod.png';
                    $winType = '_self';
                    if ($model->type == 'app') {
                        $route = $model->route;
                        $winType = '_blank';
                        $img = '@web/images/app.png';
                    } else {
                        $route = Url::to([$model->route]);
                    }
                    ?>
                    <a class="dhdc-app-card" href="<?= Html::encode($route) ?>" target="<?= Html::encode($winType) ?>">
                        <?= Html::img($img, ['class' => 'dhdc-app-icon', 'alt' => $model->name]) ?>
                        <span class="dhdc-app-name"><?= Html::encode($model->name) ?></span>
                        <span class="dhdc-list-meta"><?= Html::encode($model->type) ?> · <?= Html::encode($model->mod_name) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
