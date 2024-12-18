<?php

namespace app\models\register;

use Yii;
use yii\db\ActiveRecord;

use app\models\service\Bot;


class RegisterRejected extends ActiveRecord
{

	public static function tableName()
	{
		return 'register_rejected';
	}

	public function rules()
	{
		return [
			[['id'], 'unique'],
			[['id', 'status', 'reject_reason'], 'integer'],
			[['fio', 'group_name', 'email', 'student_email'], 'string', 'max' => 50],
			[['email', 'student_email'], 'email'],
			[['birth_date'], 'date', 'format' => 'php:Y-m-d'],
			[['register_datetime'], 'date', 'format' => 'php:Y-m-d H:i:s'],
			[['fio', 'group_name', 'email', 'student_email', 'birth_date'], 'required'],
		];
	}

	public static function primaryKey()
	{
		return [
			'id'
		];
	}

	public function attributeLabels()
	{
		return [
			'id'	=> Yii::t('app', 'Код'),
			'fio'	=> Yii::t('app', 'Ф.И.О.'),
			'group_name'	=> Yii::t('app', 'Группа'),
			'email'	=> Yii::t('app', 'Личная почта'),
			'student_email'	=> Yii::t('app', 'Корпоративная студенческая почта'),
			'birth_date'	=> Yii::t('app', 'Дата рождения'),
		];
	}

	public function init()
	{
		$this->on(static::EVENT_BEFORE_VALIDATE, [$this, 'checkBeforeValidate']);
		$this->on(static::EVENT_AFTER_INSERT, [$this, 'sendMessage']);

		parent::init();
	}

	protected function checkBeforeValidate($event)
	{
		$this->register_datetime = date("Y-m-d H:i:s", strtotime($this->register_datetime));
		$this->birth_date = date("Y-m-d", strtotime($this->birth_date));
	}

	public function getRejectReason()
	{
		return $this->hasOne(RegisterRejectReason::class, ['reject_reason' => 'reson_id']);
	}

	public function isWrongData(): bool
	{
		return ($this->status == Register::STATUS_WRONG_DATA);
	}

	public function isBlocked(): bool
	{
		return ($this->status == Register::STATUS_BLOCKED);
	}

	public function sendMessage($event)
	{
		$bot = new Bot;
		$bot->messageRegisterRejected($this->id);
	}

}
