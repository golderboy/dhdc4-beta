<?php

namespace modules\student\models;

use Yii;

/**
 * This is the model class for table "dhdc_module_student_class".
 *
 * @property integer $id
 * @property string $class
 * @property string $class_name
 */
class Sclass extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dhdc_module_student_class';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['class', 'class_name'], 'required'],
            [['class'], 'string', 'max' => 2],
            [['class_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'class' => 'Class',
            'class_name' => 'Class Name',
        ];
    }
}
