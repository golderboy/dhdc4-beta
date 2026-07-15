<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'id ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'คลัง Script', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$route_run = \yii\helpers\Url::to(['runquery/index']);
?>

<div class="dhdc-page-header">
    <div>
        <h1 class="dhdc-page-title"><?= Html::encode($this->title) ?></h1>
        <div class="dhdc-page-subtitle"><?= Html::encode($model->topic) ?></div>
    </div>
    <div class="dhdc-toolbar">
        <?php if (\components\MyHelper::user_can('Admin')): ?>
        <?= Html::a('<i class="glyphicon glyphicon-upload"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<i class="glyphicon glyphicon-remove"></i> Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <form method="post" action="<?= $route_run ?>" class="sqlquery-run-inline">
            <input type="hidden" name="<?= Html::encode(Yii::$app->request->csrfParam) ?>" value="<?= Html::encode(Yii::$app->request->csrfToken) ?>">
            <input type="hidden" name="script_name" value="<?= Html::encode($model->topic) ?>">
            <input type="hidden" name="sql_code" value="<?= Html::encode($model->sql_script) ?>">
            <button class="btn btn-success">
                <i class="glyphicon glyphicon-play"></i> ประมวลผล
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="sqlscript-view dhdc-panel">
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'topic',
            'sql_script:ntext',
            'user',
            'd_update',
        ],
    ]) ?>
</div>
