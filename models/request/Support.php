<?php

namespace app\models\request;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\{HtmlPurifier, ArrayHelper, StringHelper, Url};
use yii\bootstrap5\Html;

use app\models\user\User;
use app\models\service\Bot;
use app\models\helpers\HtmlHelper;


class Support extends ActiveRecord
{

	public static function tableName()
	{
		return 'support';
	}

	public function rules()
	{
		return [
			[['support_id'], 'unique'],
			[['support_text', 'response_text'], 'string', 'min' => 5, 'max' => 4096],
			[['user_info'], 'string', 'max' => 4096],
			[['response_email'], 'string', 'max' => 256],
			[['response_email'], 'email'],
			[['support_id', 'user_id', 'type_id', 'status_id', 'view_status'], 'integer'],
			[['support_datetime', 'response_datetime'], 'date', 'format' => 'php:Y-m-d H:i:s'],
			[['type_id'], 'in', 'range' => array_keys($this->typeModel->list)],

			[['support_text', 'response_text'], 'filter', 'filter' => 'strip_tags'],
			[['support_text', 'type_id', 'response_text'], 'required'],
			[['response_email'], 'required', 'on' => [static::SCENARIO_CREATE_GUEST]],
		];
	}

	public static function primaryKey()
	{
		return [
			'support_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'support_id' => Yii::t('app','№ обращения'),
			'support_text' => Yii::t('app','Текст обращения'),
			'response_text' => Yii::t('app','Текст ответа'),
			'response_email' => Yii::t('app','Почта для обратной связи'),
			'user_id' => Yii::t('app','Обратившийся пользователь'),
			'type_id' => Yii::t('app','Тип обращения'),
		];
	}

	public const SCENARIO_CREATE = 'create';
	public const SCENARIO_CREATE_GUEST = 'create-guest';
	public const SCENARIO_RESPONSE = 'response';

	public const STATUS_SENT = 1;
	public const STATUS_IN_PROGRESS = 2;
	public const STATUS_SOLVED = 3;

	public const STATUS_VIEW_TO_MODERATOR_SENT = 1;
	public const STATUS_VIEW_TO_MODERATOR_SEEN = 2;
	public const STATUS_VIEW_TO_USER_SENT = 3;
	public const STATUS_VIEW_TO_USER_SEEN = 4;


	public function init()
	{
		$this->on(static::EVENT_BEFORE_VALIDATE, [$this, 'checkBeforeValidate']);
		$this->on(static::EVENT_AFTER_INSERT, [$this, 'sendMessage']);

		parent::init();
	}

	public function scenarios()
	{
		return array_merge(parent::scenarios(), [
			self::SCENARIO_CREATE => [
				'support_text', 'type_id', '!user_id', '!status_id',
				'!user_info',
				'!view_status', '!support_datetime'
			],
			self::SCENARIO_CREATE_GUEST => [
				'support_text', 'type_id', '!user_id', '!status_id',
				'response_email', '!user_info',
				'!view_status', '!support_datetime'
			],
			self::SCENARIO_RESPONSE => [
				'response_text', '!status_id', '!view_status', '!response_datetime',
			],
		]);
	}

	protected function checkBeforeValidate($event)
	{
		if ($this->scenario == static::SCENARIO_CREATE) {
			$this->user_id = Yii::$app->user->identity->id;
		}
		if (in_array($this->scenario, [static::SCENARIO_CREATE, static::SCENARIO_CREATE_GUEST])) {
			$this->user_info = Yii::t('app', "IP Address: {ip}, {agent}", [
				'ip' => Yii::$app->request->userIp,
				'agent' => Yii::$app->request->userAgent,
			]);
			$this->setSent();
			$this->setToModeratorSent();
		}
		if ($this->scenario == static::SCENARIO_RESPONSE) {
			$this->response_datetime = date("Y-m-d H:i:s");
			$this->setSolved();
			$this->setToUserSent();
		}
	}

	public function getAuthor()
	{
		return $this->hasOne(User::class, ['user_id' => 'user_id']);
	}

	public function getType()
	{
		return $this->hasOne(SupportType::class, ['type_id' => 'type_id']);
	}

	public function getStatus()
	{
		return $this->hasOne(SupportStatus::class, ['status_id' => 'status_id']);
	}

	public function getTypeModel()
	{
		return (new SupportType);
	}

	public function getId()
	{
		return $this->support_id;
	}

	public function getDatetime()
	{
		return $this->report_datetime;
	}

	public function getText()
	{
		return Html::encode($this->support_text);
		// return $this->support_text;
	}

	public function getSenderName()
	{
		return ($this->isGuest() ? $this->response_email : $this->author->username);
	}

	public function getIsAuthor(): bool
	{
		return ($this->user_id == Yii::$app->user->identity->id);
	}

	public static function getAllCount()
	{
		return self::find()->count();
	}

	public static function getTodayCount()
	{
		return self::find()
			->where(['>=', 'support_datetime', date('Y-m-d 00:00:00', strtotime('-1 day'))])
			->count();
	}

