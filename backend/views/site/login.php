<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'เข้าสู่ระบบผู้ดูแล';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <div class="dhdc-login-shell">
        <div class="dhdc-login-info">
            <h1 class="dhdc-page-title"><?= Html::encode($this->title) ?></h1>
            <div class="dhdc-page-subtitle">สำหรับผู้ดูแลระบบที่ได้รับอนุญาต</div>
        </div>

        <div class="dhdc-login-card">
            <h2 class="dhdc-login-title">ลงชื่อเข้าใช้งาน</h2>
            <div class="dhdc-login-subtitle">กรอกชื่อผู้ใช้และรหัสผ่านผู้ดูแล</div>

            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

                <?= $form->field($model, 'password')->passwordInput() ?>

                <?= $form->field($model, 'rememberMe')->checkbox() ?>

                <div class="form-group">
                    <?= Html::submitButton('เข้าสู่ระบบ', ['class' => 'btn btn-primary btn-block', 'name' => 'login-button']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
