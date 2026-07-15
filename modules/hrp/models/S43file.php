<?php

namespace modules\hrp\models;

use Yii;

/**
 * This is the model class for table "dhdc_module_s43_file".
 *
 * @property string $F43_NAME
 * @property string $F43_QUERY
 * @property string $F43_WHERE
 * @property string $STATUS
 */
class S43file extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dhdc_module_s43_file';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['F43_NAME', 'F43_QUERY', 'F43_WHERE'], 'required'],
            [['F43_QUERY', 'F43_WHERE'], 'string'],
            [['F43_NAME'], 'string', 'max' => 255],
            [['STATUS'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'F43_NAME' => 'F43  Name',
            'F43_QUERY' => 'F43  Query',
            'F43_WHERE' => 'F43  Where',
            'STATUS' => 'Status',
        ];
    }
}
