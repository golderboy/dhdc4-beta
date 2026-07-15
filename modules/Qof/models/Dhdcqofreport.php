<?php

namespace modules\Qof\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
/**
 * This is the model class for table "dhdc_qof_report".
 *
 * @property int $id
 * @property string $name
 * @property string $url
 * @property string $table
 * @property string $description
 * @property string $active
 * @property string $created_at
 * @property string $created_by
 * @property string $updated_at
 * @property string $updated_by
 * @property string $data_json
 */
class Dhdcqofreport extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dhdc_qof_report';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name','active'], 'required'],
            [['description', 'data_json'], 'string'],
            [['name', 'url', 'table', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'string', 'max' => 255],
            [['active'], 'string', 'max' => 1],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'url' => 'Url',
            'table' => 'Table',
            'description' => 'Description',
            'active' => 'Active',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
            'data_json' => 'Data Json',
        ];
    }

    //Auto Save
public function behaviors() {
    return[
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()')
            ]
        ];
    }


}
