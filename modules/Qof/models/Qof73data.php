<?php

namespace modules\Qof\models;

use Yii;

/**
 * This is the model class for table "qof_73_data".
 *
 * @property string $hospcode
 * @property string $pid
 * @property string $cid
 * @property string $birth
 * @property string $sex
 * @property string $areacode
 * @property string $typearea
 * @property string $agemonth
 * @property string $date_start
 * @property string $date_end
 * @property string $date_serv_first
 * @property string $status1
 * @property string $date_serv2
 * @property string $sp_first
 * @property string $sp_last
 * @property string $date_serv_last
 * @property string $status2
 * @property string $status21
 * @property string $status22
 * @property string $status23
 * @property string $status24
 * @property string $status25
 */
class Qof73data extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qof_73_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['hospcode', 'pid', 'sex', 'areacode'], 'required'],
            [['birth', 'date_start', 'date_end', 'date_serv_first', 'date_serv2', 'date_serv_last'], 'safe'],
            [['sp_first', 'sp_last'], 'string'],
            [['hospcode'], 'string', 'max' => 5],
            [['pid', 'cid'], 'string', 'max' => 15],
            [['sex', 'typearea', 'status1', 'status2', 'status21', 'status22', 'status23', 'status24', 'status25'], 'string', 'max' => 1],
            [['areacode'], 'string', 'max' => 8],
            [['agemonth'], 'string', 'max' => 3],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'hospcode' => 'Hospcode',
            'pid' => 'Pid',
            'cid' => 'Cid',
            'birth' => 'Birth',
            'sex' => 'Sex',
            'areacode' => 'Areacode',
            'typearea' => 'Typearea',
            'agemonth' => 'Agemonth',
            'date_start' => 'Date Start',
            'date_end' => 'Date End',
            'date_serv_first' => 'Date Serv First',
            'status1' => 'Status1',
            'date_serv2' => 'Date Serv2',
            'sp_first' => 'Sp First',
            'sp_last' => 'Sp Last',
            'date_serv_last' => 'Date Serv Last',
            'status2' => 'Status2',
            'status21' => 'Status21',
            'status22' => 'Status22',
            'status23' => 'Status23',
            'status24' => 'Status24',
            'status25' => 'Status25',
        ];
    }
}
