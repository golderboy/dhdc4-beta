<?php

namespace modules\hrp\controllers;

use Yii;
use modules\hrp\models\Hrpinput;
use modules\hrp\models\HrpinputSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use components\MyHelper;
/**
 * HrpinputController implements the CRUD actions for Hrpinput model.
 */
class HrpinputController extends Controller
{
    /**
     * @inheritdoc
     */
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
     * Lists all Hrpinput models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new HrpinputSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Hrpinput model.
     * @param string $HOSPCODE
     * @param string $PID
     * @param string $GRAVIDA
     * @return mixed
     */
    public function actionView($HOSPCODE, $PID, $GRAVIDA)
    {
        $HOSPCODE = base64_decode($HOSPCODE);
        $PID = base64_decode($PID);
        $GRAVIDA = base64_decode($GRAVIDA);
        return $this->render('view', [
            'model' => $this->findModel($HOSPCODE, $PID, $GRAVIDA),
        ]);
    }

    /**
     * Creates a new Hrpinput model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Hrpinput();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'HOSPCODE' => $model->HOSPCODE, 'PID' => $model->PID, 'GRAVIDA' => $model->GRAVIDA]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Hrpinput model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $HOSPCODE
     * @param string $PID
     * @param string $GRAVIDA
     * @return mixed
     */
    public function actionUpdate($HOSPCODE, $PID, $GRAVIDA)
    {
        $HOSPCODE = base64_decode($HOSPCODE);
        $PID = base64_decode($PID);
        $GRAVIDA = base64_decode($GRAVIDA);
        $model = $this->findModel($HOSPCODE, $PID, $GRAVIDA);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 
                                    'HOSPCODE' => base64_encode($model->HOSPCODE), 
                                    'PID' => base64_encode($model->PID), 
                                    'GRAVIDA' => base64_encode($model->GRAVIDA)
                                    ]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Hrpinput model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $HOSPCODE
     * @param string $PID
     * @param string $GRAVIDA
     * @return mixed
     */
    public function actionDelete($HOSPCODE, $PID, $GRAVIDA)
    {
        $this->findModel($HOSPCODE, $PID, $GRAVIDA)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Hrpinput model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $HOSPCODE
     * @param string $PID
     * @param string $GRAVIDA
     * @return Hrpinput the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($HOSPCODE, $PID, $GRAVIDA)
    {

        if (($model = Hrpinput::findOne(['HOSPCODE' => $HOSPCODE, 'PID' => $PID, 'GRAVIDA' => $GRAVIDA])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
