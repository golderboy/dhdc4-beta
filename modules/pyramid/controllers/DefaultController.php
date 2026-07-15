<?php

namespace modules\pyramid\controllers;

use yii\web\Controller;
use modules\pyramid\models\ChospitalAmp;
use yii\filters\AccessControl;
use components\MyHelper;
/**
 * Default controller for the `pyramid` module
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
        return $this->render('index');
    }
	
	public function actionPyramid() {//คำนวนอายุประชากร
        
        $hosname ='';
        
        $sql = "SELECT  SUBSTR(t.age_range,3,10) as age ,SUM(t.male) as male,SUM(t.female)as female 
				from sys_pyramid_level_3 t
				GROUP BY t.age_range";

        if (!empty($_POST['hospcode'])) {
            $h = $_POST['hospcode'];
            $sql = "SELECT  SUBSTR(t.age_range,3,10) as age ,SUM(t.male) as male,SUM(t.female)as female 
					from sys_pyramid_level_3 t
					WHERE t.hospcode =$h
					GROUP BY t.age_range";
            
            $m= ChospitalAmp::findOne(['hoscode'=>$h]);
            $hosname = $m->hosname;
            
        }

        try {
            $rawData = \Yii::$app->db->createCommand($sql)->queryAll();
        } catch (\yii\db\Exception $e) {
            throw new \yii\web\ConflictHttpException('ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่อีกครั้ง');
        }
        $dataProvider = new \yii\data\ArrayDataProvider([
            //'key' => 'hoscode',
            'allModels' => $rawData,
            'pagination' => FALSE,
        ]);

        return $this->render('pyramid', [

                    'dataProvider' => $dataProvider,
                    'rawData' => $rawData,
                    'hospcode'=>isset($_POST['hospcode'])?$_POST['hospcode']:'',
                    'hosname'=>$hosname
                        //'date1' => $date1,
                        //'date2' => $date2
        ]);
    }
    
     public function actionChecktype() {
        $hosname ='';
        if (!empty($_POST['hospcode'])) {
            $h = $_POST['hospcode'];
            $sql = "SELECT * FROM sys_person_type WHERE total >0 AND hospcode = ".$h;
        }else{
            $sql = "SELECT sum(type1) as type1,sum(type2) as type2,sum(type3) as type3,sum(type4) as type4,
				sum(type5) as type5,sum(nottype) as nottype,sum(total) as total 
            FROM sys_person_type WHERE total >0 ;";
        }
        try {
            $rawData = \Yii::$app->db->createCommand($sql)->queryAll();
            $dataProvider = new \yii\data\ArrayDataProvider([
                'allModels' => $rawData,
                'pagination' => FALSE,
             ]);
        } catch (\yii\db\Exception $e) {
            throw new \yii\web\ConflictHttpException('ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่อีกครั้ง');
        }
    
        return $this->render('check_type', [
                    'dataProvider' => $dataProvider,
                    'hospcode'=>isset($_POST['hospcode'])?$_POST['hospcode']:'',
                    'sql' => $sql
        ]);
    }
    
    public function actionCheckcid() {

        $sql = "select  h.hoscode as hospcode ,h.hosname as hospname,
cid_isnull as CIDเป็นค่าว่าง,cid_not13 as CIDไม่เท่ากับ13หลัก,nation_isnull as สัญชาติไม่ใช่ไทย

from chospital_amp h
LEFT JOIN
          (select person.hospcode,count(distinct(person.pid)) as total,SUM(if(trim(person.cid)='' or ISNULL(person.cid),1,0)) as cid_isnull
          ,SUM(if(length(person.cid) <> 13,1,0)) as cid_not13,SUM(if(trim(person.nation)='' or ISNULL(person.nation),1,0)) as nation_isnull from person  
           where person.discharge = '9' and person.typearea in ('1', '3') and person.nation ='099'   group by person.hospcode) as p
ON h.hoscode = p.hospcode
where hostype  in ('03','04','05','07','08','09','12','13')
order by hoscode asc;";


        try {
            $rawData = \Yii::$app->db->createCommand($sql)->queryAll();
        } catch (\yii\db\Exception $e) {
            throw new \yii\web\ConflictHttpException('ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่อีกครั้ง');
        }
        $dataProvider = new \yii\data\ArrayDataProvider([
            //'key' => 'hoscode',
            'allModels' => $rawData,
            'pagination' => FALSE,
        ]);
       
        return $this->render('check_cid', [
                    'dataProvider' => $dataProvider,
                    'sql' => $sql
        ]);
    }
	
}
