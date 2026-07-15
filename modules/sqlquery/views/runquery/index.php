<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use components\MyHelper;

$this->params['breadcrumbs'][] = ['label' => 'คลัง Script', 'url' => ['sqlscript/index']];

if (isset($_POST['script_name'])) {
    $this->params['breadcrumbs'][] = $_POST['script_name'];
}
?>

<div class="dhdc-page-header">
    <div>
        <h1 class="dhdc-page-title">SQL Query</h1>
        <div class="dhdc-page-subtitle">รัน SQL script ด้วย workflow เดิมของระบบ</div>
    </div>
    <div class="dhdc-toolbar">
        <a href="<?= yii\helpers\Url::to(['sqlscript/index']) ?>" class="btn btn-primary">
            <i class="glyphicon glyphicon-list-alt"></i> คลัง script
        </a>
        <a href="#" id="btn-collape" class="btn btn-default">
            <i class="glyphicon glyphicon-resize-vertical"></i> ย่อ/ขยาย
        </a>
    </div>
</div>

<div id="frm-sql" class="dhdc-panel sqlquery-console">
    <div class="alert alert-danger">กรุณาใส่เครื่องหมาย ; ปิดท้ายคำสั่ง ตัวอย่างเช่น select * from person limit 100;</div>

    <form method="POST">
        <input type="hidden" name="<?= Html::encode(Yii::$app->request->csrfParam) ?>" value="<?= Html::encode(Yii::$app->request->csrfToken) ?>">
        <div class="form-group">
            <textarea name="sql_code" id="sql_code" class="form-control sqlquery-editor" rows="8"><?= Html::encode(@$sql_code) ?></textarea>
        </div>
        <div class="dhdc-toolbar">
            <?php if (MyHelper::modIsOn()): ?>
                <?php if (MyHelper::user_can('Admin')): ?>
                    <button class="btn btn-danger"><i class="glyphicon glyphicon-refresh"></i> รันชุดคำสั่ง</button>
                    <button name="save" value="yes" class="btn btn-success"><i class="glyphicon glyphicon-floppy-disk"></i> จัดเก็บ</button>
                <?php endif; ?>
                <a href="<?= yii\helpers\Url::to(['sqlscript/index']) ?>" class="btn btn-primary"><i class="glyphicon glyphicon-list-alt"></i> คลัง script</a>
            <?php else: ?>
                <label>ผู้ดูแลระบบปิดใช้งาน</label>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if (isset($dataProvider)): ?>
    <div class="dhdc-panel dhdc-grid-shell">
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
            'responsiveWrap' => false,
            'hover' => true,
            'panel' => [
                'before' => '',
                'type' => GridView::TYPE_INFO
            ],
            'export' => [
                'showConfirmAlert' => false,
                'target' => GridView::TARGET_BLANK
            ],
        ]);
        ?>
    </div>
<?php endif; ?>

<?php
$script = <<< JS
$(function(){
    $("label[title='Show all data']").hide();
});

$('#btn-collape').on('click', function(e) {
   e.preventDefault();
   $('#frm-sql').slideToggle();
});
JS;
$this->registerJs($script);
?>
