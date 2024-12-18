<?php

namespace app\models\question\history;

use Yii;
use yii\db\ActiveRecord;
use yii\bootstrap5\Html;
use yii\helpers\StringHelper;

use app\models\file\FileHistory;
use app\models\helpers\TextHelper;


class AnswerHistory extends ActiveRecord
{

	public static function tableName()
	{
		return 'answer_history';
	}

	public function rules()
	{
		return [
			[['history_answer_id'], 'unique' ],
			[['history_answer_id', 'answer_id'], 'integer', ],
			[['is_hidden', 'is_deleted'], 'boolean'],
			[['answer_text'], 'string', 'min' => 10, 'max' => 8192 ],
			[['answer_text'], 'required'],
			[['history_datetime'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
		];
	}

	public static function primaryKey()
	{
		return [
			'history_answer_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'history_answer_id' => Yii::t('app','№ записи'),
			'answer_id' => Yii::t('app','№ ответа'),
			'answer_text' => Yii::t('app','Текст ответа'),
			'history_datetime' => Yii::t('app','Время редактирования'),
		];
	}

	public function getAnswer()
	{
		return $this->hasOne(Answer::class, ['answer_id' => 'answer_id']);
	}

	public function getFiles()
	{
		return $this->hasMany(FileHistory::class, ['history_answer_id' => 'history_answer_id']);
	}

	public function getId()
	{
		return $this->answer_id;
	}

	public function getText()
	{
		return Html::encode($this->answer_text);
		// return Html::decode($this->answer_text);
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
		return ($this->answer->user_id == Yii::$app->user->identity->id);
	}

	public function canSee(): ?string
	{
		$message = null;
		if (!Yii::$app->user->identity->isModerator()) {
			if ($this->is_hidden and !$this->isAuthor) {
				$message = Yii::t('app', 'Ответ скрыт!');
			}
		}
		return $message;
	}

}