	public function getAllList(int $limit = 0, int $offset = 0)
	{
		$list = self::find()
			->orderBy(['support_datetime' => SORT_DESC])
			->indexBy('support_id');
		if ($limit > 0) {
			$list = $list->limit($limit);
		}
		if ($offset > 0) {
			$list = $list->offset($offset);
		}
		return $list->all();
	}

	public static function getList()
	{
		return self::find()
			->joinWith('author as author')
			->orderBy(['response_datetime' => SORT_DESC, 'support_datetime' => SORT_DESC])
			->all();
	}

	public static function getListResponse()
	{
		$list = self::getList();
		foreach ($list as $model) {
			$model->scenario = static::SCENARIO_RESPONSE;
		}
		return $list;
	}

	public static function getAuthorList()
	{
		return self::find()
			->joinWith('author as author')
			->where(['author.user_id' => Yii::$app->user->identity->id])
			->orderBy(['response_datetime' => SORT_DESC, 'support_datetime' => SORT_DESC])
			->all();
	}

	public static function getToModeratorSentList()
	{
		return self::find()
			->joinWith('author as author')
			->where(['view_status' => static::STATUS_VIEW_TO_MODERATOR_SENT])
			->orderBy(['response_datetime' => SORT_DESC, 'support_datetime' => SORT_DESC])
			->all();
	}

	public static function getToUserSentList()
	{
		return self::find()
			->joinWith('author as author')
			->where(['view_status' => static::STATUS_VIEW_TO_USER_SENT])
			->andWhere(['author.user_id' => Yii::$app->user->identity->id])
			->orderBy(['response_datetime' => SORT_DESC, 'support_datetime' => SORT_DESC])
			->all();
	}

	public function getShortText()
	{
		$text = strip_tags($this->text);
		return StringHelper::truncate($text, 50);
	}

	public function getTimeElapsed()
	{
		return HtmlHelper::getTimeElapsed($this->support_datetime);
	}

	public function getTimeFull()
	{
		return HtmlHelper::getTimeElapsed($this->support_datetime, true);
	}

	public function getResponseTimeElapsed()
	{
		return HtmlHelper::getTimeElapsed($this->response_datetime);
	}

	public function getResponseTimeFull()
	{
		return HtmlHelper::getTimeElapsed($this->response_datetime, true);
	}

	public function getRecordLink()
	{
		return Url::to(["/user/support-view/", 'id' => $this->support_id]);
	}

	public function getResponseLink()
	{
		return Url::to(["/moderator/support-response/", 'id' => $this->support_id]);
	}

	public function setText(?string $text = null)
	{
		if ($text) {
			$this->support_text = $text;
			$this->validate('support_text');
		}
	}

	public function setType(?int $id = null)
	{
		if (in_array($id, array_keys($this->typeModel->list))) {
			$this->type_id = $id;
		}
	}

	public function setAuthorListSeen()
	{
		$list = self::getAuthorList();
		foreach ($list as $record) {
			$record->setToUserSeen();
			$record->save();
		}
	}

	public function setSent()
	{
		$this->status_id = static::STATUS_SENT;
	}

	public function setInProgress()
	{
		$this->status_id = static::STATUS_IN_PROGRESS;
	}

	public function setSolved()
	{
		$this->status_id = static::STATUS_SOLVED;
	}

	public function setToModeratorSent()
	{
		$this->view_status = static::STATUS_VIEW_TO_MODERATOR_SENT;
	}

	public function setToModeratorSeen()
	{
		$this->view_status = static::STATUS_VIEW_TO_MODERATOR_SEEN;
	}

	public function setToUserSent()
	{
		$this->view_status = static::STATUS_VIEW_TO_USER_SENT;
	}

	public function setToUserSeen()
	{
		$this->view_status = static::STATUS_VIEW_TO_USER_SEEN;
	}

	public function isScenarioGuest(): bool
	{
		return ($this->scenario == self::SCENARIO_CREATE_GUEST);
	}

	public function isGuest(): bool
	{
		return !empty($this->response_email);
	}

	public function isSent(): bool
	{
		return ($this->status_id == static::STATUS_SENT);
	}

	public function isInProgress(): bool
	{
		return ($this->status_id == static::STATUS_IN_PROGRESS);
	}

	public function isSolved(): bool
	{
		return ($this->status_id == static::STATUS_SOLVED);
	}

	public function isToModeratorSent(): bool
	{
		return ($this->view_status == static::STATUS_VIEW_TO_MODERATOR_SENT);
	}

	public function isToModeratorSeen(): bool
	{
		return ($this->view_status == static::STATUS_VIEW_TO_MODERATOR_SEEN);
	}

	public function isToUserSent(): bool
	{
		return ($this->view_status == static::STATUS_VIEW_TO_USER_SENT);
	}

	public function isToUserSeen(): bool
	{
		return ($this->view_status == static::STATUS_VIEW_TO_USER_SEEN);
	}

	public function sendMessage()
	{
		$bot = new Bot;
		$bot->messageSupport($this->id);
	}

}
