<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model modules\student\models\Student */

$this->title = $model->HOSPCODE;
$this->params['breadcrumbs'][] = ['label' => 'Students', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="student-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'HOSPCODE' => $model->HOSPCODE, 'PID' => $model->PID, 'SCHOOLCODE' => $model->SCHOOLCODE, 'EDUCATIONYEAR' => $model->EDUCATIONYEAR, 'CLASS' => $model->CLASS], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'HOSPCODE' => $model->HOSPCODE, 'PID' => $model->PID, 'SCHOOLCODE' => $model->SCHOOLCODE, 'EDUCATIONYEAR' => $model->EDUCATIONYEAR, 'CLASS' => $model->CLASS], [
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
            'PID',
            'SCHOOLCODE',
            'EDUCATIONYEAR',
            'CLASS',
            'D_UPDATE',
            'GRUDATE_DATE',
            'id',
        ],
    ]) ?>

</div>
