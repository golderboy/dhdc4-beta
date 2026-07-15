<?php

namespace modules\Qof\models;

use Yii;

/**
 * This is the model class for table "qof_51_data".
 *
 * @property string $HOSPCODE
 * @property string $CID
 * @property string $PID
 * @property string $HID
 * @property string $PRENAME
 * @property string $NAME
 * @property string $LNAME
 * @property string $HN
 * @property string $SEX
 * @property string $BIRTH
 * @property string $NATION
 * @property string $DISCHARGE
 * @property string $check_typearea
 * @property string $vhid
 * @property string $check_vhid
 * @property string $maininscl
 * @property string $inscl
 * @property int $age_y
 * @property string $addr
 * @property string $HOSP_DX
 * @property string $DX_DATE
 * @property string $DIAGCODE
 * @property string $DIAGTYPE
 * @property string $HOSP_RX
 * @property string $DATE_RX
 * @property string $DNAME
 * @property int $AMOUNT
 * @property string $UNIT_PACKING
 * @property string $dru
 */
class Qof51data extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qof_51_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['BIRTH', 'DX_DATE', 'DATE_RX'], 'safe'],
            [['age_y', 'AMOUNT'], 'integer'],
            [['HOSP_DX', 'DX_DATE', 'DIAGCODE', 'DIAGTYPE'], 'required'],
            [['HOSPCODE', 'maininscl', 'inscl', 'HOSP_DX', 'HOSP_RX'], 'string', 'max' => 5],
            [['CID'], 'string', 'max' => 13],
            [['PID', 'HN'], 'string', 'max' => 15],
            [['HID'], 'string', 'max' => 14],
            [['PRENAME', 'NATION'], 'string', 'max' => 3],
            [['NAME', 'LNAME'], 'string', 'max' => 50],
            [['SEX', 'DISCHARGE', 'check_typearea', 'DIAGTYPE'], 'string', 'max' => 1],
            [['vhid', 'check_vhid'], 'string', 'max' => 8],
            [['addr'], 'string', 'max' => 100],
            [['DIAGCODE'], 'string', 'max' => 6],
            [['DNAME'], 'string', 'max' => 255],
            [['UNIT_PACKING'], 'string', 'max' => 20],
            [['dru'], 'string', 'max' => 24],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'HOSPCODE' => 'Hospcode',
            'CID' => 'Cid',
            'PID' => 'Pid',
            'HID' => 'Hid',
            'PRENAME' => 'Prename',
            'NAME' => 'Name',
            'LNAME' => 'Lname',
            'HN' => 'Hn',
            'SEX' => 'Sex',
            'BIRTH' => 'Birth',
            'NATION' => 'Nation',
            'DISCHARGE' => 'Discharge',
            'check_typearea' => 'Check Typearea',
            'vhid' => 'Vhid',
            'check_vhid' => 'Check Vhid',
            'maininscl' => 'Maininscl',
            'inscl' => 'Inscl',
            'age_y' => 'Age Y',
            'addr' => 'Addr',
            'HOSP_DX' => 'Hosp  Dx',
            'DX_DATE' => 'Dx  Date',
            'DIAGCODE' => 'Diagcode',
            'DIAGTYPE' => 'Diagtype',
            'HOSP_RX' => 'Hosp  Rx',
            'DATE_RX' => 'Date  Rx',
            'DNAME' => 'Dname',
            'AMOUNT' => 'Amount',
            'UNIT_PACKING' => 'Unit  Packing',
            'dru' => 'Dru',
        ];
    }
}
