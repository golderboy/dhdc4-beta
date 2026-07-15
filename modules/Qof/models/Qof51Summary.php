<?php

namespace modules\Qof\models;

use Yii;

/**
 * This is the model class for table "qof_51_summary".
 *
 * @property string $HOSPCODE
 * @property string $hosname
 * @property int $A
 * @property string $B
 * @property string $Per
 * @property string $Point
 */
class Qof51summary extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qof_51_summary';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['HOSPCODE'], 'required'],
            [['A'], 'integer'],
            [['B', 'Per'], 'number'],
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
            'A' => 'A',
            'B' => 'B',
            'Per' => 'Per',
            'Point' => 'Point',
        ];
    }
}
