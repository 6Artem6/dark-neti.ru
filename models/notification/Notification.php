<?php

namespace app\models\notification;

use Yii;
use yii\db\ActiveRecord;
use yii\bootstrap5\Html;

use app\models\question\{Question, Answer, Comment};
use app\models\user\User;
use app\models\request\Report;
use app\models\follow\{FollowQuestion, FollowUser, FollowDiscipline};
use app\models\helpers\HtmlHelper;


class Notification extends ActiveRecord
{

	public static function tableName()
	{
		return 'notification';
	}

	public function rules()
	{
		return [
			[['notification_id'], 'unique'],
			[['notification_id', 'user_id', 'question_id', 'answer_id',
				'comment_id', 'report_id', 'author_id', 'type_id', 'times'], 'integer'],
			[['is_unseen'], 'boolean'],
			[['notification_datetime'], 'date', 'format' => 'php:Y-m-d H:i:s'],
			[['type_id'], 'in', 'range' => static::getTypeList()],
		];
	}

	public static function primaryKey()
	{
		return [
			'notification_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'user_id' => Yii::t('app', 'Пользователь'),
		];
	}

	private $_total_count;

	public const MY_QUESTION_ANSWER = 1;
	public const MY_QUESTION_COMMENT = 2;
	public const MY_QUESTION_ANSWER_COMMENT = 3;

	public const FOLLOWED_QUESTION_EDIT = 4;
	public const FOLLOWED_QUESTION_ANSWER = 5;
	public const FOLLOWED_QUESTION_COMMENT = 6;
	public const FOLLOWED_QUESTION_ANSWER_COMMENT = 7;
	public const FOLLOWED_QUESTION_MY_ANSWER_COMMENT = 8;
	public const FOLLOWED_QUESTION_ANSWERED = 9;

	public const FOLLOWED_USER_QUESTION_CREATE = 10;
	public const FOLLOWED_USER_QUESTION_EDIT = 11;
	public const FOLLOWED_USER_ANSWER = 12;
	public const FOLLOWED_USER_COMMENT = 13;
	public const FOLLOWED_USER_QUESTION_ANSWERED = 14;

	public const FOLLOWED_DISCIPLINE_QUESTION_CREATE = 15;
	public const FOLLOWED_DISCIPLINE_QUESTION_EDIT = 16;
	public const FOLLOWED_DISCIPLINE_QUESTION_ANSWER = 17;
	public const FOLLOWED_DISCIPLINE_QUESTION_COMMENT = 18;
	public const FOLLOWED_DISCIPLINE_QUESTION_ANSWER_COMMENT = 19;
	public const FOLLOWED_DISCIPLINE_QUESTION_ANSWERED = 20;

	public const MENTION = 21;
	public const INVITE_AS_EXPERT = 22;
	public const MY_ANSWER_LIKE = 23;
	public const ANSWER_EDIT = 24;
	public const REPORT = 25;

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

	public function getAuthor()
	{
		return $this->hasOne(User::class, ['user_id' => 'author_id']);
	}

	public function getReport()
	{
		return $this->hasOne(Report::class, ['report_id' => 'report_id']);
	}

	public function getTimeElapsed()
	{
		return HtmlHelper::getTimeElapsed($this->notification_datetime);
	}

	public function getTimeFull()
	{
		return HtmlHelper::getTimeElapsed($this->notification_datetime, true);
	}

	public static function getTypeList()
	{
		return [
			static::MY_QUESTION_ANSWER, static::MY_QUESTION_COMMENT, static::MY_QUESTION_ANSWER_COMMENT,

			static::FOLLOWED_QUESTION_EDIT, static::FOLLOWED_QUESTION_ANSWERED,
			static::FOLLOWED_QUESTION_ANSWER, static::FOLLOWED_QUESTION_COMMENT,
			static::FOLLOWED_QUESTION_ANSWER_COMMENT, static::FOLLOWED_QUESTION_MY_ANSWER_COMMENT,

			static::FOLLOWED_USER_QUESTION_CREATE, static::FOLLOWED_USER_QUESTION_EDIT,
			static::FOLLOWED_USER_ANSWER, static::FOLLOWED_USER_QUESTION_ANSWERED,
			static::FOLLOWED_USER_COMMENT,

			static::FOLLOWED_DISCIPLINE_QUESTION_CREATE, static::FOLLOWED_DISCIPLINE_QUESTION_EDIT,
			static::FOLLOWED_DISCIPLINE_QUESTION_ANSWER, static::FOLLOWED_DISCIPLINE_QUESTION_ANSWERED,
			static::FOLLOWED_DISCIPLINE_QUESTION_COMMENT, static::FOLLOWED_DISCIPLINE_QUESTION_ANSWER_COMMENT,

			static::ANSWER_EDIT, static::MY_ANSWER_LIKE,

			static::REPORT,
			// static::INVITE_AS_EXPERT, static::MENTION,
		];
	}

