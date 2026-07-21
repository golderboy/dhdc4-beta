<?php

namespace common\tests\unit\models;

use Yii;
use common\models\LoginForm;
use yii\base\Model;
use yii\web\IdentityInterface;

/**
 * Login form test
 */
class LoginFormTest extends \Codeception\Test\Unit
{
    protected function _before(): void
    {
        Yii::$app->user->logout(false);
    }

    public function testLoginNoUser()
    {
        $model = new TestableLoginForm([
            'username' => 'not_existing_username',
            'password' => 'not_existing_password',
        ]);

        $this->assertFalse($model->login(), 'model should not login user');
        $this->assertTrue(Yii::$app->user->isGuest, 'user should not be logged in');
    }

    public function testLoginWrongPassword()
    {
        $model = new TestableLoginForm([
            'username' => 'bayer.hudson',
            'password' => 'wrong_password',
            'identity' => new TestIdentity('password_0'),
        ]);

        $this->assertFalse($model->login(), 'model should not login user');
        $this->assertArrayHasKey('password', $model->errors, 'error message should be set');
        $this->assertTrue(Yii::$app->user->isGuest, 'user should not be logged in');
    }

    public function testLoginCorrect()
    {
        $model = new TestableLoginForm([
            'username' => 'bayer.hudson',
            'password' => 'password_0',
            'identity' => new TestIdentity('password_0'),
        ]);

        $this->assertTrue($model->login(), 'model should login user');
        $this->assertArrayNotHasKey('password', $model->errors, 'error message should not be set');
        $this->assertFalse(Yii::$app->user->isGuest, 'user should be logged in');
    }
}

final class TestableLoginForm extends LoginForm
{
    public ?IdentityInterface $identity = null;

    protected function getUser()
    {
        return $this->identity;
    }
}

final class TestIdentity extends Model implements IdentityInterface
{
    private string $passwordHash;

    public function __construct(string $password, array $config = [])
    {
        $this->passwordHash = Yii::$app->security->generatePasswordHash($password);
        parent::__construct($config);
    }

    public static function findIdentity($id)
    {
        return null;
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    public function getId()
    {
        return 1;
    }

    public function getAuthKey()
    {
        return 'test-auth-key';
    }

    public function validateAuthKey($authKey)
    {
        return $authKey === $this->getAuthKey();
    }

    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->passwordHash);
    }
}
