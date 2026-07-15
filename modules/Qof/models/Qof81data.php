<?php

namespace modules\Qof\models;

use Yii;

/**
 * This is the model class for table "qof_81_data".
 *
 * @property int $id
 * @property string $hospcode
 * @property string $pid
 * @property string $typearea
 * @property string $vhid
 * @property string $cid
 * @property string $birth
 * @property int $age_y
 * @property string $sex
 * @property string $nation
 * @property string $adl
 * @property string $a43_care
 * @property string $D_UPDATE
 */
class Qof81data extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qof_81_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'age_y'], 'integer'],
            [['hospcode', 'pid', 'typearea', 'cid', 'age_y'], 'required'],
            [['birth', 'D_UPDATE'], 'safe'],
            [['hospcode'], 'string', 'max' => 5],
            [['pid'], 'string', 'max' => 15],
            [['typearea', 'sex'], 'string', 'max' => 1],
            [['vhid'], 'string', 'max' => 8],
            [['cid'], 'string', 'max' => 13],
            [['nation'], 'string', 'max' => 3],
            [['adl'], 'string', 'max' => 6],
            [['a43_care'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'hospcode' => 'Hospcode',
            'pid' => 'Pid',
            'typearea' => 'Typearea',
            'vhid' => 'Vhid',
            'cid' => 'Cid',
            'birth' => 'Birth',
            'age_y' => 'Age Y',
            'sex' => 'Sex',
            'nation' => 'Nation',
            'adl' => 'Adl',
            'a43_care' => 'A43 Care',
            'D_UPDATE' => 'D  Update',
        ];
    }
}
