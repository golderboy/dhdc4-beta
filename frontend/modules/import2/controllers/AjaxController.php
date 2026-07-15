<?php

namespace frontend\modules\import2\controllers;

use yii;
use yii\helpers\Html;
use yii\helpers\FileHelper;
use frontend\modules\import\models\UploadFortythree;
use yii\db\Exception;
use frontend\modules\import\models\SysFiles;
use frontend\modules\import\models\SysFileNotImport;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class AjaxController extends \yii\web\Controller {

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
                    'import' => ['post'],
                    'import-all' => ['post'],
                    'truncate' => ['post'],
                    'update' => ['post'],
                ],
            ],
        ];
    }

    protected function add_log_err($log_err) {
        $dt = date('Y-m-d H:i:s');
        \Yii::$app->db->createCommand(
            'INSERT INTO sys_dhdc_import_error (date_err, err) VALUES (:date_err, :err)',
            [':date_err' => $dt, ':err' => (string)$log_err]
        )->execute();
    }

    protected function securityLog($event, array $context = []) {
        $context['event'] = $event;
        $context['user'] = \Yii::$app->user->identity ? \Yii::$app->user->identity->username : null;
        $context['ip'] = \Yii::$app->request->userIP;
        \Yii::warning($context, 'security.import2');
    }

    protected function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }

    protected function resolveZipFilePath($path_zip, $fortythree) {
        $fileName = basename((string)$fortythree);
        if (!preg_match('/^[A-Za-z0-9_.-]+\.zip$/i', $fileName)) {
            throw new \RuntimeException('Invalid zip filename');
        }

        $basePath = rtrim(str_replace("\\", "/", realpath($path_zip) ?: $path_zip), '/');
        $filePath = $basePath . '/' . $fileName;
        $realFile = realpath($filePath);
        if ($realFile === false || strpos(str_replace("\\", "/", $realFile), $basePath . '/') !== 0) {
            throw new \RuntimeException('Zip file is outside import directory');
        }

        return $realFile;
    }

    protected function safeUnzipDir($path_unzip, $zip_file_name) {
        $basePath = rtrim(str_replace("\\", "/", realpath($path_unzip) ?: $path_unzip), '/');
        $dirName = preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($zip_file_name));
        return $basePath . '/' . $dirName;
    }

    protected function extractZipToSafeDir(\ZipArchive $zip, $path_unzip_) {
        $safeEntries = [];
        $totalSize = 0;
        $maxFiles = 256;
        $maxTotalSize = 512 * 1024 * 1024;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false || empty($stat['name'])) {
                continue;
            }
            $name = str_replace("\\", "/", $stat['name']);
            if (substr($name, -1) === '/') {
                continue;
            }
            if (!$this->isSafeZipEntry($name)) {
                throw new \RuntimeException("Unsafe zip entry $name");
            }
            if (!preg_match('/\.txt$/i', $name)) {
                continue;
            }
            $totalSize += (int)$stat['size'];
            if (count($safeEntries) >= $maxFiles || $totalSize > $maxTotalSize) {
                throw new \RuntimeException('Zip import exceeds safety limits');
            }
            $safeEntries[] = $stat['name'];
        }

        if (empty($safeEntries)) {
            throw new \RuntimeException('Zip does not contain txt files');
        }

        FileHelper::createDirectory($path_unzip_);
        if (!$zip->extractTo($path_unzip_, $safeEntries)) {
            throw new \RuntimeException('Can not extract zip file');
        }
    }

    protected function isSafeZipEntry($name) {
        if ($name === '' || $name[0] === '/' || preg_match('/^[A-Za-z]:\//', $name)) {
            return false;
        }

        foreach (explode('/', $name) as $part) {
            if ($part === '' || $part === '.' || $part === '..') {
                return false;
            }
        }

        return true;
    }

    protected function validateTableName($table) {
        $table = strtolower((string)$table);
        if (!preg_match('/^[a-z0-9_]+$/', $table)) {
            throw new \RuntimeException("Invalid table name $table");
        }

        if (\Yii::$app->db->getTableSchema($table, true) === null) {
            throw new \RuntimeException("Table $table does not exist");
        }

        return $table;
    }

    protected function loaddata($txtfile, $table, $zip_file_name, $stat = 1) {
        $table = $this->validateTableName($table);
        
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            //raw
            $sql = "LOAD DATA LOCAL INFILE " . \Yii::$app->db->quoteValue($txtfile);
            $sql.= " REPLACE INTO TABLE " . \Yii::$app->db->quoteTableName($table);
            //$sql.= " CHARACTER SET UTF8";
            $sql.= " FIELDS TERMINATED BY '|'  LINES TERMINATED BY '\r\n' IGNORE 1 LINES";
            $raw = \Yii::$app->db->createCommand($sql)->execute();

            if ($stat == 1) {
                //tmp                        
                $tmpTable = $this->validateTableName("dhdc_tmp_$table");
                $sql = "LOAD DATA LOCAL INFILE " . \Yii::$app->db->quoteValue($txtfile);
                $sql.= " REPLACE INTO TABLE " . \Yii::$app->db->quoteTableName($tmpTable);
                //$sql.= " CHARACTER SET UTF8";
                $sql.= " FIELDS TERMINATED BY '|'  LINES TERMINATED BY '\r\n' IGNORE 1 LINES";
                $sql.= " SET NOTE1=" . \Yii::$app->db->quoteValue($zip_file_name) . ",NOTE2=NOW()";
                $tmp = \Yii::$app->db->createCommand($sql)->execute();

                // count
                $sql = " REPLACE  INTO sys_count_import_file  (
                                 SELECT IF(NOTE1 is NULL," . \Yii::$app->db->quoteValue($zip_file_name) . "," . \Yii::$app->db->quoteValue($zip_file_name) . ")," . \Yii::$app->db->quoteValue($table) . ",COUNT(*),NOW(),'','','' FROM " . \Yii::$app->db->quoteTableName($tmpTable) . "
                                 WHERE NOTE1 = " . \Yii::$app->db->quoteValue($zip_file_name) . "
                            );  ";
                \Yii::$app->db->createCommand($sql)->execute();

                $sql = "DELETE FROM " . \Yii::$app->db->quoteTableName($tmpTable) . " WHERE NOTE1 = " . \Yii::$app->db->quoteValue($zip_file_name);
                \Yii::$app->db->createCommand($sql)->execute();
            }

            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        }
    }

    public function actionIndex() {
        return $this->render('index');
    }

    public function actionImport($fortythree, $upload_date, $upload_time, $id) {

        ini_set('max_execution_time', 0);

        $zip = new \ZipArchive();

        if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
            //$path_zip = 'fortythree';
            //$path_unzip = 'unzip';
            $path_zip = \Yii::getAlias('@webroot') . "/fortythree";
            $path_unzip = \Yii::getAlias('@webroot') . "/unzip";
        } else {
            $path_zip = \Yii::getAlias('@webroot') . "/fortythree/";
            $path_unzip = \Yii::getAlias('@webroot') . "/unzip/";
        }

        $model = UploadFortythree::findOne($id);
        $model->note2 = 'กำลังนำเข้า';
        $model->update(FALSE);

        $file_zip = $this->resolveZipFilePath($path_zip, $fortythree);
        $zip_file_name = basename($file_zip);
        $this->securityLog('import_start', ['file' => $zip_file_name, 'id' => $id]);

        if ($zip->open($file_zip, \ZipArchive::CHECKCONS) !== TRUE) {

            $model->note2 = 'zip err';
            $model->update(FALSE);
            $this->add_log_err("Can not open $file_zip");
            $this->securityLog('import_zip_open_failed', ['file' => $zip_file_name, 'id' => $id]);
            return 'ไม่สามารถเปิดไฟล์นำเข้าได้';
        }
        $path_unzip_ = $this->safeUnzipDir($path_unzip, $zip_file_name);
        $this->extractZipToSafeDir($zip, $path_unzip_);
        $zip->close();
        // อ่านไฟล์และนำเข้า

        $txtFiles = FileHelper::findFiles($path_unzip_, [
                    'only' => ['*.txt', '*.TXT'],
                    'recursive' => TRUE,
        ]);

        foreach ($txtFiles as $file) {

            $info = pathinfo($file);

            $table = strtolower($info['filename']);
            //echo "\t reading..." . $table . "\r\n";

            $file = str_replace("\\", "/", $file);
            $model->note3 = basename($file);

            $model->update(FALSE);
            // importing
            try {
                $this->loaddata($file, $table, $zip_file_name, 1);
                unlink($file);
            } catch (Exception $ex) {
                $log_err = $ex->getMessage();
                $this->add_log_err($log_err);
                $this->securityLog('import_failed', ['file' => $zip_file_name, 'id' => $id, 'error' => $log_err]);
                $this->deleteDirectory($path_unzip_);
                $model->note2 = 'ผิดพลาด';
                $model->update(FALSE);
                return 'ไม่สามารถนำเข้าข้อมูลได้ กรุณาตรวจสอบบันทึกข้อผิดพลาด';
                //break;
            }


            // end importing
        }

        $this->deleteDirectory($path_unzip_);
        unlink($file_zip);

        //จบอ่านและนำเข้า
        $model->note3 = '';
        $model->note2 = 'OK';
        $model->update(FALSE);
        $this->securityLog('import_success', ['file' => $zip_file_name, 'id' => $id]);

        return "นำเข้า " . $fortythree . " สำเร็จ";
    }

    public function actionImportAll($fortythree, $upload_date, $upload_time) {

        ini_set('max_execution_time', 0);

        $zip = new \ZipArchive();

        if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
            //$path_zip = 'fortythree';
            //$path_unzip = 'unzip';
            $path_zip = \Yii::getAlias('@webroot') . "/fortythree";
            $path_unzip = \Yii::getAlias('@webroot') . "/unzip";
        } else {
            $path_zip = \Yii::getAlias('@webroot') . "/fortythree/";
            $path_unzip = \Yii::getAlias('@webroot') . "/unzip/";
        }

        $file_zip = $this->resolveZipFilePath($path_zip, $fortythree);
        $file_size = number_format(filesize($file_zip) / (1024 * 1024), 3);
        $zip_file_name = basename($file_zip);
        $this->securityLog('import_all_start', ['file' => $zip_file_name]);

        if ($zip->open($file_zip, \ZipArchive::CHECKCONS) !== TRUE) {
            $this->add_log_err("Can not open $file_zip.");
            $this->securityLog('import_all_zip_open_failed', ['file' => $zip_file_name]);
            return 'ไม่สามารถเปิดไฟล์นำเข้าได้';
        }
        $path_unzip_ = $this->safeUnzipDir($path_unzip, $zip_file_name);
        $this->extractZipToSafeDir($zip, $path_unzip_);
        $zip->close();
        // อ่านไฟล์และนำเข้า

        $txtFiles = FileHelper::findFiles($path_unzip_, [
                    'only' => ['*.txt', '*.TXT'],
                    'recursive' => TRUE,
        ]);

        foreach ($txtFiles as $file) {

            $info = pathinfo($file);

            $table = strtolower($info['filename']);
            //echo "\t reading..." . $table . "\r\n";

            $file = str_replace("\\", "/", $file);

            // importing
            try {
                $this->loaddata($file, $table, $zip_file_name, 0);
                unlink($file);
            } catch (Exception $ex) {
                $log_err = $ex->getMessage();
                $this->add_log_err($log_err);
                $this->securityLog('import_all_failed', ['file' => $zip_file_name, 'error' => $log_err]);
                $this->deleteDirectory($path_unzip_);
                return 'ไม่สามารถนำเข้าข้อมูลได้ กรุณาตรวจสอบบันทึกข้อผิดพลาด';
            }



            // end importing
        }

        $this->deleteDirectory($path_unzip_);
        unlink($file_zip);

        //จบอ่านและนำเข้า

        $upload = new UploadFortythree;
        $upload->file_name = $fortythree;
        $upload->file_size = $file_size;
        $upload->upload_date = date('Y-m-d');
        $upload->upload_time = date('H:i:s');
        $upload->note2 = 'OK';
        $upload->note3 = 'import all';
        $upload->save(FALSE);
        $this->securityLog('import_all_success', ['file' => $zip_file_name]);

        return $fortythree;
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

            }

            \Yii::$app->db->createCommand("truncate sys_upload_fortythree;")->execute();
            \Yii::$app->db->createCommand("truncate sys_count_import;")->execute();
            \Yii::$app->db->createCommand("truncate  sys_count_import_file;")->execute();
            return 'ล้างข้อมูลเรียบร้อยแล้ว';
        } else {
            return 'คุณไม่มีสิทธิ์ดำเนินการนี้';
        }
    }

    public function actionUpdate() {
        ini_set('max_execution_time', 0);
        if (!\Yii::$app->user->isGuest) {
            $user = Html::encode(Yii::$app->user->identity->username);
            if ($user === 'admin') {
                $sql = "CALL zz_update_upload_log;";
                \Yii::$app->db->createCommand($sql)->execute();
                //return;
                $sql = " select table_name from information_schema.tables  "
                        . " where table_schema='dhdc' AND TABLE_NAME like 'tmp_%'; ";
                $raw = \Yii::$app->db->createCommand($sql)->queryAll();
                //\yii\helpers\VarDumper::dump($raw);
                foreach ($raw as $tb) {
                    $old = $tb['table_name'];
                    $new = "dhdc_" . $old;
                    $sql = " RENAME TABLE $old TO $new ";
                    \Yii::$app->db->createCommand($sql)->execute();
                }

                return 'ปรับปรุงข้อมูลเรียบร้อยแล้ว';
            }
        }

        return 'คุณไม่มีสิทธิ์ดำเนินการนี้';
    }

}
