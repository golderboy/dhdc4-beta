<?php
/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use frontend\assets\MuiWebAsset;
use common\models\config\SysConfigMain;
use common\models\config\SysProcessRunning;

AppAsset::register($this);
MuiWebAsset::register($this);

$config = SysConfigMain::find()->one();
$district = 'ไม่ตั้งค่า';
if ($config) {
    $district = $config->district_name;
}
?>


<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
        <meta charset="<?= Yii::$app->charset ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title>
            <?php
            //echo Html::encode($this->title);
            echo "DHDC 4.0"
            ?>
        </title>
        <?php $this->head() ?>
    </head>
    <body class="mui-web-scope" data-mui-web-color-scheme="light">
        <?php $this->beginBody() ?>

        <div class="wrap mui-web-app-shell">
            <?php
            NavBar::begin([
                'brandLabel' => '<span class="glyphicon glyphicon-phone"></span>',

                'brandUrl' => Yii::$app->homeUrl,
                'options' => [
                    'class' => 'navbar-custom navbar-fixed-top mui-web-app-bar',
                ],
            ]);

            
            $rpt_mnu_itms[] = ['label' => '<i class="glyphicon glyphicon-search"></i> รายงาน HDC', 'url' => ['/hdc/default/index']];
             $rpt_mnu_itms[] = '<li class="divider"></li>';
            $rpt_mnu_itms[] = ['label' => '<i class="glyphicon glyphicon-search"></i> Data-Exchange', 'url' => ['/hdcex/default/index']];

                 
           
            $menuItems = [
                ['label' => '<i class="glyphicon glyphicon-dashboard"></i> Dashboard', 'url' => ['/import/default/dashboard']],
                ['label' => '<i class="glyphicon glyphicon-floppy-open"></i> นำเข้า', 'url' => ['/import/upload/index']],    
            ];
            $menuItems[] = ['label' => '<i class="glyphicon glyphicon-modal-window"></i> ระบบงาน', 'url' => ['/plugin/default/index']];
            $menuItems[]=['label' => '<i class="glyphicon glyphicon-list-alt"></i> รายงาน', 'items' => $rpt_mnu_itms];
            
            if (Yii::$app->user->isGuest) {
                //$menuItems[] = ['label' => 'Signup', 'url' => ['/user/registration/register']];
                $menuItems[] = ['label' => 'เข้าระบบ', 'url' => ['/user/login']];
            } else {
                $user_items = [];
                $user_items[] = ['label' => '<i class="glyphicon glyphicon-info-sign"></i> ข้อมูลส่วนตัว', 'url' => ['/user/settings/profile', 'id' => \Yii::$app->user->id]];

                if (\Yii::$app->user->can('Backend')) {
                    $user_items[] = '<li class="divider"></li>';
                    $user_items[] = [
                        'label' => '<i class="glyphicon glyphicon-wrench"></i> จัดการระบบ',
                        'url' => \Yii::$app->urlManagerBackend->createUrl(['/site/index']),
                        'linkOptions' => ['target' => '_blank']
                    ];
                }
                $user_items[] = '<li class="divider"></li>';
                $user_items[] = ['label' => '<span class="glyphicon glyphicon-off"></span> Logout', 'url' => ['/site/logout'], 'linkOptions' => ['data-method' => 'post']];

                $menuItems[] = [
                    'label' => '<i class="glyphicon glyphicon-user"></i> ' . \Yii::$app->user->identity->username,
                    'items' => $user_items,
                ];
            }
            $menuItems[] = ['label' => 'เกี่ยวกับ', 'url' => ['/site/about']];
            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => $menuItems,
                'encodeLabels' => false,
            ]);
            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-left'],
                'encodeLabels' => false,
                'items' => [['label' => 'DHDC 4.0 ' . $district]],
            ]);


            NavBar::end();
            ?>

            <div class="container mui-web-page-container">
                <?php
                $running = SysProcessRunning::find()->one();
                ?>
                <?php if ($running && $running->is_running == 'true'): ?>
                    <div class="alert alert-warning">ระบบกำลังประมวลผล กรุณารอสักครู่</div>
                <?php endif; ?>
                <?=
                Breadcrumbs::widget([
                    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                ])
                ?>

                <?php echo \yii2mod\notify\BootstrapNotify::widget(); ?>

                <?= $content ?>
            </div>
        </div>

        <footer class="footer mui-web-footer">
            <div class="container">
                <div class="pull-left">
                    &copy; DHDC
                    <span style="margin-right: 10px"></span>
                <?= Html::img('@web/images/smc_icon.png',['width'=>'25','height'=>'25']);?>
                 
                </div>
            </div>
        </footer>

        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
