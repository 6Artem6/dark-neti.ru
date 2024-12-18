<?php

namespace app\models\request;

use Yii;
use yii\db\ActiveRecord;

use app\models\user\{User, UserRate};
use app\models\question\Question;


class DuplicateQuestionRequest extends ActiveRecord
{

	public static function tableName()
	{
		return 'duplicate_question_request';
	}

	public function rules()
	{
		return [
			[['!request_id'], 'unique' ],
			[['request_id', 'question_id', 'duplicate_question_id', 'user_id', 'request_status'], 'integer', ],
			[['request_status'], 'in', 'range' => [static::STATUS_SENT], 'on' => [self::SCENARIO_REQUEST]],
			[['request_status'], 'in', 'range' => [static::STATUS_REJECTED, static::STATUS_ACCEPTED], 'on' => [self::SCENARIO_RESPONSE]],
		];
	}

	public static function primaryKey()
	{
		return [
			'request_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'request_id' => Yii::t('app','Запрос'),
			'question_id' => Yii::t('app','Вопрос'),
			'duplicate_question_id' => Yii::t('app','Существующий вопрос'),
			'user_id' => Yii::t('app','Пользователь'),
			'request_status' => Yii::t('app','Статус запроса'),
		];
	}

	public const STATUS_SENT = 0;
	public const STATUS_REJECTED = -1;
	public const STATUS_ACCEPTED = 1;

	public const SCENARIO_REQUEST = 'request';
	public const SCENARIO_RESPONSE = 'response';

	public $question_text;

	public function scenarios()
	{
		return array_merge(parent::scenarios(), [
			self::SCENARIO_REQUEST => [
				'question_id', 'duplicate_question_id', '!user_id', '!request_status'
			],
			self::SCENARIO_RESPONSE => [
				'request_id', '!request_status'
			],
		]);
	}

	public function init()
	{
		$this->on(static::EVENT_BEFORE_VALIDATE, [$this, 'checkBeforeValidate']);
		$this->on(static::EVENT_BEFORE_VALIDATE, [$this, 'checkAfterSave']);
		parent::init();
	}

	protected function checkBeforeValidate($event)
	{
		if ($this->scenario == static::SCENARIO_REQUEST) {
			if (empty($this->question)) {
				$this->addError('question_id', Yii::t('app', 'Вопрос не был найден.'));
				return false;
			}
			if (empty($this->duplicateQuestion)) {
				$this->addError('question_id', Yii::t('app', 'Имеющийся вопрос не был найден.'));
				return false;
			}
			if($record = self::getRecord($this->question_id, $this->duplicate_question_id)) {
				$this->addError('question_id', Yii::t('app', 'Вопрос уже был предложен.'));
				return false;
			}
			$this->user_id = Yii::$app->user->identity->id;
			$this->request_status = static::STATUS_SENT;
		}
		if ($this->scenario == static::SCENARIO_RESPONSE) {
			if (!$this->question->isAuthor) {
				$this->addError('question_id', Yii::t('app', 'Вы не являетесь автором вопроса.'));
				return false;
			}
		}
	}

	protected function checkAfterSave($event)
	{
		if ($this->scenario == static::SCENARIO_RESPONSE) {
			if ($this->request_status == static::STATUS_ACCEPTED) {
				Question::setClosed($this->question_id);
				UserRate::addDuplicate($this);
				$this->author->data->updateRateSum();
			} else {
				UserRate::removeDuplicate($this);
				$this->author->data->updateRateSum();
			}
		}
	}

	public function getQuestion()
	{
		return $this->hasOne(Question::class, ['question_id' => 'question_id']);
	}

	public function getDuplicateQuestion()
	{
		return $this->hasOne(Question::class, ['question_id' => 'duplicate_question_id']);
	}

	public function getAuthor()
	{
		return $this->hasOne(User::class, ['user_id' => 'user_id']);
	}

	public static function getRecord(int $question_id, int $duplicate_question_id)
	{
		return self::find()->where(['question_id' => $question_id, 'duplicate_question_id' => $duplicate_question_id])->one();
	}

	public function isSent()
	{
		return ($this->request_status == static::STATUS_SENT);
	}

	public function isRejected()
	{
		return ($this->request_status == static::STATUS_REJECTED);
	}

	public function isAccepted()
	{
		return ($this->request_status == static::STATUS_ACCEPTED);
	}

	public function setResponsing()
	{
		$this->scenario = static::SCENARIO_RESPONSE;
	}

}
