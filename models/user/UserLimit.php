<?php

namespace app\models\user;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\{ArrayHelper, Html};

use app\models\question\{Question, Answer, Comment};
use app\models\request\ReportType;
use app\models\helpers\ModelHelper;


class UserLimit extends ActiveRecord
{

	public static function tableName()
	{
		return 'user_limit';
	}

	public function rules()
	{
		return [
			[['user_id'], 'unique'],
			[['user_id', 'question_id', 'answer_id', 'comment_id'], 'integer'],
			[['question_reason', 'answer_reason', 'comment_reason'], 'each', 'rule' => ['integer']],
			[['question_limit_date', 'answer_limit_date', 'comment_limit_date'], 'date', 'format' => 'php:Y-m-d'],

			[['question_id' ], 'checkQuestion'],
			[['answer_id' ], 'checkAnswer'],
			[['comment_id' ], 'checkComment'],

			[['time'], 'in', 'range' => array_column($this->getTimeList(), 'name')],

			[['user_id', 'time', 'question_id', 'answer_id', 'comment_id',
				'question_reason', 'answer_reason', 'comment_reason',
				'question_limit_date', 'answer_limit_date', 'comment_limit_date' ], 'required'],
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
			'user_id' => Yii::t('app', 'Пользователь'),
			'question_reason' => Yii::t('app', 'Причина ограничения вопроса'),
			'answer_reason' => Yii::t('app', 'Причина ограничения ответа'),
			'comment_reason' => Yii::t('app', 'Причина ограничения комментария'),
			'time' => Yii::t('app', 'Время ограничения'),
		];
	}

	public const SCENARIO_QUESTION = 'question';
	public const SCENARIO_ANSWER = 'answer';
	public const SCENARIO_COMMENT = 'comment';

	public $time;

	public function init()
	{

		$this->on(static::EVENT_BEFORE_INSERT, [$this, 'checkArrayToJson']);

		$this->on(static::EVENT_BEFORE_UPDATE, [$this, 'checkArrayToJson']);

		$this->on(static::EVENT_AFTER_UPDATE, [$this, 'checkAfterUpdate']);
		$this->on(static::EVENT_AFTER_UPDATE, [$this, 'checkJsonToArray']);

		$this->on(static::EVENT_AFTER_INSERT, [$this, 'checkJsonToArray']);

		$this->on(static::EVENT_AFTER_UPDATE, [$this, 'checkJsonToArray']);

		$this->on(static::EVENT_AFTER_FIND, [$this, 'checkJsonToArray']);

		parent::init();
	}

	public function scenarios()
	{
		return array_merge(parent::scenarios(), [
			self::SCENARIO_QUESTION => ['time', '!question_id', 'question_reason', '!question_limit_date'],
			self::SCENARIO_ANSWER => ['time', '!answer_id', 'answer_reason', '!answer_limit_date'],
			self::SCENARIO_COMMENT => ['time', '!comment_id', 'comment_reason', '!comment_limit_date'],
		]);
	}

	protected function checkAfterUpdate($event)
	{
		if ($this->scenario == self::SCENARIO_QUESTION) {
			$this->user->data->setCanNotWriteQuestions();
		}
		if ($this->scenario == self::SCENARIO_ANSWER) {
			$this->user->data->setCanNotWriteAnswers();
		}
		if ($this->scenario == self::SCENARIO_COMMENT) {
			$this->user->data->setCanNotWriteComments();
		}
	}

	public function checkQuestion($attribute)
	{
		$record = Question::findOne($this->question_id);
		if (!$record) {
			$this->addError('question_id', Yii::t('app', 'Вопрос не найден!'));
			return false;
		}
		$this->question_limit_date = $this->getTimeValue();
	}

	public function checkAnswer($attribute)
	{
		$record = Answer::findOne($this->answer_id);
		if (!$record) {
			$this->addError('answer_id', Yii::t('app', 'Ответ не найден!'));
			return false;
		}
		$this->answer_limit_date = $this->getTimeValue();
	}

