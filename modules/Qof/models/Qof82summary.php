<?php

namespace modules\Qof\models;

use Yii;

/**
 * This is the model class for table "qof_82_summary".
 *
 * @property string $hospcode
 * @property string $hosname
 * @property string $A
 * @property string $B
 * @property string $Per
 * @property string $Point
 */
class Qof82summary extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qof_82_summary';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['hospcode'], 'required'],
            [['A', 'B', 'Per'], 'number'],
            [['hospcode'], 'string', 'max' => 5],
            [['hosname'], 'string', 'max' => 255],
            [['Point'], 'string', 'max' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'hospcode' => 'Hospcode',
            'hosname' => 'Hosname',
            'A' => 'A',
            'B' => 'B',
            'Per' => 'Per',
            'Point' => 'Point',
        ];
    }
}
