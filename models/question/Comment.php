<?php

namespace app\models\question;

use Yii;
use yii\db\ActiveRecord;
use yii\bootstrap5\Html;
use yii\helpers\{ArrayHelper, HtmlPurifier, StringHelper, Url};

use app\models\user\User;
use app\models\question\history\CommentHistory;
use app\models\like\LikeComment;
use app\models\request\Report;

use app\models\notification\CronNotification;
use app\models\helpers\{TextHelper, HtmlHelper, UserHelper};
use app\models\service\Bot;


class Comment extends ActiveRecord
{

	public static function tableName()
	{
		return 'comment';
	}

	public function rules()
	{
		return [
			[['comment_id'], 'unique'],
			[['comment_id', 'user_id', 'question_id', 'answer_id', 'like_count'], 'integer'],
			[['is_hidden', 'is_deleted'], 'boolean'],
			[['comment_text' ], 'string', 'min' => 1, 'max' => 8192],
			[['comment_datetime', 'edited_datetime', 'checked_datetime'], 'date', 'format' => 'php:Y-m-d H:i:s'],

			[['comment_text'], 'filter', 'filter' => 'strip_tags'],
			[['comment_text'], 'filter', 'filter' => 'trim'],
			[['comment_text'], 'filter', 'filter' => [TextHelper::class, 'remove_emoji']],
			// [['comment_text'], 'checkEmptyHtml'],
			[['comment_id', 'comment_text', 'user_id', 'question_id', 'answer_id'], 'required'],
		];
	}

	public static function primaryKey()
	{
		return [
			'comment_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'comment_id'     => Yii::t('app', '№ комментария'),
			'comment_text'  => Yii::t('app', 'Текст комментария'),
			'question_id' => Yii::t('app', 'Вопрос'),
			'user_id' => Yii::t('app', 'Ответивший пользователь'),
		];
	}

	public const EVENT_AFTER_CHANGE = 'afterChange';
	public const EVENT_ADD_LIKE = 'addLike';
	public const EVENT_RETURN_LIKE = 'returnLike';
	public const EVENT_REMOVE_LIKE = 'removeLike';
	public const EVENT_RATE_SUM = 'rateSum';

	public const SCENARIO_CREATE_QUESTION = 'create-question';
	public const SCENARIO_CREATE_ANSWER = 'create-answer';
	public const SCENARIO_EDIT_QUESTION = 'edit-question';
	public const SCENARIO_EDIT_ANSWER = 'edit-answer';

	private $_is_liked = null;
	private $_report_count = null;
	private $_is_reported = null;

	public function init()
	{
		$this->on(static::EVENT_BEFORE_VALIDATE, [$this, 'checkBeforeValidate']);
		$this->on(static::EVENT_BEFORE_INSERT, [$this, 'checkBeforeInsert']);
		$this->on(static::EVENT_BEFORE_UPDATE, [$this, 'checkBeforeUpdate']);

		$this->on(static::EVENT_AFTER_INSERT, [$this, 'checkAfterInsert']);
		$this->on(static::EVENT_AFTER_INSERT, [$this, 'checkCommentCount']);
		$this->on(static::EVENT_AFTER_INSERT, [$this, 'sendMessage']);

		$this->on(static::EVENT_AFTER_CHANGE, [$this, 'checkAfterChange']);
		$this->on(static::EVENT_AFTER_CHANGE, [$this, 'checkCommentCount']);
		$this->on(static::EVENT_RATE_SUM, [$this, 'checkRateSum']);

		$this->on(static::EVENT_ADD_LIKE, [$this, 'addLike']);
		$this->on(static::EVENT_ADD_LIKE, [$this, 'updateLikeCount']);
		$this->on(static::EVENT_RETURN_LIKE, [$this, 'returnLike']);
		$this->on(static::EVENT_RETURN_LIKE, [$this, 'updateLikeCount']);
		$this->on(static::EVENT_REMOVE_LIKE, [$this, 'removeLike']);
		$this->on(static::EVENT_REMOVE_LIKE, [$this, 'updateLikeCount']);

		parent::init();
	}

