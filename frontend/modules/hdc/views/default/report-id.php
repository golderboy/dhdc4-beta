<?php

use components\MyHelper;
use components\ReportSqlHelper;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use frontend\modules\hdc\models\Hdcsql;
use common\models\config\ChospitalAmp;

$this->title = $rpt;
$id = ReportSqlHelper::safeIdentifierSuffix($id, 'report id');

$sql = "SELECT * FROM sys_report_dhdc t WHERE t.id = :id limit 1";
$raw = Yii::$app->db->createCommand($sql, [':id' => $id])->queryOne();
$tb = $raw['source_table'];
$report_name = $raw['report_name'];
$proc = $raw['t_sql'];
$sp = $tb . '_' . $id;
$sql_sp = "call $sp;";
try {
    //$res = \Yii::$app->db->createCommand($sql_sp)->execute();
} catch (\yii\db\Exception $e) {
    //echo $e->getMessage();
}

$sql = "select * from hdc_rpt_sql t where t.rpt_id = :id limit 1";
$raw = \Yii::$app->db->createCommand($sql, [':id' => $id])->queryOne();
if (!$raw) {
    $command = "CALL sys_add_report_drop('$id')";
    MyHelper::exec_sql($command);
    throw new \yii\web\NotFoundHttpException('ไม่พบรายงานที่ต้องการ');
}
$sql_sum = $raw['sql_sum'];
$sum_err = '';

try {
    $this->context->exec_sql("DROP PROCEDURE IF EXISTS hdc_sum_$id");

    $sp_sum = "CREATE PROCEDURE hdc_sum_$id()\r\n";
    $sp_sum .= " BEGIN \r\n";
    $sp_sum .= trim($sql_sum);
    $sp_sum .= "; \r\n END";

    $this->context->exec_sql($sp_sum);

    $raw_sum = $this->context->call("hdc_sum_$id", NULL);
} catch (\yii\db\Exception $e) {
    Yii::error($e, __METHOD__);
    $err_msg = 'ไม่สามารถแสดงข้อมูลสรุปได้ กรุณาลองใหม่อีกครั้ง';
    $sum_err = 'none';
}

if (empty($raw_sum)) {
    $raw_sum = ['data' => 'NULL'];
}
?>

<div class="dhdc-page-header hdc-report-header">
    <div>
        <h1 class="dhdc-page-title"><?= Html::encode($rpt) ?></h1>
        <div class="dhdc-page-subtitle">รายงานข้อมูลสุขภาพ</div>
    </div>
    <div class="dhdc-toolbar">
        <?php if (MyHelper::user_can('Admin') || MyHelper::user_can('Pm')): ?>
            <?= Html::a('SQL', ['/hdc/default/show-sql', 'id' => $id, 'rpt' => $rpt], ['target' => '_blank', 'class' => 'btn btn-default']) ?>
        <?php endif; ?>
        <?php if (!empty($err_msg) && !empty($sql_sum)): ?>
            <a href="#" onclick="show_err(); return false;" class="btn btn-warning">ดูข้อความแจ้งเตือน</a>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($err_msg)): ?>
    <div id="show_err" class="alert alert-danger" style="display: none"><?= Html::encode($err_msg) ?></div>
<?php endif; ?>

<div id="sum" class="dhdc-grid-shell hdc-summary-panel"<?= $sum_err ? ' style="display: ' . $sum_err . '"' : '' ?>>
    <?php
    if (!empty($raw_sum[0])) {
        $cols_sum = array_keys($raw_sum[0]);
    }

    $dataProvider = new ArrayDataProvider([
        'allModels' => $raw_sum,
        'sort' => !empty($cols_sum) ? ['attributes' => $cols_sum] : false,
        'pagination' => false
    ]);
    $note_sum = Hdcsql::find()->where(['rpt_id' => $id])->one();
    $note_sum = $note_sum->note_sum;

    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'responsiveWrap' => false,
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'responsive' => false,
        'hover' => true,
        'panel' => [
            'type' => 'primary',
            'heading' => $rpt,
            'before' => $note_sum,
            'footer' => 'ตาราง : ' . $raw['tb_source']
        ],
        'export' => [
            'showConfirmAlert' => false,
            'target' => '_blank'
        ],
    ]);
    ?>
</div>

<?php Pjax::begin(); ?>

<div class="dhdc-panel hdc-filter-panel">
    <div class="dhdc-section-title">ตัวกรองรายคน</div>
    <?php
    $form = ActiveForm::begin([
        'method' => 'get',
        'action' => Url::to(['/hdc/default/report-id']),
        'options' => [
            'data-pjax' => 'true'
        ],
    ]);
    echo Html::hiddenInput('id', $id);
    echo Html::hiddenInput('rpt', $rpt);
    $itms_opt = ArrayHelper::map(ChospitalAmp::find()->all(), 'hoscode', 'fullname');
    $hospcode = \Yii::$app->request->get('hospcode');
    echo '<div class="dhdc-filter-panel">';
    echo Html::dropDownList('hospcode', $hospcode, $itms_opt, [
        'prompt' => '- หน่วยบริการ -',
        'class' => 'form-control dhdc-inline-control'
    ]);
    echo Html::submitButton('ตกลง', ['class' => 'btn btn-primary']);
    echo '</div>';
    ActiveForm::end();
    ?>
</div>

<div id="indiv" class="dhdc-grid-shell hdc-individual-panel">
    <?php
    $sql_indiv = $raw['sql_indiv'];

    try {
        $this->context->exec_sql("DROP PROCEDURE IF EXISTS hdc_indiv_$id");

        $sp_indiv = "CREATE PROCEDURE hdc_indiv_$id()\r\n";
        $sp_indiv .= " BEGIN \r\n";
        if (!MyHelper::user_can('Pm')) {
            $hospcode = MyHelper::getUserHoscode(\Yii::$app->user->id);
        }
        $sp_indiv .= ReportSqlHelper::applyHospcodeFilter($sql_indiv, $hospcode);
        $sp_indiv .= "; \r\n END";

        $this->context->exec_sql($sp_indiv);

        $raw_indiv = $this->context->call("hdc_indiv_$id", NULL);
    } catch (\yii\db\Exception $e) {
        Yii::error($e, __METHOD__);
        echo Html::tag('div', 'ไม่สามารถแสดงข้อมูลรายบุคคลได้ กรุณาลองใหม่อีกครั้ง', ['class' => 'alert alert-danger']);
    }

    if (empty($raw_indiv)) {
        $raw_indiv = ['data' => 'NULL'];
    }

    if (!empty($raw_indiv[0])) {
        $cols = array_keys($raw_indiv[0]);
    }
    $dataProvider = new ArrayDataProvider([
        'allModels' => $raw_indiv,
        'sort' => !empty($cols) ? ['attributes' => $cols] : false,
        'pagination' => [
            'pageSize' => 20
        ]
    ]);

    $note_indiv = Hdcsql::find()->where(['rpt_id' => $id])->one();
    $note_indiv = $note_indiv->note_indiv;

    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'responsiveWrap' => false,
        'hover' => true,
        'panel' => [
            'before' => $note_indiv,
            'type' => 'danger',
            'heading' => "$rpt (รายคน)"
        ],
        'export' => [
            'showConfirmAlert' => false,
            'target' => '_blank'
        ],
    ]);
    ?>
</div>

<?php Pjax::end(); ?>

<?php
$js = <<<JS
function show_err(){
    $('#show_err').toggle();
}
JS;
$this->registerJs($js, \yii\web\View::POS_HEAD);
?>
