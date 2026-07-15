<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'เข้าสู่ระบบ';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-login">
    <div class="dhdc-login-shell">
        <div class="dhdc-login-info">
            <h1 class="dhdc-page-title"><?= Html::encode($this->title) ?></h1>
            <div class="dhdc-page-subtitle">ใช้บัญชีผู้ใช้เดิมของระบบเพื่อเข้าสู่หน้ารายงานและเมนูที่ต้องมีสิทธิ์</div>
        </div>

        <div class="dhdc-login-card">
            <h2 class="dhdc-login-title">ลงชื่อเข้าใช้งาน</h2>
            <div class="dhdc-login-subtitle">กรอกชื่อผู้ใช้และรหัสผ่าน</div>

            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

                <?= $form->field($model, 'password')->passwordInput() ?>

                <?= $form->field($model, 'rememberMe')->checkbox() ?>

                <div class="dhdc-login-help">
                    หากลืมรหัสผ่านสามารถ <?= Html::a('ขอรหัสผ่านใหม่', ['site/request-password-reset']) ?>
                </div>

                <div class="form-group">
                    <?= Html::submitButton('เข้าสู่ระบบ', ['class' => 'btn btn-primary btn-block', 'name' => 'login-button']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
