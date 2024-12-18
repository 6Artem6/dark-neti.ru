<?php

namespace app\models\search;

use Yii;
use yii\data\{ArrayDataProvider, Pagination, Sort};

use app\models\question\{Question, Answer, Comment};
use app\models\edu\{Discipline, Teacher};
use app\models\user\User;
use app\models\follow\FollowQuestion;
use app\models\notification\Notification;
use app\models\data\RecordList;
use app\models\helpers\{ModelHelper, TextHelper, HtmlHelper};


class QuestionSearch extends Question
{

	public $text;
	public $type_id;
	public $tag_records;
	public $faculty_id;
	public $teacher;
	public $discipline_name;
	public $date_from;
	public $date_to;
	public $new;
	public $follow;

	public $sort;
	public $answer_count;
	public $follower_count;
	public $is_answered;
	public $is_closed;
	public $record_type;

	private $_new_list;
	private $_discipline_list;
	private $_teacher_list;
	private $_question_tag_list;

	private $_pages;
	private $_search_text;
	private $_question_query;
	private $_answer_query;
	private $_comment_question_query;
	private $_comment_answer_query;

	private $_exact_strings = [];
	private $_username;
	private $_user;
	private $_record_id;

	public function rules()
	{
		return [
			[['text', 'type_id', 'tag_records', 'faculty_id', 'teacher', 'discipline_name', 'date_from', 'date_to',
				'sort', /*'answer_count', 'follower_count',*/ 'is_answered', 'is_closed', 'record_type', 'new', 'follow'], 'safe'],
			[['tag_records'], 'each', 'rule' => ['string', 'max' => 50]],
			[['type_id', 'faculty_id', /*'answer_count', 'follower_count',*/ 'is_answered', 'is_closed'], 'integer'],
			[['is_answered', 'is_closed'], 'in', 'range' => array_keys(static::getYesNoList())],
			[['text', 'discipline_name', 'teacher', 'date_from', 'date_to', 'sort'], 'string'],
			[['text', 'discipline_name'], 'trim'],
			[['new', 'follow'], 'boolean'],
			[['sort'], 'in', 'range' => array_keys(static::getSortList())],
			[['record_type'], 'in', 'range' => array_keys(static::getRecordTypeList())],

			[['text', 'discipline_name', 'teacher'], 'filter', 'filter' => 'strip_tags'],
			[['tag_records'], 'each', 'rule' => ['filter', 'filter' => 'strip_tags']],
		];
	}

	public function attributeLabels()
	{
		return [
			'text' => Yii::t('app', 'Текст'),
			'type_id' => Yii::t('app', 'Тип задания'),
			'tag_records' => Yii::t('app', 'Теги'),
			'date_from' => Yii::t('app', 'Дата: от'),
			'date_to' => Yii::t('app', 'Дата: до'),
			'faculty_id'  => Yii::t('app','Факультет'),
			'teacher'  => Yii::t('app','ФИО преподавателя'),
			'discipline_name'  => Yii::t('app','Предмет'),
			'sort'  => Yii::t('app','Сортировка'),
			'answer_count'  => Yii::t('app', 'Число ответов'),
			'follower_count'    => Yii::t('app', 'Число подписчиков'),
			'is_answered'   => Yii::t('app', 'Вопрос имеет ответ'),
			'is_closed' => Yii::t('app', 'Вопрос решён'),
			'record_type'   => Yii::t('app', 'Тип записи'),
		];
	}

	private const NEWEST_QUESTIONS = 'question_datetime';
	private const NEWEST_ANSWERS = 'answer_datetime';
	private const VIEWS = 'views';
	private const HELPFUL = 'is_helped';
	private const ANSWERS = 'answer_count';
	private const UNANSWERED = 'unanswered';

	public function search($params)
	{
		$this->load($params);
		if (!$this->validate()) {
			$this->clearFields();
		}
		$this->_new_list = Notification::getUnseenByQuestion();
		return $this->getList();
	}

	public function getPages() {
		return $this->_pages;
	}

	public function getNew_list() {
		return $this->_new_list;
	}

	public function getDiscipline_list() {
		return $this->_discipline_list;
	}

	public function getTeacher_list() {
		return $this->_teacher_list;
	}

	public function getQuestion_tag_list() {
		return $this->_question_tag_list;
	}