	protected function findTotalCount()
	{
		$this->_total_count = self::find()
			->where(['user_id' => Yii::$app->user->identity->id])
			->count();
	}

	public function getTotalCount()
	{
		if (is_null($this->_total_count)) {
			$this->findTotalCount();
		}
		return $this->_total_count;
	}

	public function getLastMessages()
	{
		$list = [];
		if ($this->totalCount) {
			$list = self::find()
				->from(['n' => self::tableName()])
				->joinWith('question')
				->joinWith('answer')
				->joinWith('comment')
				->joinWith('answer')
				->joinWith('comment')
				->joinWith('author')
				->where(['n.user_id' => Yii::$app->user->identity->id])
				->orderBy(['notification_datetime' => SORT_DESC])
				->limit(10)
				->all();
		}
		return $list;
	}

	public function getMessages()
	{
		$result = [];
		if ($this->totalCount) {
			$list = self::find()
				->from(['n' => self::tableName()])
				->joinWith('question')
				->joinWith('answer')
				->joinWith('comment')
				->joinWith('answer')
				->joinWith('comment')
				->joinWith('author')
				->where(['n.user_id' => Yii::$app->user->identity->id])
				->orderBy(['type_id' => SORT_DESC, 'notification_datetime' => SORT_DESC])
				->all();
			foreach ($list as $record) {
				$type_id = $this->getMessageListId($record);
				$result[$type_id][] = $record;
			}
		}
		return $result;
	}

	public function getMessageListId(self $record)
	{
		$type_id = $record->type_id;
		$type_list = [
			static::MY_QUESTION_ANSWER => [
				static::MY_QUESTION_ANSWER,
				static::FOLLOWED_QUESTION_ANSWER,
				static::FOLLOWED_USER_ANSWER,
				static::FOLLOWED_DISCIPLINE_QUESTION_ANSWER
			],
			static::MY_QUESTION_COMMENT => [
				static::MY_QUESTION_COMMENT,
				static::FOLLOWED_QUESTION_COMMENT,
				static::FOLLOWED_DISCIPLINE_QUESTION_COMMENT
			],
			static::MY_QUESTION_ANSWER_COMMENT => [
				static::MY_QUESTION_ANSWER_COMMENT,
				static::FOLLOWED_QUESTION_ANSWER_COMMENT,
				static::FOLLOWED_QUESTION_MY_ANSWER_COMMENT,
				static::FOLLOWED_DISCIPLINE_QUESTION_ANSWER_COMMENT
			],
			static::FOLLOWED_USER_QUESTION_CREATE => [
				static::FOLLOWED_USER_QUESTION_CREATE,
				static::FOLLOWED_DISCIPLINE_QUESTION_CREATE
			],
			static::FOLLOWED_QUESTION_ANSWERED => [
				static::FOLLOWED_QUESTION_ANSWERED,
				static::FOLLOWED_USER_QUESTION_ANSWERED,
				static::FOLLOWED_DISCIPLINE_QUESTION_ANSWERED
			],
			static::ANSWER_EDIT => [
				static::ANSWER_EDIT,
				static::FOLLOWED_QUESTION_EDIT,
				static::FOLLOWED_USER_QUESTION_EDIT
			],
		];
		if (in_array($type_id, $type_list[static::MY_QUESTION_ANSWER])) {
			$type_id = static::MY_QUESTION_ANSWER;
		} elseif (in_array($type_id, $type_list[static::MY_QUESTION_COMMENT]) or
			(($type_id == static::FOLLOWED_USER_COMMENT) and empty($record->answer))) {
			$type_id = static::MY_QUESTION_COMMENT;
		} elseif (in_array($type_id, $type_list[static::MY_QUESTION_ANSWER_COMMENT]) or
			(($type_id == static::FOLLOWED_USER_COMMENT) and !empty($record->answer))) {
			$type_id = static::MY_QUESTION_ANSWER_COMMENT;
		} elseif (in_array($type_id, $type_list[static::FOLLOWED_USER_QUESTION_CREATE])) {
			$type_id = static::FOLLOWED_USER_QUESTION_CREATE;
		} elseif (in_array($type_id, $type_list[static::FOLLOWED_QUESTION_ANSWERED])) {
			$type_id = static::FOLLOWED_QUESTION_ANSWERED;
		} elseif (in_array($type_id, $type_list[static::ANSWER_EDIT])) {
			$type_id = static::ANSWER_EDIT;
		}
		return $type_id;
	}

