<?php

namespace modules\hrp\controllers;

use Yii;
use modules\hrp\models\GisDhdcTambon;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use components\MyHelper;

use yii\helpers\Json;
/**
 * Default controller for the `hpr` module
 */
class MapController extends Controller
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

    public function actionIndex() {
        return $this->redirect(['map']);
    }

    public function actionPointHosp() {
        $sql = "SELECT h.hoscode,h.hosname,t.lat,t.lon from geojson t
INNER  JOIN chospital_amp h  on h.hoscode = t.hcode";
        $raw = \Yii::$app->db->createCommand($sql)->queryAll();
        $point = [];
        foreach ($raw as $value) {
            $p['type'] = 'Feature';
            $p['properties']['title'] = $value['hoscode'] . "-" . $value['hosname'];
            $p['properties']['marker-size'] = 'large';
            $p['properties']['marker-color'] = '#FF4500';
            $p['properties']['marker-symbol'] = 'hospital';
            $p['geometry']['type'] = "Point";
            $p['geometry']['coordinates'][0] = $value['lon'] * 1;
            $p['geometry']['coordinates'][1] = $value['lat'] * 1;
            $point[] = $p;
        }
        return Json::encode($point);
    }

    public function actionPointVill() {
        $amp = MyHelper::getSysConfig()->district_code;
        $sql = "SELECT * from gis_villages t WHERE t.properties LIKE :dolacode";
        $raw = \Yii::$app->db->createCommand($sql, [':dolacode' => '{"DOLACODE":"' . $amp . '%'])->queryAll();
        $point = [];
        foreach ($raw as $value) {
            $vill['type'] = $value['type'];
            $vill['properties'] = json_decode($value['properties']);
            $vill['geometry'] = json_decode($value['geometry']);
            $point[] = $vill;
        }
        return Json::encode($point);
    }

    public function actionPointHome() {
        //\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $sql = 'SELECT t.HOSPCODE,t.HID
,concat(t.CHANGWAT,t.AMPUR,t.TAMBON) TAMBON
,concat(t.CHANGWAT,t.AMPUR,t.TAMBON,t.VILLAGE) VILLAGE
,CONCAT(t.HOUSE," ม.",t.VILLAGE ,"  ต.",c.tambonname) TITLE
,t.LATITUDE,t.LONGITUDE
FROM home t 
LEFT JOIN ctambon c on c.tamboncodefull = concat(t.CHANGWAT,t.AMPUR,t.TAMBON)
WHERE t.LATITUDE*1 > 0 AND t.LONGITUDE*1 > 0';
        $raw = \Yii::$app->db->createCommand($sql)->queryAll();

        $point = [];
        foreach ($raw as $value) {
            $home['type'] = 'Feature';
            $home['properties']['title'] = $value['TITLE'];
            $home['properties']['marker-color'] = '#0000CD';
            $home['properties']['marker-symbol'] = 'warehouse';
            $home['geometry']['type'] = 'Point';
            $home['geometry']['coordinates'][0] = $value['LONGITUDE'] * 1;
            $home['geometry']['coordinates'][1] = $value['LATITUDE'] * 1;
            $point[] = $home;
        }



        return json_encode($point);
    }

    public function actionMap() {
        MyHelper::overclock();
        $sys = MyHelper::getSysConfig();
        if ($sys) {
            $amp = $sys->district_code;
            $model = GisDhdcTambon::find()->where(['=', 'concat(PROV_CODE,AMP_CODE)', $amp])->all();
        } else {
            $model = GisDhdcTambon::find()->where(['=', 'PROV_CODE', '10'])->all();
        }

        $tambon_pol = [];
        foreach ($model as $value) {
            $tambon_pol[] = [
                'type' => 'Feature',
                'properties' => [
                    /* 'fill' => call_user_func(function()use($value) {
                      if ($value->TAM_CODE % 2 == 0)
                      return '#4169e1';
                      if ($value->TAM_CODE % 3 == 0)
                      return '#ffd700';
                      return '#00ff7f';
                      }), */
                    //'fillOpacity'=>1,
                    'title' => "ต." . $value['TAM_NAMT'],
                ],
                'geometry' => [
                    'type' => 'MultiPolygon',
                    'coordinates' => json_decode($value['COORDINATES']),
                ]
            ];
        }
        $tambon_pol = json_encode($tambon_pol);

        return $this->renderPartial('map', [
                    'tambon_pol' => $tambon_pol
        ]);
    }

    public function actionPointRisk() {
        MyHelper::overclock();
        $sql = "SELECT IF(t1.RISK = '',0,t1.RISK) as 'RISK',concat(h.HOUSE,' ม.',h.VILLAGE,' ต.',tb.tambonname) TITLE,h.LATITUDE,h.LONGITUDE 
        FROM dhdc_module_hrp_input t1 
		INNER JOIN dhdc_module_hrp t2 ON t1.hospcode = t2.HOSPCODE AND t1.pid = t2.pid AND t1.gravida = t2.gravida
        LEFT JOIN t_person_cid p ON p.HOSPCODE = p.HOSPCODE AND p.PID = t2.PID
        LEFT JOIN home h ON h.HOSPCODE = p.HOSPCODE AND h.HID = t2.HID
        LEFT JOIN ctambon tb on tb.tamboncodefull = CONCAT(h.CHANGWAT,h.AMPUR,h.TAMBON)
        WHERE t1.Status = 'Y' 
		AND t1.RISK > 0
		AND p.DISCHARGE = '9' ";

        $raw = MyHelper::query_all($sql);

        $point = [];
        foreach ($raw as $value) {
            $home['type'] = 'Feature';
            $home['properties']['title'] = $value['TITLE'];
            $home['properties']['marker-size'] = 'large';
                switch ($value['RISK']) {
                    case '1':   $home['properties']['description'] = "RISK1"; break;
                    case '2':   $home['properties']['description'] = "RISK2"; break;
                    case '3':   $home['properties']['description'] = "RISK3"; break;
                }
                switch ($value['RISK']) {
                    case '1':   $home['properties']['marker-color'] = "#00ff00"; break;
                    case '2':   $home['properties']['marker-color'] = "#ffff00"; break;
                    case '3':   $home['properties']['marker-color'] = "#ff0000"; break;
                }
            $home['properties']['marker-symbol'] = 'roadblock';
            $home['geometry']['type'] = 'Point';
            $home['geometry']['coordinates'][0] = $value['LONGITUDE'] * 1;
            $home['geometry']['coordinates'][1] = $value['LATITUDE'] * 1;
            $point[] = $home;
        }



        return json_encode($point);
    }

}
