<?php

namespace modules\student\controllers;

use Yii;
use modules\student\models\School;
use modules\student\models\SchoolSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii\filters\AccessControl;
use components\MyHelper;
use yii\web\ConflictHttpException;
/**
 * SchoolController implements the CRUD actions for School model.
 */
class SchoolController extends Controller
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

    /**
     * Lists all School models.
     * @return mixed
     */
    public function actionIndex($SCHOOLCODE=NULL,$HOSPCODE=NULL,$BYEAR=NULL)
    {
        $searchModel = new SchoolSearch($SCHOOLCODE,$HOSPCODE,$BYEAR);
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'SCHOOLCODE' => $SCHOOLCODE,
            'HOSPCODE' => $HOSPCODE,
            'BYEAR' => $BYEAR,
        ]);
    }

    /**
     * Displays a single School model.
     * @param string $HOSPCODE
     * @param string $VID
     * @param string $SCHOOLCODE
     * @return mixed
     */
    public function actionView($HOSPCODE, $VID, $SCHOOLCODE)
    {
        return $this->render('view', [
            'model' => $this->findModel($HOSPCODE, $VID, $SCHOOLCODE),
        ]);
    }

    /**
     * Creates a new School model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new School();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'HOSPCODE' => $model->HOSPCODE, 'VID' => $model->VID, 'SCHOOLCODE' => $model->SCHOOLCODE]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing School model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $HOSPCODE
     * @param string $VID
     * @param string $SCHOOLCODE
     * @return mixed
     */
    public function actionUpdate($HOSPCODE, $VID, $SCHOOLCODE)
    {
        $model = $this->findModel($HOSPCODE, $VID, $SCHOOLCODE);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'HOSPCODE' => $model->HOSPCODE, 'VID' => $model->VID, 'SCHOOLCODE' => $model->SCHOOLCODE]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing School model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $HOSPCODE
     * @param string $VID
     * @param string $SCHOOLCODE
     * @return mixed
     */
    public function actionDelete($HOSPCODE, $VID, $SCHOOLCODE)
    {
        $this->findModel($HOSPCODE, $VID, $SCHOOLCODE)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the School model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $HOSPCODE
     * @param string $VID
     * @param string $SCHOOLCODE
     * @return School the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($HOSPCODE, $VID, $SCHOOLCODE)
    {
        if (($model = School::findOne(['HOSPCODE' => $HOSPCODE, 'VID' => $VID, 'SCHOOLCODE' => $SCHOOLCODE])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