	public static function getUnseenList(Question $question)
	{
		$id = $question->id;
		$user_id = Yii::$app->user->identity->id;
		$result = [
			'questions' => [],
			'answers' => [],
			'comments' => [],
			'edited' => [],
		];
		$type_list = [
			'questions' => [
				static::FOLLOWED_USER_QUESTION_CREATE,
				static::FOLLOWED_DISCIPLINE_QUESTION_CREATE,
			],
			'answers' => [
				static::MY_QUESTION_ANSWER,
				static::FOLLOWED_QUESTION_ANSWER,
				static::FOLLOWED_DISCIPLINE_QUESTION_ANSWER,
				static::FOLLOWED_USER_ANSWER,
			],
			'comments' => [
				static::MY_QUESTION_COMMENT,
				static::MY_QUESTION_ANSWER_COMMENT,
				static::FOLLOWED_QUESTION_COMMENT,
				static::FOLLOWED_QUESTION_ANSWER_COMMENT,
				static::FOLLOWED_QUESTION_MY_ANSWER_COMMENT,
				static::FOLLOWED_USER_COMMENT,
				static::FOLLOWED_DISCIPLINE_QUESTION_COMMENT,
				static::FOLLOWED_DISCIPLINE_QUESTION_ANSWER_COMMENT,
			],
			'edited' => [
				static::ANSWER_EDIT
			],
		];
		$list = self::find()
			->where(['user_id' => $user_id])
			->andWhere(['question_id' => $id])
			->andWhere(['IN', 'type_id', array_merge(
					$type_list['questions'],
					$type_list['answers'],
					$type_list['comments'],
					$type_list['edited'],
				)
			])
			->all();
		foreach ($list as $record) {
			if (in_array($record->type_id, $type_list['answers'])) {
				$result['questions'][] = $record->answer_id;
			} elseif (in_array($record->type_id, $type_list['answers'])) {
				$result['answers'][] = $record->answer_id;
			} elseif (in_array($record->type_id, $type_list['comments'])) {
				$result['comments'][] = $record->comment_id;
			} elseif (in_array($record->type_id, $type_list['edited'])) {
				$result['edited'][] = $record->answer_id;
			}
		}
		return $result;
	}

	public static function getUnseenByQuestion()
	{
		$user_id = Yii::$app->user->identity->id;
		$result = [];
		$type_list = [
			'answers' => [
				static::MY_QUESTION_ANSWER,
				static::FOLLOWED_QUESTION_ANSWER
			],
			'comments' => [
				static::MY_QUESTION_COMMENT,
				static::MY_QUESTION_ANSWER_COMMENT,
				static::FOLLOWED_QUESTION_COMMENT,
				static::FOLLOWED_QUESTION_ANSWER_COMMENT,
				static::FOLLOWED_QUESTION_MY_ANSWER_COMMENT
			],
			'edited' => [
				static::ANSWER_EDIT
			],
		];
		$list = self::find()
			->where(['user_id' => $user_id])
			->andWhere(['IN', 'type_id', array_merge(
					$type_list['answers'],
					$type_list['comments'],
					$type_list['edited'],
				)
			])
			->all();
		foreach ($list as $record) {
			if (empty($result[$record->question_id])) {
				$result[$record->question_id] = [
					'answers' => [],
					'comments' => [],
					'edited' => [],
				];
			}
			if (in_array($record->type_id, $type_list['answers'])) {
				$result[$record->question_id]['answers'][] = $record->answer_id;
			} elseif (in_array($record->type_id, $type_list['comments'])) {
				$result[$record->question_id]['comments'][] = $record->comment_id;
			} elseif (in_array($record->type_id, $type_list['edited'])) {
				$result[$record->question_id]['edited'][] = $record->answer_id;
			}
		}
		return $result;
	}

