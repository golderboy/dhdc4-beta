<?php

namespace modules\student\controllers;

use Yii;
use modules\student\models\Student;
use modules\student\models\StudentSearch;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use components\MyHelper;
use yii\web\ConflictHttpException;

class StudentController extends Controller
{
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => MyHelper::modIsOn(),
                        'roles' => ['User']
                    ],
                ]
            ]
        ];
    }

    public function actionIndex($SCHOOLCODE=NULL,$HOSPCODE=NULL,$EDUCATIONYEAR=NULL)
    {
        $searchModel = new StudentSearch($SCHOOLCODE,$HOSPCODE,$EDUCATIONYEAR);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'SCHOOLCODE' => $SCHOOLCODE,
            'HOSPCODE' => $HOSPCODE,
            'EDUCATIONYEAR'=>$EDUCATIONYEAR,
        ]);
    }


    public function actionView($HOSPCODE, $PID, $SCHOOLCODE, $EDUCATIONYEAR, $CLASS)
    {
        return $this->render('view', [
            'model' => $this->findModel($HOSPCODE, $PID, $SCHOOLCODE, $EDUCATIONYEAR, $CLASS),
        ]);
    }


    public function actionCreate()
    {
        $model = new Student();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'HOSPCODE' => $model->HOSPCODE, 'PID' => $model->PID, 'SCHOOLCODE' => $model->SCHOOLCODE, 'EDUCATIONYEAR' => $model->EDUCATIONYEAR, 'CLASS' => $model->CLASS]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }


    public function actionUpdate($HOSPCODE, $PID, $SCHOOLCODE, $EDUCATIONYEAR, $CLASS)
    {
        $model = $this->findModel($HOSPCODE, $PID, $SCHOOLCODE, $EDUCATIONYEAR, $CLASS);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'HOSPCODE' => $model->HOSPCODE, 'PID' => $model->PID, 'SCHOOLCODE' => $model->SCHOOLCODE, 'EDUCATIONYEAR' => $model->EDUCATIONYEAR, 'CLASS' => $model->CLASS]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }


    public function actionDelete($HOSPCODE, $PID, $SCHOOLCODE, $EDUCATIONYEAR, $CLASS)
    {
        $this->findModel($HOSPCODE, $PID, $SCHOOLCODE, $EDUCATIONYEAR, $CLASS)->delete();

        return $this->redirect(['index']);
    }


    protected function findModel($HOSPCODE, $PID, $SCHOOLCODE, $EDUCATIONYEAR, $CLASS)
    {
        if (($model = Student::findOne(['HOSPCODE' => $HOSPCODE, 'PID' => $PID, 'SCHOOLCODE' => $SCHOOLCODE, 'EDUCATIONYEAR' => $EDUCATIONYEAR, 'CLASS' => $CLASS])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
