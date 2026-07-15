<?php

namespace modules\Qof\models;

use Yii;

/**
 * This is the model class for table "qof_21_data".
 *
 * @property string $HOSPCODE
 * @property string $CID
 * @property string $PID
 * @property string $HID
 * @property string $PRENAME
 * @property string $NAME
 * @property string $LNAME
 * @property string $HN
 * @property string $SEX
 * @property string $BIRTH
 * @property string $MSTATUS
 * @property string $OCCUPATION_OLD
 * @property string $OCCUPATION_NEW
 * @property string $RACE
 * @property string $NATION
 * @property string $RELIGION
 * @property string $EDUCATION
 * @property string $FSTATUS
 * @property string $FATHER
 * @property string $MOTHER
 * @property string $COUPLE
 * @property string $VSTATUS
 * @property string $MOVEIN
 * @property string $DISCHARGE
 * @property string $DDISCHARGE
 * @property string $ABOGROUP
 * @property string $RHGROUP
 * @property string $LABOR
 * @property string $PASSPORT
 * @property string $TYPEAREA
 * @property string $D_UPDATE
 * @property string $check_hosp
 * @property string $check_typearea
 * @property string $vhid
 * @property string $check_vhid
 * @property string $maininscl
 * @property string $inscl
 * @property int $age_y
 * @property string $addr
 * @property string $source_tb
 * @property string $groupcode1560
 * @property string $groupname1560
 * @property string $mix_dx
 * @property string $t_mix_dx
 * @property string $type_dx
 * @property string $date_dx
 * @property string $hosp_dx
 * @property string $ld_hba1c
 * @property string $rs_hba1c
 * @property string $ih_hba1c
 * @property string $ld_fpg1
 * @property string $rs_fpg1
 * @property string $ih_fpg1
 * @property string $ld_fpg2
 * @property string $rs_fpg2
 * @property string $ih_fpg2
 * @property string $ld_fpg3
 * @property string $rs_fpg3
 * @property string $ih_fpg3
 * @property string $ld_creatinine
 * @property string $rs_creatinine
 * @property string $ih_creatinine
 * @property string $ld_lipid
 * @property string $rs_lipid
 * @property string $ih_lipid
 * @property string $ld_foot
 * @property string $rs_foot
 * @property string $ih_foot
 * @property string $ld_retina
 * @property string $rs_retina
 * @property string $ih_retina
 * @property string $ld_bp1
 * @property string $ih_bp1
 * @property string $rs_bps1
 * @property string $rs_bpd1
 * @property string $ld_bp2
 * @property string $ih_bp2
 * @property string $rs_bps2
 * @property string $rs_bpd2
 * @property string $complication_dm
 * @property string $complication_ht
 * @property int $control_dm
 * @property int $control_ht
 * @property string $bmi
 * @property int $obes
 * @property int $height
 * @property int $weight
 * @property int $waist_cm
 * @property string $min_date_dx_dm
 * @property string $min_date_dx_ht
 * @property string $SERVPLACE
 * @property string $SMOKE
 * @property string $ALCOHOL
 * @property string $DMFAMILY
 * @property string $HTFAMILY
 * @property string $BSTEST
 * @property int $BSLEVEL
 * @property string $chronic
 * @property string $screen
 */
