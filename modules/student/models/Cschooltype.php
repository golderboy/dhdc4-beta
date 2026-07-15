<?php

namespace modules\student\models;

use Yii;

/**
 * This is the model class for table "cschooltype".
 *
 * @property string $id_schooltype
 * @property string $schooltype
 */
class Cschooltype extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cschooltype';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_schooltype', 'schooltype'], 'required'],
            [['id_schooltype'], 'string', 'max' => 1],
            [['schooltype'], 'string', 'max' => 30],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id_schooltype' => 'Id Schooltype',
            'schooltype' => 'Schooltype',
        ];
    }
}
