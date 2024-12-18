<?php

namespace app\models\notification;

use Yii;
use \yii\db\ActiveRecord;


class UserChat extends ActiveRecord
{

	public static function tableName()
	{
		return 'user_chat';
	}

	public function rules()
	{
		return [
			[['user_id', 'chat_id'], 'unique'],
			[['user_id', 'chat_id', 'role_id'], 'integer'],
			[['status'], 'boolean'],
		];
	}

	public static function primaryKey()
	{
		return [
			'user_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'user_id' => Yii::t('app', '№ пользователя'),
			'status' => Yii::t('app', 'Статус пользователя'),
		];
	}

	public const STATUS_ACTIVE = 1;
	public const STATUS_INACTIVE = 0;

	public const ROLE_STUDENT = 1;
	public const ROLE_MODERATOR = 9;
	public const ROLE_ADMIN = 10;


	public function isActive() {
		return ($this->status == static::STATUS_ACTIVE);
	}

	public static function createStudent(int $chat_id, int $user_id): self
	{
		$subscriber = new self;
		$subscriber->chat_id = $chat_id;
		$subscriber->user_id = $user_id;
		$subscriber->role_id = static::ROLE_STUDENT;
		$subscriber->status = static::STATUS_ACTIVE;
		$subscriber->save();
		return $subscriber;
	}

	public static function findByChat(int $chat_id)
	{
		return self::find()
			->where(['chat_id' => $chat_id])
			->one();
	}

	public function setActive()
	{
		$this->status = static::STATUS_ACTIVE;
		$this->save();
	}

	public function setInactive()
	{
		$this->status = static::STATUS_INACTIVE;
		$this->save();
	}

	public static function getUserIds()
	{
		return self::find()
			->select('chat_id')
			->where(['status' => static::STATUS_ACTIVE])
			->column();
	}

	public static function getAdminIds()
	{
		return self::find()
			->select('chat_id')
			->where(['role_id' => static::ROLE_ADMIN])
			->andWhere(['status' => static::STATUS_ACTIVE])
			->column();
	}
}
