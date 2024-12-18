<?php

namespace app\models\user;

use Yii;
use yii\db\ActiveRecord;


class Subscription extends ActiveRecord
{

	public static function tableName()
	{
		return 'subscription';
	}
 
	public function rules()
	{
		return [
			[['subscription_id'], 'unique'],
			[['subscription_id', 'user_id'], 'integer'],
			[['latin_name', 'file_name'], 'string', 'max' => 50],
			[['date_from', 'date_to'], 'date', 'format' => 'php:Y-m-d'],
		];
	}

	public static function primaryKey()
	{
		return [
			'subscription_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'subscription_id' => Yii::t('app', 'Подписка'),
		];
	}

	public function getUser()
	{
		return $this->hasOne(User::class, ['user_id' => 'user_id']);
	}

	public static function getUserList(int $user_id)
	{
		return self::find()
			->where(['user_id' => $user_id])
			->all();
	}

}
