<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use components\MyHelper;

$this->title = 'คลัง Script';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="dhdc-page-header">
    <div>
        <h1 class="dhdc-page-title">คลัง Script</h1>
        <div class="dhdc-page-subtitle">จัดการและเรียกใช้ SQL script ตามสิทธิ์เดิมของระบบ</div>
    </div>
    <div class="dhdc-toolbar">
        <?php if (MyHelper::user_can('Admin')): ?>
            <?= Html::a('<i class="glyphicon glyphicon-pencil"></i> เพิ่ม SQL', ['create'], ['class' => 'btn btn-success']) ?>
            <a href="<?= \yii\helpers\Url::to(['upload']) ?>" class="btn btn-info">
                <i class="glyphicon glyphicon-file"></i> นำเข้า
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="sqlscript-index dhdc-panel dhdc-grid-shell">
<?php
echo GridView::widget([
    'responsiveWrap' => false,
    'hover' => true,
    'floatHeader' => true,
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'topic',
            'format' => 'raw',
            'value' => function ($model) {
                return Html::a($model->topic, ['view', 'id' => $model->id]);
            }
        ],
        'd_update',
        [
            'label' => 'ประมวลผล',
            'format' => 'raw',
            'value' => function ($model) {
                if (!MyHelper::user_can('Admin')) {
                    return '';
                }
                $route_run = \yii\helpers\Url::to(['runquery/index']);
                $topic = Html::encode($model->topic);
                $sql = Html::encode($model->sql_script);
                $csrfParam = Html::encode(Yii::$app->request->csrfParam);
                $csrfToken = Html::encode(Yii::$app->request->csrfToken);
                return "<form method=\"post\" action=\"$route_run\" class=\"sqlquery-run-form\">
                    <input type=\"hidden\" name=\"$csrfParam\" value=\"$csrfToken\">
                    <input type=\"hidden\" name=\"script_name\" value=\"$topic\">
                    <input type=\"hidden\" name=\"sql_code\" value=\"$sql\">
                    <button class=\"btn btn-success btn-sm\" title=\"Run script\">
                        <i class=\"glyphicon glyphicon-play\"></i>
                    </button>
                </form>";
            }
        ]
    ],
]);
?>
</div>
