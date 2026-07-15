<?php

namespace backend\modules\hdcreportsetup\controllers;

use Yii;
use frontend\modules\hdc\models\Hdcsql;
use frontend\modules\hdc\models\HdcsqlSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\filters\AccessControl;


class HdcsqlController extends Controller {

    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'create' => ['get', 'post'],
                    'update' => ['get', 'post'],
                ],
            ],
            
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
        ];
    }
    
    

    

    
    public function actionIndex() {

        $this->layout = 'hdc';
        $searchModel = new HdcsqlSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Hdcsql model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id) {
        $this->layout = 'hdc';
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Hdcsql model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
       
        //$this->identify_key();

        $this->layout = 'hdc';
        $model = new Hdcsql();



        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $cat_id = Yii::$app->request->post('cat_id', '');
            $new_id = $model->rpt_id;
            $report_name = $model->rpt_name;

            Yii::$app->db->createCommand(
                'REPLACE INTO sys_report (cat_id, id, report_name) VALUES (:cat_id, :id, :report_name)',
                [':cat_id' => $cat_id, ':id' => $new_id, ':report_name' => $report_name]
            )->execute();

            Yii::$app->db->createCommand(
                'REPLACE INTO sys_report_dhdc (cat_id, id, report_name) VALUES (:cat_id, :id, :report_name)',
                [':cat_id' => $cat_id, ':id' => $new_id, ':report_name' => $report_name]
            )->execute();

            Yii::$app->db->createCommand(
                'DELETE FROM sys_report_drop WHERE id = :id',
                [':id' => $new_id]
            )->execute();

            return $this->redirect(['view', 'id' => $new_id]);
        } else {
            return $this->render('create', [

                        'model' => $model,
            ]);
        }
    }

    public function actionUpdate($id) {
        
        $this->layout = 'hdc';


        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $cat_id = Yii::$app->request->post('cat_id', '');
            $new_id = $model->rpt_id;
            $report_name = $model->rpt_name;

            Yii::$app->db->createCommand(
                'UPDATE sys_report SET cat_id = :cat_id, id = :new_id, report_name = :report_name WHERE id = :old_id',
                [':cat_id' => $cat_id, ':new_id' => $new_id, ':report_name' => $report_name, ':old_id' => $id]
            )->execute();

            Yii::$app->db->createCommand(
                'UPDATE sys_report_dhdc SET cat_id = :cat_id, id = :new_id, report_name = :report_name WHERE id = :old_id',
                [':cat_id' => $cat_id, ':new_id' => $new_id, ':report_name' => $report_name, ':old_id' => $id]
            )->execute();

            return $this->redirect(['view', 'id' => $new_id]);
        } else {
            return $this->render('update', [
                        'model' => $model,
            ]);
        }
    }

    public function actionDelete($id) {
        

        $this->findModel($id)->delete();
        Yii::$app->db->createCommand(
            'DELETE FROM sys_report_dhdc WHERE id = :id',
            [':id' => $id]
        )->execute();
        return $this->redirect(['index']);
    }

    protected function findModel($id) {
        if (($model = Hdcsql::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionExport($id = NULL) {
        ini_set('max_execution_time', 5 * 60);


        $con_db = \Yii::$app->db;

        $raw = $con_db->createCommand(
            'SELECT * FROM hdc_rpt_sql WHERE rpt_id = :id',
            [':id' => $id]
        )->queryAll();
        if (empty($raw)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $cols = array_keys($raw[0]);



        $insert_val = '';
        foreach ($cols as $value) {
            if (empty($raw[0][$value]) or trim($raw[0][$value]) == '') {
                $val = "NULL,";
            } else {
                //$val = "'" . mysql_escape_string($raw[0][$value]) . "',";
                $val = \Yii::$app->db->quoteValue($raw[0][$value]) . ",";
            }
            $insert_val.=$val;
        }

        $cols = implode(",", $cols);
        $cols = "($cols)";
        $insert_val = rtrim($insert_val, ",");
        $insert_val = "( $insert_val )";

        $full1 = "SET NAMES 'utf8' COLLATE 'utf8_general_ci';\r\n";
        $full1.= "REPLACE INTO hdc_rpt_sql $cols VALUES $insert_val;\r\n";
        //echo $full;
//////////////


        $raw = $con_db->createCommand(
            'SELECT * FROM sys_report_dhdc WHERE id = :id',
            [':id' => $id]
        )->queryAll();
        if (empty($raw)) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        $cols = array_keys($raw[0]);



        $insert_val = '';
        foreach ($cols as $value) {

            if (empty($raw[0][$value]) or trim($raw[0][$value]) == '') {
                $val = "'',";
            } else {
                //$val = "'" . mysql_escape_string($raw[0][$value]) . "',";
                $val = \Yii::$app->db->quoteValue($raw[0][$value]) . ",";
            }
            $insert_val.=$val;
        }

        $cols = implode(",", $cols);
        $cols = "($cols)";
        $insert_val = rtrim($insert_val, ",");
        $insert_val = "( $insert_val )";

        $quotedId = Yii::$app->db->quoteValue($id);
        $full2 = "DELETE FROM sys_report_dhdc WHERE id = $quotedId;\r\n";
        $full2.= "DELETE FROM sys_report_drop WHERE id = $quotedId;\r\n";
        $full2.= "REPLACE INTO sys_report_dhdc $cols VALUES $insert_val;";

        $date = date('YmdHis');
        $filename = "rpt_script_$date.sql";
        $txt = $full1 . "\r\n" . $full2;
        return Yii::$app->response->sendContentAsFile($txt, $filename);
    }

    
    
}
