<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use kartik\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel modules\Qof\models\DhdcqofreportSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '7.2	ร้อยละของเด็กอายุ ๙, ๑๘, ๓๐, ๔๒ เดือน ได้รับการประเมินพัฒนาการ และพบเด็กสงสัยล่าช้า';
$this->params['breadcrumbs'][] = ['label' => 'รายงาน QOF', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dhdcqofreport-index">

    <h3 class="panel-title"><?= Html::encode($this->title) ?></h3>
    <br>
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
<div class="row">
    <div class="col-md-12">
        <?= GridView::widget([
            'dataProvider' => $summary,
            'panel' => [
                'heading'=>'<h3 class="panel-title">ภาพรวมคัพ</h3>',
                'type'=>'success',
            ],
        ]); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
    <?php
       echo !Yii::$app->user->isGuest ?
            GridView::widget([
            'dataProvider' => $data,
            'panel' => [
                'heading'=>'<h3 class="panel-title">รายชื่อ</h3>',
                'type'=>'info',
            ],
        ])
        : '<h3 style="color: red;">เข้าสู่ระบบหากต้องการดูข้อมูลรายบุคคล</h3>'; 
        ?>
    </div>
</div>
    <?php Pjax::end(); ?>
</div>
