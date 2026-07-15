<?php

namespace modules\student\models;

use Yii;

/**
 * This is the model class for table "student".
 *
 * @property string $HOSPCODE
 * @property string $PID
 * @property string $SCHOOLCODE
 * @property string $EDUCATIONYEAR
 * @property string $CLASS
 * @property string $D_UPDATE
 * @property string $GRUDATE_DATE
 * @property integer $id
 */
class Student extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'student';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['HOSPCODE', 'PID', 'SCHOOLCODE', 'EDUCATIONYEAR', 'CLASS', 'id'], 'required'],
            [['D_UPDATE', 'GRUDATE_DATE'], 'safe'],
            [['id'], 'integer'],
            [['HOSPCODE'], 'string', 'max' => 5],
            [['PID'], 'string', 'max' => 15],
            [['SCHOOLCODE'], 'string', 'max' => 9],
            [['EDUCATIONYEAR'], 'string', 'max' => 4],
            [['CLASS'], 'string', 'max' => 2],
            [['FNAME'], 'string', 'max' => 250],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'HOSPCODE' => 'รหัสสถานบริการ',
            'PID' => 'PID',
            'SCHOOLCODE' => 'รหัสโรงเรียน',
            'EDUCATIONYEAR' => 'ปีการศึกษา',
            'CLASS' => 'ชั้นเรียน',
            'D_UPDATE' => 'D  Update',
            'GRUDATE_DATE' => 'Grudate  Date',
            'id' => 'ID',
            'FNAME' => 'ชื่อนักเรียน',
        ];
    }

    public function getSclass() {
        return $this->hasOne(Sclass::className(),['CLASS' => 'CLASS']);
     }

     public function getSschool() {
        return $this->hasOne(School::className(),['SCHOOLCODE' => 'SCHOOLCODE','HOSPCODE'=>'HOSPCODE']);
     } 

     public function getPersonname() {
        return $this->hasOne(Person::className(),['PID' => 'PID','HOSPCODE'=>'HOSPCODE']);
     } 

}
