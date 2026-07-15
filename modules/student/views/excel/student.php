<?php

use yii\widgets\ActiveForm;
use kartik\grid\GridView;
use kartik\file\FileInput;
use yii\helpers\Url;
use yii\helpers\Html;
//use frontend\modules\cfo\models\LogImport;

$this->title = "นำเข้า EXCEL ข้อมูลนักเรียน";
$this->params['breadcrumbs'][] = ['label' => 'ระบบข้อมูลนักเรียน', 'url' => ['/student/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'ข้อมูลนักเรียน', 'url' => ['/student/student/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $form = ActiveForm::begin() ?>

<?php // $form->field($mUpload, 'dataFile')->fileInput() ?>
<?= $form->field($mUpload, 'dataFile')->widget(FileInput::classname(), [
    //'options' => ['accept' => 'image/*'],
    'pluginOptions' => [
        'initialPreview'=>[],
        'allowedFileExtensions'=>['xlsx'],
        'showPreview' => false,
        'showRemove' => true,
        'showUpload' => false
     ]
]); ?>
<button>นำเข้า</button>
<p>นำเข้าไฟล์นามสกุล *.Xlsx เท่านั้น<p>
<p>
<?php echo Html::a('วิธีการดึงรายงาน','https://goo.gl/3QuWTj');?> 
<p>
<?php ActiveForm::end() ?>

