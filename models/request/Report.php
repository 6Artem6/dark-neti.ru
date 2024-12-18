<?php

namespace app\models\request;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\{HtmlPurifier, ArrayHelper, StringHelper};

use app\models\user\{User, UserRate};
use app\models\question\{Question, Answer, Comment};
use app\models\notification\Notification;


class Report extends ActiveRecord
{

	public static function tableName()
	{
		return 'report';
	}

	public function rules()
	{
		return [
			[['report_id', ], 'unique'],
			[['report_id', 'author_id', 'user_id', 'report_type', 'question_id', 'answer_id', 'comment_id' ], 'integer'],
			[['report_status'], 'in', 'range' => [static::STATUS_SENT, static::STATUS_EDITED, static::STATUS_REJECTED, static::STATUS_CLOSED]],
			[['report_datetime'], 'date', 'format' => 'php:Y-m-d H:i:s'],

			[['question_id' ], 'checkQuestion'],
			[['answer_id' ], 'checkAnswer'],
			[['comment_id' ], 'checkComment'],

			[['report_id', 'author_id', 'user_id', 'report_type', 'question_id', 'answer_id', 'comment_id' ], 'required'],
		];
	}

	public static function primaryKey()
	{
		return [
			'report_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'report_id' => Yii::t('app', '№ записи'),
			'report_type' => Yii::t('app', 'Тип обращения'),
			'author_id' => Yii::t('app', 'Автор'),
			'user_id' => Yii::t('app', 'Пользователь'),
			'question_id' => Yii::t('app', 'Вопрос'),
			'answer_id' => Yii::t('app', 'Ответ'),
			'comment_id' => Yii::t('app', 'Комментарий'),
		];
	}

	public const SCENARIO_QUESTION = 'question';
	public const SCENARIO_ANSWER = 'answer';
	public const SCENARIO_COMMENT = 'comment';
	public const SCENARIO_CHANGE_STATUS = 'change_status';

	public const STATUS_SENT = 0;
	public const STATUS_EDITED = 1;
	public const STATUS_REJECTED = 2;
	public const STATUS_CLOSED = 3;

	public function init()
	{
		$this->on(static::EVENT_BEFORE_VALIDATE, [$this, 'checkBeforeValidate']);
		$this->on(static::EVENT_AFTER_INSERT, [$this, 'checkAfterInsert']);
		parent::init();
	}

	public function scenarios()
	{
		return array_merge(parent::scenarios(), [
			self::SCENARIO_QUESTION => ['report_type', 'question_id', '!author_id', '!user_id'],
			self::SCENARIO_ANSWER => ['report_type', 'answer_id', '!author_id', '!user_id'],
			self::SCENARIO_COMMENT => ['report_type', 'comment_id', '!author_id', '!user_id'],
			self::SCENARIO_CHANGE_STATUS => ['report_status'],
		]);
	}

	protected function checkBeforeValidate()
	{
		$this->author_id = Yii::$app->user->identity->id;
	}

	protected function checkAfterInsert()
	{
		Notification::addReport($this);
	}

	public function checkQuestion($attribute)
	{
		if ($this->isNewRecord) {
			$author_id = Yii::$app->user->identity->id;
			if (!in_array($this->report_type, array_keys(ReportType::getQuestionList()))) {
				$this->addError('report_type', Yii::t('app', 'Неверный тип обраения!').$this->report_type);
				return false;
			}
			$record = Question::findOne($this->question_id);
			if (!$record) {
				$this->addError('question_id', Yii::t('app', 'Вопрос не найден!'));
				return false;
			}
			$report = self::findOne(['author_id' => $author_id, 'question_id' => $this->question_id]);
			if ($report) {
				$this->addError('question_id', Yii::t('app', 'Вы уже отправляли жалобу на этот вопрос!'));
				return false;
			}
			if ($author_id == $record->user_id) {
				$this->addError('report_type', Yii::t('app', 'Автор не может пожаловаться на свою запись!'));
				return false;
			}
			$this->user_id = $record->user_id;
		}
	}

	public function checkAnswer($attribute)
	{
		if ($this->isNewRecord) {
			$author_id = Yii::$app->user->identity->id;
			if (!in_array($this->report_type, array_keys(ReportType::getAnswerList()))) {
				$this->addError('report_type', Yii::t('app', 'Неверный тип обраения!').$this->report_type);
				return false;
			}
			$record = Answer::findOne($this->answer_id);
			if (!$record) {
				$this->addError('answer_id', Yii::t('app', 'Ответ не найден!'));
				return false;
			}
			$report = self::findOne(['author_id' => $author_id, 'answer_id' => $this->answer_id]);
			if ($report) {
				$this->addError('answer_id', Yii::t('app', 'Вы уже отправляли жалобу на этот ответ!'));
				return false;
			}
			if ($author_id == $record->user_id) {
				$this->addError('report_type', Yii::t('app', 'Автор не может пожаловаться на свою запись!'));
				return false;
			}
			$this->user_id = $record->user_id;
		}
	}

	public function checkComment($attribute)
	{
		if ($this->isNewRecord) {
			$author_id = Yii::$app->user->identity->id;
			if (!in_array($this->report_type, array_keys(ReportType::getCommentList()))) {
				$this->addError('report_type', Yii::t('app', 'Неверный тип обраения!').$this->report_type);
				return false;
			}
			$record = Comment::findOne($this->comment_id);
			if (!$record) {
				$this->addError('comment_id', Yii::t('app', 'Комментарий не найден!'));
				return false;
			}
			$report = self::findOne(['author_id' => $author_id, 'comment_id' => $this->comment_id]);
			if ($report) {
				$this->addError('comment_id', Yii::t('app', 'Вы уже отправляли жалобу на этот комментарий!'));
				return false;
			}
			if ($author_id == $record->user_id) {
				$this->addError('report_type', Yii::t('app', 'Автор не может пожаловаться на свою запись!'));
				return false;
			}
			$this->user_id = $record->user_id;
		}
	}