	public static function getRecordTypeList() {
		return [
			ModelHelper::TYPE_ALL => Yii::t('app', 'Всё'),
			ModelHelper::TYPE_QUESTION => Yii::t('app', 'Вопросы'),
			ModelHelper::TYPE_ANSWER => Yii::t('app', 'Ответы'),
			ModelHelper::TYPE_COMMENT => Yii::t('app', 'Комментарии'),
		];
	}

	public static function getSortList() {
		return [
			'-' . static::NEWEST_QUESTIONS => Yii::t('app', 'Сперва новые вопросы'),
			'-' . static::NEWEST_ANSWERS => Yii::t('app', 'Сперва новые ответы'),
			'-' . static::VIEWS => Yii::t('app', 'Сперва больше просмотров'),
			'-' . static::HELPFUL => Yii::t('app', 'Сперва самые полезные'),
			static::ANSWERS => Yii::t('app', 'Сперва больше ответов'),
			'-' . static::UNANSWERED => Yii::t('app', 'Сперва без ответов'),
		];
	}

	public static function getYesNoList() {
		return [
			null => Yii::t('app', 'Выберите тип'),
			1 => Yii::t('app', 'Да'),
			-1 => Yii::t('app', 'Нет'),
		];
	}

	public function getList()
	{
		$this->prepareQuery();

		$counts = [];
		if ($this->hasQuestions()) {
			$count_query = clone $this->_question_query;
			$counts['questions'] = $count_query->count();
		}
		if ($this->hasAnswers()) {
			$count_query = clone $this->_answer_query;
			$counts['answers'] = $count_query->count();
		}
		if ($this->hasComments()) {
			$count_query = clone $this->_comment_question_query;
			$counts['comment_questions'] = $count_query->count();
			$count_query = clone $this->_comment_answer_query;
			$counts['comment_answers'] = $count_query->count();
		}
		$totalCount = 0;
		foreach ($counts as $count) {
		    $totalCount += $count;
		}

		$page_size = 15;
		$this->_pages = new Pagination([
			'totalCount' => $totalCount,
			'defaultPageSize' => $page_size,
		]);
		$page_offset = $this->_pages->offset;
		$page_limit = $this->_pages->limit;

		if ($totalCount) {
			$limit = 10;
			$discipline_query = clone $this->_question_query;
			$discipline_ids = $discipline_query->select('question.discipline_id')->distinct()->column();
			$this->_discipline_list = RecordList::getDisciplineQuestionCounts($discipline_ids, $limit);

			$teacher_query = clone $this->_question_query;
			$teacher_ids = $discipline_query->select('question.teacher_id')->distinct()->column();
			$this->_teacher_list = RecordList::getTeacherQuestionCounts($teacher_ids, $limit);

			$tag_query = clone $this->_question_query;
			$tag_ids = $tag_query->select('tags.tag_id')->distinct()->column();
			$this->_question_tag_list = RecordList::getTagQuestionCounts($tag_ids, $limit);
		}

		$models = [];
		$count = 0;
		$offset = $page_offset;
		$record_count = 0;
		$record_limit = $page_limit;
		if ($this->hasQuestions()) {
			$count += $counts['questions'];
			if ($page_offset < $count) {
				$ids_query = clone $this->_question_query;
				$ids = $ids_query->select('question.question_id')
					->offset($page_offset)
					->limit($record_limit)
					->asArray()
					->column();
				$question_models = $this->_question_query
					->distinct()
					->joinWith('files as file')
					// ->joinWith('tagRecords as tags')
					->joinWith('discipline')
					->joinWith('type')
					->joinWith('follow')
					->joinWith('faculty')
					->joinWith('author')
					->joinWith('reports')
					// ->joinWith(['answers' => function ($query) {
					// 	return $query->distinct();
					// }])
					->andWhere(['IN', 'question.question_id', $ids])
					->all();
				$models = array_merge($models, $question_models);
				$record_limit -= count($models);
			}
			$offset -= $counts['questions'];
		}

		if ($this->hasAnswers() and $record_limit) {
			$count += $counts['answers'];
			if ($page_offset < $count) {
				$ids_query = clone $this->_answer_query;
				$ids = $ids_query->select('answer.answer_id')
					->offset($page_offset)
					->limit($record_limit)
					->asArray()
					->column();
				$answer_models = $this->_answer_query
					->distinct()
					->joinWith('question')
					->joinWith('files as file')
					->joinWith('author')
					->joinWith('reports')
					->joinWith('comments')
					->andWhere(['IN', 'answer.answer_id', $ids])
					->all();
				$models = array_merge($models, $answer_models);
				$record_limit -= count($models);
			}
			$offset -= $counts['answers'];
		}
		if ($this->hasComments() and $record_limit) {
			$count += $counts['comment_questions'];
			if ($page_offset < $count) {
				$ids_query = clone $this->_comment_question_query;
				$ids = $ids_query->select('comment.comment_id')
					->offset($page_offset)
					->limit($record_limit)
					->asArray()
					->column();
				$comment_question_models = $this->_comment_question_query
					->distinct()
					->innerJoinWith('question as question')
					->joinWith('author')
					->joinWith('reports')
					->andWhere(['IN', 'comment.comment_id', $ids])
					->all();
				$models = array_merge($models, $comment_question_models);
				$record_limit -= count($models);
			}
			$offset -= $counts['comment_questions'];
		}
		if ($this->hasComments() and $record_limit) {
			$count += $counts['comment_answers'];
			if ($page_offset < $count) {
				$ids_query = clone $this->_comment_answer_query;
				$ids = $ids_query->select('comment.comment_id')
					->offset($page_offset)
					->limit($record_limit)
					->asArray()
					->column();
				$comment_answer_models = $this->_comment_answer_query
					->distinct()
					->innerJoinWith('answer as answer')
					->joinWith('author')
					->joinWith('reports')
					->andWhere(['IN', 'comment.comment_id', $ids])
					->all();
				$models = array_merge($models, $comment_answer_models);
				$record_limit -= count($models);
			}
			$offset -= $counts['comment_answers'];
		}
		return $models;
	}

