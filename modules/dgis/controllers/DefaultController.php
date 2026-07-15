<?php

namespace modules\dgis\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;
use components\MyHelper;

/**
 * Default controller for the `dgis` module
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
        return $this->render('dgis/home/index');
    }
}
