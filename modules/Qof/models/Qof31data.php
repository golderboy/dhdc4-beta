<?php

namespace modules\Qof\models;

use Yii;

/**
 * This is the model class for table "qof_31_data".
 *
 * @property string $check_hosp
 * @property string $PID
 * @property string $GRAVIDA
 * @property string $LMP
 * @property string $EDC
 * @property string $BDATE
 * @property string $BRESULT
 * @property string $BPLACE
 * @property string $BHOSP
 * @property string $BDOCTOR
 * @property int $LBORN
 * @property int $SBORN
 * @property string $input_bhosp
 * @property string $g1_ga
 * @property string $g1_date
 * @property string $g1_hospcode
 * @property string $g1_input_hosp
 * @property string $g2_ga
 * @property string $g2_date
 * @property string $g2_hospcode
 * @property string $g2_input_hosp
 * @property string $g3_ga
 * @property string $g3_date
 * @property string $g3_hospcode
 * @property string $g3_input_hosp
 * @property string $g4_ga
 * @property string $g4_date
 * @property string $g4_hospcode
 * @property string $g4_input_hosp
 * @property string $g5_ga
 * @property string $g5_date
 * @property string $g5_hospcode
 * @property string $g5_input_hosp
 * @property string $CHECK
 */
class Qof31data extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qof_31_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['PID', 'GRAVIDA', 'LMP', 'BDATE', 'BRESULT', 'BPLACE', 'BDOCTOR', 'LBORN', 'SBORN'], 'required'],
            [['LMP', 'EDC', 'BDATE', 'g1_date', 'g2_date', 'g3_date', 'g4_date', 'g5_date'], 'safe'],
            [['LBORN', 'SBORN'], 'integer'],
            [['check_hosp', 'BHOSP', 'input_bhosp', 'g1_hospcode', 'g1_input_hosp', 'g2_hospcode', 'g2_input_hosp', 'g3_hospcode', 'g3_input_hosp', 'g4_hospcode', 'g4_input_hosp', 'g5_hospcode', 'g5_input_hosp'], 'string', 'max' => 5],
            [['PID'], 'string', 'max' => 15],
            [['GRAVIDA', 'g1_ga', 'g2_ga', 'g3_ga', 'g4_ga', 'g5_ga'], 'string', 'max' => 2],
            [['BRESULT'], 'string', 'max' => 6],
            [['BPLACE', 'BDOCTOR', 'CHECK'], 'string', 'max' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'check_hosp' => 'Check Hosp',
            'PID' => 'Pid',
            'GRAVIDA' => 'Gravida',
            'LMP' => 'Lmp',
            'EDC' => 'Edc',
            'BDATE' => 'Bdate',
            'BRESULT' => 'Bresult',
            'BPLACE' => 'Bplace',
            'BHOSP' => 'Bhosp',
            'BDOCTOR' => 'Bdoctor',
            'LBORN' => 'Lborn',
            'SBORN' => 'Sborn',
            'input_bhosp' => 'Input Bhosp',
            'g1_ga' => 'G1 Ga',
            'g1_date' => 'G1 Date',
            'g1_hospcode' => 'G1 Hospcode',
            'g1_input_hosp' => 'G1 Input Hosp',
            'g2_ga' => 'G2 Ga',
            'g2_date' => 'G2 Date',
            'g2_hospcode' => 'G2 Hospcode',
            'g2_input_hosp' => 'G2 Input Hosp',
            'g3_ga' => 'G3 Ga',
            'g3_date' => 'G3 Date',
            'g3_hospcode' => 'G3 Hospcode',
            'g3_input_hosp' => 'G3 Input Hosp',
            'g4_ga' => 'G4 Ga',
            'g4_date' => 'G4 Date',
            'g4_hospcode' => 'G4 Hospcode',
            'g4_input_hosp' => 'G4 Input Hosp',
            'g5_ga' => 'G5 Ga',
            'g5_date' => 'G5 Date',
            'g5_hospcode' => 'G5 Hospcode',
            'g5_input_hosp' => 'G5 Input Hosp',
            'CHECK' => 'Check',
        ];
    }
}
