<?php

namespace modules\dgis\models;

use Yii;

/**
 * This is the model class for table "home".
 *
 * @property string $HOSPCODE
 * @property string $HID
 * @property string $HOUSE_ID
 * @property string $HOUSETYPE
 * @property string $ROOMNO
 * @property string $CONDO
 * @property string $HOUSE
 * @property string $SOISUB
 * @property string $SOIMAIN
 * @property string $ROAD
 * @property string $VILLANAME
 * @property string $VILLAGE
 * @property string $TAMBON
 * @property string $AMPUR
 * @property string $CHANGWAT
 * @property string $TELEPHONE
 * @property string $LATITUDE
 * @property string $LONGITUDE
 * @property string $NFAMILY
 * @property string $LOCATYPE
 * @property string $VHVID
 * @property string $HEADID
 * @property string $TOILET
 * @property string $WATER
 * @property string $WATERTYPE
 * @property string $GARBAGE
 * @property string $HOUSING
 * @property string $DURABILITY
 * @property string $CLEANLINESS
 * @property string $VENTILATION
 * @property string $LIGHT
 * @property string $WATERTM
 * @property string $MFOOD
 * @property string $BCONTROL
 * @property string $ACONTROL
 * @property string $CHEMICAL
 * @property string $OUTDATE
 * @property string $D_UPDATE
 */
class Home extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'home';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['HOSPCODE', 'HID', 'HOUSETYPE', 'D_UPDATE'], 'required'],
            [['LATITUDE', 'LONGITUDE'], 'number'],
            [['OUTDATE', 'D_UPDATE'], 'safe'],
            [['HOSPCODE'], 'string', 'max' => 5],
            [['HID'], 'string', 'max' => 14],
            [['HOUSE_ID'], 'string', 'max' => 11],
            [['HOUSETYPE', 'LOCATYPE', 'TOILET', 'WATER', 'WATERTYPE', 'GARBAGE', 'HOUSING', 'DURABILITY', 'CLEANLINESS', 'VENTILATION', 'LIGHT', 'WATERTM', 'MFOOD', 'BCONTROL', 'ACONTROL', 'CHEMICAL'], 'string', 'max' => 1],
            [['ROOMNO'], 'string', 'max' => 10],
            [['CONDO', 'HOUSE'], 'string', 'max' => 75],
            [['SOISUB', 'SOIMAIN', 'ROAD', 'VILLANAME'], 'string', 'max' => 255],
            [['VILLAGE', 'TAMBON', 'AMPUR', 'CHANGWAT', 'NFAMILY'], 'string', 'max' => 2],
            [['TELEPHONE', 'VHVID', 'HEADID'], 'string', 'max' => 15],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'HOSPCODE' => 'Hospcode',
            'HID' => 'Hid',
            'HOUSE_ID' => 'House  ID',
            'HOUSETYPE' => 'Housetype',
            'ROOMNO' => 'Roomno',
            'CONDO' => 'Condo',
            'HOUSE' => 'House',
            'SOISUB' => 'Soisub',
            'SOIMAIN' => 'Soimain',
            'ROAD' => 'Road',
            'VILLANAME' => 'Villaname',
            'VILLAGE' => 'Village',
            'TAMBON' => 'Tambon',
            'AMPUR' => 'Ampur',
            'CHANGWAT' => 'Changwat',
            'TELEPHONE' => 'Telephone',
            'LATITUDE' => 'Latitude',
            'LONGITUDE' => 'Longitude',
            'NFAMILY' => 'Nfamily',
            'LOCATYPE' => 'Locatype',
            'VHVID' => 'Vhvid',
            'HEADID' => 'Headid',
            'TOILET' => 'Toilet',
            'WATER' => 'Water',
            'WATERTYPE' => 'Watertype',
            'GARBAGE' => 'Garbage',
            'HOUSING' => 'Housing',
            'DURABILITY' => 'Durability',
            'CLEANLINESS' => 'Cleanliness',
            'VENTILATION' => 'Ventilation',
            'LIGHT' => 'Light',
            'WATERTM' => 'Watertm',
            'MFOOD' => 'Mfood',
            'BCONTROL' => 'Bcontrol',
            'ACONTROL' => 'Acontrol',
            'CHEMICAL' => 'Chemical',
            'OUTDATE' => 'Outdate',
            'D_UPDATE' => 'D  Update',
        ];
    }

	public function getTambon() {
		$Tcode = $this->CHANGWAT.''.$this->AMPUR.''.$this->TAMBON;
        $raw = Tambon::find()
				->where(['tamboncodefull'=>$Tcode] )
				->one();
		return $raw->tambonname.' หมู่ '.$this->VILLAGE;
    }

}