	public function scenarios()
	{
		return array_merge(parent::scenarios(), [
			self::SCENARIO_CREATE_QUESTION => [
				'comment_text', '!user_id', '!question_id',
				'!comment_datetime'
			],
			self::SCENARIO_CREATE_ANSWER => [
				'comment_text', '!user_id', '!question_id', '!answer_id',
				'!comment_datetime'
			],
			self::SCENARIO_EDIT_QUESTION => [
				'comment_text', '!user_id', '!question_id', '!comment_id',
				'!comment_datetime', '!edited_datetime'
			],
			self::SCENARIO_EDIT_ANSWER => [
				'comment_text', '!user_id', '!question_id', '!answer_id', '!comment_id',
				'!comment_datetime', '!edited_datetime'
			],
		]);
	}

	public function checkEmptyHtml($attribute)
	{
		if (HtmlHelper::isEmptyHtml($this->{$attribute})) {
			$this->addError($attribute, Yii::t('app', 'Текст не должен быть пустым.'));
			return false;
		}
		return true;
	}

	protected function checkBeforeValidate($event)
	{
		if ($this->isNewRecord) {
			if ($this->scenario == static::SCENARIO_CREATE_QUESTION) {
				if (empty($this->question)) {
					$this->addError('answer_text', Yii::t('app', 'Вопрос не был найден.'));
					return false;
				}
				if ($message = $this->canCreate()) {
					$this->addError('comment_text', $message);
					Yii::$app->session->setFlash('error', $message);
					return null;
				}
			} elseif ($this->scenario == static::SCENARIO_CREATE_ANSWER) {
				if (empty($this->answer)) {
					$this->addError('answer_text', Yii::t('app', 'Ответ не был найден.'));
					return false;
				}
				if ($message = $this->canCreate()) {
					$this->addError('comment_text', $message);
					Yii::$app->session->setFlash('error', $message);
					return null;
				}
				$this->question_id = $this->answer->question_id;
			} elseif (($this->scenario == static::SCENARIO_EDIT_QUESTION) or
				($this->scenario == static::SCENARIO_EDIT_ANSWER)) {
				if ($message = $this->canEdit()) {
					$this->addError('comment_text', $message);
					Yii::$app->session->setFlash('error', $message);
					return null;
				}
			}
			$this->user_id = Yii::$app->user->identity->id;
		}
	}

	protected function checkBeforeInsert($event)
	{
		$this->comment_datetime = date("Y-m-d H:i:s");
	}

	protected function checkBeforeUpdate($event)
	{
		if (($this->scenario == static::SCENARIO_EDIT_QUESTION) or
			($this->scenario == static::SCENARIO_EDIT_ANSWER)) {
			$this->edited_datetime = date("Y-m-d H:i:s");
			$this->createHistoryRecord();
		}
	}

	protected function checkAfterInsert($event)
	{
		if (($this->scenario == static::SCENARIO_CREATE_QUESTION) or
			($this->scenario == static::SCENARIO_CREATE_ANSWER)) {
			CronNotification::addComment($this);
		}
		$this->author->data->badgeData->updateCommentCount();
	}

	protected function checkAfterChange($event)
	{
		$data = $this->author->data->badgeData;
		$data->updateCommentCount();
		$data->updateCommentLikeCount();
		if ($this->is_hidden or $this->is_deleted) {
			CronNotification::removeCommentAfterChange($this);
		}
		$this->trigger(static::EVENT_RATE_SUM);
	}

	protected function checkRateSum($event)
	{
		$this->author->data->updateRateSum();
	}

