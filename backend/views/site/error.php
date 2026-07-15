<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $referenceId string */

use yii\helpers\Html;


?>
<div class="site-error">

    

    <div class="alert alert-danger">
        <?= nl2br(Html::encode($message)) ?>
        <div><small>รหัสอ้างอิง: <?= Html::encode($referenceId) ?></small></div>
    </div>

    

</div>
