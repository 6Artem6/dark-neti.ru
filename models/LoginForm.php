<?php

namespace app\models;

use Yii;
use yii\base\Model;

use app\models\user\User;


class LoginForm extends Model
{
	public $username;
	public $password;
	public $rememberMe = true;

	private $_user = false;

	public function rules()
	{
		return [
			[['username', 'password'], 'required'],
			['rememberMe', 'boolean'],
			['password', 'validatePassword'],
		];
	}

	public function attributeLabels()
	{
		return [
			'username' => Yii::t('app', 'Логин или почта'),
			'password' => Yii::t('app', 'Пароль'),
			'rememberMe' => Yii::t('app', 'Запомнить меня'),
		];
	}

	public function validatePassword($attribute, $params)
	{
		if (!$this->hasErrors()) {
			$user = $this->getUser();

			if (!$user || !$user->validatePassword($this->password)) {
				$this->addError($attribute, Yii::t('app', 'Неверный логин и/или пароль.'));
			} elseif ($user and $user->isInactive()) {
				$this->addError('username', Yii::t('app', 'Вы больше не можете входить в аккаунт.'));
			}
		}
	}

	public function login()
	{
		if ($this->validate()) {
			return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 60*60*24*7 : 0);
		}
		return false;
	}

	public function loginGuest()
	{
		$id = User::GUEST_ID;
		$user = User::findOne($id);
		return Yii::$app->user->login($user);
	}

	public function getUser()
	{
		if ($this->_user === false) {
			if (str_contains($this->username, '@')) {
				$this->_user = User::findByStudentEmail($this->username);
			} else {
				$this->_user = User::findByUsername($this->username);
			}
		}

		return $this->_user;
	}
}