	public static function addQuestion(Question $question)
	{
		$question_id = $question->id;
		$discipline_id = $question->discipline_id;
		$author_id = $question->user_id;
		$followDisciplineIds = FollowDiscipline::getUserIds($discipline_id, $author_id);
		$followUserIds = FollowUser::getUserIds($author_id);
		$userIds = array_merge($followDisciplineIds, $followUserIds);
		$userIds = array_unique($userIds);
		$list = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.siteSettings'])
			->where(['OR',
				['followed_discipline_question_create' => true],
				['followed_user_question_create' => true],
			])
			->andWhere(['IN', 'user.user_id', $userIds])
			->all();
		foreach ($list as $user) {
			$params = [
				'user_id' => $user->user_id,
				'author_id' => $author_id,
				'question_id' => $question_id,
			];
			if ($user->user_id != $author_id) {
				if ($user->data->siteSettings->followed_discipline_question_create) {
					$record = new self($params);
					$record->type_id = static::FOLLOWED_DISCIPLINE_QUESTION_CREATE;
					$record->save();
				} elseif ($user->data->siteSettings->followed_user_question_create) {
					$record = new self($params);
					$record->type_id = static::FOLLOWED_USER_QUESTION_CREATE;
					$record->save();
				}
			}
		}
	}

	public static function addAnswer(Answer $answer)
	{
		$id = $answer->id;
		$author_id = $answer->user_id;
		$question_id = $answer->question_id;
		$question_author_id = $answer->question->user_id;
		$userIds = FollowQuestion::getUserIds($question_id, $author_id);
		$list = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.siteSettings'])
			->where(['OR',
				['my_question_answer' => true],
				['followed_question_answer' => true],
				['followed_user_answer' => true],
				['followed_discipline_question_answer' => true],
			])
			->andWhere(['IN', 'user.user_id', $userIds])
			->all();
		foreach ($list as $user) {
			$params = [
				'user_id' => $user->user_id,
				'author_id' => $author_id,
				'question_id' => $question_id,
				'answer_id' => $id,
			];
			if ($user->user_id == $question_author_id) {
				if ($user->data->siteSettings->my_question_answer) {
					$record = new self($params);
					$record->type_id = static::MY_QUESTION_ANSWER;
					$record->save();
				}
			} else {
				if ($user->data->siteSettings->followed_question_answer) {
					$record = new self($params);
					$record->type_id = static::FOLLOWED_QUESTION_ANSWER;
					$record->save();
				} elseif ($user->data->siteSettings->followed_user_answer) {
					$record = new self($params);
					$record->type_id = static::FOLLOWED_USER_ANSWER;
					$record->save();
				} elseif ($user->data->siteSettings->followed_discipline_question_answer) {
					$record = new self($params);
					$record->type_id = static::FOLLOWED_DISCIPLINE_QUESTION_ANSWER;
					$record->save();
				}
			}
		}
	}

