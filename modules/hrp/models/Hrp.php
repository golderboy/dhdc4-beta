<?php

namespace modules\hrp\models;

use Yii;

/**
 * This is the model class for table "dhdc_module_hrp".
 *
 * @property string $HOSPCODE
 * @property string $PID
 * @property string $PRENAME
 * @property string $FNAME
 * @property string $LNAME
 * @property integer $HID
 * @property string $HOUSE
 * @property string $VILLAGE
 * @property string $ADDR
 * @property string $TYPEAREA
 * @property string $GRAVIDA
 * @property string $EDC
 * @property string $LMP
 * @property string $BDATE
 * @property string $BPLACE
 * @property string $LABOR
 * @property string $ANC12W
 * @property string $ANC5
 */
class Hrp extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dhdc_module_hrp';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['HOSPCODE', 'PID', 'HID', 'TYPEAREA', 'GRAVIDA', 'LABOR', 'ANC12W', 'ANC5'], 'required'],
            [['HID'], 'integer'],
            [['EDC', 'LMP', 'BDATE'], 'safe'],
            [['HOSPCODE', 'BPLACE'], 'string', 'max' => 5],
            [['PID'], 'string', 'max' => 13],
            [['PRENAME'], 'string', 'max' => 20],
            [['FNAME', 'LNAME'], 'string', 'max' => 200],
            [['HOUSE', 'VILLAGE', 'ADDR'], 'string', 'max' => 255],
            [['TYPEAREA', 'LABOR', 'ANC12W', 'ANC5'], 'string', 'max' => 1],
            [['GRAVIDA'], 'string', 'max' => 2],
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
            'PRENAME' => 'คำนำหน้า',
            'FNAME' => 'ชื่อ',
            'LNAME' => 'นามสกุล',
            'HID' => 'Hid',
            'HOUSE' => 'บ้านเลขที่',
            'VILLAGE' => 'หมู่บ้าน',
            'ADDR' => 'ที่อยู่',
            'TYPEAREA' => 'Typearea',
            'GRAVIDA' => 'ครรถ์ที่',
            'EDC' => 'กำหนดคลอด',
            'LMP' => 'วันสุดท้าย ปจด.',
            'BDATE' => 'วันที่คลอด',
            'BPLACE' => 'สถานที่คลอด',
            'LABOR' => 'สถานะการคลอด',
            'ANC12W' => 'ANC < 12Wk',
            'ANC5' => 'Anc >= 5 ',
        ];
    }

}
