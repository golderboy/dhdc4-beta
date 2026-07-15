<?php

use common\models\config\ChospitalAmp;
use yii\data\ArrayDataProvider;
use kartik\grid\GridView;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\helpers\Html;
use components\MyHelper;
use components\ReportSqlHelper;

$db = \Yii::$app->db;
$ex_id = ReportSqlHelper::safeIdentifierSuffix($ex_id, 'exchange id');
?>

<div class="dhdc-page-header">
    <div>
        <h1 class="dhdc-page-title"><?= Html::encode($title) ?></h1>
        <div class="dhdc-page-subtitle">รายงานแลกเปลี่ยนข้อมูลสุขภาพ</div>
    </div>
</div>

<div class="dhdc-panel">
    <div class="dhdc-section-title">Filter</div>
    <?php
    $skip_id = ['c10a2aa18f688027da4746eff598172b', 'eaf586ae6959ac7ef7d30513aa05a4d2'];

    $form = ActiveForm::begin([
        'method' => 'get',
        'action' => Url::to(['/hdcex/default/report-id']),
    ]);
    echo Html::hiddenInput('ex_id', $ex_id);
    echo Html::hiddenInput('title', $title);
    $itms_opt = ArrayHelper::map(ChospitalAmp::find()->all(), 'hoscode', 'fullname');
    $hospcode = \Yii::$app->request->get('hospcode');
    if (!MyHelper::user_can('Pm')) {
        $hospcode = MyHelper::getUserHoscode(\Yii::$app->user->id);
    }
    if ($hospcode !== null && $hospcode !== '' && $hospcode !== 'all' && !preg_match('/^[0-9A-Za-z_-]+$/', (string)$hospcode)) {
        throw new \yii\web\BadRequestHttpException('รหัสหน่วยบริการไม่ถูกต้อง');
    }
    echo '<div class="dhdc-filter-panel">';
    echo Html::dropDownList('hospcode', $hospcode, $itms_opt, [
        'prompt' => '- หน่วยบริการ -',
        'class' => 'form-control dhdc-inline-control',
    ]);
    echo Html::submitButton('ตกลง', ['class' => 'btn btn-primary']);
    if (MyHelper::user_can('Pm')) {
        if (!in_array($ex_id, $skip_id)) {
            echo Html::a('ทั้งหมด', ['/hdcex/default/report-all', 'ex_id' => $ex_id, 'title' => $title, 'hospcode' => 'all'], ['class' => 'btn btn-warning']);
        }
    }
    echo '</div>';

    ActiveForm::end();
    ?>
</div>

<?php
$update = " UPDATE sys_config a set a.provincecode = (SELECT provcode from sys_config_main LIMIT 1) ";
$db->createCommand($update)->execute();

$sql = "select t.title, ex_sql from sys_data_exchange t where t.ex_id = :ex_id";

$raw = $db->createCommand($sql, [':ex_id' => $ex_id])->queryOne();
//$what = $raw['note1'];
$what = "t1.hospcode";
if ($ex_id == '12489be4fcf94dc14de42607aa2f7aa0') {
    //$what = "d.hospcode";
}

if ($hospcode <> 'all') {
    $ex_sql = str_replace('{exp_office}', "  and $what = '$hospcode'  ", $raw['ex_sql']);
} else {
    $ex_sql = str_replace('{exp_office}', "  ", $raw['ex_sql']);
}
$ex_sql = str_replace('tmp_export_exchange', "tmp_export_exchange_$ex_id", $ex_sql);
$ex_sql = str_replace('chospital', "chospital_amp", $ex_sql);


$ex_sql = "DROP TABLE IF EXISTS tmp_export_exchange_$ex_id;\r\n" . $ex_sql;


$exchangeError = null;

try {
    $this->context->exec_sql("SET SESSION sql_mode=''");
    $this->context->exec_sql("DROP PROCEDURE IF EXISTS tmp_export_exchange_$ex_id;");

    $sp = "CREATE PROCEDURE tmp_export_exchange_$ex_id()\r\n";
    $sp.=" BEGIN \r\n";
    $ex_sql = rtrim($ex_sql);
    if ($ex_sql !== '' && substr($ex_sql, -1) !== ';') {
        $ex_sql .= ';';
    }
    $sp.= $ex_sql;
    $sp.=" \r\n END";
    $this->context->exec_sql($sp);

    $this->context->exec_sql("call tmp_export_exchange_$ex_id");
} catch (\Throwable $e) {
    Yii::error($e, __METHOD__);
    $exchangeError = $e;
}
?>


<?php
$raw = [];

if ($exchangeError === null) {
    try {
        $sql = "select * from tmp_export_exchange_$ex_id limit 1";
        $this->context->query_one($sql);
        $sql = "select * from tmp_export_exchange_$ex_id ";
        $raw = $this->context->query_all($sql);
    } catch (\Throwable $e) {
        Yii::error($e, __METHOD__);
        $exchangeError = $e;
    }
}

echo '<div class="dhdc-grid-shell">';
if ($exchangeError !== null) {
    echo '<div class="alert alert-danger dhdc-report-error">';
    echo '<strong>ไม่สามารถแสดงรายงาน Data-Exchange นี้ได้</strong>';
    echo '<div>กรุณาลองใหม่อีกครั้ง หรือติดต่อผู้ดูแลระบบ</div>';
    echo '</div>';
} elseif (empty($raw)) {
    echo '<div class="dhdc-empty-state">ไม่พบข้อมูลจากรายการ Data-Exchange นี้</div>';
} else {
    $dataProvider = new ArrayDataProvider([
        'allModels' => $raw
    ]);

    echo GridView::widget([
        'dataProvider' => $dataProvider,
        'responsiveWrap' => false,
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
        'panel' => [
            'heading' => $title
        ]
    ]);
}
echo '</div>';
?>