	public static function addQuestionComment(Comment $comment)
	{
		$id = $comment->id;
		$author_id = $comment->user_id;
		$question_id = $comment->question_id;
		$question_author_id = $comment->question->user_id;
		$userIds = FollowQuestion::getUserIds($question_id, $author_id);
		$list = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.siteSettings'])
			->where(['OR',
				['my_question_comment' => true],
				['followed_question_comment' => true],
				['followed_user_comment' => true],
				['followed_discipline_question_comment' => true],
			])
			->andWhere(['IN', 'user.user_id', $userIds])
			->all();
		foreach ($list as $user) {
			$params = [
				'user_id' => $user->user_id,
				'author_id' => $author_id,
				'question_id' => $question_id,
				'comment_id' => $id,
			];
			if ($user->user_id == $question_author_id) {
				if ($user->data->siteSettings->my_question_comment) {
					$record = new self($params);
					$record->type_id = static::MY_QUESTION_COMMENT;
					$record->save();
				}
			} else {
				if ($user->data->siteSettings->followed_question_comment) {
					$record = new self($params);
					$record->type_id = static::FOLLOWED_QUESTION_COMMENT;
					$record->save();
				} elseif ($user->data->siteSettings->followed_user_comment) {
					$record = new self($params);
					$record->type_id = static::FOLLOWED_USER_COMMENT;
					$record->save();
				} elseif ($user->data->siteSettings->followed_discipline_question_comment) {
					$record = new self($params);
					$record->type_id = static::FOLLOWED_DISCIPLINE_QUESTION_COMMENT;
					$record->save();
				}
			}
		}
	}

	public static function addAnswerComment(Comment $comment)
	{
		$id = $comment->id;
		$author_id = $comment->user_id;
		$question_id = $comment->question_id;
		$answer_id = $comment->answer_id;
		$question_author_id = $comment->question->user_id;
		$answer_user_id = $comment->answer->user_id;
		$userIds = FollowQuestion::getUserIds($question_id, $author_id);
		$list = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.siteSettings'])
			->where(['OR',
				['my_question_answer_comment' => true],
				['followed_question_answer_comment' => true],
				['followed_question_my_answer_comment' => true],
				['followed_user_comment' => true],
				['followed_discipline_question_answer_comment' => true],
			])
			->andWhere(['IN', 'user.user_id', $userIds])
			->all();
		foreach ($list as $user) {
			$params = [
				'user_id' => $user->user_id,
				'author_id' => $author_id,
				'question_id' => $question_id,
				'answer_id' => $answer_id,
				'comment_id' => $id,
			];
			if ($user->user_id == $question_author_id) {
				if ($user->data->siteSettings->my_question_answer_comment) {
					$record = new self($params);
					$record->type_id = static::MY_QUESTION_ANSWER_COMMENT;
					$record->save();
				}
			} elseif ($user->user_id == $answer_user_id) {
				if ($user->data->siteSettings->followed_question_my_answer_comment) {
					$record = new self($params);
					$record->type_id = static::FOLLOWED_QUESTION_MY_ANSWER_COMMENT;
					$record->save();
				}
			} else {
				if ($user->data->siteSettings->followed_question_answer_comment) {
					$record = new self($params);
					$record->type_id = static::FOLLOWED_QUESTION_ANSWER_COMMENT;
					$record->save();
				} elseif ($user->data->siteSettings->followed_user_comment) {
					$record = new self($params);
					$record->type_id = static::FOLLOWED_USER_COMMENT;
					$record->save();
				} elseif ($user->data->siteSettings->followed_discipline_question_answer_comment) {
					$record = new self($params);
					$record->type_id = static::FOLLOWED_DISCIPLINE_QUESTION_ANSWER_COMMENT;
					$record->save();
				}
			}
		}
	}

	public static function addQuestionAnswered(Answer $answer)
	{
		$id = $answer->answer_id;
		$author_id = $answer->user_id;
		$question_id = $answer->question_id;
		$answer_id = $answer->id;
		$userIds = FollowQuestion::getUserIds($question_id, $author_id);
		$list = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.siteSettings'])
			->where(['OR',
				['followed_question_answered' => true],
				['followed_user_question_answered' => true],
				['followed_discipline_question_answered' => true],
			])
			->andWhere(['IN', 'user.user_id', $userIds])
			->all();
		foreach ($list as $user) {
			$params = [
				'user_id' => $user->user_id,
				'author_id' => $author_id,
				'question_id' => $question_id,
				'answer_id' => $answer_id
			];
			if ($user->data->siteSettings->followed_question_answered) {
				$record = new self($params);
				$record->type_id = static::FOLLOWED_QUESTION_ANSWERED;
				$record->save();
			} elseif ($user->data->siteSettings->followed_user_question_answered) {
				$record = new self($params);
				$record->type_id = static::FOLLOWED_USER_QUESTION_ANSWERED;
				$record->save();
			} elseif ($user->data->siteSettings->followed_discipline_question_answered) {
				$record = new self($params);
				$record->type_id = static::FOLLOWED_DISCIPLINE_QUESTION_ANSWERED;
				$record->save();
			}
		}
	}

