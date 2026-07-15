<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model modules\student\models\School */

$this->title = $model->HOSPCODE;
$this->params['breadcrumbs'][] = ['label' => 'Schools', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="school-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'HOSPCODE' => $model->HOSPCODE, 'VID' => $model->VID, 'SCHOOLCODE' => $model->SCHOOLCODE], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'HOSPCODE' => $model->HOSPCODE, 'VID' => $model->VID, 'SCHOOLCODE' => $model->SCHOOLCODE], [
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
            'VID',
            'SCHOOLCODE',
            'SCHOOLID',
            'SCHOOLNAME',
            'SCHOOLOWNER',
            'SCHOOLTYPE',
            'CLOSEDDATE',
            'D_UPDATE',
        ],
    ]) ?>

</div>
