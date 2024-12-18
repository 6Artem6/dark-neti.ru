<?php

namespace app\models\like;

use Yii;
use yii\db\ActiveRecord;

use app\models\question\Comment;
use app\models\user\UserRate;


class LikeComment extends ActiveRecord
{

	public static function tableName()
	{
		return 'like_comment';
	}

	public function rules()
	{
		return [
			[['like_id'], 'unique'],
			[['like_id', 'user_id', 'comment_id'], 'integer'],
			[['is_removed'], 'boolean'],
			[['user_id', 'comment_id'], 'required'],
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
			'like_id'     => Yii::t('app','№ записи'),
			'comment_id' => Yii::t('app','Ответ'),
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

	public function getComment()
	{
		return $this->hasOne(Comment::class, ['comment_id' => 'comment_id']);
	}

	public static function getRecord(int $comment_id, int $user_id)
	{
		return self::find()->where(['comment_id' => $comment_id, 'user_id' => $user_id])->one();
	}

	public static function getLikecount(int $comment_id)
	{
		return self::find()->where(['comment_id' => $comment_id])->count();
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
			->joinWith('comment as c')
			->joinWith('comment.question as q')
			->joinWith('comment.answer as a')
			->where(['c.user_id' => $user_id])
			->andWhere(['c.is_hidden' => false])
			->andWhere(['c.is_deleted' => false])
			->andWhere(['a.is_hidden' => false])
			->andWhere(['a.is_deleted' => false])
			->andWhere(['q.is_deleted' => false])
			->andWhere(['q.is_hidden' => false])
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
		$comment = Comment::findOne($id);
		if (!$comment) {
			$message = Yii::t('app', 'Комментарий не был найден');
		} elseif($comment->isAuthor) {
			$message = Yii::t('app', 'Автор комментария не может отметить его');
		} elseif($record = self::getRecord($id, $user_id)) {
			if ($record->is_removed) {
				$record->is_removed = false;
				if ($record->save()) {
					$message = Yii::t('app', 'Вы отметили комментарий полезным!');
					$record->trigger(Comment::EVENT_RETURN_LIKE);
					UserRate::addLikeAnswer($record);
					$ok = true;
				} else {
					$message = Yii::t('app', 'Не удалось отметить комментарий');
				}
			} else {
				$message = Yii::t('app', 'Вы уже отметили комментарий полезным');
			}
		} else {
			$record = new self;
			$record->comment_id = $id;
			$record->user_id = $user_id;
			if ($record->save()) {
				$message = Yii::t('app', 'Вы отметили комментарий полезным!');
				$comment->trigger(Comment::EVENT_ADD_LIKE);
				UserRate::addLikeComment($record);
				$ok = true;
			} else {
				$message = Yii::t('app', 'Не удалось отметить комментарий');
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
		$comment = Comment::findOne($id);
		if (!$comment) {
			$message = Yii::t('app', 'Комментарий не был найден');
		} elseif($comment->isAuthor) {
			$message = Yii::t('app', 'Автор комментария не может отметить его');
		} elseif(!($record = self::getRecord($id, $user_id))) {
			$message = Yii::t('app', 'Вы уже убрали отметку о пользе комментария');
		} else {
			$record->is_removed = true;
			if ($record->save()) {
				$message = Yii::t('app', 'Вы убрали отметку о пользе комментария!');
				$comment->trigger(Comment::EVENT_REMOVE_LIKE);
				UserRate::removeLikeComment($record);
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
