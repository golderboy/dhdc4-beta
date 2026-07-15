<?php

use dektrium\user\widgets\Connect;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var dektrium\user\models\LoginForm $model
 * @var dektrium\user\Module $module
 */

$this->title = Yii::t('user', 'Sign in');
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('/_alert', ['module' => Yii::$app->getModule('user')]) ?>

<div class="dhdc-login-shell">
    <div class="dhdc-login-info">
        <h1 class="dhdc-page-title">เข้าสู่ระบบผู้ดูแล</h1>
        <div class="dhdc-page-subtitle">สำหรับผู้ดูแลระบบที่ได้รับอนุญาต</div>
    </div>

    <div class="dhdc-login-card">
        <h2 class="dhdc-login-title">ลงชื่อเข้าใช้งาน</h2>
        <div class="dhdc-login-subtitle">กรอกชื่อผู้ใช้หรืออีเมลและรหัสผ่านผู้ดูแล</div>

        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
            'enableAjaxValidation' => true,
            'enableClientValidation' => false,
            'validateOnBlur' => false,
            'validateOnType' => false,
            'validateOnChange' => false,
        ]) ?>

        <?= $form->field($model, 'login', [
            'inputOptions' => [
                'autofocus' => 'autofocus',
                'class' => 'form-control',
                'tabindex' => '1',
            ],
        ])->label('ชื่อผู้ใช้หรืออีเมล') ?>

        <?= $form->field($model, 'password', [
            'inputOptions' => [
                'class' => 'form-control',
                'tabindex' => '2',
            ],
        ])->passwordInput()->label(
            'รหัสผ่าน' . ($module->enablePasswordRecovery
                ? ' (' . Html::a('ลืมรหัสผ่าน?', ['/user/recovery/request'], ['tabindex' => '5']) . ')'
                : '')
        ) ?>

        <?= $form->field($model, 'rememberMe')->checkbox(['tabindex' => '3'])->label('จดจำการเข้าสู่ระบบ') ?>

        <?= Html::submitButton('เข้าสู่ระบบ', ['class' => 'btn btn-primary btn-block', 'tabindex' => '4']) ?>

        <?php ActiveForm::end(); ?>

        <?php if ($module->enableConfirmation): ?>
            <p class="text-center dhdc-login-help">
                <?= Html::a(Yii::t('user', 'Didn\'t receive confirmation message?'), ['/user/registration/resend']) ?>
            </p>
        <?php endif ?>
        <?php if ($module->enableRegistration): ?>
            <p class="text-center dhdc-login-help">
                <?= Html::a(Yii::t('user', 'Don\'t have an account? Sign up!'), ['/user/registration/register']) ?>
            </p>
        <?php endif ?>
        <?= Connect::widget([
            'baseAuthUrl' => ['/user/security/auth'],
        ]) ?>
    </div>
</div>
