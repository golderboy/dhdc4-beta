<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model modules\Qof\models\Dhdcqofreport */

$this->title = 'สร้างรายงาน QOF';
$this->params['breadcrumbs'][] = ['label' => 'รายงาน QOF', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="dhdcqofreport-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
