<?php

namespace components;

use Yii;
use yii\web\ErrorAction;
use yii\web\HttpException;

class SafeErrorAction extends ErrorAction
{
    private $referenceId;

    public function init()
    {
        parent::init();
        $this->referenceId = strtoupper(bin2hex(random_bytes(6)));
    }

    public function run()
    {
        Yii::$app->response->headers->set('X-Request-ID', $this->referenceId);
        Yii::error([
            'referenceId' => $this->referenceId,
            'exceptionClass' => get_class($this->exception),
            'statusCode' => $this->getStatusCode(),
        ], 'application.error-reference');

        return parent::run();
    }

    protected function renderAjaxResponse()
    {
        return $this->getSafeMessage() . ' (รหัสอ้างอิง: ' . $this->referenceId . ')';
    }

    protected function getViewRenderParams()
    {
        return [
            'name' => 'ไม่สามารถดำเนินการได้',
            'message' => $this->getSafeMessage(),
            'referenceId' => $this->referenceId,
            'exception' => $this->exception,
        ];
    }

    private function getStatusCode()
    {
        return $this->exception instanceof HttpException ? (int) $this->exception->statusCode : 500;
    }

    private function getSafeMessage()
    {
        switch ($this->getStatusCode()) {
            case 401:
                return 'กรุณาเข้าสู่ระบบก่อนดำเนินการ';
            case 403:
                return 'คุณไม่มีสิทธิ์เข้าถึงข้อมูลนี้';
            case 404:
                return 'ไม่พบข้อมูลที่ต้องการ';
            case 422:
                return 'ข้อมูลที่ส่งมาไม่ถูกต้อง กรุณาตรวจสอบแล้วลองใหม่อีกครั้ง';
            default:
                return 'ไม่สามารถดำเนินการได้ กรุณาลองใหม่อีกครั้ง หรือติดต่อผู้ดูแลระบบ';
        }
    }
}
