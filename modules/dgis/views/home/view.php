<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model frontend\modules\dgis\models\Home */

$this->title = $model->HOSPCODE;
$this->params['breadcrumbs'][] = ['label' => 'Homes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="home-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'HOSPCODE' => $model->HOSPCODE, 'HID' => $model->HID], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'HOSPCODE' => $model->HOSPCODE, 'HID' => $model->HID], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'HOSPCODE',
            'HID',
            'HOUSE_ID',
            'HOUSETYPE',
            'ROOMNO',
            'CONDO',
            'HOUSE',
            'SOISUB',
            'SOIMAIN',
            'ROAD',
            'VILLANAME',
            'VILLAGE',
            'TAMBON',
            'AMPUR',
            'CHANGWAT',
            'TELEPHONE',
            'LATITUDE',
            'LONGITUDE',
            'NFAMILY',
            'LOCATYPE',
            'VHVID',
            'HEADID',
            'TOILET',
            'WATER',
            'WATERTYPE',
            'GARBAGE',
            'HOUSING',
            'DURABILITY',
            'CLEANLINESS',
            'VENTILATION',
            'LIGHT',
            'WATERTM',
            'MFOOD',
            'BCONTROL',
            'ACONTROL',
            'CHEMICAL',
            'OUTDATE',
            'D_UPDATE',
        ],
    ]) ?>

</div>
