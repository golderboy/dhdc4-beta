<?php

namespace modules\student\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;
use components\MyHelper;
use yii\web\ConflictHttpException;


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
        return $this->render('index');
    }
}
