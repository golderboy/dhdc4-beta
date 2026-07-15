<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model modules\Qof\models\Dhdcqofreport */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'รายงาน QOF', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dhdcqofreport-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('ลบ', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('สร้าง+', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'url:url',
            'table',
            'description:ntext',
            'active',
        ],
    ]) ?>

</div>
