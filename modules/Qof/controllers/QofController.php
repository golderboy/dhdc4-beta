<?php

namespace modules\Qof\controllers;

use yii\web\Controller;
use yii\data\ActiveDataProvider;
use modules\Qof\models\Dhdcqofreport;
use modules\Qof\models\Qof11data;
use modules\Qof\models\Qof11summary;
use modules\Qof\models\Qof21data;
use modules\Qof\models\Qof21summary;
use modules\Qof\models\Qof31data;
use modules\Qof\models\Qof31summary;
use modules\Qof\models\Qof41data;
use modules\Qof\models\Qof41summary;
use modules\Qof\models\Qof51data;
use modules\Qof\models\Qof51summary;
use modules\Qof\models\Qof52data;
use modules\Qof\models\Qof52summary;
use modules\Qof\models\Qof61data;
use modules\Qof\models\Qof61summary;
use modules\Qof\models\Qof71data;
use modules\Qof\models\Qof71summary;
use modules\Qof\models\Qof72data;
use modules\Qof\models\Qof72summary;
use modules\Qof\models\Qof73data;
use modules\Qof\models\Qof73summary;
use modules\Qof\models\Qof81data;
use modules\Qof\models\Qof81summary;
use modules\Qof\models\Qof82data;
use modules\Qof\models\Qof82summary;
/*
use modules\Qof\models\Qof91data;
use modules\Qof\models\Qof91summary;
*/
/**
 * Default controller for the `Qof` module
 */
class QofController extends Controller
{
public function actionIndex()
{
    $query = Dhdcqofreport::find();
    $dataProvider = new ActiveDataProvider(['query' => $query,]);
    return $this->render('index',[
            'dataProvider'=>$dataProvider,
        ]);
}

public function actionQof11()
    {
        $query_data = Qof11data::find();
        $data = new ActiveDataProvider(['query' => $query_data,]);
        $query_summary = Qof11summary::find();
        $summary = new ActiveDataProvider(['query' => $query_summary,]);
        return $this->render('qof11',[
                'data'=>$data,
                'summary'=>$summary,
            ]);
    }

public function actionQof21()
    {
        $query_data = Qof21data::find();
        $data = new ActiveDataProvider(['query' => $query_data,]);
        $query_summary = Qof21summary::find();
        $summary = new ActiveDataProvider(['query' => $query_summary,]);
        return $this->render('qof21',[
                'data'=>$data,
                'summary'=>$summary,
            ]);
    }

public function actionQof31()
    {
        $query_data = Qof31data::find();
        $data = new ActiveDataProvider(['query' => $query_data,]);
        $query_summary = Qof31summary::find();
        $summary = new ActiveDataProvider(['query' => $query_summary,]);
        return $this->render('qof31',[
                'data'=>$data,
                'summary'=>$summary,
            ]);
    }

public function actionQof41()
    {
        $query_data = Qof41data::find();
        $data = new ActiveDataProvider(['query' => $query_data,]);
        $query_summary = Qof41summary::find();
        $summary = new ActiveDataProvider(['query' => $query_summary,]);
        return $this->render('qof41',[
                'data'=>$data,
                'summary'=>$summary,
            ]);
    }

public function actionQof51()
    {
        $query_data = Qof51data::find();
        $data = new ActiveDataProvider(['query' => $query_data,]);
        $query_summary = Qof51summary::find();
        $summary = new ActiveDataProvider(['query' => $query_summary,]);
        return $this->render('qof51',[
                'data'=>$data,
                'summary'=>$summary,
            ]);
    }

    
public function actionQof52()
{
    $query_data = Qof52data::find();
    $data = new ActiveDataProvider(['query' => $query_data,]);
    $query_summary = Qof52summary::find();
    $summary = new ActiveDataProvider(['query' => $query_summary,]);
    return $this->render('qof52',[
            'data'=>$data,
            'summary'=>$summary,
        ]);
}

public function actionQof61()
    {
        $query_data = Qof61data::find();
        $data = new ActiveDataProvider(['query' => $query_data,]);
        $query_summary = Qof61summary::find();
        $summary = new ActiveDataProvider(['query' => $query_summary,]);
        return $this->render('qof61',[
                'data'=>$data,
                'summary'=>$summary,
            ]);
    }

public function actionQof71()
    {
        $query_data = Qof71data::find();
        $data = new ActiveDataProvider(['query' => $query_data,]);
        $query_summary = Qof71summary::find();
        $summary = new ActiveDataProvider(['query' => $query_summary,]);
        return $this->render('qof71',[
                'data'=>$data,
                'summary'=>$summary,
            ]);
    }

public function actionQof72()
    {
        $query_data = Qof72data::find();
        $data = new ActiveDataProvider(['query' => $query_data,]);
        $query_summary = Qof72summary::find();
        $summary = new ActiveDataProvider(['query' => $query_summary,]);
        return $this->render('qof72',[
                'data'=>$data,
                'summary'=>$summary,
            ]);
    }

public function actionQof73()
    {
        $query_data = Qof73data::find();
        $data = new ActiveDataProvider(['query' => $query_data,]);
        $query_summary = Qof73summary::find();
        $summary = new ActiveDataProvider(['query' => $query_summary,]);
        return $this->render('qof73',[
                'data'=>$data,
                'summary'=>$summary,
            ]);
    }

public function actionQof81()
    {
        $query_data = Qof81data::find();
        $data = new ActiveDataProvider(['query' => $query_data,]);
        $query_summary = Qof81summary::find();
        $summary = new ActiveDataProvider(['query' => $query_summary,]);
        return $this->render('qof81',[
                'data'=>$data,
                'summary'=>$summary,
            ]);
    }

public function actionQof82()
    {
        $query_data = Qof82data::find();
        $data = new ActiveDataProvider(['query' => $query_data,]);
        $query_summary = Qof82summary::find();
        $summary = new ActiveDataProvider(['query' => $query_summary,]);
        return $this->render('qof82',[
                'data'=>$data,
                'summary'=>$summary,
            ]);
    }

    /*
public function actionQof91()
    {
        $query_data = Qof91data::find();
        $data = new ActiveDataProvider(['query' => $query_data,]);
        $query_summary = Qof91summary::find();
        $summary = new ActiveDataProvider(['query' => $query_summary,]);
        return $this->render('qof91',[
                'data'=>$data,
                'summary'=>$summary,
            ]);
    }
    */

}
