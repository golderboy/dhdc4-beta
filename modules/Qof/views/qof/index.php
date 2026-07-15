<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel modules\Qof\models\DhdcqofreportSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'รายงาน QOF เขต 1';
$this->params['breadcrumbs'][] = $this->title;

$totalReports = $dataProvider->getTotalCount();
$currentPageReports = count($dataProvider->getModels());
?>

<div class="dhdcqofreport-index">
    <div class="dhdc-page-header">
        <div>
            <h1 class="dhdc-page-title"><?= Html::encode($this->title) ?></h1>
            <div class="dhdc-page-subtitle">รายการรายงาน QOF จากข้อมูลจริงของระบบเดิม</div>
        </div>
        <div class="dhdc-toolbar">
            <?= Html::a('<i class="glyphicon glyphicon-cog"></i> จัดการรายงาน QOF', ['/Qof/dhdcqofreport/index'], ['class' => 'btn btn-success']) ?>
        </div>
    </div>

    <div class="dhdc-stat-grid">
        <div class="dhdc-stat-card" color="primary">
            <div class="dhdc-stat-label">รายงานทั้งหมด</div>
            <div class="dhdc-stat-value"><?= number_format($totalReports) ?></div>
            <div class="dhdc-stat-note">จาก Dhdcqofreport</div>
        </div>
        <div class="dhdc-stat-card" color="secondary">
            <div class="dhdc-stat-label">แสดงในหน้านี้</div>
            <div class="dhdc-stat-value"><?= number_format($currentPageReports) ?></div>
            <div class="dhdc-stat-note">ตาม pagination เดิมของ GridView</div>
        </div>
        <div class="dhdc-stat-card" color="info">
            <div class="dhdc-stat-label">Interaction</div>
            <div class="dhdc-stat-value">Pjax</div>
            <div class="dhdc-stat-note">ยังใช้ widget และ behavior เดิม</div>
        </div>
        <div class="dhdc-stat-card" color="inherit">
            <div class="dhdc-stat-label">License UI</div>
            <div class="dhdc-stat-value">MIT</div>
            <div class="dhdc-stat-note">เลือกตัวชี้วัดเพื่อดูรายละเอียด</div>
        </div>
    </div>

    <div class="dhdc-panel">
        <?php Pjax::begin(); ?>
        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'panel' => [
                'heading' => '<h3 class="panel-title">'.$this->title.'</h3>',
                'type' => 'info',
            ],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'name',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->active == "N" ? Html::encode($model->name) :
                            Html::a('<i class="glyphicon glyphicon-list-alt"></i> '.Html::encode($model->name), [$model->url], ['target' => '_blank']);
                    }
                ],
            ],
        ]); ?>
        <?php Pjax::end(); ?>
    </div>
</div>
