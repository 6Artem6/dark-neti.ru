<?php

namespace app\models\question\history;

use Yii;
use yii\db\ActiveRecord;
use yii\bootstrap5\Html;
use yii\helpers\StringHelper;

use app\models\helpers\TextHelper;


class CommentHistory extends ActiveRecord
{

	public static function tableName()
	{
		return 'comment_history';
	}

	public function rules()
	{
		return [
			[['history_comment_id'], 'unique' ],
			[['history_comment_id', 'comment_id'], 'integer', ],
			[['is_hidden', 'is_deleted'], 'boolean'],
			[['comment_text'], 'string', 'min' => 10, 'max' => 8192 ],
			[['comment_text'], 'required'],
			[['history_datetime'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
		];
	}

	public static function primaryKey()
	{
		return [
			'history_comment_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'history_comment_id' => Yii::t('app','№ записи'),
			'comment_id' => Yii::t('app','№ комментария'),
			'comment_text' => Yii::t('app','Текст комментария'),
			'history_datetime' => Yii::t('app','Время редактирования'),
		];
	}

	public function getComment()
	{
		return $this->hasOne(Comment::class, ['comment_id' => 'comment_id']);
	}

	public function getId()
	{
		return $this->comment_id;
	}

	public function getText()
	{
		return Html::encode($this->comment_text);
		// return Html::decode($this->comment_text);
	}

	public function getDatetime()
	{
		return $this->history_datetime;
	}

	public function getShortText()
	{
		$text = strip_tags($this->text);
		return StringHelper::truncate($text, 50);
	}

	public function getTimeElapsed()
	{
		return TextHelper::getTimeElapsed($this->history_datetime);
	}

	public function getTimeFull()
	{
		return TextHelper::getTimeElapsed($this->history_datetime, true);
	}

	public function getIsAuthor()
	{
		return ($this->comment->user_id == Yii::$app->user->identity->id);
	}

	public function canSee(): ?string
	{
		$message = null;
		if (!Yii::$app->user->identity->isModerator()) {
			if ($this->is_hidden and !$this->isAuthor) {
				$message = Yii::t('app', 'Комментарий скрыт!');
			}
		}
		return $message;
	}

}