	public function checkComment($attribute)
	{
		$record = Comment::findOne($this->comment_id);
		if (!$record) {
			$this->addError('comment_id', Yii::t('app', 'Комментарий не найден!'));
			return false;
		}
		$this->comment_limit_date = $this->getTimeValue();
	}

	protected function checkJsonToArray($event)
	{
		if (!$this->isNewRecord) {
			ModelHelper::attributeJsonToArray($this, 'question_reason');
			ModelHelper::attributeJsonToArray($this, 'answer_reason');
			ModelHelper::attributeJsonToArray($this, 'comment_reason');
		}
	}

	protected function checkArrayToJson($event)
	{
		ModelHelper::attributeArrayToJson($this, 'question_reason');
		ModelHelper::attributeArrayToJson($this, 'answer_reason');
		ModelHelper::attributeArrayToJson($this, 'comment_reason');
	}

	public function getUser()
	{
		return $this->hasOne(User::class, ['user_id' => 'user_id']);
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

	public function getReportTypeModel()
	{
		return (new ReportType);
	}

	public function getQuestionReasons()
	{
		$list = [];
		$type_list = ReportType::getQuestionList(false);
		foreach ($this->question_reason as $reason) {
			$list[] = $type_list[$reason];
		}
		return $list;
	}

	public function getAnswerReasons()
	{
		$list = [];
		$type_list = ReportType::getAnswerList(false);
		foreach ($this->answer_reason as $reason) {
			$list[] = $type_list[$reason];
		}
		return $list;
	}

	public function getCommentReasons()
	{
		$list = [];
		$type_list = ReportType::getCommentList(false);
		foreach ($this->comment_reason as $reason) {
			$list[] = $type_list[$reason];
		}
		return $list;
	}

	public function getAllList()
	{
		$list = [];
		$type_list = ReportType::getListsByType(false);
		foreach ($this->question_reason as $reason) {
			$list[] = $type_list[ReportType::TYPE_QUESTION][$reason];
		}
		foreach ($this->answer_reason as $reason) {
			$list[] = $type_list[ReportType::TYPE_ANSWER][$reason];
		}
		foreach ($this->comment_reason as $reason) {
			$list[] = $type_list[ReportType::TYPE_COMMENT][$reason];
		}
		return $list;
	}

	public function getTimeList()
	{
		return [
			[
				'name' => 'week',
				'title' => Yii::t('app', 'Неделя'),
				'time' => date('Y-m-d', strtotime('+7 days')),
			],
			[
				'name' => 'two-weeks',
				'title' => Yii::t('app', 'Две недели'),
				'time' => date('Y-m-d', strtotime('+14 days')),
			],
			[
				'name' => 'month',
				'title' => Yii::t('app', 'Месяц'),
				'time' => date('Y-m-d', strtotime('+1 month')),
			],
			[
				'name' => 'two-months',
				'title' => Yii::t('app', 'Два месяца'),
				'time' => date('Y-m-d', strtotime('+2 months')),
			],
			[
				'name' => 'six-months',
				'title' => Yii::t('app', 'Шесть месяцев'),
				'time' => date('Y-m-d', strtotime('+6 months')),
			],
			[
				'name' => 'year',
				'title' => Yii::t('app', 'Год'),
				'time' => date('Y-m-d', strtotime('+1 year')),
			],
		];
	}

	public function getTimeValue()
	{
		$list = ArrayHelper::map($this->getTimeList(), 'name', 'time');
		return $list[$this->time] ?? null;
	}

	public function getQuestionMessage(): ?string
	{
		$message = null;
		if ($this->question_id) {
			$date = date('d.m.Y', strtotime($this->question_limit_date));
			$link = $this->question->getRecordLink();
			$reason = implode(', ', $this->getQuestionReasons());
			if (!$reason) {
				$message = Yii::t('app', 'Вы не можете задавать вопросы до {date} числа из-за {link}.', [
					'date' => $date,
					'link' => Html::a(Yii::t('app', 'этого вопроса', $link)),
				]);
			} else {
				$message = Yii::t('app', 'Вы не можете задавать вопросы до {date} числа из-за {link}, потому что {reason}.', [
					'date' => $date,
					'link' => Html::a(Yii::t('app', 'этого вопроса', $link)),
					'reason' => $reason,
				]);
			}
		}
		return $message;
	}

	public function getAnswerMessage(): ?string
	{
		$message = null;
		if ($this->answer_id) {
			$date = date('d.m.Y', strtotime($this->answer_limit_date));
			$link = $this->answer->getRecordLink();
			$reason = implode(', ', $this->getAnswerReasons());
			if (!$reason) {
				$message = Yii::t('app', 'Вы не можете давать ответы до {date} числа из-за {link}.', [
					'date' => $date,
					'link' => Html::a(Yii::t('app', 'этого ответа', $link)),
				]);
			} else {
				$message = Yii::t('app', 'Вы не можете давать ответы до {date} числа из-за {link}, потому что {reason}.', [
					'date' => $date,
					'link' => Html::a(Yii::t('app', 'этого ответа', $link)),
					'reason' => $reason,
				]);
			}
		}
		return $message;
	}

	public function getCommentMessage(): ?string
	{
		$message = null;
		if ($this->comment_id) {
			$date = date('d.m.Y', strtotime($this->comment_limit_date));
			$link = $this->comment->getRecordLink();
			$reason = implode(', ', $this->getCommentReasons());
			if (!$reason) {
				$message = Yii::t('app', 'Вы не можете оставлять комментарии до {date} числа из-за {link}.', [
					'date' => $date,
					'link' => Html::a(Yii::t('app', 'этого комментария', $link)),
				]);
			} else {
				$message = Yii::t('app', 'Вы не можете оставлять комментарии до {date} числа из-за {link}, потому что {reason}.', [
					'date' => $date,
					'link' => Html::a(Yii::t('app', 'этого комментария', $link)),
					'reason' => $reason,
				]);
			}
		}
		return $message;
	}

	public function checkCanQuestion()
	{
		$can = false;
		if (!$this->question_id) {
			$can = true;
		} else {
			if (date('Ymd') > date('Ymd', strtotime($this->question_limit_date))) {
				$this->createHistoryQuestionRecord();

				$this->question_limit_date = null;
				$this->question_id = null;
				$this->question_reason = null;
				$this->save();
				$can = true;
			}
		}
		return $can;
	}

	public function checkCanAnswer()
	{
		$can = false;
		if (!$this->answer_id) {
			$can = true;
		} else {
			if (date('Ymd') > date('Ymd', strtotime($this->answer_limit_date))) {
				$this->createHistoryAnswerRecord();

				$this->answer_limit_date = null;
				$this->answer_id = null;
				$this->answer_reason = null;
				$this->save();
				$can = true;
			}
		}
		return $can;
	}

	public function checkCanComment()
	{
		$can = false;
		if (!$this->comment_id) {
			$can = true;
		} else {
			if (date('Ymd') > date('Ymd', strtotime($this->comment_limit_date))) {
				$this->createHistoryCommentRecord();

				$this->comment_limit_date = null;
				$this->comment_id = null;
				$this->comment_reason = null;
				$this->save();
				$can = true;
			}
		}
		return $can;
	}

	public function createHistoryQuestionRecord()
	{
		$result = false;
		if (!$this->isNewRecord) {
			$data = $this->getOldAttributes();
			$history = new UserLimitHistory(['scenario' => UserLimitHistory::SCENARIO_QUESTION]);
			$history->load($data, '');
			$result = $history->save();
		}
		return $result;
	}

	public function createHistoryAnswerRecord()
	{
		$result = false;
		if (!$this->isNewRecord) {
			$data = $this->getOldAttributes();
			$history = new UserLimitHistory(['scenario' => UserLimitHistory::SCENARIO_ANSWER]);
			$history->load($data, '');
			$result = $history->save();
		}
		return $result;
	}

	public function createHistoryCommentRecord()
	{
		$result = false;
		if (!$this->isNewRecord) {
			$data = $this->getOldAttributes();
			$history = new UserLimitHistory(['scenario' => UserLimitHistory::SCENARIO_COMMENT]);
			$history->load($data, '');
			$result = $history->save();
		}
		return $result;
	}

}
