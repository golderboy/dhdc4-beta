<?php

namespace modules\dgis\controllers;

use Yii;
use modules\dgis\models\Home;
use modules\dgis\models\HomeSearch;
use yii\web\Controller;
use yii\filters\AccessControl;
use components\MyHelper;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * HomeController implements the CRUD actions for Home model.
 */
class HomeController extends Controller
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
     * Lists all Home models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new HomeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Home model.
     * @param string $HOSPCODE
     * @param string $HID
     * @return mixed
     */
    public function actionView($HOSPCODE, $HID)
    {
        return $this->render('view', [
            'model' => $this->findModel($HOSPCODE, $HID),
        ]);
    }

    /**
     * Creates a new Home model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Home();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'HOSPCODE' => $model->HOSPCODE, 'HID' => $model->HID]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Home model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $HOSPCODE
     * @param string $HID
     * @return mixed
     */
    public function actionUpdate($HOSPCODE, $HID)
    {
        $model = $this->findModel($HOSPCODE, $HID);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'HOSPCODE' => $model->HOSPCODE, 'HID' => $model->HID]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
	
    public function actionUpdategis($HOSPCODE, $HID)
    {
        $model = $this->findModel($HOSPCODE, $HID);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'HOSPCODE' => $model->HOSPCODE, 'HID' => $model->HID]);
        } else {
            return $this->render('updategis', [
                'model' => $model,
            ]);
        }
    }
	
    /**
     * Deletes an existing Home model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $HOSPCODE
     * @param string $HID
     * @return mixed
     */
    public function actionDelete($HOSPCODE, $HID)
    {
        $this->findModel($HOSPCODE, $HID)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Home model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $HOSPCODE
     * @param string $HID
     * @return Home the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($HOSPCODE, $HID)
    {
        if (($model = Home::findOne(['HOSPCODE' => $HOSPCODE, 'HID' => $HID])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
