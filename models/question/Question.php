<?php

namespace app\models\question;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use yii\bootstrap5\Html;
use yii\helpers\{ArrayHelper, StringHelper, Url};

use app\models\file\File;
use app\models\user\User;
use app\models\question\history\QuestionHistory;
use app\models\follow\FollowQuestion;
use app\models\edu\{Discipline, Faculty, Teacher};
use app\models\request\{Report, DuplicateQuestionRequest};

use app\models\notification\CronNotification;
use app\models\helpers\{ModelHelper, TextHelper, HtmlHelper, UserHelper};
use app\models\service\Bot;

use \Parsedown;


class Question extends ActiveRecord
{

	public static function tableName()
	{
		return 'question';
	}

	public function rules()
	{
		return [
			[['question_id'], 'unique'],
			[['question_id', 'user_id', 'type_id', 'faculty_id', 'discipline_id', 'teacher_id',
				'views', 'followers', 'answer_count', 'comment_count'], 'integer'],
			[['is_helped', 'is_closed', 'is_hidden', 'is_deleted'], 'boolean'],

			[['question_text', 'question_title'], 'filter', 'filter' => 'strip_tags'],
			[['question_text', 'question_title'], 'filter', 'filter' => [TextHelper::class, 'remove_emoji']],
			[['question_text'], 'filter', 'filter' => 'trim'],
			[['question_title'], 'filter', 'filter' => [TextHelper::class, 'remove_multiple_whitespaces']],
			[['tag_list'], 'each', 'rule' => ['filter', 'filter' => 'strip_tags']],
			[['tag_list'], 'each', 'rule' => ['filter', 'filter' => [TextHelper::class, 'remove_multiple_whitespaces']]],
			[['tag_list'], 'each', 'rule' => ['filter', 'filter' => [TextHelper::class, 'remove_emoji']]],
			[['tag_list'], 'checkListAttribute', 'params' => ['max' => 5]],

			[['question_text'], 'string', 'min' => 30, 'max' => 8192],
			[['question_title'], 'string', 'min' => 15, 'max' => 100],
			[['discipline_name'], 'string', 'min' => 2, 'max' => 256],
			[['tag_list'], 'each', 'rule' => ['string', 'min' => 2, 'max' => 50], 'skipOnEmpty' => true],

			[['question_datetime', 'edited_datetime', 'checked_datetime'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
			[['end_datetime'], 'datetime', 'format' => 'php:d.m.Y H:i'],
			[['type_id'], 'in', 'range' => array_keys($this->typeModel->list)],
			[['faculty_id'], 'in', 'range' => array_keys($this->facultyModel->list)],

			[['question_text'], 'checkEmptyField'],
			[['question_title', 'question_text', 'type_id', /*'faculty_id',*/ 'user_id' ], 'required' ],
			[['discipline_name' ], 'required', 'on' => [static::SCENARIO_CREATE, static::SCENARIO_EDIT] ],

			[['upload_files'], 'file', 'extensions' => static::getExtensionList(), 'maxSize' => 2 * 1024 * 1024, 'maxFiles' => 10],
			[['upload_files'], 'each', 'rule' => [ 'safe' ]],
			[['old_files'], 'each', 'rule' => ['integer']],
		];
	}

	public static function primaryKey()
	{
		return [
			'question_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'question_id' => Yii::t('app', '№ вопроса'),
			'question_title' => Yii::t('app', 'Описание вопроса'),
			'question_text' => Yii::t('app', 'Текст вопроса'),
			'question_datetime' => Yii::t('app', 'Время вопроса'),
			'user_id' => Yii::t('app', 'Задавший вопрос'),
			'type_id' => Yii::t('app', 'Тип задания'),
			'tags' => Yii::t('app', 'Теги'),
			'tag_list' => Yii::t('app', 'Теги'),
			'faculty_id' => Yii::t('app', 'Факультет'),
			'discipline_id' => Yii::t('app', 'Предмет'),
			'discipline_name' => Yii::t('app', 'Предмет'),
			'teacher_id' => Yii::t('app', 'ФИО преподавателя'),
			'end_datetime' => Yii::t('app', 'Срок сдачи'),
			'upload_files' => Yii::t('app', 'Прикрепляемые файлы'),
		];
	}

	public const EVENT_BEFORE_ANSWER = 'beforeAnswer';
	public const EVENT_AFTER_CHANGE = 'afterChange';
	public const EVENT_RATE_SUM = 'rateSum';

	public const SCENARIO_CREATE = 'create';
	public const SCENARIO_EDIT = 'edit';

	public $tag_list;
	public $discipline_name;
	public $upload_files;
	public $old_files;

	private $_has_duplicate = null;
	private $_is_followed = null;
	private $_file_count = null;
	private $_report_count = null;
	private $_is_reported = null;

	public function init()
	{
		$this->on(static::EVENT_INIT, [$this, 'checkInit']);

		$this->on(static::EVENT_BEFORE_VALIDATE, [$this, 'checkJsonToArray']);
		$this->on(static::EVENT_BEFORE_VALIDATE, [$this, 'checkBeforeValidate']);

		$this->on(static::EVENT_BEFORE_INSERT, [$this, 'checkArrayToJson']);
		$this->on(static::EVENT_BEFORE_INSERT, [$this, 'checkBeforeInsert']);

		$this->on(static::EVENT_BEFORE_UPDATE, [$this, 'checkArrayToJson']);
		$this->on(static::EVENT_BEFORE_UPDATE, [$this, 'checkBeforeUpdate']);

		// $this->on(static::EVENT_AFTER_FIND, [$this, 'checkJsonToArray']);

		$this->on(static::EVENT_AFTER_INSERT, [$this, 'checkJsonToArray']);
		$this->on(static::EVENT_AFTER_INSERT, [$this, 'checkAfterInsert']);
		$this->on(static::EVENT_AFTER_INSERT, [$this, 'sendMessage']);

		$this->on(static::EVENT_AFTER_UPDATE, [$this, 'checkJsonToArray']);
		$this->on(static::EVENT_AFTER_UPDATE, [$this, 'checkAfterUpdate']);

		$this->on(static::EVENT_BEFORE_ANSWER, [$this, 'checkBeforeAnswer']);
		$this->on(static::EVENT_AFTER_CHANGE, [$this, 'checkAfterChange']);
		$this->on(static::EVENT_RATE_SUM, [$this, 'checkRateSum']);

		parent::init();
	}

	public function scenarios()
	{
		return array_merge(parent::scenarios(), [
			self::SCENARIO_CREATE => [
				'question_title', 'question_text', 'type_id', '!faculty_id',
				'discipline_name', 'tag_list', 'teacher_id', 'end_datetime', '!user_id',
				'upload_files',
				'!question_datetime'
			],
			self::SCENARIO_EDIT => [
				'question_title', 'question_text', 'type_id', '!faculty_id',
				'discipline_name', 'tag_list', 'teacher_id', 'end_datetime', '!user_id', '!quesetion_id',
				'upload_files', 'old_files',
				'!question_datetime', '!edited_datetime'
			],
		]);
	}

	protected function checkInit($event)
	{
		if ($this->scenario == static::SCENARIO_CREATE) {
			$this->end_datetime = date("d.m.Y 18:00", strtotime("+1 day"));
		}
	}

	protected function checkBeforeValidate($event)
	{
		if ($this->scenario == static::SCENARIO_CREATE) {
			$this->user_id = Yii::$app->user->identity->id;
		}
		if ($this->end_datetime) {
			$this->end_datetime = date("d.m.Y H:i", strtotime($this->end_datetime));
		}
	}

	protected function checkBeforeInsert($event)
	{
		if ($this->scenario == static::SCENARIO_CREATE) {
			$this->faculty_id = Yii::$app->user->identity->group->faculty_id;
			if ($message = $this->canCreate()) {
				$this->addError('question_text', $message);
				Yii::$app->session->setFlash('error', $message);
				return null;
			}
			$this->question_datetime = date("Y-m-d H:i:s");
			if ($this->end_datetime) {
				$this->end_datetime = date("Y-m-d H:i:s", strtotime($this->end_datetime));
			}
			$this->checkDiscipline();
		}
	}

	protected function checkBeforeUpdate($event)
	{
		if ($this->end_datetime) {
			$this->end_datetime = date("Y-m-d H:i:s", strtotime($this->end_datetime));
		}
		if ($this->scenario == static::SCENARIO_EDIT) {
			if ($message = $this->canEdit()) {
				$this->addError('question_text', $message);
				Yii::$app->session->setFlash('error', $message);
				return null;
			}
			$this->edited_datetime = date("Y-m-d H:i:s");
			$this->checkDiscipline();
			$this->createHistoryRecord();
		}
	}

	protected function checkAfterInsert($event)
	{
		$this->addAuthorFollow();
		$this->updateFollowers();
		$this->saveFiles();
		$this->saveDiscipline();
		$this->saveTags();
		CronNotification::addQuestion($this);
		$this->trigger(static::EVENT_AFTER_CHANGE);
	}

	protected function checkAfterUpdate($event)
	{
		if ($this->scenario == static::SCENARIO_EDIT) {
			$this->saveFiles();
			$this->saveDiscipline();
			$this->saveTags();
			CronNotification::addQuestionEdit($this);
		}
		if ($list = $this->reportsSent) {
			foreach ($list as $record) {
				$record->setEdited();
			}
		}
	}

	protected function checkAfterChange($event)
	{
		$this->author->data->badgeData->updateQuestionCount();
		if ($this->is_hidden or $this->is_deleted) {
			CronNotification::removeQuestionAfterChange($this);
		}
		$this->trigger(static::EVENT_RATE_SUM);
	}

	protected function checkRateSum($event)
	{
		$this->author->data->updateRateSum();
	}

	public function checkEmptyField($attribute)
	{
		if (HtmlHelper::isEmptyMarkdown($this->{$attribute})) {
			$this->addError($attribute, Yii::t('app', 'Текст не должен быть пустым.'));
			return false;
		}
		return true;
	}

	public function checkListAttribute($attribute, $params)
	{
		if (empty($params['max'])) {
			$params['max'] = 1;
		}
		ModelHelper::attributeListClear($this, $attribute, $params['max']);
	}

	protected function checkJsonToArray($event)
	{
		if (!$this->isNewRecord) {
			ModelHelper::attributeJsonToArray($this, 'tag_list');
		}
	}

	protected function checkArrayToJson($event)
	{
		ModelHelper::attributeArrayToJson($this, 'tag_list');
	}

	protected function checkBeforeAnswer($event)
	{
		$this->addViews();
		CronNotification::removeQuestion($this);
	}

	protected function checkDiscipline()
	{
		$discipline = $this->disciplineModel->checkRecord($this->discipline_name);
		$this->discipline_id = $discipline->discipline_id;
	}

	protected function sendMessage($event)
	{
		$bot = new Bot;
		$bot->messageQuestion($this->id);
	}

	public function getAuthor()
	{
		return $this->hasOne(User::class, ['user_id' => 'user_id'])
			// ->joinWith('data')
			;
	}

	public function getDiscipline()
	{
		return $this->hasOne(Discipline::class, ['discipline_id' => 'discipline_id'])
			->onCondition([Discipline::tableName() . '.is_checked' => true]);
	}

	public function getDisciplineRecord()
	{
		return $this->hasOne(Discipline::class, ['discipline_id' => 'discipline_id']);
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

	public function getAnswers()
	{
		$link = $this->hasMany(Answer::class, ['question_id' => 'question_id'])
			->inverseOf('question')
			// ->joinWith('author')
			// ->joinWith('likes')
			// ->joinWith('reports')
			;
		if (!UserHelper::canSeeAll()) {
			$link = $link->onCondition([Answer::tableName().'.is_deleted' => false]);
		}
		return $link;
	}

	public function getAnswersHelped()
	{
		$link = $this->hasMany(Answer::class, ['question_id' => 'question_id'])
			->onCondition([Answer::tableName().'.is_helped' => true])
			->inverseOf('question')
			// ->joinWith('author')
			// ->joinWith('likes')
			// ->joinWith('reports')
			;
		if (!UserHelper::canSeeAll()) {
			$link = $link->onCondition([Answer::tableName().'.is_deleted' => false]);
		}
		return $link;
	}

	public function getLastAnswer()
	{
		$record = null;
		$list = $this->answer;
		if ($list) {
			usort($list, function($a, $b) {
				return $b->answer_datetime <=> $a->answer_datetime;
			});
			$record = $list[0];
		}
		return $record;
	}

	public function getQuestionComments()
	{
		$link = $this->hasMany(Comment::class, ['question_id' => 'question_id'])
			->inverseOf('question')
			->joinWith('author')
			->joinWith('likes')
			->joinWith('reports')
			->onCondition(['OR',
				['IS', Comment::tableName().'.answer_id', NULL],
				['=', Comment::tableName().'.answer_id', 0]
			]);
		if (!UserHelper::canSeeAll()) {
			$link = $link->onCondition([Comment::tableName().'.is_deleted' => false]);
		}
		return $link;
	}

	public function getComments()
	{
		$link = $this->hasMany(Comment::class, ['question_id' => 'question_id'])
			->inverseOf('question');
		if (!UserHelper::canSeeAll()) {
			$link = $link->onCondition([Comment::tableName().'.is_deleted' => false]);
		}
		return $link;
	}

	public function getAnswersSearch()
	{
		$link = $this->hasMany(Answer::class, ['question_id' => 'question_id']);
		if (!UserHelper::canSeeAll()) {
			$link = $link->onCondition([Answer::tableName().'.is_deleted' => false]);
		}
		return $link;
	}

	public function getCommentsSearch()
	{
		$link = $this->hasMany(Comment::class, ['question_id' => 'question_id']);
		if (!UserHelper::canSeeAll()) {
			$link = $link->onCondition([Comment::tableName().'.is_deleted' => false]);
		}
		return $link;
	}

	public function getLastAnswerSearch()
	{
		$link = $this->hasOne(Answer::class, ['question_id' => 'question_id']);
		if (!UserHelper::canSeeAll()) {
			$link = $link->onCondition([Answer::tableName().'.is_deleted' => false]);
		}
		$link->orderBy(['answer_datetime' => SORT_DESC]);
		return $link;
	}

	public function getAnswersAll()
	{
		return $this->hasMany(Answer::class, ['question_id' => 'question_id']);
	}

	public function getCommentsAll()
	{
		return $this->hasMany(Comment::class, ['question_id' => 'question_id']);
	}

	public function getFiles()
	{
		return $this->hasMany(File::class, ['question_id' => 'question_id'])
			->onCondition(['IS', File::tableName() . '.answer_id', NULL]);
	}

	public function getHistoryRecords()
	{
		return $this->hasMany(QuestionHistory::class, ['question_id' => 'question_id'])
			->orderBy(['history_question_id' => SORT_DESC]);
	}

	public function getLastHistoryRecord()
	{
		if ($this->isEdited()) {
			return $this->hasOne(QuestionHistory::class, ['question_id' => 'question_id'])
				->orderBy(['history_question_id' => SORT_DESC]);
		}
	}

	public function getFollow()
	{
		return $this->hasMany(FollowQuestion::class, ['question_id' => 'question_id']);
	}

	public function getReports()
	{
		return $this->hasMany(Report::class, ['question_id' => 'question_id']);
	}

	public function getReportsSent()
	{
		return $this->hasMany(Report::class, ['question_id' => 'question_id'])
			->onCondition(['report_status' => Report::STATUS_SENT]);
	}

	public function getDuplicateRequests()
	{
		return $this->hasMany(DuplicateQuestionRequest::class, ['question_id' => 'question_id']);
	}

	public function getDuplicateAccepted()
	{
		return $this->hasMany(DuplicateQuestionRequest::class, ['question_id' => 'question_id'])
			->onCondition(['request_status' => DuplicateQuestionRequest::STATUS_ACCEPTED]);
	}

	public function getTagRecords()
	{
		return $this->hasMany(Tag::class, ['tag_id' => 'tag_id'])
			->via('tagToQuestion');
	}

	public function getTagToQuestion()
	{
		return $this->hasMany(TagToQuestion::class, ['question_id' => 'question_id']);
	}

	public function getTypeModel(): QuestionType
	{
		return (new QuestionType);
	}

	public function getFacultyModel(): Faculty
	{
		return (new Faculty);
	}

	public function getDisciplineModel(): Discipline
	{
		return (new Discipline);
	}

	public function getTeacherModel(): Teacher
	{
		return (new Teacher);
	}

	public function getTagModel(): Tag
	{
		return (new Tag);
	}

	public static function getAllCount()
	{
		return self::find()
			->where(['is_deleted' => false])
			->count();
	}

	public static function getTodayCount()
	{
		return self::find()
			->where(['is_deleted' => false])
			->andWhere(['>=', 'question_datetime', date('Y-m-d 00:00:00', strtotime('-1 day'))])
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
			->andWhere(['>=', 'question_datetime', date('Y-m-d 00:00:00', strtotime('-1 day'))])
			->count();
	}

	public static function getRecordForAnswer(int $id)
	{
		return self::find()
			->from(['q' => self::tableName()])
			->joinWith('files')
			->joinWith('type')
			->joinWith('follow')
			->joinWith('faculty')
			->joinWith('author')
			->joinWith('reports')
			->joinWith('teacher')
			->joinWith('tagRecords')
			->joinWith('discipline')
			->joinWith('duplicateAccepted.duplicateQuestion.answers')
			->joinWith(['answers as answers' => function ($query) use($id) {
				return $query->onCondition(['answers.question_id' => $id])
					->joinWith('files');
			}])
			// ->joinWith('answers.files')
			->joinWith('answersAll')
			// ->joinWith('commentsAll')
			// ->joinWith('questionComments')
			->where(['q.question_id' => $id])
			->andWhere(['q.is_deleted' => false])
			->one();
	}

	public static function getRecordForFollow(int $id)
	{
		return self::find()
			->from(['q' => self::tableName()])
			->joinWith('follow.follower.data')
			->where(['q.question_id' => $id])
			->andWhere(['q.is_deleted' => false])
			->one();
	}

	public function getNewByDisciplineList()
	{
		$list = [];
		if ($this->discipline) {
			$list = self::find()
				->where(['discipline_id' => $this->discipline_id])
				->andWhere(['is_deleted' => false])
				->andWhere(['is_hidden' => false])
				->andWhere(['!=', 'question_id', $this->question_id])
				->orderBy(['question_datetime' => SORT_DESC])
				->limit(10)
				->all();
		}
		return $list;
	}

	public function getAllList(int $limit = 0, int $offset = 0)
	{
		$list = self::find()
			->orderBy(['question_datetime' => SORT_DESC])
			->indexBy('question_id');
		if ($limit > 0) {
			$list = $list->limit($limit);
		}
		if ($offset > 0) {
			$list = $list->offset($offset);
		}
		return $list->all();
	}

	public function getAllUserList(int $user_id, int $limit = 0, int $offset = 0)
	{
		$list = self::find()
			->where(['user_id' => $user_id])
			->andWhere(['is_deleted' => false])
			->orderBy(['question_datetime' => SORT_DESC])
			->indexBy('question_id');
		if ($limit > 0) {
			$list = $list->limit($limit);
		}
		if ($offset > 0) {
			$list = $list->offset($offset);
		}
		return $list->all();
	}

	public function getAnswerHelpedList()
	{
		$list = $this->answers;
		$list = array_filter($list, function($record) {
			return $record->is_helped;
		});
		usort($list, function($a, $b) {
			if ($a->like_count == $b->like_count) {
				return $a->datetime <=> $b->datetime;
			} else {
				return $b->like_count <=> $a->like_count;
			}
		});
		return $list;
	}

	public function getAnswerNotHelpedList()
	{
		$list = $this->answers;
		$list = array_filter($list, function($record) {
			return !$record->is_helped;
		});
		usort($list, function($a, $b) {
			if ($a->like_count == $b->like_count) {
				return $a->datetime <=> $b->datetime;
			} else {
				return $b->like_count <=> $a->like_count;
			}
		});
		return $list;
	}

	public function getAnswerBest()
	{
		$answer = $this->getAnswerHelpedList()[0] ?? null;
		if (!$answer) {
			$answer = $this->getAnswerNotHelpedList()[0] ?? null;
		}
		return $answer;
	}

	public function getFilesDocs()
	{
		return array_filter($this->files, function($file) {
			return !$file->isImg;
		});
	}

	public function getFilesImages()
	{
		return array_filter($this->files, function($file) {
			return $file->isImg;
		});
	}

	public function getReportsByType()
	{
		$list = [];
		foreach ($this->reports as $report) {
			$list[$report->report_type][] = $report;
		}
		return $list;
	}

	public function getId(): ?int
	{
		return $this->question_id;
	}

	public function getText(): ?string
	{
		$parse = new Parsedown;
		$text = $parse->text($this->question_text);
		return $text;
		// return Html::encode($text);
		// return Html::decode($this->question_text);
	}

	public function getDatetime(): ?string
	{
		return $this->question_datetime;
	}

	public function getShortText()
	{
		$text = strip_tags($this->text);
		return StringHelper::truncate($text, 175, asHtml: true);
	}

	public function getTimeElapsed()
	{
		return HtmlHelper::getTimeElapsed($this->question_datetime);
	}

	public function getTimeFull()
	{
		return HtmlHelper::getTimeElapsed($this->question_datetime, true);
	}

	public function getEndTimeFull()
	{
		return HtmlHelper::getTimeElapsed($this->end_datetime, true);
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

	public function isEdited()
	{
		return !empty($this->edited_datetime);
	}

	public function isChecked()
	{
		return !empty($this->checked_datetime);
	}

	public function getRecordLink(bool $scheme = false)
	{
		return Url::to(["/question/answer/{$this->id}"], $scheme);
	}

	public function getCommentsLink(bool $scheme = false)
	{
		return Url::to(["/question/answer/{$this->id}/comments"], $scheme);
	}

	public function getAnswersLink(bool $scheme = false)
	{
		return Url::to(["/question/answer/{$this->id}/allAnswers"], $scheme);
	}

	public function getEditLink(bool $scheme = false)
	{
		return Url::to(["/question/edit", 'id' => $this->question_id], $scheme);
	}

	public function getFollowersLink(bool $scheme = false)
	{
		return Url::to(["/question/followers", 'id' => $this->id, '#' => 'followers'], $scheme);
	}

	public function getHistoryLink(bool $scheme = false)
	{
		return Url::to(["/history/question", 'id' => $this->id], $scheme);
	}

	public function getLimitLink(bool $scheme = false)
	{
		return Url::to(["/moderator/limit-question", 'id' => $this->id], $scheme);
	}

	public static function getExtensionList(): array
	{
		return [
			'jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx',
			'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip'
		];
	}

	public function findHasDuplicate()
	{
		$this->_has_duplicate = (bool)$this->duplicateAccepted;
	}

	public function findIsFollowed()
	{
		$id = Yii::$app->user->identity->id;
		$list = ArrayHelper::index($this->follow, 'follower_id');
		$this->_is_followed = !empty($list[$id]);
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

	public function getUnanswered(): bool
	{
		if (is_null($this->_unanswered)) {
			$this->findUnanswered();
		}
		return ($this->answer_count == 0);
	}

	public function getIsAnswered(): bool
	{
		return ($this->answer_count > 0);
	}

	public function getHasDuplicate(): bool
	{
		if (is_null($this->_has_duplicate)) {
			$this->findHasDuplicate();
		}
		return $this->_has_duplicate;
	}

	public function getIsFollowed(): bool
	{
		if (is_null($this->_is_followed)) {
			$this->findIsFollowed();
		}
		return $this->_is_followed;
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

	public function getCommentCount(): int
	{
		// if (is_null($this->_comment_count)) {
		// 	$this->findCommentCount();
		// }
		return $this->comment_count;
	}

	public function getCommentAllCount(): int
	{
		// if (is_null($this->_comment_all_count)) {
		// 	$this->findCommentAllCount();
		// }
		return $this->comment_count;
	}

	public function getIsAuthor(): bool
	{
		return ($this->user_id == Yii::$app->user->identity->id);
	}

	public function getNextId()
	{
		$id = 1;
		$user = Yii::$app->user->identity;
		$model = self::find()
			->where(['>', 'question_id', $this->id]);
		if (!$user->isModerator()) {
			$model->andWhere(['is_deleted' => false]);
			$model->andWhere(['is_hidden' => false]);
		}
		$model = $model->orderBy(['question_id' => SORT_ASC])->one();
		if (!$model) {
			$model = self::find();
				if (!$user->isModerator()) {
					$model->andWhere(['is_deleted' => false]);
					$model->andWhere(['is_hidden' => false]);
				}
				$model = $model->orderBy(['question_id' => SORT_ASC])->one();
		}
		if ($model) {
			$id = $model->id;
		}
		return $id;
	}

	public function getPrevId()
	{
		$id = 1;
		$user = Yii::$app->user->identity;
		$model = self::find()
			->where(['<', 'question_id', $this->id]);
		if (!$user->isModerator()) {
			$model->andWhere(['is_deleted' => false]);
			$model->andWhere(['is_hidden' => false]);
		}
		$model = $model->orderBy(['question_id' => SORT_DESC])->one();
		if (!$model) {
			$model = self::find();
				if (!$user->isModerator()) {
					$model->andWhere(['is_deleted' => false]);
					$model->andWhere(['is_hidden' => false]);
				}
				$model = $model->orderBy(['question_id' => SORT_DESC])->one();
		}
		if ($model) {
			$id = $model->id;
		}
		return $id;
	}

	protected function addViews()
	{
		if (!$this->isNewRecord) {
			$this->views++;
			$this->save();
		}
	}

	public function addAuthorFollow()
	{
		FollowQuestion::follow($this->question_id, $this->user_id, true);
	}

	public function updateFollowers()
	{
		$this->followers = FollowQuestion::getFollowersCount($this->question_id);
		$this->save();
	}

	protected function saveFiles()
	{
		$this->upload_files = UploadedFile::getInstances($this, 'upload_files');
		if (is_array($this->upload_files)) {
			foreach ($this->upload_files as $file) {
				if ($file) {
					$file_model = new File;
					$file_model->saveFromQuestion($this, $file);
				}
			}
		}
	}

	protected function saveDiscipline()
	{
		$discipline = $this->disciplineModel->checkRecord($this->discipline_name);
		$discipline->updateFollowers();
		$discipline->updateQuestionCount();
	}

	protected function saveTags()
	{
		$tags = [];
		$tag_ids = [];
		if ($this->tag_list) {
			foreach ($this->tag_list as $tag) {
				$tags[] = $this->tagModel->checkRecordStudent($tag, $this->discipline_id);
				$tag_ids[] = $tag->tag_id;
			}
		}
		if ($this->tagRecords) {
			foreach ($this->tagRecords as $tag) {
				if (!in_array($tag->tag_id, $tag_ids)) {
					TagToQuestion::removeRecord($tag->tag_id, $this->question_id);
				}
			}
		}
		if ($tags) {
			foreach ($tags as $tag) {
				if (!in_array($tag->tag_id, $tag_ids)) {
					TagToQuestion::createRecord($tag->tag_id, $this->question_id);
				}
			}
		}
	}

	public function changeFileLinks()
	{
		if ($files = $this->files) {
			foreach ($files as $file) {
				$file->replaceBucketLink();
			}
		}
	}

	public function createHistoryRecord()
	{
		$result = false;
		if (!$this->isNewRecord) {
			$data = $this->getOldAttributes();
			if (is_null($this->old_files)) {
				$this->old_files = [];
			}
			$history = new QuestionHistory;
			$history->load($data, '');
			$result = $history->save();
			if ($this->files) {
				foreach ($this->files as $file) {
					$question = $history->question;
					$need_to_delete = false;
					if (!in_array($file->file_id, $this->old_files)) {
						$need_to_delete = true;
					}
					$file->createQuestionHistoryRecord($question, $need_to_delete);
				}
			}
		}
		return $result;
	}

	public function loadTest()
	{
		$this->question_title = "Решение РГР";
		$this->question_text = 'Как нужно решать эту РГР?';
		$this->type_id = 2;
		$this->tag_list = ['РГР'];
		$this->faculty_id = 3;
		$this->discipline_name = "Химия";
		$this->teacher_id = 93663;
		$this->end_datetime = date("d.m.Y 18:00", strtotime('+1 day'));
	}

	public function canCreate(): ?string
	{
		return $message = null;
		$user = Yii::$app->user->identity;
		if (!$user->isModerator()) {
			if (!$user->canQuestion()) {
				$message = $user->limit->getQuestionMessage();
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
				$message = Yii::t('app', 'Вы не являетесь автором вопроса!');
			} elseif ($this->is_closed) {
				$message = Yii::t('app', 'Для редактирования вопроса он должен быть открыт!');
			/*} elseif (date('YmdHis', strtotime($this->datetime)) < date('YmdHis', strtotime("+30 day"))) {
				$message = Yii::t('app', 'Время для редактирования вышло!');*/
			} elseif (!$user->canQuestion()) {
				$message = $user->limit->getQuestionMessage();
			}
		}
		return $message;
	}

	public function canDelete(): ?string
	{
		$message = null;
		if (!Yii::$app->user->identity->isModerator()) {
			if (!$this->isAuthor) {
				$message = Yii::t('app', 'Вы не можете выполнить данное действие!');
			} elseif ($this->answerAllCount or $this->commentAllCount) {
				$message = Yii::t('app', 'Удалить вопрос можно, если на него не было ответов или комментариев!');
			}
		}
		return $message;
	}

	public function canSee(): ?string
	{
		$message = null;
		if (!Yii::$app->user->identity->isModerator()) {
			if ($this->is_deleted) {
				$message = Yii::t('app', 'Вопрос удалён!');
			}
			if ($this->is_hidden and !$this->isAuthor) {
				$message = Yii::t('app', 'Вопрос времмено скрыт!');
			}
		}
		return $message;
	}

	public function setEditing()
	{
		$this->scenario = static::SCENARIO_EDIT;
		if ($this->end_datetime) {
			$this->end_datetime = date("d.m.Y H:i", strtotime($this->end_datetime));
		}
		if (!empty($this->discipline)) {
			$this->discipline_name = $this->discipline->name;
		}
		if (!empty($this->tagRecords)) {
			$this->tag_list = array_column($this->tagRecords, 'tag_name');
			ModelHelper::attributeJsonToArray($this, 'tag_list');
		}
	}

	public static function setClosed(int $id): array
	{
		$ok = false;
		$record = self::findOne($id);
		if (!$record) {
			$message = Yii::t('app', 'Вопрос не был найден');
		} elseif(!$record->isAuthor) {
			$message = Yii::t('app', 'Вы не являетесь автором вопроса');
		} elseif($record->is_deleted) {
			$message = Yii::t('app', 'Вопрос удалён');
		} elseif(!$record->isAnswered and !$record->hasDuplicate) {
			$message = Yii::t('app', 'Вы можете закрыть вопрос только если он отвечен или имеет дупликат с решением');
		} elseif($record->is_closed) {
			$message = Yii::t('app', 'Вопрос уже закрыт');
		} else {
			$record->is_closed = true;
			if ($record->save()) {
				$message = Yii::t('app', 'Вопрос закрыт');
				$ok = true;
			} else {
				$message = Yii::t('app', 'Вопрос не закрыт');
			}
		}
		return [
			'status' => $ok,
			'message' => $message
		];
	}

	public static function setOpened(int $id): array
	{
		$ok = false;
		$record = self::findOne($id);
		if (!$record) {
			$message = Yii::t('app', 'Вопрос не был найден');
		} elseif($record->is_deleted) {
			$message = Yii::t('app', 'Вопрос удалён');
		} elseif(!$record->isAuthor) {
			$message = Yii::t('app', 'Вы не являетесь автором вопроса');
		} elseif(!$record->is_closed) {
			$message = Yii::t('app', 'Вопрос уже открыт');
		} else {
			$record->is_closed = false;
			if ($record->save()) {
				$message = Yii::t('app', 'Вопрос открыт');
				$ok = true;
			} else {
				$message = Yii::t('app', 'Вопрос не открыт');
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
			$message = Yii::t('app', 'Вопрос не был найден');
		} elseif($record->is_deleted) {
			$message = Yii::t('app', 'Вопрос удалён');
		} elseif(!Yii::$app->user->identity->isModerator()) {
			$message = Yii::t('app', 'Вы не являетесь модератором');
		} elseif($record->is_deleted) {
			$message = Yii::t('app', 'Вопрос удалён');
		} elseif($record->is_hidden) {
			$message = Yii::t('app', 'Вопрос уже скрыт');
		} else {
			$record->is_hidden = true;
			if ($record->save()) {
				$message = Yii::t('app', 'Вопрос скрыт');
				$record->changeFileLinks();
				$record->trigger(static::EVENT_AFTER_CHANGE);
				$ok = true;
			} else {
				$message = Yii::t('app', 'Вопрос не скрыт');
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
			$message = Yii::t('app', 'Вопрос не был найден');
		} elseif($record->is_deleted) {
			$message = Yii::t('app', 'Вопрос удалён');
		} elseif(!Yii::$app->user->identity->isModerator()) {
			$message = Yii::t('app', 'Вы не являетесь модератором');
		} elseif($record->is_deleted) {
			$message = Yii::t('app', 'Вопрос удалён');
		} elseif(!$record->is_hidden) {
			$message = Yii::t('app', 'Вопрос уже показан');
		} else {
			$record->is_hidden = false;
			if ($record->save()) {
				$message = Yii::t('app', 'Вопрос показан');
				$record->trigger(static::EVENT_AFTER_CHANGE);
				$ok = true;
			} else {
				$message = Yii::t('app', 'Вопрос не показан');
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
			$message = Yii::t('app', 'Вопрос не был найден');
		} elseif(!is_null($record->canDelete())) {
			$message = Yii::t('app', 'Вы не можете выполнять данное действие');
		} elseif($record->is_deleted) {
			$message = Yii::t('app', 'Вопрос уже удалён');
		} else {
			$record->is_deleted = true;
			if ($record->save()) {
				$message = Yii::t('app', 'Вопрос удалён');
				$record->changeFileLinks();
				$record->trigger(static::EVENT_AFTER_CHANGE);
				$ok = true;
			} else {
				$message = Yii::t('app', 'Вопрос не удалён');
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
			$message = Yii::t('app', 'Вопрос не был найден');
		} elseif(!is_null($record->canDelete())) {
			$message = Yii::t('app', 'Вы не можете выполнять данное действие');
		} elseif(!$record->is_deleted) {
			$message = Yii::t('app', 'Вопрос не удалён');
		} else {
			$record->is_deleted = false;
			if ($record->save()) {
				$message = Yii::t('app', 'Вопрос восстановлен');
				$record->changeFileLinks();
				$record->trigger(static::EVENT_AFTER_CHANGE);
				$ok = true;
			} else {
				$message = Yii::t('app', 'Вопрос не восстановлен');
			}
		}
		return [
			'status' => $ok,
			'message' => $message
		];
	}

}
