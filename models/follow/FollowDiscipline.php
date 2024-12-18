<?php

namespace app\models\follow;

use Yii;
use yii\db\ActiveRecord;

use app\models\user\User;
use app\models\edu\Discipline;


class FollowDiscipline extends ActiveRecord
{

	public static function tableName()
	{
		return 'follow_discipline';
	}

	public function rules()
	{
		return [
			[['discipline_id', 'follower_id'], 'integer'],
			[['discipline_id', 'follower_id'], 'unique', 'targetAttribute' => ['discipline_id', 'follower_id']],
			[['follow_datetime'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
		];
	}

	public static function primaryKey()
	{
		return [
			'discipline_id', 'follower_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'discipline_id' => Yii::t('app', 'Предмет'),
			'follower_id' => Yii::t('app', 'Подписчик'),
		];
	}

	public function getFollower()
	{
		return $this->hasOne(User::class, ['user_id' => 'follower_id']);
	}

	public function getDiscipline()
	{
		return $this->hasOne(Discipline::class, ['discipline_id' => 'discipline_id']);
	}

	public static function getAuthorDisciplineIds(int $follower_id) {
		return self::find()
			->select('discipline_id')
			->where(['follower_id' => $follower_id])
			->column();
	}

	public static function getUserDisciplineIds() {
		return self::getAuthorDisciplineIds(Yii::$app->user->identity->id);
	}

	public static function getFollowersCount(int $discipline_id) {
		return self::find()
			->where(['discipline_id' => $discipline_id])
			->count();
	}

	public static function getRecord(int $discipline_id, int $follower_id) {
		return self::find()
			->where(['discipline_id' => $discipline_id])
			->andWhere(['follower_id' => $follower_id])
			->one();
	}

	public static function getUserIds(int $discipline_id, ?int $not_id) {
		$list = self::find()
			->select('follower_id')
			->where(['discipline_id' => $discipline_id]);
		if ($not_id) {
			$list = $list->andWhere(['!=', 'follower_id', $not_id]);
		}
		return $list->column();
	}

	public static function follow(int $id, int $follower_id): array
	{
		$ok = false;
		$discipline = Discipline::findOne($id);
		if (!$discipline) {
			$message = Yii::t('app', 'Предмет не был найден!');
		} elseif (!$discipline->is_checked) {
			$message = Yii::t('app', 'Предмет ещё не был проверен!');
		} elseif ($record = self::getRecord($id, $follower_id)) {
			$message = Yii::t('app', 'Вы уже подписаны на предмет!');
		} else {
			$record = new self;
			$record->discipline_id = $id;
			$record->follower_id = $follower_id;
			if ($record->save()) {
				$message = Yii::t('app', 'Вы подписаны на предмет!');
				$discipline->updateFollowers();
				$ok = true;
			} else {
				$message = Yii::t('app', 'Не удалось подписаться на предмет!');
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
		$discipline = Discipline::findOne($id);
		if (!$discipline) {
			$message = Yii::t('app', 'Предмет не был найден!');
		} elseif(!($record = self::getRecord($id, $follower_id))) {
			$message = Yii::t('app', 'Вы уже отписаны от вопроса!');
		} else {
			if ($record->delete()) {
				$message = Yii::t('app', 'Вы отписались от предмета!');
				$discipline->updateFollowers();
				$ok = true;
			} else {
				$message = Yii::t('app', 'Не удалось отписаться от предмета!');
			}
		}
		return [
			'status' => $ok,
			'message' => $message
		];
	}

}
