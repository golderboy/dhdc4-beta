<?php

namespace modules\Qof\models;

use Yii;

/**
 * This is the model class for table "qof_61_summary".
 *
 * @property string $diaggroup
 * @property string $HOSPCODE
 * @property string $B
 * @property string $A
 * @property string $Xi
 * @property string $Point
 */
class Qof61summary extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qof_61_summary';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['B', 'A', 'Xi'], 'number'],
            [['diaggroup'], 'string', 'max' => 8],
            [['HOSPCODE'], 'string', 'max' => 5],
            [['Point'], 'string', 'max' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'diaggroup' => 'Diaggroup',
            'HOSPCODE' => 'Hospcode',
            'B' => 'B',
            'A' => 'A',
            'Xi' => 'Xi',
            'Point' => 'Point',
        ];
    }
}
