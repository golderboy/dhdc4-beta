<?php
use yii\helpers\Html;

$this->title = 'SQL : ' . $rpt;
?>
<div >

    <p><?= Html::encode($rpt) ?></p>

    <pre><?= Html::encode($show_sql) ?></pre>

</div>


