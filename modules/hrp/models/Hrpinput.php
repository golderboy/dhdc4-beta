<?php

namespace modules\hrp\models;

use Yii;

/**
 * This is the model class for table "dhdc_module_hrp_input".
 *
 * @property string $HOSPCODE
 * @property string $PID
 * @property string $GRAVIDA
 * @property string $RISK1
 * @property string $RISK2
 * @property string $RISK3
 * @property string $RISK
 * @property string $PLAN
 * @property string $OSM
 * @property string $INFO
 * @property string $STATUS
 */
class Hrpinput extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dhdc_module_hrp_input';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['HOSPCODE', 'PID', 'GRAVIDA'], 'required'],
            [['HOSPCODE'], 'string', 'max' => 5],
            [['PID'], 'string', 'max' => 13],
            [['GRAVIDA'], 'string', 'max' => 2],
            [['RISK1', 'RISK2', 'RISK3', 'PLAN', 'OSM', 'INFO'], 'string', 'max' => 200],
            [['RISK', 'STATUS'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'HOSPCODE' => 'รหัสสถานบริการ',
            'PID' => 'Pid',
            'GRAVIDA' => 'ครรถ์ที่',
            'RISK1' => 'Risk1',
            'RISK2' => 'Risk2',
            'RISK3' => 'Risk3',
            'RISK' => 'ระดับความเสี่ยง',
            'PLAN' => 'แผนการคลอด',
            'OSM' => 'ผู้ดูแล',
            'INFO' => 'ติดต่อ',
            'STATUS' => 'สถานะ',
            'FNAME'=>'FNAME'
        ];
    }
	
	public function getFullname() {
       $Hrps = Hrp::find()
        ->where(['HOSPCODE' => $this->HOSPCODE, 'PID' => $this->PID,'GRAVIDA' => $this->GRAVIDA]);
		if($Hrps->count() > 0){
            $Hrps = $Hrps->one();
            return $Hrps->PRENAME.' '.$Hrps->FNAME.' '.$Hrps->LNAME;
        }else{
            return "-";
        }

        
    }

    public function getAddrs() {
        $Hrps = Hrp::find()
         ->where(['HOSPCODE' => $this->HOSPCODE, 'PID' => $this->PID,'GRAVIDA' => $this->GRAVIDA])
         ->one();
         return $Hrps->HOUSE.' บ.'.$Hrps->VILLAGE.' '.$Hrps->ADDR;
     }
    
     public function getDetail() {
        $Hrps = Hrp::find()
         ->where(['HOSPCODE' => $this->HOSPCODE, 'PID' => $this->PID,'GRAVIDA' => $this->GRAVIDA])
         ->asArray()->one();
         return $Hrps;
     }

    public function getHospital() {
        return $this->hasOne(Chospitalamp::className(),['hoscode' => 'HOSPCODE']);
     }
    public function getBplece() {
        $Hrps = Chospitalamp::find()->where(['HOSCODE' => $this->detail['BPLACE']]);
        if($Hrps->count() > 0){
            $Hrps = $Hrps->one();
            return $Hrps->hosname;
        }else{
            return "-";
        }
     }
	 
}
