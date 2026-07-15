<?php

namespace modules\ehr\controllers;

use yii\web\Controller;
use Yii;
use yii\data\ArrayDataProvider;
use components\MyHelper;
use modules\ehr\models\LogEhr;


class DefaultController extends Controller {

    public function behaviors() {
        return[
            'access' => [
                'class' => \yii\filters\AccessControl::className(), 
                'only'=>['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => MyHelper::modIsOn(),
                        'roles' => ['@'],
                    ],
                    
                ],
            ],
        ];
    }

    public function actionIndex() {
        
        //throw ConflictHttpException('ระบบ EHR ถูกปิด');
        

        // connect database
        $connection = Yii::$app->db;

        $tname = '';
        $taddr = '';
        $sex = '1';
        $chronic = '';
        $cid = '';
        $seq = '';
        $hospcode = '';
        $an = '';
        $date_serv = '';
        $cc = '';
        $sbp = '';
        $dbp = '';
        $pr = '';
        $rr = '';
        $btemp = '';
        $hospname = '';
        $timeserv = '';
        $birth = '';
        $limitedHospcode = '';

        if (!MyHelper::user_can('Admin') && !MyHelper::user_can('Pm')) {
            $limitedHospcode = $this->resolveHospcode(MyHelper::getUserHoscode(Yii::$app->user->id));
        }


        if (\Yii::$app->request->isPost) {

            $cid = $this->resolveCid(\Yii::$app->request->post('cid'));
            Yii::$app->session['cid'] = $cid;

            $log = new LogEhr();
            $log->username = \Yii::$app->user->identity->username;
            $log->patient_cid = $cid;
            $log->datetime = date('Y-m-d H:i:s');
            $log->ip = \Yii::$app->request->getUserIP();

            if ($log->save()) {
                //MyHelper::setAlert('success','......');
            }
        }
        if (Yii::$app->request->get('hospcode') !== null) {
            $cid = $this->resolveCid(Yii::$app->session['cid']);
            $seq = $this->resolveVisitToken(Yii::$app->request->get('seq'));
            $hospcode = $this->resolveHospcode(Yii::$app->request->get('hospcode'));
            $an = $this->resolveVisitToken(Yii::$app->request->get('an'));
            if ($limitedHospcode !== '' && $hospcode !== $limitedHospcode) {
                throw new \yii\web\ForbiddenHttpException('You are not allowed to access this EHR visit.');
            }
        }

        if (Yii::$app->request->get('page') !== null) {
            $cid = $this->resolveCid(Yii::$app->session['cid']);
        }

        // ข้อมูลบุคคล
        $personScopeSql = '';
        $personParams = [':cid' => $cid];
        if ($limitedHospcode !== '') {
            $personScopeSql = " AND EXISTS (
                    SELECT 1 FROM service s2
                    WHERE s2.hospcode = :person_hospcode
                    AND s2.hospcode = p.hospcode
                    AND s2.pid = p.pid
                )";
            $personParams[':person_hospcode'] = $limitedHospcode;
        }

        $sql = "SELECT p.cid,CONCAT(n.prename,p.name,' ',p.lname) AS tname,sex,
                CONCAT('เลขที่ ',h.HOUSE,' ต.',t.tambonname,' อ.',a.ampurname,' จ.',c.changwatname) AS taddr,
                CONCAT(tc.chronic,' ',i.diagename)  as chronic,birth
                FROM person p
                LEFT JOIN cprename n ON n.id_prename = p.prename
                LEFT JOIN home h ON h.HOSPCODE = p.HOSPCODE AND h.HID = p.HID
                LEFT JOIN tmp_chronic tc on tc.cid = p.cid
                LEFT JOIN cicd10tm i ON i.diagcode = tc.chronic
                LEFT JOIN campur a ON a.ampurcode = h.AMPUR AND a.changwatcode =  h.CHANGWAT
                LEFT JOIN cchangwat c  ON c.changwatcode = h.CHANGWAT
                LEFT JOIN ctambon t ON t.tamboncode = h.TAMBON AND t.ampurcode = CONCAT(c.changwatcode,a.ampurcode)
                WHERE  p.cid = :cid
                $personScopeSql
                LIMIT 1";

        $data = $connection->createCommand($sql, $personParams)
                ->queryAll();

        for ($i = 0; $i < sizeof($data); $i++) {
            $tname = $data[$i]['tname'];
            $taddr = $data[$i]['taddr'];
            $sex = $data[$i]['sex'];
            $chronic = $data[$i]['chronic'];
            $birth = $data[$i]['birth'];
        }


        // ข้อมูลวันที่มารักษา
        $visitScopeSql = '';
        $visitParams = [':cid' => $cid];
        if ($limitedHospcode !== '') {
            $visitScopeSql = " AND s.hospcode = :visit_hospcode";
            $visitParams[':visit_hospcode'] = $limitedHospcode;
        }

        $sqld = "SELECT CONCAT(s.date_serv,' ',left(time_serv,2),':',SUBSTR(time_serv,3,2),':',right(time_serv,2)) tdate,
                s.hospcode,s.seq,h.hosname as hospname,p.pid,
                IF(a.an IS NULL,'N','Y') AS tadmit,
                IF(a.an IS NULL,' ',a.AN) AS an
                FROM service s
                LEFT JOIN person p ON p.hospcode = s.hospcode AND p.pid =s.pid
                LEFT JOIN chospital  h ON h.hoscode = s.hospcode
		LEFT JOIN tmp_admission a ON a.HOSPCODE = s.HOSPCODE AND a.SEQ = s.SEQ
                WHERE  p.cid = :cid
                $visitScopeSql
                ORDER BY date_serv DESC";
        $rawData = $connection->createCommand($sqld, $visitParams)
                ->queryAll();

        $dataProvider = new ArrayDataProvider([
            //'key' => 'hoscode',
            'allModels' => $rawData,
            'pagination' => [
                'pageSize' => 10
            ],
        ]);

        //วินิจฉัย
        $sqli = "SELECT d.diagcode,diagename,diagtype
                    FROM tmp_diag_opd  d
                    LEFT JOIN cicd10tm i ON i.diagcode = d.diagcode
                    WHERE cid = :cid
                    AND seq = :seq AND hospcode = :hospcode
                    UNION ALL
                    SELECT d.diagcode,diagename,diagtype
                    FROM diagnosis_ipd d
                    LEFT JOIN cicd10tm i ON i.diagcode = d.diagcode
                    WHERE an = :an AND hospcode = :hospcode";
        $rawi = $connection->createCommand($sqli, [
                    ':cid' => $cid,
                    ':seq' => $seq,
                    ':hospcode' => $hospcode,
                    ':an' => $an,
                ])
                ->queryAll();

        $dataProvideri = new ArrayDataProvider([
            //'key' => 'hoscode',
            'allModels' => $rawi,
            'pagination' => [
                'pageSize' => 20
            ],
        ]);
        //อาการ
        $sqlcc = "SELECT date_serv,CHIEFCOMP,sbp,dbp,pr,rr,btemp,h.hosname as hospname,
                    CONCAT(left(time_serv,2),':',SUBSTR(time_serv,3,2),':',right(time_serv,2)) as time_serv
                    FROM service s
                    LEFT JOIN chospital  h ON h.hoscode = s.hospcode
                    WHERE s.hospcode = :hospcode AND seq = :seq
                    LIMIT 1";
        $datacc = $connection->createCommand($sqlcc, [
                    ':hospcode' => $hospcode,
                    ':seq' => $seq,
                ])
                ->queryAll();

        for ($i = 0; $i < sizeof($datacc); $i++) {
            $date_serv = $datacc[$i]['date_serv'];
            $cc = $datacc[$i]['CHIEFCOMP'];
            $sbp = $datacc[$i]['sbp'];
            $dbp = $datacc[$i]['dbp'];
            $pr = $datacc[$i]['pr'];
            $rr = $datacc[$i]['rr'];
            $btemp = $datacc[$i]['btemp'];
            $hospname = $datacc[$i]['hospname'];
            $hospname = str_replace("โรงพยาบาลส่งเสริมสุขภาพตำบล", "รพสต.", $hospname);
            $timeserv = $datacc[$i]['time_serv'];
        }
        //LAB
        $sqll = "SELECT l.labtest, t.labtest AS tlname,labresult
                    FROM  tmp_labfu l
                    LEFT JOIN clabtest t ON t.id_labtest = l.labtest
                    WHERE cid = :cid
                    AND seq = :seq AND hospcode = :hospcode";
        $rawl = $connection->createCommand($sqll, [
                    ':cid' => $cid,
                    ':seq' => $seq,
                    ':hospcode' => $hospcode,
                ])
                ->queryAll();

        $dataProviderl = new ArrayDataProvider([
            //'key' => 'hoscode',
            'allModels' => $rawl,
            'pagination' => [
                'pageSize' => 20
            ],
        ]);

        //ยา
        $sqldr = "SELECT d.dname,d.AMOUNT
                FROM tmp_drug_opd  d 
                WHERE cid = :cid
                      AND HOSPCODE = :hospcode AND seq = :seq
                UNION ALL
                SELECT d.dname,d.AMOUNT
                FROM drug_ipd  d
               
                WHERE an = :an AND hospcode = :hospcode";
        $rawdr = $connection->createCommand($sqldr, [
                    ':cid' => $cid,
                    ':hospcode' => $hospcode,
                    ':seq' => $seq,
                    ':an' => $an,
                ])
                ->queryAll();

        $dataProviderdr = new ArrayDataProvider([
            //'key' => 'hoscode',
            'allModels' => $rawdr,
            'pagination' => [
                'pageSize' => 20
            ],
        ]);

        return $this->render('index', ['cid' => $cid, 'tname' => $tname, 'taddr' => $taddr, 'sex' => $sex, 'chronic' => $chronic, 'birth' => $birth,
                    'dataProvider' => $dataProvider,
                    'dataProvideri' => $dataProvideri,
                    'dataProviderl' => $dataProviderl,
                    'dataProviderdr' => $dataProviderdr,
                    'dateserv' => $date_serv,
                    'cc' => $cc,
                    'sbp' => $sbp,
                    'dbp' => $dbp,
                    'pr' => $pr,
                    'rr' => $rr,
                    'btemp' => $btemp,
                    'hospcode' => $hospcode,
                    'hospname' => $hospname,
                    'timeserv' => $timeserv
        ]);
    }

    protected function resolveCid($cid) {
        $cid = trim((string)$cid);
        return preg_match('/^\d{13}$/', $cid) ? $cid : '';
    }

    protected function resolveHospcode($hospcode) {
        $hospcode = trim((string)$hospcode);
        return preg_match('/^[0-9A-Za-z_-]+$/', $hospcode) ? $hospcode : '';
    }

    protected function resolveVisitToken($value) {
        $value = trim((string)$value);
        return preg_match('/^[0-9A-Za-z_.-]+$/', $value) ? $value : '';
    }

}
