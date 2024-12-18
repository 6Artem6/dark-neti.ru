<?php

namespace app\models\like;

use Yii;
use yii\db\ActiveRecord;

use app\models\question\Answer;
use app\models\user\UserRate;


class LikeAnswer extends ActiveRecord
{

	public static function tableName()
	{
		return 'like_answer';
	}

	public function rules()
	{
		return [
			[['like_id'], 'unique'],
			[['like_id', 'user_id', 'answer_id'], 'integer'],
			[['is_removed'], 'boolean'],
			[['user_id', 'answer_id'], 'required'],
		];
	}

	public static function primaryKey()
	{
		return [
			'like_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'like_id' => Yii::t('app','№ записи'),
			'answer_id' => Yii::t('app','Ответ'),
			'user_id' => Yii::t('app','Отметивший пользователь'),
		];
	}

	public function init()
	{
		$this->on(static::EVENT_BEFORE_VALIDATE, [$this, 'checkBeforeValidate']);

		parent::init();
	}

	protected function checkBeforeValidate($event)
	{
		if ($this->isNewRecord) {
			$this->user_id = Yii::$app->user->identity->id;
		}
	}

	public function getAuthor()
	{
		return $this->hasOne(User::class, ['user_id' => 'user_id']);
	}

	public function getAnswer()
	{
		return $this->hasOne(Answer::class, ['answer_id' => 'answer_id']);
	}

	public static function getRecord(int $answer_id, int $user_id)
	{
		return self::find()->where(['answer_id' => $answer_id, 'user_id' => $user_id])->one();
	}

	public static function getLikecount(int $answer_id)
	{
		return self::find()->where(['answer_id' => $answer_id])->count();
	}

	public static function getAuthorList(int $user_id)
	{
		return self::find()
			->where(['user_id' => $user_id])
			->indexBy('like_id')
			->all();
	}

	public static function getUserList(int $user_id)
	{
		return self::find()
			->joinWith('answer as a')
			->joinWith('answer.question as q')
			->where(['a.user_id' => $user_id])
			->andWhere(['a.is_hidden' => false])
			->andWhere(['a.is_deleted' => false])
			->andWhere(['q.is_hidden' => false])
			->andWhere(['q.is_deleted' => false])
			->indexBy('like_id')
			->all();
	}

	public function isAuthor()
	{
		return ($this->user_id == Yii::$app->user->identity->id);
	}

	public static function addLike(int $id, int $user_id): array
	{
		$ok = false;
		$answer = Answer::findOne($id);
		if (!$answer) {
			$message = Yii::t('app', 'Ответ не был найден');
		} elseif($answer->isAuthor) {
			$message = Yii::t('app', 'Автор ответа не может отметить его');
		} elseif($record = self::getRecord($id, $user_id)) {
			if ($record->is_removed) {
				$record->is_removed = false;
				if ($record->save()) {
					$message = Yii::t('app', 'Вы отметили ответ полезным!');
					$answer->trigger(Answer::EVENT_RETURN_LIKE);
					UserRate::addLikeAnswer($record);
					$ok = true;
				} else {
					$message = Yii::t('app', 'Не удалось отметить решение');
				}
			} else {
				$message = Yii::t('app', 'Вы уже отметили ответ полезным');
			}
		} else {
			$record = new self;
			$record->answer_id = $id;
			$record->user_id = $user_id;
			if ($record->save()) {
				$message = Yii::t('app', 'Вы отметили ответ полезным!');
				$answer->trigger(Answer::EVENT_ADD_LIKE);
				UserRate::addLikeAnswer($record);
				$ok = true;
			} else {
				$message = Yii::t('app', 'Не удалось отметить решение');
			}
		}
		return [
			'status' => $ok,
			'message' => $message
		];
	}

	public static function removeLike(int $id, int $user_id): array
	{
		$ok = false;
		$answer = Answer::findOne($id);
		if (!$answer) {
			$message = Yii::t('app', 'Ответ не был найден');
		} elseif($answer->isAuthor) {
			$message = Yii::t('app', 'Автор ответа не может отметить его');
		} elseif(!($record = self::getRecord($id, $user_id))) {
			$message = Yii::t('app', 'Вы уже убрали отметку о пользе ответа');
		} else {
			$record->is_removed = true;
			if ($record->save()) {
				$message = Yii::t('app', 'Вы убрали отметку о пользе ответа!');
				$answer->trigger(Answer::EVENT_REMOVE_LIKE);
				UserRate::removeLikeAnswer($record);
				$ok = true;
			} else {
				$message = Yii::t('app', 'Не удалось убрали отметку');
			}
		}
		return [
			'status' => $ok,
			'message' => $message
		];
	}

}
