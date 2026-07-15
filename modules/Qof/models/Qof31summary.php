<?php

namespace modules\Qof\models;

use Yii;

/**
 * This is the model class for table "qof_31_summary".
 *
 * @property string $HOSPCODE
 * @property string $hosname
 * @property int $B
 * @property int $A
 * @property string $Per
 * @property string $Point
 */
class Qof31summary extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qof_31_summary';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['B', 'A'], 'integer'],
            [['Per'], 'number'],
            [['HOSPCODE'], 'string', 'max' => 5],
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
            'HOSPCODE' => 'Hospcode',
            'hosname' => 'Hosname',
            'B' => 'B',
            'A' => 'A',
            'Per' => 'Per',
            'Point' => 'Point',
        ];
    }
}
