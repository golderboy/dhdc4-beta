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
 * Default controller for the `hpr` module
 */
class DefaultController extends Controller
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

    public function actionIndex()
    {
        $searchModel = new HrpinputSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('/hrpinput/index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

}
