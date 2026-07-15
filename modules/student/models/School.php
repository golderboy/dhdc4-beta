<?php

namespace modules\student\models;

use Yii;

/**
 * This is the model class for table "school".
 *
 * @property string $HOSPCODE
 * @property string $VID
 * @property string $SCHOOLCODE
 * @property string $SCHOOLID
 * @property string $SCHOOLNAME
 * @property string $SCHOOLOWNER
 * @property string $SCHOOLTYPE
 * @property string $CLOSEDDATE
 * @property string $D_UPDATE
 */
class School extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'school';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['HOSPCODE', 'VID', 'SCHOOLCODE'], 'required'],
            [['CLOSEDDATE', 'D_UPDATE'], 'safe'],
            [['HOSPCODE'], 'string', 'max' => 5],
            [['VID'], 'string', 'max' => 8],
            [['SCHOOLCODE'], 'string', 'max' => 9],
            [['SCHOOLID'], 'string', 'max' => 15],
            [['SCHOOLNAME'], 'string', 'max' => 250],
            [['SCHOOLOWNER'], 'string', 'max' => 2],
            [['SCHOOLTYPE'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'HOSPCODE' => 'รหัสสถานบริการ',
            'VID' => 'รหัสชุมชน',
            'SCHOOLCODE' => 'รหัสโรงเรียน',
            'SCHOOLID' => 'รหัสสถานศึกษา',
            'SCHOOLNAME' => 'ชื่อสถานศึกษา',
            'SCHOOLOWNER' => 'สังกัด',
            'SCHOOLTYPE' => 'ประเภท',
            'CLOSEDDATE' => 'วันปิดทำการ',
            'D_UPDATE' => 'D  Update',
        ];
    }

    public function getCschooltype() {
        return $this->hasOne(Cschooltype::className(),['ID_SCHOOLTYPE' => 'SCHOOLTYPE']);
     } 

}
