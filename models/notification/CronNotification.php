<?php

namespace app\models\notification;

use Yii;
use yii\db\ActiveRecord;

use app\models\question\{Question, Answer, Comment};
use app\models\user\User;
use app\models\request\Report;
use app\models\follow\{FollowQuestion, FollowUser, FollowDiscipline};


class CronNotification extends ActiveRecord
{

	public static function tableName()
	{
		return 'cron_notification';
	}

	public function rules()
	{
		return [
			[['id'], 'unique'],
			[['id', 'question_id', 'answer_id', 'comment_id', 'report_id', 'author_id'], 'integer'],
			[['is_taken'], 'boolean'],
			[['notification_datetime'], 'date', 'format' => 'php:Y-m-d H:i:s'],
			[['type_id'], 'in', 'range' => static::getTypeList()],
		];
	}

	public static function primaryKey()
	{
		return [
			'id'
		];
	}

	public function attributeLabels()
	{
		return [
			'id' => Yii::t('app', 'Уведомление')
		];
	}

	public const QUESTION_ADD = 1;
	public const ANSWER_ADD = 2;
	public const COMMENT_ADD = 3;
	public const QUESTION_ANSWERED = 4;
	public const QUESTION_EDIT = 5;
	public const ANSWER_EDIT = 6;
	public const ANSWER_LIKE = 7;
	public const REPORT_ADD = 8;
	public const MENTION = 9;
	public const INVITE_AS_EXPERT = 10;

	public const QUESTION_REMOVE = 11;
	public const ANSWER_REMOVE = 12;
	public const COMMENT_REMOVE = 13;
	public const QUESTION_ANSWERED_REMOVE = 14;
	public const ANSWER_LIKE_REMOVE = 15;
	public const REPORT_REMOVE = 16;
	public const MENTION_REMOVE = 17;
	public const INVITE_AS_EXPERT_REMOVE = 18;

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

	public function getReport()
	{
		return $this->hasOne(Report::class, ['report_id' => 'report_id']);
	}

	public static function getTypeList()
	{
		return array_merge(
			static::getAddTypeList(),
			static::getRemoveTypeList()
		);
	}

	public static function getAddTypeList()
	{
		return [
			static::QUESTION_ADD, static::ANSWER_ADD, static::COMMENT_ADD,
			static::QUESTION_ANSWERED, static::QUESTION_EDIT,
			static::ANSWER_EDIT, static::ANSWER_LIKE,
			static::REPORT_ADD,
			// static::MENTION, static::INVITE_AS_EXPERT,
		];
	}

	public static function getRemoveTypeList()
	{
		return [
			static::QUESTION_REMOVE, static::ANSWER_REMOVE, static::COMMENT_REMOVE,
			static::QUESTION_ANSWERED_REMOVE, static::ANSWER_LIKE_REMOVE,
			static::REPORT_REMOVE,
			// static::MENTION_REMOVE, static::INVITE_AS_EXPERT_REMOVE,
		];
	}

	public function setTaken()
	{
		if (!$this->is_taken) {
			$this->is_taken = true;
			$this->save();
		}
	}

	public static function addQuestion(Question $question)
	{
		$record = new self;
		$record->question_id = $question->id;
		$record->type_id = static::QUESTION_ADD;
		$record->save();
	}

	public static function addAnswer(Answer $answer)
	{
		$record = new self;
		$record->answer_id = $answer->id;
		$record->type_id = static::ANSWER_ADD;
		$record->save();
	}

	public static function addComment(Comment $comment)
	{
		$record = new self;
		$record->comment_id = $comment->id;
		$record->type_id = static::COMMENT_ADD;
		$record->save();
	}

	public static function addQuestionAnswered(Answer $answer)
	{
		$record = new self;
		$record->answer_id = $answer->id;
		$record->type_id = static::QUESTION_ANSWERED;
		$record->save();
	}

