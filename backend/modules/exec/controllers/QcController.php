<?php

namespace backend\modules\exec\controllers;

use yii\web\Controller;
use common\models\config\SysProcessRunning;
use frontend\modules\import\models\SysFiles;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class QcController extends Controller {

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['Admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'exec' => ['post'],
                    'truncate' => ['post'],
                ],
            ],
        ];
    }

    public function actionExec() {
        $this->exec_sql("SET SESSION sql_mode='';");
        $this->exec_sql("SET NAMES utf8 COLLATE utf8_general_ci;");
        $this->exec_sql("SET SESSION character_set_collations='utf8mb3=utf8mb3_general_ci,utf8mb4=utf8mb4_general_ci';");
        $running = SysProcessRunning::find()->one();
        if ($running->is_running == 'true') {
            return 'ระบบกำลังประมวลผล กรุณารอสักครู่';
        }

        //sleep(5);
        $sql = "call err_all();";
        try {
            $this->exec_sql($sql);
        } catch (\yii\db\Exception $e) {
            \Yii::error($e, __METHOD__);
            return 'ไม่สามารถประมวลผลได้ กรุณาลองใหม่อีกครั้ง';
        }
        return 'ประมวลผลเสร็จสมบูรณ์';
    }

    public function actionTruncate() {
        ini_set('max_execution_time', 0);

        if (\Yii::$app->user->can('Admin')) {

            $model = SysFiles::find()->asArray()->all();
            foreach ($model as $m) {
                $table = $m['file_name'];
                $sql = "truncate $table";
                \Yii::$app->db->createCommand($sql)->execute();

                //$sql = "truncate dhdc_tmp_$table";
                //\Yii::$app->db->createCommand($sql)->execute();
                //echo $sql . "<br>";
            }

            $this->exec_sql("truncate sys_upload_fortythree;");
            $this->exec_sql("truncate sys_count_import;");
            $this->exec_sql("truncate  sys_count_import_file;"); 
            sleep(3);
            $this->exec_sql("call err_all();");
            sleep(3);
            $this->exec_sql("call sys_transform_all();");
            
            return 'ล้างและประมวลผลข้อมูลเรียบร้อยแล้ว';
        } else {
            return 'คุณไม่มีสิทธิ์ดำเนินการนี้';
        }
    }

    protected function exec_sql($sql = NULL) {
        return \Yii::$app->db->createCommand($sql)->execute();
    }

    protected function query_all($sql = NULL) {
        return \Yii::$app->db->createCommand($sql)->queryAll();
    }

    protected function query_one($sql = NULL) {
        return \Yii::$app->db->createCommand($sql)->queryOne();
    }

}
