<?php

namespace modules\Qof\models;

use Yii;

/**
 * This is the model class for table "qof_41_data".
 *
 * @property string $HOSPCODE
 * @property string $PID
 * @property string $ptname
 * @property string $date_diag
 * @property string $DIAGCODE
 * @property string $DATE_SP
 * @property string $PPSPECIAL
 * @property string $C
 */
class Qof41data extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qof_41_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['PID', 'date_diag'], 'required'],
            [['date_diag', 'DATE_SP'], 'safe'],
            [['DIAGCODE', 'PPSPECIAL'], 'string'],
            [['HOSPCODE'], 'string', 'max' => 5],
            [['PID'], 'string', 'max' => 15],
            [['ptname'], 'string', 'max' => 101],
            [['C'], 'string', 'max' => 1],
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
            'ptname' => 'Ptname',
            'date_diag' => 'Date Diag',
            'DIAGCODE' => 'Diagcode',
            'DATE_SP' => 'Date  Sp',
            'PPSPECIAL' => 'Ppspecial',
            'C' => 'C',
        ];
    }
}
