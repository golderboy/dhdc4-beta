<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;
use yii\data\ArrayDataProvider;

$this->params['breadcrumbs'][] = 'ตรวจสอบประวัติวัคซีน';
?>
<div class="vaccine-default-index">
    <div class="panel panel-danger">
        <div class="panel-heading">ตรวจสอบ</div>
        <div class="panel-body">
            <?php
            $cid = trim(\Yii::$app->request->post('cid'));
            $bdate = trim(\Yii::$app->request->post('bdate'));
            $bdate_begin = trim(\Yii::$app->request->post('bdate_begin'));
            $bdate_end = trim(\Yii::$app->request->post('bdate_end'));
            ?>
            <div class="row">
                <div class="col-md-6">
                    <?php $form = ActiveForm::begin(); ?>
                    <?= Html::textInput('cid', $cid, ['placeholder' => 'เลข 13 หลัก']) ?>
                    <?= Html::submitButton('ค้นหา'); ?>
                    <?php ActiveForm::end(); ?>
                </div>
                <div class="col-md-6">
                    <?php $form = ActiveForm::begin(); ?>
                    เกิดระหว่าง:
                    <?= Html::textInput('bdate_begin', '', ['placeholder' => 'yyyy-mm-dd']) ?>
                    ถึง <?= Html::textInput('bdate_end', '', ['placeholder' => 'yyyy-mm-dd']) ?>
                    <?= Html::hiddenInput('bdate', 'yes') ?>
                    <?= Html::submitButton('ค้นหา'); ?>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
            <br>
            <?php if ($cid): ?>    
                ผลการค้นหาด้วย 13 หลัก
                <?php
                $sql = "SELECT p.HOSPCODE,p.PID,p.CID,concat(p.`NAME`,' ',p.LNAME) NAME,p.SEX,p.BIRTH,TIMESTAMPDIFF(MONTH,p.BIRTH,CURDATE()) AGE_MON

,t.DATE_SERV,concat(t.VACCINETYPE,'-',v.engvaccine) VACC 
,t.VACCINEPLACE
,TIMESTAMPDIFF(MONTH,p.BIRTH,t.DATE_SERV) VAC_MON

FROM epi t
LEFT JOIN t_person_cid p on t.HOSPCODE = p.HOSPCODE AND t.PID = p.PID
LEFT JOIN cvaccinetype v ON v.vaccinecode = t.VACCINETYPE
HAVING VACC is not NULL
AND CID = '$cid'
ORDER BY t.DATE_SERV ASC
";
                try {
                    $raw = \Yii::$app->db->createCommand($sql)->queryAll();
                } catch (\yii\db\Exception $e) {
                    throw new yii\web\ForbiddenHttpException('ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่อีกครั้ง');
                }
                $dataProvider = new ArrayDataProvider([
                    'allModels' => $raw
                ]);
                ?>
                
                <?php
                $info ='';
                if(count($raw)>0){
                $info = $raw[0]['HOSPCODE'].'-'.$raw[0]['PID'].'  ชื่อ '.$raw[0]['NAME']
                        .',เกิด '.$raw[0]['BIRTH']
                        .' เพศ '.$raw[0]['SEX']
                        .' อายุปัจจุบัน '.$raw[0]['AGE_MON'].' ด.' ;
                }
                echo GridView::widget([
                    'panel' => ['before' => $info],
                    'responsiveWrap' => false,
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        'DATE_SERV:text:วดป.ฉีด',
                        'VAC_MON:text:อายุ ณ วันฉีด(ด)',
                        'VACC:text:วัคซีน',
                        'VACCINEPLACE:text:ฉีดที่',
                        
                    ]
                ]);
                ?>

            <?php endif; ?>

            <?php if ($bdate): ?>
               <?php
                $sql = "SELECT concat(p.`NAME`,' ',p.LNAME) name,t.* from t_person_epi t 
LEFT JOIN t_person_cid p on t.cid = p.CID
WHERE t.birth BETWEEN '$bdate_begin' AND '$bdate_end' order by t.birth ASC";
                try {
                    $raw = \Yii::$app->db->createCommand($sql)->queryAll();
                } catch (\yii\db\Exception $e) {
                    throw new yii\web\ForbiddenHttpException('ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่อีกครั้ง');
                }
                $dataProvider = new ArrayDataProvider([
                    'allModels' => $raw
                ]);
                ?>
                
                <?php
                $info = 'รายการที่พบ' ;
                echo GridView::widget([
                    'panel' => ['before' => $info],
                    'responsiveWrap' => false,
                    'dataProvider' => $dataProvider,
                   
                ]);
                ?>
            <?php endif; ?>
        </div>
    </div>
</div>