	protected function checkCommentCount($event)
	{
		if ($this->isForQuestion) {
			$question = Question::findOne($this->question_id);
			if ($question) {
				$question->comment_count = self::find()
					->where(['question_id' => $this->question_id])
					->andWhere(['IS', 'answer_id', NULL])
					->andWhere(['is_deleted' => false])
					->andWhere(['is_hidden' => false])
					->count();
				$question->save();
			}
		} else {
			$answer = Answer::findOne($this->answer_id);
			if ($answer) {
				$answer->comment_count = self::find()
					->where(['answer_id' => $this->answer_id])
					->andWhere(['is_deleted' => false])
					->andWhere(['is_hidden' => false])
					->count();
				$answer->save();
			}
		}
	}

	protected function addLike($event)
	{
		$this->author->data->badgeData->updateCommentLikeCount();
		$this->trigger(static::EVENT_RATE_SUM);
		// CronNotification::addLikeComment($this);
	}

	protected function returnLike($event)
	{
		$this->author->data->badgeData->updateAnswerLikeCount();
		$this->trigger(static::EVENT_RATE_SUM);
	}

	protected function removeLike($event)
	{
		$this->author->data->badgeData->updateCommentLikeCount();
		$this->author->data->updateRateSum();
		// CronNotification::removeLikeComment($this);
	}

	protected function updateLikeCount()
	{
		$this->like_count = count($this->likes);
		$this->save();
	}

	protected function sendMessage($event)
	{
		$bot = new Bot;
		$bot->messageComment($this->id);
	}

	protected function createHistoryRecord()
	{
		$result = false;
		if (!$this->isNewRecord) {
			$data = $this->getOldAttributes();
			$history = new CommentHistory;
			$history->load($data, '');
			$result = $history->save();
		}
		return $result;
	}

	public function getAuthor()
	{
		return $this->hasOne(User::class, ['user_id' => 'user_id'])
			->joinWith('data');
	}

	public function getQuestion()
	{
		return $this->hasOne(Question::class, ['question_id' => 'question_id']);
	}

	public function getAnswer()
	{
		return $this->hasOne(Answer::class, ['answer_id' => 'answer_id']);
	}

	public function getHistoryRecords()
	{
		return $this->hasMany(CommentHistory::class, ['comment_id' => 'comment_id'])
			->orderBy(['history_comment_id' => SORT_DESC]);
	}

	public function getLastHistoryRecord()
	{
		if ($this->isEdited()) {
			return $this->hasOne(CommentHistory::class, ['comment_id' => 'comment_id'])
				->orderBy(['history_comment_id' => SORT_DESC]);
		}
	}

	public function getLikes()
	{
		return $this->hasMany(LikeComment::class, ['comment_id' => 'comment_id']);
	}

	public function getReports()
	{
		return $this->hasMany(Report::class, ['comment_id' => 'comment_id']);
	}

	public static function getAllCount(int $question_id = 0, int $answer_id = 0)
	{
		$query = self::find()
			->where(['is_deleted' => false]);
		if ($question_id) {
			$query = $query->andWhere(['question_id' => $question_id]);
		} elseif ($answer_id) {
			$query = $query->andWhere(['answer_id' => $answer_id]);
		}
		return $query->count();
	}

	public static function getTodayCount()
	{
		return self::find()
			->where(['is_deleted' => false])
			->andWhere(['>=', 'comment_datetime', date('Y-m-d 00:00:00', strtotime('-1 day'))])
			->count();
	}

	public static function getHiddenCount()
	{
		return self::find()
			->where(['is_hidden' => true])
			->count();
	}

	public static function getDeletedCount()
	{
		return self::find()
			->where(['is_deleted' => true])
			->count();
	}

	public static function getAllUserCount(int $user_id)
	{
		return self::find()
			->where(['user_id' => $user_id])
			->andWhere(['is_deleted' => false])
			->count();
	}