	public function prepareQuery()
	{
		$user = Yii::$app->user->identity;

		$this->filterFields();
		$this->extractExtraParams();
		$this->_search_text = TextHelper::remove_non_alphanumeric($this->_search_text);

		$this->_question_query = Question::find()
			->from(['question' => Question::tableName()])
			->joinWith('files as file')
			->joinWith('tagRecords as tags', false)
			// ->joinWith('answersSearch', false)
			// ->joinWith('answersSearch.files as answer_file', false)
			->andWhere(['question.is_deleted' => false])
			;

		if (!$user->isModerator()) {
			$this->_question_query->andWhere(['question.is_hidden' => false]);
		}

		if ($this->_exact_strings) {
			foreach ($this->_exact_strings as $string) {
				$string = trim($string, '"');
				$this->_question_query->andWhere(['OR',
					['LIKE', 'question.question_title', $string],
					['LIKE', 'question.question_text', $string],
					// ['LIKE', 'answer.answer_text', $string],
					['LIKE', 'file.file_text', $string],
					['LIKE', 'file.file_user_name', $string],
					// ['LIKE', 'answer_file.file_text', $string],
					// ['LIKE', 'answer_file.file_user_name', $string],
				]);
			}
		}
		if ($this->_search_text) {
			$strings = explode(' ', $this->_search_text);
			foreach ($strings as $string) {
				$string = TextHelper::remove_word_end($string);
				$this->_question_query->andWhere(['OR',
					['LIKE', 'question.question_title', $string],
					['LIKE', 'question.question_text', $string],
					// ['LIKE', 'answer.answer_text', $string],
					['LIKE', 'file.file_text', $string],
					['LIKE', 'file.file_user_name', $string],
					// ['LIKE', 'answer_file.file_text', $string],
					// ['LIKE', 'answer_file.file_user_name', $string],
				]);
			}
		}
		if ($this->type_id) {
			$this->_question_query->andWhere(['=', 'question.type_id', $this->type_id]);
		}
		if ($this->faculty_id) {
			$this->_question_query->andWhere(['=', 'question.faculty_id', $this->faculty_id]);
		}
		if ($this->teacher) {
			$teacher = Teacher::getRecordByName($this->teacher);
			if ($teacher) {
				$this->_question_query = $this->_question_query
					->joinWith('teacher', false);
				$this->_question_query->andWhere(['teacher.teacher_id' => $teacher->id]);
			}
		}
		if ($this->discipline_name) {
			$discipline = Discipline::findByName($this->discipline_name);
			if ($discipline) {
				$this->_question_query->andWhere(['question.discipline_id' => $discipline->id]);
			}
		}
		if ($this->follower_count) {
			$this->_question_query->andWhere(['>=', 'question.followers', $this->follower_count]);
		}
		if(!is_null($this->answer_count)) {
			if (!$this->answer_count) {
				$this->_question_query->andWhere(['=', 'question.answer_count', 0]);
			} else {
				$this->_question_query->andWhere(['>=', 'question.answer_count', $this->answer_count]);
			}
		}
		if ($this->is_answered == 1) {
			$this->_question_query->andWhere(['=', 'question.answer_count', 0]);
		} elseif ($this->is_answered == -1) {
			$this->_question_query->andWhere(['>', 'question.answer_count', 0]);
		}
		if ($this->is_closed == 1) {
			$this->_question_query->andWhere(['question.is_closed' => true]);
		} elseif ($this->is_closed == -1) {
			$this->_question_query->andWhere(['question.is_closed' => false]);
		}
		if ($this->tag_records) {
			foreach ($this->tag_records as $tag) {
				$this->_question_query->andWhere(['LIKE', 'tags.tag_name', $tag]);
			}
		}
		if ($this->new) {
			$this->_question_query->andWhere(['IN', 'question.question_id', array_keys($this->_new_list)]);
		}
		if ($this->follow) {
			$this->_question_query->andWhere(['IN', 'question.question_id', FollowQuestion::getUserQuestionIds()]);
			if(is_null($this->_user) or
				(!is_null($this->_user) and ($this->_user != $user->id))) {
				$this->_question_query->andWhere(['!=', 'question.user_id', $user->id]);
			}
		}
		if ($this->hasQuestions()) {
			if(!is_null($this->_user)) {
				$this->_question_query->andWhere(['=', 'question.user_id', $this->_user]);
			}
			if(!is_null($this->_record_id)) {
				$this->_question_query->andWhere(['=', 'question.question_id', $this->_record_id]);
			}
			if ($this->date_from) {
				$this->_question_query->andWhere(['>=', 'question_datetime', date("Y-m-d 00:00:00", strtotime($this->date_from))]);
			}
			if ($this->date_to) {
				$this->_question_query->andWhere(['<=', 'question_datetime', date("Y-m-d 23:59:59", strtotime($this->date_to))]);
			}
		}


		$field = $this->getSortField();
		if ($field == static::NEWEST_QUESTIONS) {
			$this->_question_query->orderBy(['question_datetime' => SORT_DESC]);
		} elseif ($field == static::NEWEST_ANSWERS) {
			$this->_question_query->orderBy([
				// 'lastAnswerSearch.answer_datetime' => SORT_DESC,
				'question_datetime' => SORT_DESC
			]);
		} elseif ($field == static::VIEWS) {
			$this->_question_query->orderBy([
				'question.views' => SORT_DESC,
				'is_helped' => SORT_DESC,
				'answer_count' => SORT_DESC,
			]);
		} elseif ($field == static::ANSWERS) {
			$this->_question_query->orderBy([
				'answer_count' => SORT_DESC,
			]);
		} elseif ($field == static::UNANSWERED) {
			$this->_question_query->orderBy([
				'answer_count' => SORT_ASC,
			]);
		}

		if ($this->hasAnswers()) {
			$this->_answer_query = Answer::find()
				->from(['answer' => Answer::tableName()])
				->andWhere(['answer.is_deleted' => false]);
			if ($this->_search_text or $this->_exact_strings) {
				$this->_answer_query = $this->_answer_query
					->distinct()
					->joinWith('files as file', false);
			}
			if ($this->_exact_strings) {
				foreach ($this->_exact_strings as $string) {
					$string = trim($string, '"');
					$this->_answer_query->andWhere(['OR',
						['LIKE', 'answer.answer_text', $string],
						['LIKE', 'file.file_text', $string],
						['LIKE', 'file.file_user_name', $string],
					]);
				}
			}
			if ($this->_search_text) {
				$strings = explode(' ', $this->_search_text);
				foreach ($strings as $string) {
					$string = TextHelper::remove_word_end($string);
					$this->_answer_query->andWhere(['OR',
						['LIKE', 'answer.answer_text', $string],
						['LIKE', 'file.file_text', $string],
						['LIKE', 'file.file_user_name', $string],
					]);
				}
			}
			if (!$user->isModerator()) {
				$this->_answer_query->andWhere(['answer.is_hidden' => false]);
			}
			if(!is_null($this->_user)) {
				$this->_answer_query->andWhere(['=', 'answer.user_id', $this->_user]);
			}
			if(!is_null($this->_record_id)) {
				$this->_answer_query->andWhere(['=', 'answer.question_id', $this->_record_id]);
			}
			if ($this->date_from) {
				$this->_answer_query->andWhere(['>=', 'answer_datetime', date("Y-m-d 00:00:00", strtotime($this->date_from))]);
			}
			if ($this->date_to) {
				$this->_answer_query->andWhere(['<=', 'answer_datetime', date("Y-m-d 23:59:59", strtotime($this->date_to))]);
			}
		}

		if ($this->hasComments()) {
			$this->_comment_question_query = Comment::find()
				->from(['comment' => Comment::tableName()])
				->andWhere(['comment.is_deleted' => false])
				->andWhere(['IS', 'comment.answer_id', NULL]);;

			$this->_comment_answer_query = Comment::find()
				->from(['comment' => Comment::tableName()])
				->andWhere(['comment.is_deleted' => false])
				->andWhere(['IS NOT', 'comment.answer_id', NULL]);
			if ($this->_exact_strings) {
				foreach ($this->_exact_strings as $string) {
					$string = trim($string, '"');
					$this->_comment_question_query->andWhere(['LIKE', 'comment.comment_text', $string]);
					$this->_comment_answer_query->andWhere(['LIKE', 'comment.comment_text', $string]);
				}
			}
			if ($this->_search_text) {
				$strings = explode(' ', $this->_search_text);
				foreach ($strings as $string) {
					$string = TextHelper::remove_word_end($string);
					$this->_comment_question_query->andWhere(['LIKE', 'comment.comment_text', $string]);
					$this->_comment_answer_query->andWhere(['LIKE', 'comment.comment_text', $string]);
				}
			}

			if(!is_null($this->_user)) {
				$this->_comment_question_query->andWhere(['=', 'comment.user_id', $this->_user]);
				$this->_comment_answer_query->andWhere(['=', 'comment.user_id', $this->_user]);
			}
			if(!is_null($this->_record_id)) {
				$this->_comment_question_query->andWhere(['=', 'comment.question_id', $this->_record_id]);
				$this->_comment_answer_query->andWhere(['=', 'comment.question_id', $this->_record_id]);
			}
			if ($this->date_from) {
				$this->_comment_question_query->andWhere(['>=', 'comment_datetime', date("Y-m-d 00:00:00", strtotime($this->date_from))]);
				$this->_comment_answer_query->andWhere(['>=', 'comment_datetime', date("Y-m-d 00:00:00", strtotime($this->date_from))]);
			}
			if ($this->date_to) {
				$this->_comment_question_query->andWhere(['<=', 'comment_datetime', date("Y-m-d 23:59:59", strtotime($this->date_to))]);
				$this->_comment_answer_query->andWhere(['<=', 'comment_datetime', date("Y-m-d 23:59:59", strtotime($this->date_to))]);
			}
		}

	}

