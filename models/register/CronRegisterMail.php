<?php

namespace app\models\register;

use Yii;
use yii\db\ActiveRecord;


class CronRegisterMail extends ActiveRecord
{

	public static function tableName()
	{
		return 'cron_register_mail';
	}

	public function rules()
	{
		return [
			[['!register_id'], 'unique'],
			[['register_id'], 'integer'],
			[['!repeat_datetime'], 'date', 'format' => 'php:Y-m-d H:i:s'],
		];
	}

	public static function primaryKey()
	{
		return [
			'register_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'register_id' => Yii::t('app', 'Код'),
			'repeat_datetime' => Yii::t('app', 'Время'),
		];
	}

	public function getRegister()
	{
		return $this->hasOne(Register::class, ['id' => 'register_id']);
	}

	public static function getList()
	{
		return self::find()
			->joinWith('register')
			->all();
	}

	public static function getCurrentList()
	{
		return self::find()
			->joinWith('register')
			->where(['<', 'repeat_datetime', date('Y-m-d H:i:s')])
			->all();
	}

	public static function checkRegisterMail()
	{
		$list = self::getCurrentList();
		foreach ($list as $record) {
			$record->register->sendMailStudentMail();
			$record->delete();
		}
	}

}
