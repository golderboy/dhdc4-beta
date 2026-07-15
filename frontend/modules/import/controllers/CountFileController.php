<?php

namespace frontend\modules\import\controllers;

use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;

class CountFileController extends \yii\web\Controller {

    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['User'],
                    ],
                ],
            ],
        ];
    }

    protected function call($store_name, $arg = NULL) {
        $sql = "";
        if ($arg != NULL) {
            $sql = "call " . $store_name . "(" . $arg . ");";
        } else {
            $sql = "call " . $store_name . "();";
        }
        return $this->query_all($sql);
    }

    protected function exec_sql($sql) {
        $affect_row = \Yii::$app->db->createCommand($sql)->execute();
        return $affect_row;
    }

    protected function query_all($sql) {
        $rawData = \Yii::$app->db->createCommand($sql)->queryAll();
        return $rawData;
    }

    public function actionIndex($tb = 'service', $b_year = null) {
        //$post = Yii::$app->request->post();
        //$year = date('m') >= 10 ? date('Y') + 544 : date('Y') + 543;

        //$tb = !empty($post['tb']) ? $post['tb'] : 'service';
        //$b_year = !empty($post['b_year']) ? $post['b_year'] : $year;

        $b_year = $this->resolveBudgetYear($b_year);
        $tb = $this->resolveTableName($tb);
        
        $sql = " SELECT h.hoscode,h.hosname,t.* FROM chospital_amp h  
LEFT JOIN sys_dhdc_count_file t ON h.hoscode = t.hospcode
AND t.b_year = :b_year AND  t.tb = :tb ";
        
        $rawData = Yii::$app->db->createCommand($sql, [
            ':b_year' => $b_year,
            ':tb' => $tb,
        ])->queryAll();
        if (!empty($rawData[0])) {
            $cols = array_keys($rawData[0]);
        }
        $dataProvider = new ArrayDataProvider([
            'allModels' => $rawData,
            'sort' => !empty($cols) ? [ 'attributes' => $cols] : FALSE,
            'pagination' => false
        ]);
        
        $sql_last = "SELECT max(t.date_process) lat_process FROM sys_dhdc_count_file t 
WHERE t.tb = :tb AND t.b_year = :b_year
GROUP BY t.tb,t.b_year";
        $last_process = Yii::$app->db->createCommand($sql_last, [
            ':b_year' => $b_year,
            ':tb' => $tb,
        ])->queryScalar();
        
        return $this->render('index', [
                    'b_year' => $b_year,
                    'tb' => $tb,
                    'dataProvider'=>$dataProvider,
                    'last_process'=>$last_process
        ]);
    }

    protected function resolveBudgetYear($b_year) {
        if ($b_year !== null && $b_year !== '' && preg_match('/^\d{4}$/', (string) $b_year)) {
            return (string) $b_year;
        }

        $currentYear = Yii::$app->db->createCommand(
            'SELECT CAST(yearprocess AS UNSIGNED) + 543 FROM pk_byear LIMIT 1'
        )->queryScalar();

        return $currentYear ? (string) $currentYear : '2569';
    }

    protected function resolveTableName($tb) {
        if ($tb !== null && preg_match('/^[A-Za-z0-9_]+$/', (string) $tb)) {
            return (string) $tb;
        }

        return 'service';
    }

}
