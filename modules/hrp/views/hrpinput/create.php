<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model modules\hrp\models\Hrpinput */

$this->title = 'Create Hrpinput';
$this->params['breadcrumbs'][] = ['label' => 'Hrpinputs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hrpinput-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
