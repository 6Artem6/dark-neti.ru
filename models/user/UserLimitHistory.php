<?php

namespace app\models\user;

use Yii;
use yii\db\ActiveRecord;

use app\models\question\{Question, Answer, Comment};
use app\models\request\ReportType;


class UserLimitHistory extends ActiveRecord
{

	public static function tableName()
	{
		return 'user_limit_history';
	}

	public function rules()
	{
		return [
			[['history_limit_id', 'user_id'], 'unique'],
			[['history_limit_id', 'user_id', 'question_id', 'answer_id', 'comment_id'], 'integer'],
			[['question_reason', 'answer_reason', 'comment_reason'], 'each', 'rule' => ['integer']],
			[['question_limit_date', 'answer_limit_date', 'comment_limit_date'], 'date', 'format' => 'php:Y-m-d'],
		];
	}

	public static function primaryKey()
	{
		return [
			'history_limit_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'user_id' => Yii::t('app','Пользователь'),
		];
	}

	public const SCENARIO_QUESTION = 'question';
	public const SCENARIO_ANSWER = 'answer';
	public const SCENARIO_COMMENT = 'comment';

	public function scenarios()
	{
		return array_merge(parent::scenarios(), [
			self::SCENARIO_QUESTION => ['question_id', 'question_reason', 'question_limit_date'],
			self::SCENARIO_ANSWER => ['answer_id', 'answer_reason', 'answer_limit_date'],
			self::SCENARIO_COMMENT => ['comment_id', 'comment_reason', 'comment_limit_date'],
		]);
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

	public function getQuestionList()
	{
		$list = [];
		$type_list = ReportType::getQuestionList();
		foreach ($this->question_reason as $reason) {
			$list[] = $type_list[$reason];
		}
		return $list;
	}

	public function getAnswerList()
	{
		$list = [];
		$type_list = ReportType::getAnswerList();
		foreach ($this->answer_reason as $reason) {
			$list[] = $type_list[$reason];
		}
		return $list;
	}

	public function getCommentList()
	{
		$list = [];
		$type_list = ReportType::getCommentList();
		foreach ($this->comment_reason as $reason) {
			$list[] = $type_list[$reason];
		}
		return $list;
	}

	public function getAllList()
	{
		$list = [];
		$type_list = ReportType::getListsByType();
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

}