	public static function addQuestionEdit(Question $question)
	{
		$record = new self;
		$record->question_id = $question->id;
		$record->type_id = static::QUESTION_EDIT;
		$record->save();
	}

	public static function addAnswerEdit(Answer $answer)
	{
		$record = new self;
		$record->answer_id = $answer->id;
		$record->type_id = static::ANSWER_EDIT;
		$record->save();
	}

	public static function addLikeAnswer(Answer $answer, int $author_id)
	{
		$record = new self;
		$record->answer_id = $answer->id;
		$record->author_id = $author_id;
		$record->type_id = static::ANSWER_LIKE;
		$record->save();
	}

	public static function addReport(Report $report)
	{
		$record = new self;
		$record->report_id = $report->report_id;
		$record->type_id = static::REPORT_ADD;
		$record->save();
	}

	public static function removeQuestion(Question $question)
	{
		$id = $question->id;
		$type_id = static::QUESTION_REMOVE;
		$exists = self::find()
			->where(['question_id' => $id])
			->andWhere(['type_id' => $type_id])
			->exists();
		if (!$exists) {
			$record = new self;
			$record->question_id = $id;
			$record->type_id = $type_id;
			$record->save();
		}
	}

	public static function removeAnswer(Answer $answer)
	{
		$id = $answer->id;
		$type_id = static::ANSWER_REMOVE;
		$exists = self::find()
			->where(['answer_id' => $id])
			->andWhere(['type_id' => $type_id])
			->exists();
		if (!$exists) {
			$record = new self;
			$record->answer_id = $id;
			$record->type_id = $type_id;
			$record->save();
		}
	}

	public static function removeComment(Comment $comment)
	{
		$id = $comment->id;
		$type_id = static::COMMENT_REMOVE;
		$exists = self::find()
			->where(['comment_id' => $id])
			->andWhere(['type_id' => $type_id])
			->exists();
		if (!$exists) {
			$record = new self;
			$record->comment_id = $id;
			$record->type_id = $type_id;
			$record->save();
		}
	}

	public static function removeQuestionAnswered(Answer $answer)
	{
		$id = $answer->id;
		$type_id = static::QUESTION_ANSWERED_REMOVE;
		$exists = self::find()
			->where(['answer_id' => $id])
			->andWhere(['type_id' => $type_id])
			->exists();
		if (!$exists) {
			$record = new self;
			$record->answer_id = $id;
			$record->type_id = $type_id;
			$record->save();
		}
	}

	public static function removeLikeAnswer(Answer $answer, int $author_id)
	{
		$id = $answer->id;
		$type_id = static::ANSWER_LIKE_REMOVE;
		$exists = self::find()
			->where(['answer_id' => $id])
			->andWhere(['type_id' => $type_id])
			->andWhere(['author_id' => $author_id])
			->exists();
		if (!$exists) {
			$record = new self;
			$record->answer_id = $id;
			$record->type_id = $type_id;
			$record->author_id = $author_id;
			$record->save();
		}
	}

	public static function removeReport(Report $report)
	{
		$id = $report->report_id;
		$type_id = static::REPORT_REMOVE;
		$exists = self::find()
			->where(['report_id' => $id])
			->andWhere(['type_id' => $type_id])
			->exists();
		if (!$exists) {
			$record = new self;
			$record->report_id = $id;
			$record->type_id = $type_id;
			$record->save();
		}
	}

