<?php
/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;


AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title>DHDC จัดการระบบ</title>
        <?php $this->head() ?>
    </head>
    <body>
        <?php $this->beginBody() ?>

        <div class="wrap">
            <?php
            NavBar::begin([
                'brandLabel' => 'DHDC จัดการระบบ',
                'brandUrl' => Yii::$app->homeUrl,
                'options' => [
                    'class' => 'navbar-custom navbar-fixed-top',
                ],
            ]);
            $menuItems[] = ['label' => '<i class="glyphicon glyphicon-home"></i> หน้าหลัก', 'url' => \Yii::$app->homeUrl];
            if (\Yii::$app->user->can('Admin')) {
                $menuItems[] = ['label' => 'จัดการผู้ใช้', 'url' => ['/user/admin/index']];
            }
            if (\Yii::$app->user->can('Admin')) {
                $menuItems[] = ['label' => 'สิทธิใช้งาน', 'url' => ['/gate/default/rbac-gate']];
            }
            if (Yii::$app->user->isGuest) {
                $menuItems[] = ['label' => 'Login', 'url' => ['/user/security/login']];
            } else {
                $menuItems[] = [
                    'label' => '<span class="glyphicon glyphicon-user"></span> ' . \Yii::$app->user->identity->username,
                    'items' => [
                        ['label' => '<i class="glyphicon glyphicon-info-sign"></i> ข้อมูลส่วนตัว', 'url' => ['/user/settings/profile', 'id' => \Yii::$app->user->id]],
                        '<li class="divider"></li>',
                        ['label' => '<span class="glyphicon glyphicon-off"></span> Logout', 'url' => ['/user/security/logout'], 'linkOptions' => ['data-method' => 'post']],
                    ],
                ];
            }
            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => $menuItems,
                'encodeLabels' => false,
            ]);
            NavBar::end();
            ?>

            <div class="container">
                <?=
                Breadcrumbs::widget([
                    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                ])
                ?>
               <?php echo \yii2mod\notify\BootstrapNotify::widget(); ?>
                <?= $content ?>
            </div>
        </div>

     <footer class="footer">
            <div class="container">
                <div class="pull-left">&copy; DHDC</div>
            </div>
        </footer>

<?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