class Qof21data extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'qof_21_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['CID'], 'required'],
            [['BIRTH', 'MOVEIN', 'DDISCHARGE', 'D_UPDATE', 'ld_hba1c', 'ld_fpg1', 'ld_fpg2', 'ld_fpg3', 'ld_creatinine', 'ld_lipid', 'ld_foot', 'ld_retina', 'ld_bp1', 'ld_bp2', 'min_date_dx_dm', 'min_date_dx_ht'], 'safe'],
            [['age_y', 'control_dm', 'control_ht', 'obes', 'height', 'weight', 'waist_cm', 'BSLEVEL'], 'integer'],
            [['bmi'], 'number'],
            [['HOSPCODE', 'check_hosp', 'maininscl', 'inscl', 'ih_hba1c', 'ih_fpg1', 'ih_fpg2', 'ih_fpg3', 'ih_creatinine', 'ih_lipid', 'ih_foot', 'ih_retina', 'ih_bp1', 'ih_bp2'], 'string', 'max' => 5],
            [['CID', 'FATHER', 'MOTHER', 'COUPLE'], 'string', 'max' => 13],
            [['PID', 'HN'], 'string', 'max' => 15],
            [['HID'], 'string', 'max' => 14],
            [['PRENAME', 'OCCUPATION_OLD', 'RACE', 'NATION'], 'string', 'max' => 3],
            [['NAME', 'LNAME'], 'string', 'max' => 50],
            [['SEX', 'MSTATUS', 'FSTATUS', 'VSTATUS', 'DISCHARGE', 'ABOGROUP', 'RHGROUP', 'TYPEAREA', 'check_typearea', 'SERVPLACE', 'SMOKE', 'ALCOHOL', 'DMFAMILY', 'HTFAMILY', 'BSTEST', 'chronic', 'screen'], 'string', 'max' => 1],
            [['OCCUPATION_NEW'], 'string', 'max' => 4],
            [['RELIGION', 'EDUCATION', 'LABOR', 'type_dx'], 'string', 'max' => 2],
            [['PASSPORT', 'vhid', 'check_vhid'], 'string', 'max' => 8],
            [['addr', 'groupcode1560', 'groupname1560'], 'string', 'max' => 100],
            [['source_tb', 'mix_dx', 't_mix_dx', 'date_dx', 'hosp_dx'], 'string', 'max' => 255],
            [['rs_hba1c', 'rs_fpg1', 'rs_fpg2', 'rs_fpg3', 'rs_creatinine', 'rs_lipid', 'rs_foot', 'rs_retina', 'rs_bps1', 'rs_bpd1', 'rs_bps2', 'rs_bpd2'], 'string', 'max' => 10],
            [['complication_dm', 'complication_ht'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'HOSPCODE' => 'Hospcode',
            'CID' => 'Cid',
            'PID' => 'Pid',
            'HID' => 'Hid',
            'PRENAME' => 'Prename',
            'NAME' => 'Name',
            'LNAME' => 'Lname',
            'HN' => 'Hn',
            'SEX' => 'Sex',
            'BIRTH' => 'Birth',
            'MSTATUS' => 'Mstatus',
            'OCCUPATION_OLD' => 'Occupation  Old',
            'OCCUPATION_NEW' => 'Occupation  New',
            'RACE' => 'Race',
            'NATION' => 'Nation',
            'RELIGION' => 'Religion',
            'EDUCATION' => 'Education',
            'FSTATUS' => 'Fstatus',
            'FATHER' => 'Father',
            'MOTHER' => 'Mother',
            'COUPLE' => 'Couple',
            'VSTATUS' => 'Vstatus',
            'MOVEIN' => 'Movein',
            'DISCHARGE' => 'Discharge',
            'DDISCHARGE' => 'Ddischarge',
            'ABOGROUP' => 'Abogroup',
            'RHGROUP' => 'Rhgroup',
            'LABOR' => 'Labor',
            'PASSPORT' => 'Passport',
            'TYPEAREA' => 'Typearea',
            'D_UPDATE' => 'D  Update',
            'check_hosp' => 'Check Hosp',
            'check_typearea' => 'Check Typearea',
            'vhid' => 'Vhid',
            'check_vhid' => 'Check Vhid',
            'maininscl' => 'Maininscl',
            'inscl' => 'Inscl',
            'age_y' => 'Age Y',
            'addr' => 'Addr',
            'source_tb' => 'Source Tb',
            'groupcode1560' => 'Groupcode1560',
            'groupname1560' => 'Groupname1560',
            'mix_dx' => 'Mix Dx',
            't_mix_dx' => 'T Mix Dx',
            'type_dx' => 'Type Dx',
            'date_dx' => 'Date Dx',
            'hosp_dx' => 'Hosp Dx',
            'ld_hba1c' => 'Ld Hba1c',
            'rs_hba1c' => 'Rs Hba1c',
            'ih_hba1c' => 'Ih Hba1c',
            'ld_fpg1' => 'Ld Fpg1',
            'rs_fpg1' => 'Rs Fpg1',
            'ih_fpg1' => 'Ih Fpg1',
            'ld_fpg2' => 'Ld Fpg2',
            'rs_fpg2' => 'Rs Fpg2',
            'ih_fpg2' => 'Ih Fpg2',
            'ld_fpg3' => 'Ld Fpg3',
            'rs_fpg3' => 'Rs Fpg3',
            'ih_fpg3' => 'Ih Fpg3',
            'ld_creatinine' => 'Ld Creatinine',
            'rs_creatinine' => 'Rs Creatinine',
            'ih_creatinine' => 'Ih Creatinine',
            'ld_lipid' => 'Ld Lipid',
            'rs_lipid' => 'Rs Lipid',
            'ih_lipid' => 'Ih Lipid',
            'ld_foot' => 'Ld Foot',
            'rs_foot' => 'Rs Foot',
            'ih_foot' => 'Ih Foot',
            'ld_retina' => 'Ld Retina',
            'rs_retina' => 'Rs Retina',
            'ih_retina' => 'Ih Retina',
            'ld_bp1' => 'Ld Bp1',
            'ih_bp1' => 'Ih Bp1',
            'rs_bps1' => 'Rs Bps1',
            'rs_bpd1' => 'Rs Bpd1',
            'ld_bp2' => 'Ld Bp2',
            'ih_bp2' => 'Ih Bp2',
            'rs_bps2' => 'Rs Bps2',
            'rs_bpd2' => 'Rs Bpd2',
            'complication_dm' => 'Complication Dm',
            'complication_ht' => 'Complication Ht',
            'control_dm' => 'Control Dm',
            'control_ht' => 'Control Ht',
            'bmi' => 'Bmi',
            'obes' => 'Obes',
            'height' => 'Height',
            'weight' => 'Weight',
            'waist_cm' => 'Waist Cm',
            'min_date_dx_dm' => 'Min Date Dx Dm',
            'min_date_dx_ht' => 'Min Date Dx Ht',
            'SERVPLACE' => 'Servplace',
            'SMOKE' => 'Smoke',
            'ALCOHOL' => 'Alcohol',
            'DMFAMILY' => 'Dmfamily',
            'HTFAMILY' => 'Htfamily',
            'BSTEST' => 'Bstest',
            'BSLEVEL' => 'Bslevel',
            'chronic' => 'Chronic',
            'screen' => 'Screen',
        ];
    }
}
