<?php
/* @var $this yii\web\View */

use yii\bootstrap\Html;
use kartik\grid\GridView;
use kartik\tabs\TabsX;
use yii\i18n\Formatter;

$formatter = new Formatter();
?>

<div class="dhdc-page-header ehr-page-header">
    <div>
        <h1 class="dhdc-page-title">Electronic Health Record (EHR)</h1>
        <div class="dhdc-page-subtitle">ค้นหาและแสดงข้อมูลผู้ป่วยตามสิทธิ์และ workflow เดิมของระบบ</div>
    </div>
    <div class="dhdc-toolbar">
        <span class="dhdc-status-pill dhdc-status-running">Protected data</span>
    </div>
</div>

<div class="panel panel-info ehr-search-panel">
    <div class="panel-heading"><i class="fa fa-search"></i> ค้นหาผู้ป่วย</div>
    <div class="panel-body">
        <?= Html::beginForm(); ?>
        <div class="dhdc-filter-panel">
            <label for="cid">เลขบัตรประชาชน 13 หลัก</label>
            <input type="text" name="cid" id="cid" class="form-control dhdc-inline-control" value="<?= Html::encode($cid) ?>">
            <button class="btn btn-primary">ค้นหา</button>
        </div>
        <?= Html::endForm(); ?>
    </div>
</div>

<?php if ($cid <> '') { ?>
    <div class="panel panel-info ehr-profile-panel">
        <div class="panel-heading"><i class="fa fa-id-card-o"></i> ข้อมูลบุคคล</div>
        <div class="panel-body">
            <?php
            if ($sex == '1') {
                $ipath = Yii::$app->request->baseUrl . '/images/men.png';
            } else {
                $ipath = Yii::$app->request->baseUrl . '/images/women.png';
            }
            ?>

            <div class="ehr-profile-card">
                <div class="ehr-avatar">
                    <img src="<?= $ipath ?>" class="img-circle" alt="User Image" height="100" width="100">
                </div>
                <div class="ehr-profile-content">
                    <div class="ehr-profile-name"><?= Html::encode($tname) ?></div>
                    <div class="ehr-profile-meta">CID: <?= Html::encode($cid) ?></div>
                    <div class="ehr-profile-meta">ที่อยู่: <?= Html::encode($taddr) ?></div>
                    <div class="ehr-profile-meta">โรคประจำตัว: <?= Html::encode($chronic) ?></div>
                    <div class="ehr-profile-meta">วันเกิด: <?= Html::encode($formatter->asDate($birth)) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row ehr-workspace">
        <div class="col-md-3">
            <div class="panel panel-info ehr-visit-panel">
                <div class="panel-heading"><i class="fa fa-calendar-check-o"></i> วันที่รับบริการ</div>
                <div class="panel-body">
                    <?php
                    $gridColumns = [
                        ['class' => 'kartik\grid\SerialColumn'],
                        [
                            'attribute' => 'tdate',
                            'label' => 'วัน/เวลามารับบริการ',
                            'value' => function ($model, $key, $index, $widget) {
                                if ($model['tadmit'] === 'N') {
                                    return "<font color='000000'>" . $model['tdate'] . "</font>";
                                } else {
                                    return "<font color='ff0066'>" . $model['tdate'] . "</font>";
                                }
                            },
                            'filterType' => GridView::FILTER_COLOR,
                            'vAlign' => 'middle',
                            'format' => 'raw',
                            'width' => '150px',
                            'noWrap' => true
                        ],
                        [
                            'attribute' => 'hospcode',
                            'label' => 'สถานที่',
                            'value' => function ($model, $key) {
                                return Html::a($model['hospcode'], ['/ehr', 'hospcode' => $model['hospcode'],
                                    'pid' => $model['pid'],
                                    'an' => $model['an'],
                                    'seq' => $model['seq']], ['title' => $model['hospname']]);
                            },
                            'filterType' => GridView::FILTER_COLOR,
                            'hAlign' => 'center',
                            'format' => 'raw',
                        ]
                    ];

                    echo GridView::widget([
                        'dataProvider' => $dataProvider,
                        'autoXlFormat' => true,
                        'export' => [
                            'fontAwesome' => true,
                            'showConfirmAlert' => false,
                            'target' => GridView::TARGET_BLANK
                        ],
                        'columns' => $gridColumns,
                        'resizableColumns' => true,
                        'resizeStorageKey' => Yii::$app->user->id . '-' . date("m"),
                    ]);
                    ?>
                </div>
            </div>
        </div>

    <?php if ($hospcode <> '') { ?>
        <div class="col-md-9">
            <div class="panel panel-primary ehr-detail-panel">
                <div class="panel-heading"><i class="fa fa-th-large"></i> รายละเอียด</div>
                <div class="panel-body">
                    <?php
                    echo TabsX::widget([
                        'position' => TabsX::POS_ABOVE,
                        'align' => TabsX::ALIGN_LEFT,
                        'items' => [
                            [
                                'label' => 'อาการ/วินิจฉัย',
                                'content' => $this->render('diag', [
                                    'dataProvider' => $dataProvideri,
                                    'dateserv' => $dateserv,
                                    'cc' => $cc,
                                    'sbp' => $sbp,
                                    'dbp' => $dbp,
                                    'pr' => $pr,
                                    'rr' => $rr,
                                    'btemp' => $btemp,
                                    'timeserv' => $timeserv,
                                    'hospname' => $hospname,
                                    'hospcode' => $hospcode,
                                ]),
                                'active' => true
                            ],
                            [
                                'label' => 'ยา',
                                'content' => $this->render('drug', [
                                    'dataProvider' => $dataProviderdr,
                                ]),
                            ],
                            [
                                'label' => 'Lab',
                                'content' => $this->render('lab', [
                                    'dataProvider' => $dataProviderl,
                                ]),
                            ],
                            [
                                'label' => 'วัคซีน',
                                'content' => "รออัพเดท",
                                'headerOptions' => ['style' => 'font-weight:bold'],
                                'options' => ['id' => 'myveryownID'],
                            ],
                            [
                                'label' => 'ANC',
                                'content' => "รออัพเดท",
                                'headerOptions' => ['style' => 'font-weight:bold'],
                                'options' => ['id' => 'myveryownID'],
                            ],
                        ],
                    ]);
                    ?>
                </div>
            </div>
        </div>
<?php } ?>
    </div>
<?php } ?>

<?php
$this->registerJs('');
?>