	/*
	public function addMention(Comment $comment, array $userIds)
	{
		$id = $comment->id;
		$author_id = $comment->user_id;
		$question_id = $comment->question_id;
		$list = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.siteSettings'])
			->where(['mention' => true])
			->andWhere(['IN', 'user.user_id', $userIds])
			->all();
		foreach ($list as $user) {
			$record = new self;
			$record->user_id => $user->user_id,
			$record->author_id = $author_id;
			$record->question_id = $question_id;
			$record->comment_id = $id;
			$record->type_id = static::FOLLOWED_QUESTION_ANSWERED;
			$record->save();
		}
	}
	*/

	/*public static function addInvite(Question $question, int $user_id)
	{
		$question_id = $question->id;
		$author_id = Yii::$app->user->identity->id;
		$record = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.siteSettings'])
			->where(['invite_as_expert' => true])
			->andWhere(['user.user_id' => $user_id])
			->one();
		if ($record) {
			$record = new self;
			$record->user_id = $user_id;
			$record->author_id = $author_id;
			$record->question_id = $question_id;
			$record->type_id = static::INVITE_AS_EXPERT;
			$record->save();
		}
	}*/

	public static function addLikeAnswer(Answer $answer, int $author_id)
	{
		$id = $answer->id;
		$question_id = $answer->question_id;
		$record = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.siteSettings'])
			->where(['my_answer_like' => true])
			->andWhere(['user.user_id' => $answer->user_id])
			->one();
		if ($record) {
			$record = new self;
			$record->user_id = $answer->user_id;
			$record->author_id = $author_id;
			$record->question_id = $answer->question_id;
			$record->answer_id = $id;
			$record->type_id = static::MY_ANSWER_LIKE;
			$record->save();
		}
	}

	public static function addQuestionEdit(Question $question)
	{
		$id = $question->id;
		$author_id = $question->user_id;
		$userIds = FollowQuestion::getUserIds($id, $author_id);
		$list = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.siteSettings'])
			->where(['OR',
				['followed_question_edit' => true],
				['followed_user_question_edit' => true],
				['followed_discipline_question_edit' => true],
			])
			->andWhere(['IN', 'user.user_id', $userIds])
			->all();
		foreach ($list as $user) {
			$params = [
				'user_id' => $user->user_id,
				'author_id' => $author_id,
				'question_id' => $id,
				'answer_id' => $id,
			];
			if ($user->data->siteSettings->followed_question_edit) {
				$record = new self($params);
				$record->type_id = static::FOLLOWED_QUESTION_EDIT;
				$record->save();
			} elseif ($user->data->siteSettings->followed_user_question_edit) {
				$record = new self($params);
				$record->type_id = static::FOLLOWED_USER_QUESTION_EDIT;
				$record->save();
			} elseif ($user->data->siteSettings->followed_discipline_question_edit) {
				$record = new self($params);
				$record->type_id = static::FOLLOWED_DISCIPLINE_QUESTION_EDIT;
				$record->save();
			}
		}
	}

	public static function addAnswerEdit(Answer $answer)
	{
		$id = $answer->id;
		$author_id = $answer->user_id;
		$question_id = $answer->question_id;
		$userIds = FollowQuestion::getUserIds($question_id, $author_id);
		$list = User::find()
			->from(['user' => User::tableName()])
			->joinWith(['data.siteSettings'])
			->where(['answer_edit' => true])
			->andWhere(['IN', 'user.user_id', $userIds])
			->all();
		foreach ($list as $user) {
			$record = new self;
			$record->user_id = $user->user_id;
			$record->author_id = $author_id;
			$record->question_id = $question_id;
			$record->answer_id = $id;
			$record->type_id = static::ANSWER_EDIT;
			$record->save();
		}
	}

	public static function addReport(Report $report)
	{
		$record = new self;
		$record->user_id = $report->user_id;
		$record->author_id = $report->author_id;
		$record->report_id = $report->report_id;
		$record->type_id = static::REPORT;
		$record->save();
	}

