<?php

namespace modules\sqlquery\controllers;

use Yii;
use components\MyHelper;
use modules\sqlquery\models\Sqlscript;
use yii\filters\AccessControl;
use yii\web\Controller;

class RunqueryController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => MyHelper::modIsOn(),
                        'roles' => ['Admin'],
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        $saved = false;
        if (Yii::$app->request->isPost) {
            $sql = trim((string)Yii::$app->request->post('sql_code'));

            try {
                $sql = $this->normalizeReadOnlySelect($sql);
                Yii::warning([
                    'event' => 'sql_runner_attempt',
                    'user' => Yii::$app->user->identity ? Yii::$app->user->identity->username : null,
                    'ip' => Yii::$app->request->userIP,
                    'sql_hash' => hash('sha256', $sql),
                ], 'security.sqlquery');
                $rawData = Yii::$app->db->createCommand($sql)->queryAll();
            } catch (\yii\web\HttpException $e) {
                Yii::warning([
                    'event' => 'sql_runner_rejected',
                    'user' => Yii::$app->user->identity ? Yii::$app->user->identity->username : null,
                    'ip' => Yii::$app->request->userIP,
                    'reason' => $e->getMessage(),
                ], 'security.sqlquery');
                throw $e;
            } catch (\yii\db\Exception $e) {
                Yii::warning([
                    'event' => 'sql_runner_db_error',
                    'user' => Yii::$app->user->identity ? Yii::$app->user->identity->username : null,
                    'ip' => Yii::$app->request->userIP,
                    'code' => $e->getCode(),
                ], 'security.sqlquery');
                throw new \yii\web\ConflictHttpException('ไม่สามารถประมวลผลคำขอได้ กรุณาตรวจสอบข้อมูลแล้วลองใหม่อีกครั้ง');
            }

            if (isset($_POST['save'])) {
                $model = new Sqlscript();
                $model->topic = 'กรุณาแก้ชื่อ script';
                $model->sql_script = $sql;
                $model->user = Yii::$app->user->identity->username;
                $model->d_update = date('Y-m-d H:i:s');
                if ($model->save(false)) {
                    $saved = true;
                }
            }

            $dataProvider = new \yii\data\ArrayDataProvider([
                'allModels' => $rawData,
                'pagination' => false,
            ]);

            return $this->render('index', [
                'dataProvider' => $dataProvider,
                'sql_code' => $sql,
                'saved' => $saved ? '[บันทึก script แล้ว]' : '',
            ]);
        }

        return $this->render('index', [
            'saved' => '',
        ]);
    }

    public function actionResult()
    {
    }

    protected function normalizeReadOnlySelect($sql)
    {
        $sql = trim((string)$sql);
        if ($sql === '') {
            throw new \yii\web\BadRequestHttpException('SQL is required.');
        }

        $statements = $this->splitSqlStatements($sql);
        if (count($statements) !== 1) {
            throw new \yii\web\ConflictHttpException('อนุญาตเฉพาะ SQL แบบ statement เดียว');
        }

        $sql = trim($statements[0]);
        $firstToken = strtolower($this->firstSqlToken($sql));
        if (!in_array($firstToken, ['select', 'with'], true)) {
            throw new \yii\web\ConflictHttpException('อนุญาตเฉพาะคำสั่ง SELECT แบบอ่านข้อมูลเท่านั้น');
        }

        $guardSql = strtolower($this->stripSqlCommentsAndStrings($sql));
        if (preg_match('/\binto\s+(?:out|dump)file\b/i', $guardSql) || preg_match('/\bfor\s+update\b/i', $guardSql)) {
            throw new \yii\web\ConflictHttpException('ไม่อนุญาต SELECT ที่เขียนไฟล์หรือล็อกข้อมูล');
        }

        return $sql;
    }

    protected function splitSqlStatements($sql)
    {
        $statements = [];
        $current = '';
        $quote = null;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $i + 1 < $length ? $sql[$i + 1] : '';

            if ($quote !== null) {
                $current .= $char;
                if ($char === $quote && ($i === 0 || $sql[$i - 1] !== '\\')) {
                    $quote = null;
                }
                continue;
            }

            if ($char === '-' && $next === '-') {
                while ($i < $length && $sql[$i] !== "\n") {
                    $current .= $sql[$i++];
                }
                if ($i < $length) {
                    $current .= $sql[$i];
                }
                continue;
            }
            if ($char === '#') {
                while ($i < $length && $sql[$i] !== "\n") {
                    $current .= $sql[$i++];
                }
                if ($i < $length) {
                    $current .= $sql[$i];
                }
                continue;
            }
            if ($char === '/' && $next === '*') {
                $current .= $char . $next;
                $i += 2;
                while ($i < $length) {
                    $current .= $sql[$i];
                    if ($sql[$i] === '*' && $i + 1 < $length && $sql[$i + 1] === '/') {
                        $current .= '/';
                        $i++;
                        break;
                    }
                    $i++;
                }
                continue;
            }

            if ($char === '\'' || $char === '"' || $char === '`') {
                $quote = $char;
                $current .= $char;
                continue;
            }

            if ($char === ';') {
                if (trim($current) !== '') {
                    $statements[] = $current;
                }
                $current = '';
                continue;
            }

            $current .= $char;
        }

        if (trim($current) !== '') {
            $statements[] = $current;
        }

        return $statements;
    }

    protected function firstSqlToken($sql)
    {
        $sql = ltrim($this->stripLeadingSqlComments((string)$sql));
        return preg_match('/^([A-Za-z]+)/', $sql, $match) ? $match[1] : '';
    }

    protected function stripLeadingSqlComments($sql)
    {
        do {
            $before = $sql;
            $sql = preg_replace('/^\s*(?:--[^\r\n]*(?:\r?\n|$)|#[^\r\n]*(?:\r?\n|$)|\/\*.*?\*\/)/s', '', $sql);
        } while ($sql !== $before);

        return $sql;
    }

    protected function stripSqlCommentsAndStrings($sql)
    {
        $out = '';
        $quote = null;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            $next = $i + 1 < $length ? $sql[$i + 1] : '';

            if ($quote !== null) {
                if ($char === $quote && ($i === 0 || $sql[$i - 1] !== '\\')) {
                    $quote = null;
                }
                $out .= ' ';
                continue;
            }
            if ($char === '\'' || $char === '"' || $char === '`') {
                $quote = $char;
                $out .= ' ';
                continue;
            }
            if ($char === '-' && $next === '-') {
                while ($i < $length && $sql[$i] !== "\n") {
                    $i++;
                }
                $out .= ' ';
                continue;
            }
            if ($char === '#') {
                while ($i < $length && $sql[$i] !== "\n") {
                    $i++;
                }
                $out .= ' ';
                continue;
            }
            if ($char === '/' && $next === '*') {
                $i += 2;
                while ($i < $length && !($sql[$i] === '*' && $i + 1 < $length && $sql[$i + 1] === '/')) {
                    $i++;
                }
                $i++;
                $out .= ' ';
                continue;
            }

            $out .= $char;
        }

        return $out;
    }
}
