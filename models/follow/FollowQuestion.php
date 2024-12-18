<?php

namespace app\models\follow;

use Yii;
use yii\db\ActiveRecord;

use app\models\user\User;
use app\models\question\Question;


class FollowQuestion extends ActiveRecord
{

	public static function tableName()
	{
		return 'follow_question';
	}

	public function rules()
	{
		return [
			[['question_id', 'follower_id'], 'integer'],
			[['question_id', 'follower_id'], 'unique', 'targetAttribute' => ['question_id', 'follower_id']],
			[['follow_datetime'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
		];
	}

	public static function primaryKey()
	{
		return [
			'question_id', 'follower_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'question_id' => Yii::t('app', 'Предмет'),
			'follower_id' => Yii::t('app', 'Подписчик'),
		];
	}

	public function getFollower()
	{
		return $this->hasOne(User::class, ['user_id' => 'follower_id']);
	}

	public static function getAuthorQuestionIds(int $follower_id) {
		return self::find()
			->select('question_id')
			->where(['follower_id' => $follower_id])
			->column();
	}

	public static function getUserQuestionIds() {
		return self::getAuthorQuestionIds(Yii::$app->user->identity->id);
	}

	public static function getFollowersCount(int $question_id) {
		return self::find()
			->where(['question_id' => $question_id])
			->count();
	}

	public static function getRecord(int $question_id, int $follower_id) {
		return self::find()
			->where(['question_id' => $question_id])
			->andWhere(['follower_id' => $follower_id])
			->one();
	}

	public static function getUserIds(int $question_id, ?int $not_id) {
		$list = self::find()
			->select('follower_id')
			->where(['question_id' => $question_id]);
		if ($not_id) {
			$list = $list->andWhere(['!=', 'follower_id', $not_id]);
		}
		return $list->column();
	}

	public function getQuestionIds(int $follower_id) {
		return self::find()
			->select('question_id')
			->where(['follower_id' => $follower_id])
			->column();
	}

	public static function follow(int $id, int $follower_id, bool $question_create = false): array
	{
		$ok = false;
		$question = Question::findOne($id);
		if (!$question) {
			$message = Yii::t('app', 'Вопрос не был найден');
		} elseif(!$question_create and $question->isAuthor) {
			$message = Yii::t('app', 'Автор уже подписан на вопрос!');
		} elseif($record = self::getRecord($id, $follower_id)) {
			$message = Yii::t('app', 'Вы уже подписаны на вопрос!');
		} else {
			$record = new self;
			$record->question_id = $id;
			$record->follower_id = $follower_id;
			if ($record->save()) {
				$message = Yii::t('app', 'Вы подписаны на вопрос!');
				$question->updateFollowers();
				$ok = true;
			} else {
				$message = Yii::t('app', 'Не удалось подписаться на вопрос!');
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
		$question = Question::findOne($id);
		if (!$question) {
			$message = Yii::t('app', 'Вопрос не был найден');
		} elseif($question->isAuthor) {
			$message = Yii::t('app', 'Автор вопроса не может отписаться от него!');
		} elseif(!($record = self::getRecord($id, $follower_id))) {
			$message = Yii::t('app', 'Вы уже отписаны от вопроса!');
		} else {
			if ($record->delete()) {
				$message = Yii::t('app', 'Вы отписались от вопроса!');
				$question->updateFollowers();
				$ok = true;
			} else {
				$message = Yii::t('app', 'Не удалось отписаться от вопроса!');
			}
		}
		return [
			'status' => $ok,
			'message' => $message
		];
	}

}
