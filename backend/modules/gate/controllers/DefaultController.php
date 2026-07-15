<?php

namespace backend\modules\gate\controllers;

use yii\web\Controller;
use components\MyHelper;
use yii\filters\AccessControl;

/**
 * Default controller for the `gate` module
 */
class DefaultController extends Controller {

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
        ];
    }

    public function actionIndex(){
        MyHelper::exec_sql("CALL z_set_mysql_system;");
        return $this->render('index');
    }
    public function actionRbacGate(){
        return $this->render('rbac-gate');
    }
    
  

}