	public function extractExtraParams()
	{
		if ($this->text) {
			$strings = $this->text;
			do {
				$exact_string = TextHelper::get_string_between($strings, '"', '"');
				if ($exact_string) {
					$exact_string = '"' . $exact_string . '"';
					$this->_exact_strings[] = $exact_string;
					$strings = str_replace($exact_string, '', $strings);
				}
			} while ($exact_string);
			$strings = explode(' ', $strings);
			foreach ($strings as $key => $string) {
				if ($user = User::findByAtUsername($string)) {
					$this->_username = $user->username;
					$this->_user = $user->id;
					unset($strings[$key]);
				} elseif(!is_null($user_id = $this->getParam($string, 'user'))) {
					$this->_user = $user_id;
					unset($strings[$key]);
				} elseif(!is_null($record_id = $this->getParam($string, 'record-id'))) {
					$this->_record_id = $record_id;
					unset($strings[$key]);
				}
			}
			$this->_search_text = implode(' ', $strings);
		}
	}

	public function filterFields()
	{
		$this->text = TextHelper::remove_multiple_whitespaces($this->text);
		if (!$this->record_type) {
			$this->record_type = ModelHelper::TYPE_QUESTION;
		}
		if ($this->date_from and ($this->date_from != date('d.m.Y', strtotime($this->date_from)))) {
			$this->date_from = '';
		}
		if ($this->date_to and ($this->date_to != date('d.m.Y', strtotime($this->date_to)))) {
			$this->date_to = '';
		}
		if (is_array($this->tag_records)) {
			foreach ($this->tag_records as &$tag) {
				$tag = str_replace(['\'', '"', '[', ']', '{', '}', '(', ')', ':'], '', (string)$tag);
			}
		}
		// if (is_array($this->teachers)) {
		// 	foreach ($this->teachers as &$teacher) {
		// 		$teacher = str_replace(['\'', '"', '[', ']', '{', '}', '(', ')', ':'], '', (string)$teacher);
		// 	}
		// }
		if (($this->answer_count === '') or ($this->answer_count < 0)) {
			$this->answer_count = null;
		}
		if ($this->answer_count) {
			$this->answer_count = (int)$this->answer_count;
		}
		if (($this->follower_count === '') or ($this->follower_count < 0)) {
			$this->follower_count = null;
		}
		if ($this->follower_count) {
			$this->follower_count = (int)$this->follower_count;
		}
	}

