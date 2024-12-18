<?php

namespace app\models\follow;

use Yii;
use yii\db\ActiveRecord;

use app\models\user\{User, UserData};


class FollowUser extends ActiveRecord
{

	public static function tableName()
	{
		return 'follow_user';
	}

	public function rules()
	{
		return [
			[['user_id', 'follower_id'], 'integer'],
			[['user_id', 'follower_id'], 'unique', 'targetAttribute' => ['user_id', 'follower_id']],
			[['follow_datetime'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
		];
	}

	public static function primaryKey()
	{
		return [
			'user_id', 'follower_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'user_id' => Yii::t('app', 'Пользователь'),
			'follower_id' => Yii::t('app', 'Подписчик'),
		];
	}

	public function getUser()
	{
		return $this->hasOne(UserData::class, ['user_id' => 'user_id']);
	}

	public function getFollower()
	{
		return $this->hasOne(User::class, ['user_id' => 'follower_id']);
	}

	public static function getFollowersCount(int $user_id) {
		return self::find()
			->where(['user_id' => $user_id])
			->count();
	}

	public static function getRecord(int $user_id, int $follower_id) {
		return self::find()
			->where(['user_id' => $user_id])
			->andWhere(['follower_id' => $follower_id])
			->one();
	}

	public static function getUserIds(int $user_id) {
		return self::find()
			->select('follower_id')
			->where(['user_id' => $user_id])
			->column();
	}

	public static function follow(int $id, int $follower_id): array
	{
		$ok = false;
		$user = User::findOne($id);
		if (!$user) {
			$message = Yii::t('app', 'Пользователь не был найден!');
		} elseif ($user->id == Yii::$app->user->identity->id) {
			$message = Yii::t('app', 'Пользователь не может быть подписан на себя!');
		} elseif ($record = self::getRecord($id, $follower_id)) {
			$message = Yii::t('app', 'Вы уже подписаны на пользователя!');
		} else {
			$record = new self;
			$record->user_id = $id;
			$record->follower_id = $follower_id;
			if ($record->save()) {
				$message = Yii::t('app', 'Вы подписаны на пользователя!');
				$user->data->updateFollowers();
				$ok = true;
			} else {
				$message = Yii::t('app', 'Не удалось подписаться на пользователя!');
			}
		}
		return [
			'status' => $ok,
			'message' => $message
		];
	}

	public static function unfollow(int $id, int $follower_id): array
	{
		$ok = false;
		$user = User::findOne($id);
		if (!$user) {
			$message = Yii::t('app', 'Пользователь не был найден!');
		} elseif(!($record = self::getRecord($id, $follower_id))) {
			$message = Yii::t('app', 'Вы уже отписаны от пользователя!');
		} else {
			if ($record->delete()) {
				$message = Yii::t('app', 'Вы отписались от пользователя!');
				$user->data->updateFollowers();
				$ok = true;
			} else {
				$message = Yii::t('app', 'Не удалось отписаться от пользователя!');
			}
		}
		return [
			'status' => $ok,
			'message' => $message
		];
	}

}