	public static function getTodayUserCount(int $user_id)
	{
		return self::find()
			->where(['user_id' => $user_id])
			->andWhere(['is_deleted' => false])
			->andWhere(['>=', 'comment_datetime', date('Y-m-d 00:00:00', strtotime('-1 day'))])
			->count();
	}

	public function getAuthorList(int $user_id)
	{
		return self::find()
			->where(['user_id' => $user_id])
			->orderBy(['comment_datetime' => SORT_DESC])
			->indexBy('comment_id')
			->all();
	}

	public function getAllList(int $limit = 0, int $offset = 0, int $question_id = 0, int $answer_id = 0)
	{
		$list = self::find();
		if ($question_id > 0) {
			$list = $list->where(['question_id' => $question_id]);
		} elseif ($answer_id > 0) {
			$list = $list->where(['answer_id' => $answer_id]);
		}
		$list = $list->orderBy(['comment_datetime' => SORT_DESC]);
		if ($limit > 0) {
			$list = $list->limit($limit);
		}
		if ($offset > 0) {
			$list = $list->offset($offset);
		}
		$list = $list->indexBy('comment_datetime');
		return $list->all();
	}

	public function getAllUserList(int $user_id, int $limit = 0, int $offset = 0, int $question_id = 0, int $answer_id = 0)
	{
		$list = self::find()
			->where(['user_id' => $user_id])
			->andWhere(['is_deleted' => false]);
		if ($question_id > 0) {
			$list = $list->andWhere(['question_id' => $question_id]);
		} elseif ($answer_id > 0) {
			$list = $list->andWhere(['answer_id' => $answer_id]);
		}
		$list = $list->orderBy(['comment_datetime' => SORT_DESC]);
		if ($limit > 0) {
			$list = $list->limit($limit);
		}
		if ($offset > 0) {
			$list = $list->offset($offset);
		}
		$list = $list->indexBy('comment_datetime');
		return $list->all();
	}

	public function getReportsByType()
	{
		$list = [];
		foreach ($this->reports as $report) {
			$list[$report->report_type][] = $report;
		}
		return $list;
	}

	public function getId()
	{
		return $this->comment_id;
	}

	public function getText()
	{
		$text = Html::encode($this->comment_text);
		return nl2br($text);
		// return Html::decode($this->comment_text);
	}

	public function getDatetime()
	{
		return $this->comment_datetime;
	}

	public function getShortText()
	{
		$text = strip_tags($this->text);
		return StringHelper::truncate($text, 50, asHtml: true);
	}

	public function getTimeElapsed()
	{
		return HtmlHelper::getTimeElapsed($this->comment_datetime);
	}

	public function getTimeFull()
	{
		return HtmlHelper::getTimeElapsed($this->comment_datetime, true);
	}

	public function getEditedTimeElapsed()
	{
		return HtmlHelper::getTimeElapsed($this->edited_datetime);
	}

	public function getEditedTimeFull()
	{
		return HtmlHelper::getTimeElapsed($this->edited_datetime, true);
	}

	public function getCheckedTimeFull()
	{
		return HtmlHelper::getTimeElapsed($this->checked_datetime, true);
	}

	public function getCheckedTimeElapsed()
	{
		return HtmlHelper::getTimeElapsed($this->checked_datetime);
	}

	public function getRecordLink(bool $scheme = false)
	{
		return Url::to(["/question/answer/{$this->question_id}/comment-{$this->id}"], $scheme);
	}

	public function getEditLink(bool $scheme = false)
	{
		return Url::to(["/question/answer/{$this->question_id}/editComment-{$this->id}"], $scheme);
	}

	public function getHistoryLink(bool $scheme = false)
	{
		return Url::to(["/history/comment", 'id' => $this->id], $scheme);
	}

	public function getLimitLink(bool $scheme = false)
	{
		return Url::to(["/moderator/limit-comment", 'id' => $this->id], $scheme);
	}

	public function isEdited()
	{
		return !empty($this->edited_datetime);
	}