	public function clearFields()
	{
		if ($this->errors) {
			foreach ($this->errors as $attribute => $value) {
				$this->{$attribute} = null;
				$this->clearErrors($attribute);
			}
		}
	}

	public function isEmpty()
	{
		return (
			!$this->text and !$this->type_id and !$this->tag_records and
			!$this->faculty_id and !$this->teacher and !$this->discipline_name and
			!$this->date_from and !$this->date_to and
			!$this->follow and !$this->new and
			!$this->is_answered and
			($this->record_type == ModelHelper::TYPE_QUESTION) and
			!$this->is_closed and !$this->sort
		);
	}

	public function isExtra()
	{
		return (
			$this->tag_records or $this->is_answered or
			$this->date_from or $this->date_to or
			$this->is_closed or $this->teacher or
			($this->sort and $this->sort != '-' . static::NEWEST_QUESTIONS)
			// $this->answer_count or $this->follower_count or
		);
	}

	public function getParam(string $text, string $param)
	{
		if (mb_stripos($text, $param . ':') === 0) {
			$value = mb_substr($text, mb_strlen($param) + 1);
			if ($value == (int)$value) {
				return $value;
			}
		}
		return null;
	}

	public function getOrder()
	{
		$field = static::NEWEST_QUESTIONS;
		$order = SORT_DESC;
		if ($this->sort) {
			if (mb_substr($this->sort, 0, 1) == '-') {
				$field = mb_substr($this->sort, 1);
				$order = SORT_DESC;
			} else {
				$field = $this->sort;
				$order = SORT_ASC;
			}
		}
		return [$field => $order];
	}