	public function getType()
	{
		return $this->hasOne(ReportType::class, ['type_id' => 'report_type']);
	}

	public function getQuestion()
	{
		return $this->hasOne(Question::class, ['question_id' => 'question_id']);
	}

	public function getAnswer()
	{
		return $this->hasOne(Answer::class, ['answer_id' => 'answer_id']);
	}

	public function getComment()
	{
		return $this->hasOne(Comment::class, ['comment_id' => 'comment_id']);
	}

	public function getAuthor()
	{
		return $this->hasOne(User::class, ['author_id' => 'user_id']);
	}

	public function getIsQuestion()
	{
		return !empty($this->question_id);
	}

	public function getIsAnswer()
	{
		return !empty($this->answer_id);
	}

	public function getIsComment()
	{
		return !empty($this->comment_id);
	}

	public function getIsSent()
	{
		return ($this->report_status == static::STATUS_SENT);
	}

	public function getIsEdited()
	{
		return ($this->report_status == static::STATUS_EDITED);
	}

	public function getIsRejected()
	{
		return ($this->report_status == static::STATUS_REJECTED);
	}

	public function getRecord()
	{
		$record = null;
		if ($this->isQuestion) {
			$record = $this->question;
		} elseif ($this->isAnswer) {
			$record = $this->answer;
		} elseif ($this->isComment) {
			$record = $this->comment;
		}
		return $record;
	}

	public function getText()
	{
		$text = "";
		if ($this->isQuestion) {
			$text = $this->question->text;
		} elseif ($this->isAnswer) {
			$text = $this->answer->text;
		} elseif ($this->isComment) {
			$text = $this->comment->text;
		}
		return $text;
	}

	public function getShortText()
	{
		$text = "";
		if ($this->isQuestion) {
			$text = $this->question->shortText;
		} elseif ($this->isAnswer) {
			$text = $this->answer->shortText;
		} elseif ($this->isComment) {
			$text = $this->comment->shortText;
		}
		return $text;
	}

	public function getDatetime()
	{
		return $this->report_datetime;
	}

	public static function getNotClosedList()
	{
		$questions = Question::find()
			->joinWith('author')
			->joinWith('reports as report')
			->where(['!=', 'report.report_status', static::STATUS_CLOSED])
			->orderBy(['question_id' => SORT_DESC])
			->limit(5)
			->all();
		$answers = Answer::find()
			->joinWith('author')
			->joinWith('reports as report')
			->where(['!=', 'report.report_status', static::STATUS_CLOSED])
			->orderBy(['answer_id' => SORT_DESC])
			->limit(5)
			->all();
		$comments = Comment::find()
			->joinWith('author')
			->joinWith('reports as report')
			->where(['!=', 'report.report_status', static::STATUS_CLOSED])
			->orderBy(['comment_id' => SORT_DESC])
			->limit(5)
			->all();
		$result = [
			'questions' => $questions,
			'answers' => $answers,
			'comments' => $comments,
		];
		return $result;
	}

	public static function getAuthorList()
	{
		$questions = Question::find()
			->joinWith('reports as report')
			->where(['report.user_id' => Yii::$app->user->identity->id])
			->orderBy(['question_id' => SORT_DESC])
			->limit(5)
			->all();
		$answers = Answer::find()
			->joinWith('reports as report')
			->where(['report.user_id' => Yii::$app->user->identity->id])
			->orderBy(['answer_id' => SORT_DESC])
			->limit(5)
			->all();
		$comments = Comment::find()
			->joinWith('reports as report')
			->where(['report.user_id' => Yii::$app->user->identity->id])
			->orderBy(['comment_id' => SORT_DESC])
			->limit(5)
			->all();
		$result = [
			'questions' => $questions,
			'answers' => $answers,
			'comments' => $comments,
		];
		return $result;
	}

	public function setEditedAll(int $id)
	{
		$report = self::findOne($id);
		$ids = ArrayHelper::getColumn($report->record->reports, 'report_id');
		$list = self::find()->where(['IN', 'report_id', $ids])->all();
		foreach ($list as $record) {
			$record->scenario = static::SCENARIO_CHANGE_STATUS;
			$record->setEdited();
		}
	}

	public static function setRejectedInType(int $id)
	{
		$report = self::findOne($id);
		$ids = ArrayHelper::getColumn($report->record->reports, 'report_id');
		$list = self::find()
			->where(['IN', 'report_id', $ids])
			->andWhere(['report_type' => $report->report_type])
			->andWhere(['report_status' => static::STATUS_SENT])
			->all();
		foreach ($list as $record) {
			$record->scenario = static::SCENARIO_CHANGE_STATUS;
			$record->setRejected();
		}
	}

	public function setEdited()
	{
		$this->report_status = static::STATUS_EDITED;
		$this->save();
	}

	public function setRejected()
	{
		$this->report_status = static::STATUS_REJECTED;
		$this->save();
		UserRate::removeReport($this);
		$this->author->data->updateRateSum();
	}

	public function setClosed()
	{
		$this->report_status = static::STATUS_CLOSED;
		$this->save();
		UserRate::addReport($this);
		$this->author->data->updateRateSum();
	}

}
