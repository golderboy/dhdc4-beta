<?php

namespace modules\Qof\models;

use Yii;

/**
 * This is the model class for table "qof_41_summary".
 *
 * @property string $HOSPCODE
 * @property string $HOSNAME
 * @property int $A
 * @property string $B
 * @property string $Per
 * @property string $Point
 */
class Qof41summary extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qof_41_summary';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['A'], 'integer'],
            [['B', 'Per'], 'number'],
            [['HOSPCODE'], 'string', 'max' => 5],
            [['HOSNAME'], 'string', 'max' => 255],
            [['Point'], 'string', 'max' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'HOSPCODE' => 'Hospcode',
            'HOSNAME' => 'Hosname',
            'A' => 'A',
            'B' => 'B',
            'Per' => 'Per',
            'Point' => 'Point',
        ];
    }
}