	public function isChecked()
	{
		return !empty($this->checked_datetime);
	}

	protected function findIsLiked()
	{
		$user_id = Yii::$app->user->identity->id;
		$list = ArrayHelper::index($this->likes, 'user_id');
		$this->_is_liked = !empty($list[$user_id]);
	}

	public function findReportCount()
	{
		$this->_report_count = count($this->reports);
	}

	public function findIsReported()
	{
		$id = Yii::$app->user->identity->id;
		$list = ArrayHelper::index($this->reports, 'author_id');
		$this->_is_reported = !empty($list[$id]);
	}

	public function getIsLiked(): ?bool
	{
		if (is_null($this->_is_liked)) {
			$this->findIsLiked();
		}
		return $this->_is_liked;
	}

	public function getReportCount(): int
	{
		if (is_null($this->_report_count)) {
			$this->findReportCount();
		}
		return $this->_report_count;
	}

	public function getIsReported(): bool
	{
		if (is_null($this->_is_reported)) {
			$this->findIsReported();
		}
		return $this->_is_reported;
	}

	public function getIsAuthorQuestionAuthor()
	{
		return ($this->question->user_id == $this->user_id);
	}

	public function getIsUserQuestionAuthor()
	{
		return ($this->question->user_id == Yii::$app->user->identity->id);
	}

	public function getIsAuthor()
	{
		return ($this->user_id == Yii::$app->user->identity->id);
	}

	public function getIsForAnswer()
	{
		return (!$this->isNewRecord and !empty($this->answer_id));
	}

	public function getIsForQuestion()
	{
		return (!$this->isNewRecord and !empty($this->answer_id));
	}

	public function canCreate(): ?string
	{
		return $message = null;
		$user = Yii::$app->user->identity;
		if (!$user->isModerator()) {
			if (!$user->canComment()) {
				$message = $user->limit->getCommentMessage();
			} elseif ($this->isForQuestion and $this->question->is_deleted) {
				$message = Yii::t('app', 'Вопрос был удалён!');
			} elseif ($this->isForQuestion and $this->question->is_hidden) {
				$message = Yii::t('app', 'Для редактирования комментария вопрос не должен быть скрыт!');
			} elseif ($this->isForAnswer and $this->answer->is_deleted) {
				$message = Yii::t('app', 'Ответ был удалён!');
			} elseif ($this->isForAnswer and $this->answer->is_hidden) {
				$message = Yii::t('app', 'Для редактирования комментария ответ не должен быть скрыт!');
			}
		}
		return $message;
	}

	public function canEdit(): ?string
	{
		$message = null;
		$user = Yii::$app->user->identity;
		if (!$user->isModerator()) {
			if (!$this->isAuthor) {
				$message = Yii::t('app', 'Вы не являетесь автором комментария!');
			} elseif ($this->isForQuestion and $this->question->is_deleted) {
				$message = Yii::t('app', 'Вопрос был удалён!');
			} elseif ($this->isForQuestion and $this->question->is_hidden) {
				$message = Yii::t('app', 'Для редактирования комментария вопрос не должен быть скрыт!');
			} elseif ($this->isForAnswer and $this->answer->is_deleted) {
				$message = Yii::t('app', 'Ответ был удалён!');
			} elseif ($this->isForAnswer and $this->answer->is_hidden) {
				$message = Yii::t('app', 'Для редактирования комментария ответ не должен быть скрыт!');
			/*} elseif (date('YmdHis', strtotime($this->datetime)) < date('YmdHis', strtotime("+30 day"))) {
				$message = Yii::t('app', 'Время для редактирования вышло!');*/
			} elseif (!$user->canComment()) {
				$message = $user->limit->getCommentMessage();
			}
		}
		return $message;
	}

	public function canDelete(): ?string
	{
		$message = null;
		if (!Yii::$app->user->identity->isModerator()) {
			$message = Yii::t('app', 'Вы не можете выполнить данное действие!');
		}
		return $message;
	}

