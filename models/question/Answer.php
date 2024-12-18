<?php

namespace app\models\question;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use yii\bootstrap5\Html;
use yii\helpers\{ArrayHelper, HtmlPurifier, StringHelper, Url};

use app\models\file\File;
use app\models\user\{User, UserRate};
use app\models\question\history\AnswerHistory;
use app\models\like\LikeAnswer;
use app\models\request\Report;

use app\models\notification\CronNotification;
use app\models\helpers\{TextHelper, HtmlHelper, UserHelper};
use app\models\service\Bot;

use \Parsedown;


class Answer extends ActiveRecord
{

	public static function tableName()
	{
		return 'answer';
	}

	public function rules()
	{
		return [
			[['answer_id'], 'unique'],
			[['answer_id', 'user_id', 'question_id', 'comment_count', 'like_count'], 'integer'],
			[['is_helped', 'is_hidden', 'is_deleted'], 'boolean'],
			[['answer_text'], 'string', 'min' => 1, 'max' => 8192 ],
			[['answer_datetime', 'edited_datetime', 'checked_datetime'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],

			[['answer_text'], 'filter', 'filter' => 'strip_tags'],
			[['answer_text'], 'filter', 'filter' => 'trim'],
			[['answer_text'], 'filter', 'filter' => [TextHelper::class, 'remove_emoji']],
			[['answer_text'], 'checkEmptyField'],
			[['answer_text', 'user_id', 'question_id'], 'required'],

			[['upload_files'], 'file', 'extensions' => static::getExtensionList(), 'maxSize' => 1024 * 1024, 'maxFiles' => 10],
			[['old_files'], 'each', 'rule' => ['integer']],
		];
	}

	public static function primaryKey()
	{
		return [
			'answer_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'answer_id'     => Yii::t('app', '№ ответа'),
			'answer_text'  => Yii::t('app', 'Текст ответа'),
			'question_id' => Yii::t('app', 'Вопрос'),
			'user_id' => Yii::t('app', 'Ответивший пользователь'),
			'upload_files'  => Yii::t('app', 'Прикрепляемые файлы'),
		];
	}

	public const EVENT_AFTER_HELP = 'afterHelp';
	public const EVENT_AFTER_CHANGE = 'afterChange';
	public const EVENT_ADD_LIKE = 'addLike';
	public const EVENT_RETURN_LIKE = 'returnLike';
	public const EVENT_REMOVE_LIKE = 'removeLike';
	public const EVENT_RATE_SUM = 'rateSum';

	public const SCENARIO_CREATE = 'create';
	public const SCENARIO_EDIT = 'edit';

	public $upload_files;
	public $old_files;

	private $_is_liked = null;
	private $_file_count = null;
	private $_report_count = null;
	private $_is_reported = null;

	public function init()
	{
		$this->on(static::EVENT_BEFORE_VALIDATE, [$this, 'checkBeforeValidate']);
		$this->on(static::EVENT_BEFORE_INSERT, [$this, 'checkBeforeInsert']);
		$this->on(static::EVENT_BEFORE_UPDATE, [$this, 'checkBeforeUpdate']);;

		$this->on(static::EVENT_AFTER_INSERT, [$this, 'checkAfterInsert']);
		$this->on(static::EVENT_AFTER_INSERT, [$this, 'checkAnswerCount']);
		$this->on(static::EVENT_AFTER_INSERT, [$this, 'sendMessage']);

		$this->on(static::EVENT_AFTER_UPDATE, [$this, 'checkAfterUpdate']);

		$this->on(static::EVENT_AFTER_HELP, [$this, 'checkHelp']);
		$this->on(static::EVENT_AFTER_CHANGE, [$this, 'checkAfterChange']);
		$this->on(static::EVENT_AFTER_CHANGE, [$this, 'checkAnswerCount']);
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
			self::SCENARIO_CREATE => [
				'answer_text', 'upload_files',
				'!user_id', '!question_id',
				'!answer_datetime'
			],
			self::SCENARIO_EDIT => [
				'answer_text', 'upload_files', 'old_files',
				'!user_id', '!question_id', '!answer_id',
				'!answer_datetime', '!edited_datetime',
			],
		]);
	}

	public function checkEmptyField($attribute)
	{
		if (HtmlHelper::isEmptyMarkdown($this->{$attribute})) {
			$this->addError($attribute, Yii::t('app', 'Текст не должен быть пустым.'));
			return false;
		}
		return true;
	}

	protected function checkBeforeValidate($event)
	{
		if (!$this->question) {
			$this->addError('answer_text', Yii::t('app', 'Вопрос не был найден.'));
			return false;
		}
		if ($this->scenario == static::SCENARIO_CREATE) {
			if ($message = $this->canCreate()) {
				$this->addError('answer_text', $message);
				Yii::$app->session->setFlash('error', $message);
				return false;
			}
			$this->user_id = Yii::$app->user->identity->id;
			if (empty($this->answer_text)) {
				$this->upload_files = UploadedFile::getInstances($this, 'upload_files');
				if (!empty($this->upload_files)) {
					$this->answer_text = Yii::t('app', 'Ответ прикреплён на фото.');
				}
			}
		} elseif ($this->scenario == static::SCENARIO_EDIT) {
			if ($message = $this->canEdit()) {
				Yii::$app->session->setFlash('error', $message);
				return false;
			}
			if (empty($this->answer_text)) {
				if (!empty($this->old_files)) {
					$this->answer_text = Yii::t('app', 'Ответ прикреплён на фото.');
				} else {
					$this->upload_files = UploadedFile::getInstances($this, 'upload_files');
					if (!empty($this->upload_files)) {
						$this->answer_text = Yii::t('app', 'Ответ прикреплён на фото.');
					}
				}
			}
		}
	}

	protected function checkBeforeInsert($event)
	{
		$this->answer_datetime = date("Y-m-d H:i:s");
	}

	protected function checkBeforeUpdate($event)
	{
		if ($this->scenario == static::SCENARIO_EDIT) {
			$this->edited_datetime = date("Y-m-d H:i:s");
			$this->createHistoryRecord();
		}
	}

	protected function checkAfterInsert($event)
	{
		if ($this->scenario == static::SCENARIO_CREATE) {
			CronNotification::addAnswer($this);
			$discipline = $this->question->disciplineRecord;
			if ($discipline) {
				$discipline->updateAnswerCount();
			}
		}
		$this->saveFiles();
		$this->author->data->badgeData->updateAnswerCount();
	}

	protected function checkAfterUpdate($event)
	{
		if ($this->scenario == static::SCENARIO_EDIT) {
			CronNotification::addAnswerEdit($this);
		}
		$this->saveFiles();
	}

	protected function checkAfterChange($event)
	{
		$data = $this->author->data->badgeData;
		$data->updateAnswerCount();
		$data->updateAnswerHelpedCount();
		$data->updateAnswerLikeCount();
		$discipline = $this->question->disciplineRecord;
		if ($discipline) {
			$discipline->updateQuestionHelpedCount();
			$discipline->updateAnswerCount();
		}
		if ($this->is_hidden or $this->is_deleted) {
			CronNotification::removeAnswerAfterChange($this);
		}
		$this->trigger(static::EVENT_RATE_SUM);
	}

	protected function checkRateSum($event)
	{
		$this->author->data->updateRateSum();
	}

	protected function checkHelp($event)
	{
		$this->author->data->badgeData->updateAnswerHelpedCount();
		$discipline = $this->question->disciplineRecord;
		if ($discipline) {
			$discipline->updateQuestionHelpedCount();
		}
		if ($this->is_helped) {
			UserRate::addAnswerHelped($this);
			CronNotification::addQuestionAnswered($this);
		} else {
			UserRate::removeAnswerHelped($this);
			CronNotification::removeQuestionAnswered($this);
		}
	}

	protected function checkAnswerCount($event)
	{
		$question = Question::findOne($this->question_id);
		if ($question) {
			$question->answer_count = self::find()
				->where(['question_id' => $this->question_id])
				->andWhere(['is_deleted' => false])
				->andWhere(['is_hidden' => false])
				->count();
			$question->is_helped = self::find()
				->where(['question_id' => $this->question_id])
				->andWhere(['is_helped' => true])
				->andWhere(['is_deleted' => false])
				->andWhere(['is_hidden' => false])
				->exists();
			$question->save();
		}
	}

	protected function addLike($event)
	{
		$author_id = Yii::$app->user->identity->id;
		$this->author->data->badgeData->updateAnswerLikeCount();
		CronNotification::addLikeAnswer($this, $author_id);
		$this->trigger(static::EVENT_RATE_SUM);
	}

	protected function returnLike($event)
	{
		$this->author->data->badgeData->updateAnswerLikeCount();
		$this->trigger(static::EVENT_RATE_SUM);
	}

	protected function removeLike($event)
	{
		$author_id = Yii::$app->user->identity->id;
		$this->author->data->badgeData->updateAnswerLikeCount();
		CronNotification::removeLikeAnswer($this, $author_id);
		$this->trigger(static::EVENT_RATE_SUM);
	}

	protected function updateLikeCount()
	{
		$this->like_count = count($this->likes);
		$this->save();
	}

	protected function saveFiles()
	{
		$this->upload_files = UploadedFile::getInstances($this, 'upload_files');
		if (is_array($this->upload_files)) {
			foreach ($this->upload_files as $file) {
				if ($file) {
					$file_model = new File;
					$file_model->saveFromAnswer($this, $file);
				}
			}
		}
	}

	protected function sendMessage($event)
	{
		$bot = new Bot;
		$bot->messageAnswer($this->id);
	}

	public function changeFileLinks()
	{
		if ($files = $this->files) {
			foreach ($files as $file) {
				$file->replaceBucketLink();
			}
		}
	}

	protected function createHistoryRecord()
	{
		$result = false;
		if (!$this->isNewRecord) {
			$data = $this->getOldAttributes();
			if (is_null($this->old_files)) {
				$this->old_files = [];
			}
			$history = new AnswerHistory;
			$history->load($data, '');
			$result = $history->save();
			if ($this->files) {
				foreach ($this->files as $file) {
					$answer = $history->answer;
					$need_to_delete = false;
					if (!in_array($file->file_id, $this->old_files)) {
						$need_to_delete = true;
					}
					$file->createAnswerHistoryRecord($answer, $need_to_delete);
				}
			}
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

	public function getComments()
	{
		$link = $this->hasMany(Comment::class, ['question_id' => 'question_id', 'answer_id' => 'answer_id'])
			->inverseOf('answer')
			->joinWith('author')
			->joinWith('likes')
			->joinWith('reports');
		if (!UserHelper::canSeeAll()) {
			$link = $link->onCondition([Comment::tableName().'.is_deleted' => false]);
		}
		return $link;
	}

	public function getFiles()
	{
		return $this->hasMany(File::class, ['question_id' => 'question_id', 'answer_id' => 'answer_id']);
	}

	public function getHistoryRecords()
	{
		return $this->hasMany(AnswerHistory::class, ['answer_id' => 'answer_id'])
			->orderBy(['history_answer_id' => SORT_DESC]);
	}

	public function getLastHistoryRecord()
	{
		if ($this->isEdited()) {
			return $this->hasOne(AnswerHistory::class, ['answer_id' => 'answer_id'])
				->orderBy(['history_answer_id' => SORT_DESC]);
		}
	}

	public function getLikes()
	{
		return $this->hasMany(LikeAnswer::class, ['answer_id' => 'answer_id']);
	}

	public function getReports()
	{
		return $this->hasMany(Report::class, ['answer_id' => 'answer_id']);
	}

	public static function getAllCount(int $question_id = 0)
	{
		$query = self::find()
			->where(['is_deleted' => false]);
		if ($question_id) {
			$query = $query->andWhere(['question_id' => $question_id]);
		}
		return $query->count();
	}

	public static function getTodayCount()
	{
		return self::find()
			->where(['is_deleted' => false])
			->andWhere(['>=', 'answer_datetime', date('Y-m-d 00:00:00', strtotime('-1 day'))])
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
			->andWhere(['>=', 'answer_datetime', date('Y-m-d 00:00:00', strtotime('-1 day'))])
			->count();
	}

	public function getAuthorList(int $user_id, int $limit = 0)
	{
		$list = self::find()
			->where(['user_id' => $user_id])
			->orderBy(['answer_datetime' => SORT_DESC])
			->indexBy('answer_id');
		if ($limit > 0) {
			$list = $list->limit($limit);
		}
		return $list->all();
	}

	public static function getQuestionAnswerList(int $id)
	{
		return Answer::find()
			->from(['answer' => self::tableName()])
			->joinWith('question as question')
			->joinWith('files')
			->joinWith('likes')
			->where(['question.question_id' => $id])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['answer.is_helped' => false])
			->orderBy(['like_count' => SORT_DESC, 'answer_datetime' => SORT_DESC])
			->all();
	}

	public static function getQuestionAnswerHelpedList(int $id)
	{
		return Answer::find()
			->from(['answer' => self::tableName()])
			->joinWith('question as question')
			->joinWith('files')
			->joinWith('likes')
			->where(['question.question_id' => $id])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['answer.is_helped' => true])
			->orderBy(['like_count' => SORT_DESC, 'answer_datetime' => SORT_DESC])
			->all();
	}

	public function getCommentListHelpful(int $min_count = 0)
	{
		$list = $this->comments;
		usort($list, function($a, $b) {
			return $a->datetime <=> $b->datetime;
		});
		$list = array_filter($list, function($record) use($min_count) {
			return $record->like_count >= $min_count;
		});
		return $list;
	}

	public function getCommentFeedList()
	{
		$list = $this->comments;
		usort($list, function($a, $b) {
			if ($a->like_count == $b->like_count) {
				return $a->datetime <=> $b->datetime;
			} else {
				return $b->like_count <=> $a->like_count;
			}
		});
		return array_slice($list, 0, 2);
	}

	public function getAllList(int $limit = 0, int $offset = 0, int $question_id = 0)
	{
		$list = self::find();
		if ($question_id > 0) {
			$list = $list->where(['question_id' => $question_id]);
		}
		$list = $list->orderBy(['answer_datetime' => SORT_DESC]);
		if ($limit > 0) {
			$list = $list->limit($limit);
		}
		if ($offset > 0) {
			$list = $list->offset($offset);
		}
		$list = $list->indexBy('answer_id');
		return $list->all();
	}

	public function getAllUserList(int $user_id, int $limit = 0, int $offset = 0, int $question_id = 0)
	{
		$list = self::find()
			->where(['user_id' => $user_id])
			->andWhere(['is_deleted' => false]);
		if ($question_id > 0) {
			$list = $list->andWhere(['question_id' => $question_id]);
		}
		$list = $list->orderBy(['answer_datetime' => SORT_DESC]);
		if ($limit > 0) {
			$list = $list->limit($limit);
		}
		if ($offset > 0) {
			$list = $list->offset($offset);
		}
		$list = $list->indexBy('answer_id');
		return $list->all();
	}

	public function getFilesDocs()
	{
		$list = [];
		foreach ($this->files as $file) {
			if (!$file->isImg) {
				$list[] = $file;
			}
		}
		return $list;
	}

	public function getFilesImages()
	{
		$list = [];
		foreach ($this->files as $file) {
			if ($file->isImg) {
				$list[] = $file;
			}
		}
		return $list;
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
		return $this->answer_id;
	}

	public function getText()
	{
		$parse = new Parsedown;
		$text = $parse->text($this->answer_text);
		return $text;
		// return Html::encode($text);
		// return Html::decode($this->answer_text);
	}

	public function getDatetime()
	{
		return $this->answer_datetime;
	}

	public static function getExtensionList(): array
	{
		return [
			'jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx',
			'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip'
		];
	}

	public function getShortText()
	{
		$text = strip_tags($this->text);
		return StringHelper::truncate($text, 50, asHtml: true);
	}

	public function getTimeElapsed()
	{
		return HtmlHelper::getTimeElapsed($this->answer_datetime);
	}

	public function getTimeFull()
	{
		return HtmlHelper::getTimeElapsed($this->answer_datetime, true);
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
		return Url::to(["/question/answer/{$this->question_id}/answer-{$this->id}"], $scheme);
	}

	public function getCommentsLink(bool $scheme = false)
	{
		return Url::to(["/question/answer/{$this->question_id}/answerComments-{$this->id}"], $scheme);
	}

	public function getEditLink(bool $scheme = false)
	{
		return Url::to(["/question/answer/{$this->question_id}/editAnswer-{$this->id}"], $scheme);
	}

	public function getHistoryLink(bool $scheme = false)
	{
		return Url::to(["/history/answer", 'id' => $this->id], $scheme);
	}

	public function getLimitLink(bool $scheme = false)
	{
		return Url::to(["/moderator/limit-answer", 'id' => $this->id], $scheme);
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

	public function findFileCount()
	{
		$this->_file_count = count($this->files);
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

	public function getFileCount(): int
	{
		if (is_null($this->_file_count)) {
			$this->findFileCount();
		}
		return $this->_file_count;
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

	public function setEditing()
	{
		$this->scenario = static::SCENARIO_EDIT;
	}

	public function canCreate(): ?string
	{
		return $message = null;
		$user = Yii::$app->user->identity;
		$record = self::find()
			->where(['question_id' => $this->question_id])
			->andWhere(['user_id' => $user->id])
			->one();
		if (!empty($record)) {
			$link = Html::a(Yii::t('app', 'имеющийся ответ'), $record->getRecordLink(), ['class' => 'text-decoration-underline']);
			$message = Yii::t('app', 'Вы уже дали ответ на этот вопрос.') . "<br>" .
				Yii::t('app', 'Если хотите внести дополнениия, то Вы можете отредактировать {link}.', ['link' => $link]);
		}
		if (!$user->isModerator()) {
			if (!$message and !$user->canAnswer()) {
				$message = $user->limit->getAnswerMessage();
			} elseif ($this->question->is_closed) {
				$message = Yii::t('app', 'Ответы больше не принимаются.');
			} elseif ($this->question->is_deleted) {
				$message = Yii::t('app', 'Вопрос был удалён!');
			} elseif ($this->question->is_hidden) {
				$message = Yii::t('app', 'Для редактирования ответа вопрос не должен быть скрыт!');
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
				$message = Yii::t('app', 'Вы не являетесь автором ответа!');
			} elseif ($this->question->is_closed) {
				$message = Yii::t('app', 'Для редактирования ответа вопрос должен быть открыт!');
			} elseif ($this->question->is_deleted) {
				$message = Yii::t('app', 'Вопрос был удалён!');
			} elseif ($this->question->is_hidden) {
				$message = Yii::t('app', 'Для редактирования ответа вопрос не должен быть скрыт!');
			/*} elseif (date('YmdHis', strtotime($this->datetime)) < date('YmdHis', strtotime("+30 day"))) {
				$message = Yii::t('app', 'Время для редактирования вышло!');*/
			} elseif (!$user->canAnswer()) {
				$message = $user->limit->getAnswerMessage();
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
				$message = Yii::t('app', 'Ответ удалён!');
			}
			if ($this->is_hidden and !$this->isAuthor) {
				$message = Yii::t('app', 'Ответ времмено скрыт!');
			}
		}
		return $message;
	}

	public static function setHelped(int $id): array
	{
		$ok = false;
		$record = self::find()
			->joinWith('question')
			->where(['answer_id' => $id])
			->one();
		if (!$record) {
			$message = Yii::t('app', 'Ответ не был найден');
		} elseif($record->is_deleted) {
			$message = Yii::t('app', 'Ответ удалён');
		} elseif(!$record->isUserQuestionAuthor) {
			$message = Yii::t('app', 'Вы не являетесь автором вопроса');
		} elseif($record->is_helped) {
			$message = Yii::t('app', 'Ответ уже отмечен решившим вопрос');
		} else {
			$record->is_helped = true;
			if ($record->save()) {
				$record->trigger(static::EVENT_AFTER_HELP);
				$record->trigger(static::EVENT_AFTER_CHANGE);
				$message = Yii::t('app', 'Ответ решил вопрос');
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

	public static function setNotHelped(int $id): array
	{
		$ok = false;
		$record = self::find()
			->joinWith('question')
			->where(['answer_id' => $id])
			->one();
		if (!$record) {
			$message = Yii::t('app', 'Ответ не был найден');
		} elseif($record->is_deleted) {
			$message = Yii::t('app', 'Ответ удалён');
		} elseif(!$record->isUserQuestionAuthor) {
			$message = Yii::t('app', 'Вы не являетесь автором вопроса');
		} elseif(!$record->is_helped) {
			$message = Yii::t('app', 'Ответ уже отмечен нерешившим вопрос');
		} else {
			$record->is_helped = false;
			if ($record->save()) {
				$record->trigger(static::EVENT_AFTER_HELP);
				$record->trigger(static::EVENT_AFTER_CHANGE);
				$message = Yii::t('app', 'Ответ не решил вопрос');
				$ok = true;
			} else {
				$message = Yii::t('app', 'Не удалось убрать отметку');
			}
		}
		return [
			'status' => $ok,
			'message' => $message
		];
	}

	public static function setHidden(int $id): array
	{
		$ok = false;
		$record = self::findOne($id);
		if (!$record) {
			$message = Yii::t('app', 'Ответ не был найден');
		} elseif(!Yii::$app->user->identity->isModerator()) {
			$message = Yii::t('app', 'Вы не являетесь модератором');
		} elseif($record->is_deleted) {
			$message = Yii::t('app', 'Ответ удалён');
		} elseif($record->is_hidden) {
			$message = Yii::t('app', 'Ответ уже скрыт');
		} else {
			$record->is_hidden = true;
			if ($record->save()) {
				$message = Yii::t('app', 'Ответ скрыт');
				$record->changeFileLinks();
				$record->trigger(static::EVENT_AFTER_CHANGE);
				$ok = true;
			} else {
				$message = Yii::t('app', 'Ответ не скрыт');
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
			$message = Yii::t('app', 'Ответ не был найден');
		} elseif(!Yii::$app->user->identity->isModerator()) {
			$message = Yii::t('app', 'Вы не являетесь модератором');
		} elseif($record->is_deleted) {
			$message = Yii::t('app', 'Ответ удалён');
		} elseif(!$record->is_hidden) {
			$message = Yii::t('app', 'Ответ уже показан');
		} else {
			$record->is_hidden = false;
			if ($record->save()) {
				$message = Yii::t('app', 'Ответ показан');
				$record->trigger(static::EVENT_AFTER_CHANGE);
				$ok = true;
			} else {
				$message = Yii::t('app', 'Ответ не показан');
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
			$message = Yii::t('app', 'Ответ не был найден');
		} elseif(!Yii::$app->user->identity->isModerator()) {
			$message = Yii::t('app', 'Вы не являетесь модератором');
		} elseif($record->is_deleted) {
			$message = Yii::t('app', 'Ответ уже удалён');
		} else {
			$record->is_deleted = true;
			if ($record->save()) {
				$message = Yii::t('app', 'Ответ удалён');
				$record->changeFileLinks();
				$record->trigger(static::EVENT_AFTER_CHANGE);
				$ok = true;
			} else {
				$message = Yii::t('app', 'Ответ не удалён');
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
			$message = Yii::t('app', 'Ответ не был найден');
		} elseif(!Yii::$app->user->identity->isModerator()) {
			$message = Yii::t('app', 'Вы не являетесь модератором');
		} elseif(!$record->is_deleted) {
			$message = Yii::t('app', 'Ответ не удалён');
		} else {
			$record->is_deleted = false;
			if ($record->save()) {
				$message = Yii::t('app', 'Ответ восстановлен');
				$record->changeFileLinks();
				$record->trigger(static::EVENT_AFTER_CHANGE);
				$ok = true;
			} else {
				$message = Yii::t('app', 'Ответ не восстановлен');
			}
		}
		return [
			'status' => $ok,
			'message' => $message
		];
	}

}
