<?php

namespace modules\Qof\models;

use Yii;

/**
 * This is the model class for table "qof_72_summary".
 *
 * @property string $hospcode
 * @property string $hosname
 * @property int $B
 * @property int $A
 * @property string $Per
 * @property string $Point
 */
class Qof72summary extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qof_72_summary';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['hospcode'], 'required'],
            [['B', 'A'], 'integer'],
            [['Per'], 'number'],
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
            'B' => 'B',
            'A' => 'A',
            'Per' => 'Per',
            'Point' => 'Point',
        ];
    }
}
