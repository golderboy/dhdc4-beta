<?php

namespace frontend\modules\import\controllers;

use yii\data\ArrayDataProvider;
use yii\web\Controller;

/**
 * Default controller for the `import` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionDashboard()
    {
        $uploadSummary = \Yii::$app->db->createCommand("
            SELECT
                COUNT(*) AS total_uploads,
                SUM(CASE WHEN note2 = 'OK' THEN 1 ELSE 0 END) AS ok_uploads,
                SUM(CASE WHEN note2 LIKE '%ผิดพลาด%' THEN 1 ELSE 0 END) AS error_uploads,
                SUM(CASE WHEN note2 LIKE '%รอนำเข้า%' OR note2 LIKE '%เธฃเธญเธเธณเน€เธเนเธฒ%' THEN 1 ELSE 0 END) AS pending_uploads
            FROM sys_upload_fortythree
        ")->queryOne();

        $latestUpload = \Yii::$app->db->createCommand("
            SELECT id, hospcode, file_name, file_size, upload_date, upload_time, note2, note3
            FROM sys_upload_fortythree
            ORDER BY upload_date DESC, upload_time DESC, id DESC
            LIMIT 1
        ")->queryOne();

        $countSummary = \Yii::$app->db->createCommand("
            SELECT
                COUNT(*) AS imported_files,
                COALESCE(SUM(TOTAL_RECORD), 0) AS imported_records,
                MAX(IMPORT_DATE) AS latest_import_date
            FROM sys_count_import_file
        ")->queryOne();

        $processStatus = \Yii::$app->db->createCommand("
            SELECT
                (SELECT is_running FROM sys_process_running LIMIT 1) AS is_running,
                (SELECT fnc_name FROM sys_check_process LIMIT 1) AS fnc_name,
                (SELECT time FROM sys_check_process LIMIT 1) AS process_time,
                (SELECT last_time FROM last_transform LIMIT 1) AS last_transform,
                (SELECT last_time FROM last_err_check LIMIT 1) AS last_err_check
        ")->queryOne();

        $fileCounts = \Yii::$app->db->createCommand("
            SELECT FILE_NAME, TOTAL_RECORD, IMPORT_DATE
            FROM sys_count_import_file
            ORDER BY TOTAL_RECORD DESC, FILE_NAME ASC
            LIMIT 15
        ")->queryAll();

        $qcRows = \Yii::$app->db->createCommand("
            SELECT file_name, qc
            FROM sys_files
            ORDER BY qc ASC, file_name ASC
            LIMIT 15
        ")->queryAll();

        return $this->render('dashboard', [
            'uploadSummary' => $uploadSummary,
            'latestUpload' => $latestUpload,
            'countSummary' => $countSummary,
            'processStatus' => $processStatus,
            'fileCountProvider' => new ArrayDataProvider([
                'allModels' => $fileCounts,
                'pagination' => false,
            ]),
            'qcProvider' => new ArrayDataProvider([
                'allModels' => $qcRows,
                'pagination' => false,
            ]),
        ]);
    }
  
}