	public static function sendRecords()
	{
		$delete_records = self::find()
			->from(['notification' => self::tableName()])
			->joinWith('question')
			->joinWith('answer')
			->joinWith('comment')
			->joinWith('comment.answer')
			->joinWith('comment.question')
			->joinWith('report')
			->where(['IN', 'notification.type_id', static::getRemoveTypeList()])
			->andWhere(['is_taken' => false])
			->all();

		foreach ($delete_records as $record) {
			$record->setTaken();
		}
		foreach ($delete_records as $record) {
		    if ($record->type_id == static::QUESTION_REMOVE) {
				$record->deleteQuestionAfterChange();
			} elseif ($record->type_id == static::ANSWER_REMOVE) {
				$record->deleteAnswerAfterChange();
			} elseif ($record->type_id == static::COMMENT_REMOVE) {
				$record->deleteCommentAfterChange();
			} elseif ($record->type_id == static::QUESTION_ANSWERED_REMOVE) {
				$record->deleteQuestionAnswered();
			} elseif ($record->type_id == static::ANSWER_LIKE_REMOVE) {
				$record->deleteLikeAnswer();
			} elseif ($record->type_id == static::REPORT_REMOVE) {
				$record->deleteReport();
			}
			$record->delete();
		}

		$send_records = self::find()
			->from(['notification' => self::tableName()])
			->joinWith('question')
			->joinWith('answer')
			->joinWith('comment')
			->joinWith('comment.answer')
			->joinWith('comment.question')
			->joinWith('report')
			->where(['IN', 'notification.type_id', static::getAddTypeList()])
			->andWhere(['is_taken' => false])
			->all();

		foreach ($send_records as $record) {
			$record->setTaken();
		}
		foreach ($send_records as $record) {
			if ($record->type_id == static::QUESTION_ADD) {
				$record->sendQuestion();
			} elseif ($record->type_id == static::ANSWER_ADD) {
				$record->sendAnswer();
			} elseif ($record->type_id == static::COMMENT_ADD) {
				$record->sendComment();
			} elseif ($record->type_id == static::QUESTION_ANSWERED) {
				$record->sendQuestionAnswered();
			} elseif ($record->type_id == static::QUESTION_EDIT) {
				$record->sendQuestionEdit();
			} elseif ($record->type_id == static::ANSWER_EDIT) {
				$record->sendAnswerEdit();
			} elseif ($record->type_id == static::ANSWER_LIKE) {
				$record->sendLikeAnswer();
			} elseif ($record->type_id == static::REPORT_ADD) {
				$record->sendReport();
			}
			$record->delete();
		}
	}

	public function sendQuestion()
	{
		Notification::addQuestion($this->question);
		NotificationBot::addQuestion($this->question);
	}

	public function sendAnswer()
	{
		Notification::addAnswer($this->answer);
		NotificationBot::addAnswer($this->answer);
	}

	public function sendComment()
	{
		if ($this->comment->isForAnswer) {
			Notification::addAnswerComment($this->comment);
			NotificationBot::addAnswerComment($this->comment);
		} else {
			Notification::addQuestionComment($this->comment);
			NotificationBot::addQuestionComment($this->comment);
		}
	}

	public function sendQuestionAnswered()
	{
		Notification::addQuestionAnswered($this->question);
		NotificationBot::addQuestionAnswered($this->question);
	}

	public function sendQuestionEdit()
	{
		Notification::addQuestionEdit($this->question);
		NotificationBot::addQuestionEdit($this->question);
	}

	public function sendAnswerEdit()
	{
		Notification::addAnswerEdit($this->answer);
		NotificationBot::addAnswerEdit($this->answer);
	}

	public function sendLikeAnswer()
	{
		Notification::addLikeAnswer($this->answer, $this->author_id);
		NotificationBot::addLikeAnswer($this->answer, $this->author_id);
	}

	public function sendReport()
	{
		Notification::addReport($this->report);
		NotificationBot::addReport($this->report);
	}

	public function deleteQuestionAnswered()
	{
		Notification::removeQuestionAnswered($this->answer);
	}

	public function deleteLikeAnswer()
	{
		Notification::removeLikeAnswer($this->answer, $this->author_id);
	}

	public function deleteReport()
	{
		Notification::removeReport($this->report);
	}

	public function deleteQuestionAfterChange()
	{
		Notification::removeQuestionAfterChange($this->question);
	}

	public function deleteAnswerAfterChange()
	{
		Notification::removeAnswerAfterChange($this->answer);
	}

	public function deleteCommentAfterChange()
	{
		Notification::removeCommentAfterChange($this->acomment);
	}

}