	public function getSortField()
	{
		if (mb_substr($this->sort, 0, 1) == '-') {
			return mb_substr($this->sort, 1);
		}
		return $this->sort;
	}

	public function hasQuestions() {
		return in_array($this->record_type, [ModelHelper::TYPE_QUESTION, ModelHelper::TYPE_ALL]);
	}

	public function hasAnswers() {
		return in_array($this->record_type, [ModelHelper::TYPE_ANSWER, ModelHelper::TYPE_ALL]);
	}

	public function hasComments() {
		return in_array($this->record_type, [ModelHelper::TYPE_COMMENT, ModelHelper::TYPE_ALL]);
	}

	public function hasDisciplines() {
		return in_array($this->record_type, [ModelHelper::TYPE_DISCIPLINE, ModelHelper::TYPE_ALL]);
	}

	public function hasTeachers() {
		return in_array($this->record_type, [ModelHelper::TYPE_TEACHER, ModelHelper::TYPE_ALL]);
	}

	public function hasTags() {
		return in_array($this->record_type, [ModelHelper::TYPE_TAG, ModelHelper::TYPE_ALL]);
	}

	public function getTabs()
	{
		return [
			ModelHelper::TYPE_ALL => [
				'text' => HtmlHelper::getIconText(Yii::t('app', 'Всё'), true),
				'class' => 'bi-circle'
			],
			ModelHelper::TYPE_QUESTION => [
				'text' => HtmlHelper::getIconText(Yii::t('app', 'Вопросы'), true),
				'class' => 'bi-question-circle'
			],
			ModelHelper::TYPE_ANSWER => [
				'text' => HtmlHelper::getIconText(Yii::t('app', 'Ответы'), true),
				'class' => 'bi-chat-left-text'
			],
			ModelHelper::TYPE_COMMENT => [
				'text' => HtmlHelper::getIconText(Yii::t('app', 'Комментарии'), true),
				'class' => 'bi-chat'
			],
		];
	}
}