	public static function removeNotification(int $id)
	{
		$ok = false;
		$message = Yii::t('app', 'Запись не была найдена!');
		$user_id = Yii::$app->user->identity->id;
		$record = self::find()
			->where(['notification_id' => $id])
			->andWhere(['user_id' => $user_id])
			->one();
		if ($record) {
			if ($record->delete()) {
				$message = Yii::t('app', 'Уведомление прочитано!');
				$ok = true;
			} else {
				$message = Yii::t('app', 'Не удалось прочитать уведомление!');
			}
		}
		return [
			'status' => $ok,
			'message' => $message
		];
	}

	public static function removeQuestionAnswered(Answer $answer)
	{
		$id = $answer->id;
		$question_id = $answer->question_id;
		$list = self::find()
			->where(['answer_id' => $id])
			->andWhere(['question_id' => $question_id])
			->andWhere(['IN', 'type_id', [
				static::FOLLOWED_QUESTION_ANSWERED,
				static::FOLLOWED_USER_QUESTION_ANSWERED,
				static::FOLLOWED_DISCIPLINE_QUESTION_ANSWERED
			]])
			->all();
		foreach ($list as $record) {
			$record->delete();
		}
	}

	public static function removeLikeAnswer(Answer $answer, int $author_id)
	{
		$id = $answer->id;
		$record = self::find()
			->where(['answer_id' => $id])
			->andWhere(['author_id' => $author_id])
			->andWhere(['type_id' => static::MY_ANSWER_LIKE])
			->one();
		if ($record) {
			$record->delete();
		}
	}

	public static function removeReport(Report $report)
	{
		$id = $record->report_id;
		$record = self::find()
			->where(['report_id' => $id])
			->andWhere(['type_id' => static::REPORT])
			->one();
		if ($record) {
			$record->delete();
		}
	}

	public static function removeQuestion(Question $question)
	{
		$id = $question->id;
		$user_id = Yii::$app->user->identity->id;
		$list = self::find()
			->where(['user_id' => $user_id])
			->andWhere(['question_id' => $id])
			->andWhere(['!=', 'type_id', static::REPORT])
			->all();
		foreach ($list as $record) {
			$record->delete();
		}
	}

	public static function removeQuestionAfterChange(Question $question)
	{
		$id = $question->id;
		$list = self::find()
			->where(['question_id' => $id])
			->andWhere(['!=', 'type_id', static::REPORT])
			->all();
		foreach ($list as $record) {
			$record->delete();
		}
	}

	public static function removeAnswerAfterChange(Answer $answer)
	{
		$id = $answer->id;
		$list = self::find()
			->where(['answer_id' => $id])
			->andWhere(['IS', 'comment_id', NULL])
			->all();
		foreach ($list as $record) {
			$record->delete();
		}
	}

	public static function removeCommentAfterChange(Comment $comment)
	{
		$id = $comment->id;
		$list = self::find()
			->where(['comment_id' => $id])
			->all();
		foreach ($list as $record) {
			$record->delete();
		}
	}

	public static function removeQuestionAll()
	{
		$user_id = Yii::$app->user->identity->id;
		$list = self::find()
			->where(['user_id' => $user_id])
			->andWhere(['!=', 'type_id', static::REPORT])
			->all();
		foreach ($list as $record) {
			$record->delete();
		}
	}

	public static function removeAllNotifications()
	{
		$user_id = Yii::$app->user->identity->id;
		$list = self::find()
			->where(['user_id' => $user_id])
			->all();
		foreach ($list as $record) {
			$record->delete();
		}
		$message = Yii::t('app', 'Уведомления прочитаны!');
		$ok = true;
		return [
			'status' => $ok,
			'message' => $message
		];
	}

	public static function seeLastNotifications(int $id = 0)
	{
		$user_id = Yii::$app->user->identity->id;
		$list = self::find()
			->where(['user_id' => $user_id])
			->andWhere(['<=', 'notification_id', $id])
			->andWhere(['is_unseen' => true])
			->all();
		foreach ($list as $record) {
			$record->is_unseen = false;
			$record->true();
		}
		$ok = true;
		return [
			'status' => $ok,
		];
	}

}