	public function canSee(): ?string
	{
		$message = null;
		if (!Yii::$app->user->identity->isModerator()) {
			if ($this->is_deleted) {
				$message = Yii::t('app', 'Комментарий удалён!');
			}
			if ($this->is_hidden and !$this->isAuthor) {
				$message = Yii::t('app', 'Комментарий времмено скрыт!');
			}
		}
		return $message;
	}

	public function setEditing()
	{
		if ($this->answer_id) {
			$this->scenario = static::SCENARIO_EDIT_ANSWER;
		} else {
			$this->scenario = static::SCENARIO_EDIT_QUESTION;
		}
	}

	public static function setHidden(int $id): array
	{
		$ok = false;
		$record = self::findOne($id);
		if (!$record) {
			$message = Yii::t('app', 'Комментарий не был найден');
		} elseif(!Yii::$app->user->identity->isModerator()) {
			$message = Yii::t('app', 'Вы не являетесь модератором');
		} elseif($record->is_deleted) {
			$message = Yii::t('app', 'Комментарий удалён');
		} elseif($record->is_hidden) {
			$message = Yii::t('app', 'Комментарий уже скрыт');
		} else {
			$record->is_hidden = true;
			if ($record->save()) {
				$message = Yii::t('app', 'Комментарий скрыт');
				$ok = true;
			} else {
				$message = Yii::t('app', 'Комментарий не скрыт');
			}
		}
		return [
			'status' => $ok,
			'message' => $message
		];
	}

	public static function setShown(int $id): array
	{
		$ok = false;
		$record = self::findOne($id);
		if (!$record) {
			$message = Yii::t('app', 'Комментарий не был найден');
		} elseif(!Yii::$app->user->identity->isModerator()) {
			$message = Yii::t('app', 'Вы не являетесь модератором');
		} elseif($record->is_deleted) {
			$message = Yii::t('app', 'Комментарий удалён');
		} elseif(!$record->is_hidden) {
			$message = Yii::t('app', 'Комментарий уже показан');
		} else {
			$record->is_hidden = false;
			if ($record->save()) {
				$message = Yii::t('app', 'Комментарий показан');
				$ok = true;
			} else {
				$message = Yii::t('app', 'Комментарий не показан');
			}
		}
		return [
			'status' => $ok,
			'message' => $message
		];
	}

	public static function setDeleted(int $id): array
	{
		$ok = false;
		$record = self::findOne($id);
		if (!$record) {
			$message = Yii::t('app', 'Комментарий не был найден');
		} elseif(!Yii::$app->user->identity->isModerator()) {
			$message = Yii::t('app', 'Вы не являетесь модератором');
		} elseif($record->is_deleted) {
			$message = Yii::t('app', 'Комментарий уже удалён');
		} else {
			$record->is_deleted = true;
			if ($record->save()) {
				$message = Yii::t('app', 'Комментарий удалён');
				$ok = true;
			} else {
				$message = Yii::t('app', 'Комментарий не удалён');
			}
		}
		return [
			'status' => $ok,
			'message' => $message
		];
	}

	public static function setRestored(int $id): array
	{
		$ok = false;
		$record = self::findOne($id);
		if (!$record) {
			$message = Yii::t('app', 'Комментарий не был найден');
		} elseif(!Yii::$app->user->identity->isModerator()) {
			$message = Yii::t('app', 'Вы не являетесь модератором');
		} elseif(!$record->is_deleted) {
			$message = Yii::t('app', 'Комментарий не удалён');
		} else {
			$record->is_deleted = false;
			if ($record->save()) {
				$message = Yii::t('app', 'Комментарий восстановлен');
				$ok = true;
			} else {
				$message = Yii::t('app', 'Комментарий не восстановлен');
			}
		}
		return [
			'status' => $ok,
			'message' => $message
		];
	}

}
