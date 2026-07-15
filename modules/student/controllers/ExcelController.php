<?php

namespace modules\student\controllers;

use yii;
use modules\student\components\Excel;
use modules\student\models\School;
use modules\student\models\Student;
//use frontend\modules\cfo\models\LogImport;
use modules\student\models\UploadForm;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\ConflictHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use components\MyHelper;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;

/**
 * Default controller for the `import` module
 */
class ExcelController extends Controller {


    public function behaviors()
    {
			return [
				'access' => [
					'class' => AccessControl::className(),
					'rules' => [
						[
							'allow' => MyHelper::modIsOn(),
							'roles' => ['Admin']
						],
					]
				]
			];
    }
	 
	public function actionSchool() {
        $mUpload = new UploadForm();

        if (\Yii::$app->request->isPost) {
            $mUpload->dataFile = UploadedFile::getInstance($mUpload, 'dataFile');
            if ($mUpload->upload()) {
                $filename = $mUpload->fname;

                $excel = new Excel($filename);
                $array = $excel->toArray();
				$rec = 0;
					foreach ($array as $value) {
						if( $value['HOSPCODE'] != "" && $value['SCHOOLCODE'] != "" ){
							$count = School::find()
								->where(['HOSPCODE' => $value['HOSPCODE'],
										'SCHOOLCODE' => $value['SCHOOLCODE']		
								])
								->count();
							if($count < 1){
								$value['D_UPDATE'] = date('Y-m-d H:i:s', SpreadsheetDate::excelToTimestamp($value['D_UPDATE']));
								//$value['Month'] = str_pad($value['Month'],2,"0",STR_PAD_LEFT);
								$data = new School();
								$data->attributes = $value;
								$data->save(FALSE);
							$rec++;
							}
						}
					}
					if($rec < 1){
						\Yii::$app->session->setFlash('danger', "ไม่อนุญาติให้นำเข้าข้อมูลซ้ำ ");
						return $this->redirect(['/student/excel/school']);
					}else{
						Yii::$app->session->setFlash('success', "นำเข้าสำเร็จ!!!");
						return $this->redirect(['/student/school/index']);
					}

				}	
			}

        return $this->render('school', ['mUpload' => $mUpload]);
	}
	
	public function actionStudent() {
        $mUpload = new UploadForm();

        if (\Yii::$app->request->isPost) {
            $mUpload->dataFile = UploadedFile::getInstance($mUpload, 'dataFile');
            if ($mUpload->upload()) {
                $filename = $mUpload->fname;

                $excel = new Excel($filename);
                $array = $excel->toArray();
				$rec = 0;
					foreach ($array as $value) {
						if( $value['HOSPCODE'] != "" && $value['SCHOOLCODE'] != "" ){
							$count = Student::find()
								->where(['HOSPCODE' => $value['HOSPCODE'],
										'SCHOOLCODE' => $value['SCHOOLCODE'],
										'PID' => $value['PID'],
										'EDUCATIONYEAR' => $value['EDUCATIONYEAR'],
										'CLASS' => $value['CLASS'],		
								])
								->count();
							if($count < 1){
								$value['D_UPDATE'] = date('Y-m-d H:i:s', SpreadsheetDate::excelToTimestamp($value['D_UPDATE']));
								//$value['Month'] = str_pad($value['Month'],2,"0",STR_PAD_LEFT);
								$data = new Student();
								$data->attributes = $value;
								$data->save(FALSE);
							$rec++;
							}
						}
					}
					if($rec < 1){
						\Yii::$app->session->setFlash('danger', "ไม่อนุญาติให้นำเข้าข้อมูลซ้ำ ");
						return $this->redirect(['/student/excel/student']);
					}else{
						Yii::$app->session->setFlash('success', "นำเข้าสำเร็จ!!!");
						return $this->redirect(['/student/student/index']);
					}

				}	
			}

        return $this->render('student', ['mUpload' => $mUpload]);
	}

}
