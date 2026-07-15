<?php

namespace modules\Qof\models;

use Yii;

/**
 * This is the model class for table "qof_61_data".
 *
 * @property string $HOSPCODE
 * @property string $PID
 * @property string $AN
 * @property string $DATETIME_ADMIT
 * @property string $WARDDIAG
 * @property string $DIAGTYPE
 * @property string $DIAGCODE
 * @property string $PROVIDER
 * @property string $D_UPDATE
 * @property string $CID
 * @property string $BIRTH
 * @property string $SEX
 * @property int $age1
 * @property int $age2
 * @property string $INSTYPE_NEW
 * @property string $STARTDATE
 * @property string $EXPIREDATE
 * @property string $MAIN
 * @property string $SUB
 * @property string $diaggroup
 */
class Qof61data extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qof_61_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['HOSPCODE', 'PID', 'AN', 'DATETIME_ADMIT', 'WARDDIAG', 'DIAGTYPE', 'DIAGCODE', 'D_UPDATE', 'INSTYPE_NEW'], 'required'],
            [['DATETIME_ADMIT', 'D_UPDATE', 'BIRTH', 'STARTDATE', 'EXPIREDATE'], 'safe'],
            [['age1', 'age2'], 'integer'],
            [['HOSPCODE', 'WARDDIAG', 'MAIN', 'SUB'], 'string', 'max' => 5],
            [['PID', 'PROVIDER'], 'string', 'max' => 15],
            [['AN'], 'string', 'max' => 9],
            [['DIAGTYPE', 'SEX'], 'string', 'max' => 1],
            [['DIAGCODE'], 'string', 'max' => 6],
            [['CID'], 'string', 'max' => 13],
            [['INSTYPE_NEW'], 'string', 'max' => 4],
            [['diaggroup'], 'string', 'max' => 8],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'HOSPCODE' => 'Hospcode',
            'PID' => 'Pid',
            'AN' => 'An',
            'DATETIME_ADMIT' => 'Datetime  Admit',
            'WARDDIAG' => 'Warddiag',
            'DIAGTYPE' => 'Diagtype',
            'DIAGCODE' => 'Diagcode',
            'PROVIDER' => 'Provider',
            'D_UPDATE' => 'D  Update',
            'CID' => 'Cid',
            'BIRTH' => 'Birth',
            'SEX' => 'Sex',
            'age1' => 'Age1',
            'age2' => 'Age2',
            'INSTYPE_NEW' => 'Instype  New',
            'STARTDATE' => 'Startdate',
            'EXPIREDATE' => 'Expiredate',
            'MAIN' => 'Main',
            'SUB' => 'Sub',
            'diaggroup' => 'Diaggroup',
        ];
    }
}
