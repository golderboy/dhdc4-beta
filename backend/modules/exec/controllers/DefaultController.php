<?php

namespace backend\modules\exec\controllers;

use yii\web\Controller;
use yii\data\ArrayDataProvider;
use common\models\config\TranformLog;
use backend\models\SysCheckProcess;
use common\models\config\SysProcessRunning;
use components\MyHelper;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;

/**
 * Default controller for the `gate` module
 */
class DefaultController extends Controller {

    /**
     * Renders the index view for the module
     * @return string
     */
    
      public function behaviors() {

        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => [],
                'rules' => [
                    [
                        //'actions' => ['*'],
                        'allow' => true,
                        'roles' => ['Admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'check-process' => ['post'],
                ],
            ],
        ];
    }
    public function actionIndex() {
        $sql = "SHOW FULL PROCESSLIST;";
        $raw = \Yii::$app->db->createCommand($sql)->queryAll();
        $dataProvider = new ArrayDataProvider([
            'allModels' => $raw
        ]);
        $Tranform = TranformLog::find()->orderBy(['id' => SORT_DESC])->one();
        $current_process = 'ว่าง';
        $time_process = 'ว่าง';
        if ($Tranform) {
            $current_process = $Tranform->p_name;
        }

        $sys_process = SysCheckProcess::find()->one();
        $time_process = $sys_process->time;

        return $this->render('index', [
                    'dataProvider' => $dataProvider,
                    'current_process' => $current_process,
                    'time_process' => $time_process,
                    'sys_process' => $sys_process->fnc_name
        ]);
    }

    public function actionCheckProcess($p) {
        if ($p === 'end') {
            return 'ประมวลผลทั้งหมดเรียบร้อยแล้ว';
        }
        if (!preg_match('/^[A-Za-z0-9_]+$/', (string)$p)) {
            throw new BadRequestHttpException('ข้อมูลกระบวนการไม่ถูกต้อง');
        }
        $running = SysProcessRunning::find()->one();
        if ($running->is_running == 'true') {
            return 'ระบบกำลังประมวลผล กรุณารอสักครู่';
        }

        //sleep(5);
        $sql = "CALL `$p`";
        try {
            MyHelper::exec_sql($sql);
        } catch (yii\db\Exception $e) {
            \Yii::error($e, __METHOD__);
            return 'ไม่สามารถประมวลผลได้ กรุณาลองใหม่อีกครั้ง';
        }
        return 'ประมวลผลเสร็จสมบูรณ์';
    }

}
