<?php

namespace modules\hrp\controllers;

use yii;
use yii\helpers\Html;
use yii\helpers\FileHelper;
use yii\db\Exception;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;

class JsonController extends Controller {

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
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'read' => ['post'],
                ],
            ],
        ];
    }

    protected function addRecord($prov, $amp, $tam, $namt, $name, $coord) {
        \Yii::$app->db->createCommand(
            'REPLACE INTO gis_dhdc_tambon (PROV_CODE, AMP_CODE, TAM_CODE, TAM_NAMT, TAM_NAME, COORDINATES)
             VALUES (:prov, :amp, :tam, :namt, :name, :coord)',
            [
                ':prov' => $prov,
                ':amp' => $amp,
                ':tam' => $tam,
                ':namt' => $namt,
                ':name' => $name,
                ':coord' => $coord,
            ]
        )->execute();
       
    }

    public function actionRead($file) {
        ini_set('memory_limit','2048M');
        $file = $this->resolveJsonFile($file);
        $data = file_get_contents($file);
        $data = json_decode($data, TRUE);
        $total = count($data['features']);

        for ($i = 0; $i < $total; $i++) {

            $prov = $data['features'][$i]['properties']['PROV_CODE'];

            $amp = $data['features'][$i]['properties']['AMP_CODE'];
            $amp=strlen($amp)<2?"0$amp":$amp; 

            $tam = $data['features'][$i]['properties']['TAM_CODE'];
            $tam=strlen($tam)<2?"0$tam":$tam; 

            $namt = $data['features'][$i]['properties']['TAM_NAMT'];

            $name = $data['features'][$i]['properties']['TAM_NAME'];
            // coord เป็น polygon
            //$coord = $data['features'][$i]['geometry']['coordinates'];
            
            $coord = json_encode($data['features'][$i]['geometry']['coordinates']);
            $coord = "[".$coord."]";
             
          
            
            try {
                $this->addRecord($prov, $amp, $tam, $namt, $name, $coord);
            } catch (\yii\db\Exception $e) {
                \Yii::error($e, __METHOD__);
                throw new \yii\web\ServerErrorHttpException('ไม่สามารถนำเข้าข้อมูลแผนที่ได้');
            }
        }

        return 'นำเข้าข้อมูลแผนที่เรียบร้อยแล้ว';
    }

    protected function resolveJsonFile($file) {
        $realPath = realpath((string)$file);
        $root = realpath(\Yii::getAlias('@app') . '/..');
        if ($realPath === false || $root === false) {
            throw new BadRequestHttpException('ไฟล์ข้อมูลแผนที่ไม่ถูกต้อง');
        }

        $normalizedFile = str_replace("\\", "/", $realPath);
        $normalizedRoot = rtrim(str_replace("\\", "/", $root), '/');
        $extension = strtolower(pathinfo($normalizedFile, PATHINFO_EXTENSION));
        if (strpos($normalizedFile, $normalizedRoot . '/') !== 0 || !in_array($extension, ['json', 'geojson'], true)) {
            throw new BadRequestHttpException('ไฟล์ข้อมูลแผนที่ไม่ถูกต้อง');
        }

        return $realPath;
    }

}
