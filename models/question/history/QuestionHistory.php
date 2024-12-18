<?php

namespace app\models\question\history;

use Yii;
use yii\db\ActiveRecord;
use yii\bootstrap5\Html;
use yii\helpers\StringHelper;

use app\models\question\{Question, QuestionType, Tag, TagToQuestion};
use app\models\file\FileHistory;
use app\models\edu\{Discipline, Faculty, Teacher};
use app\models\helpers\TextHelper;


class QuestionHistory extends ActiveRecord
{

	public static function tableName()
	{
		return 'question_history';
	}

	public function rules()
	{
		return [
			[['history_question_id'], 'unique' ],
			[['history_question_id', 'question_id', 'type_id', 'faculty_id', 'discipline_id', 'teacher_id'], 'integer', ],
			[['is_hidden', 'is_deleted'], 'boolean'],
			[['question_text'], 'string', 'min' => 10, 'max' => 8192 ],
			[['question_title'], 'string', 'min' => 5, 'max' => 512 ],
			[['question_title', 'question_text', 'type_id', 'faculty_id', 'discipline_id'], 'required'],
			[['history_datetime'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
			[['end_datetime'], 'datetime', 'format' => 'php:d.m.Y H:i'],
		];
	}

	public static function primaryKey()
	{
		return [
			'history_question_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'history_question_id' => Yii::t('app','№ записи'),
			'question_id' => Yii::t('app','№ вопроса'),
			'question_title' => Yii::t('app','Заголовок вопроса'),
			'question_text' => Yii::t('app','Текст вопроса'),
			'history_datetime' => Yii::t('app','Время редактирования'),
			'type_id' => Yii::t('app','Тип вопроса'),
			'faculty_id' => Yii::t('app','Факультет'),
			'discipline_id' => Yii::t('app','Предмет'),
			'teacher_id' => Yii::t('app','Ф.И.О. преподавателя'),
			'end_datetime' => Yii::t('app','Срок сдачи'),
			'upload_files' => Yii::t('app','Прикрепляемые файлы'),
		];
	}

	public function init()
	{
		$this->on(static::EVENT_BEFORE_VALIDATE, [$this, 'checkBeforeValidate']);
		$this->on(static::EVENT_BEFORE_INSERT, [$this, 'checkBeforeInsert']);
		$this->on(static::EVENT_BEFORE_UPDATE, [$this, 'checkBeforeInsert']);
		parent::init();
	}

	protected function checkBeforeValidate($event)
	{
		if ($this->end_datetime) {
			$this->end_datetime = date("d.m.Y H:i", strtotime($this->end_datetime));
		}
	}

	protected function checkBeforeInsert($event)
	{
		if ($this->end_datetime) {
			$this->end_datetime = date("Y-m-d H:i:s", strtotime($this->end_datetime));
		}
	}

	public function getQuestion()
	{
		return $this->hasOne(Question::class, ['question_id' => 'question_id']);
	}

	public function getDiscipline()
	{
		return $this->hasOne(Discipline::class, ['discipline_id' => 'discipline_id'])
			->onCondition(['is_checked' => true]);
	}

	public function getFiles()
	{
		return $this->hasMany(FileHistory::class, ['history_question_id' => 'history_question_id'])
			->onCondition(['IS', 'history_answer_id', NULL]);
	}

	public function getFaculty()
	{
		return $this->hasOne(Faculty::class, ['faculty_id' => 'faculty_id']);
	}

	public function getType()
	{
		return $this->hasOne(QuestionType::class, ['type_id' => 'type_id']);
	}

	public function getTeacher()
	{
		return $this->hasOne(Teacher::class, ['teacher_id' => 'teacher_id']);
	}

	public function getTagsList()
	{
		return $this->hasMany(Tag::class, ['tag_id' => 'tag_id'])
			->via('tagToQuestion');
	}

	public function getTagToQuestion()
	{
		return $this->hasMany(TagToQuestion::class, ['question_id' => 'question_id']);
	}

	public function getId()
	{
		return $this->question_id;
	}

	public function getText()
	{
		return Html::encode($this->question_text);
		// return Html::decode($this->question_text);
	}

	public function getDatetime()
	{
		return $this->history_datetime;
	}

	public function getShortText()
	{
		$text = strip_tags($this->text);
		return StringHelper::truncate($text, 100);
	}

	public function getTimeElapsed()
	{
		return TextHelper::getTimeElapsed($this->history_datetime);
	}

	public function getTimeFull()
	{
		return TextHelper::getTimeElapsed($this->history_datetime, true);
	}

	public function getEndTimeFull()
	{
		return TextHelper::getTimeElapsed($this->end_datetime, true);
	}

	public function getIsAuthor()
	{
		return ($this->question->user_id == Yii::$app->user->identity->id);
	}

	public function canSee(): ?string
	{
		$message = null;
		if (!Yii::$app->user->identity->isModerator()) {
			if ($this->is_hidden and !$this->isAuthor) {
				$message = Yii::t('app', 'Вопрос скрыт!');
			}
		}
		return $message;
	}

}
