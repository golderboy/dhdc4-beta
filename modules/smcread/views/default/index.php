<?php

use yii\bootstrap\Html;
use yii\helpers\Json;

$smartcardBaseUrl = trim((string) getenv('DHDC_SMARTCARD_BASE_URL'));
if ($smartcardBaseUrl === '') {
    $smartcardBaseUrl = 'http://127.0.0.1:8080/smartcard';
}

$smartcardUrl = parse_url($smartcardBaseUrl);
$smartcardScheme = strtolower((string) ($smartcardUrl['scheme'] ?? ''));
$smartcardHost = strtolower((string) ($smartcardUrl['host'] ?? ''));
$loopbackHosts = ['localhost', '127.0.0.1', '::1'];
$isSecureEndpoint = $smartcardScheme === 'https';
$isLoopbackEndpoint = $smartcardScheme === 'http' && in_array($smartcardHost, $loopbackHosts, true);

if (!$isSecureEndpoint && !$isLoopbackEndpoint) {
    throw new \RuntimeException('DHDC_SMARTCARD_BASE_URL must use HTTPS or an HTTP loopback host.');
}

$smartcardBaseUrl = rtrim($smartcardBaseUrl, '/');
$smartcardPictureUrl = $smartcardBaseUrl . '/picture/';
$smartcardDataUrl = $smartcardBaseUrl . '/data/';

$this->params['breadcrumbs'][] = "ระบบการตรวจสอบสิทธิด้วยบัตรประชาชน"
?>
<div class="smcread-default-index">
    <div style="margin-bottom: 5px">
        <img src="<?= Html::encode($smartcardPictureUrl) ?>" width="250" height="250" alt="Smart card portrait"/>
    </div>
    <div class="input-group">
        <input type="password" class="form-control" placeholder="รหัสยืนยัน" name="pass" id="pass" autocomplete="off">
        <span class="input-group-btn" style="width:0;">
            <button class="btn btn-default" type="button" id="btn-go">OK !</button>
        </span>
    </div>
</div>

<?php
$smartcardDataUrlJson = Json::htmlEncode($smartcardDataUrl);
$js = <<<JS
   
    $.ajaxSetup({
        async: false
    });
    var url = {$smartcardDataUrlJson};
    $('#btn-go').click(function(e){
        var pass = $('#pass').val();
        var data = getCard(url);
        
        if (!data) {
            window.alert('ไม่สามารถอ่านข้อมูลจากบัตรได้ กรุณาลองใหม่อีกครั้ง');
        }
        
    });
    var getCard = function(url){   
        var data = null;
        $.getJSON(url, function( res ){
            data = res;
        });
        return data;
    };
JS;

$this->registerJs($js);
