<?php

namespace app\models\user;

use Yii;
use yii\db\ActiveRecord;

use app\models\question\Answer;
use app\models\like\{
	LikeAnswer, LikeComment
};
use app\models\request\{
	DuplicateQuestionRequest, Report
};


class UserRate extends ActiveRecord
{

	public static function tableName()
	{
		return 'user_rate';
	}

	public function rules()
	{
		return [
			[['rate_id'], 'unique'],
			[['rate_id', 'user_id', 'duplicate_request_id', 'answer_helped_id',
				'answer_like_id', 'comment_like_id', 'report_id'], 'integer'],
			[['has_subscription'], 'boolean'],
		];
	}

	public static function primaryKey()
	{
		return [
			'rate_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'rate_id' => Yii::t('app', 'Оценка'),
			'user_id' => Yii::t('app', 'Оцениваемый пользователь'),
		];
	}

	public const DUPLICATE_VALUE = 2;
	public const ANSWER_HELPED_VALUE = 5;
	public const ANSWER_LIKE_VALUE = 4;
	public const COMMENT_LIKE_VALUE = 3;
	public const REPORT_VALUE = 1;

	public const SUBSCRIPTION_X = 1.2;

	public function getUser()
	{
		return $this->hasOne(User::class, ['user_id' => 'user_id']);
	}

	public static function getUserList(int $user_id)
	{
		return self::find()
			->where(['user_id' => $user_id])
			->all();
	}

	public static function getRateSum(int $user_id)
	{
		$sum = 0;
		$list = self::getUserList($user_id);
		foreach ($list as $record) {
			$rate = 0;
			if ($record->duplicate_request_id) {
				$rate = static::DUPLICATE_VALUE;
			} elseif ($record->answer_helped_id) {
				$rate = static::ANSWER_HELPED_VALUE;
			} elseif ($record->answer_like_id) {
				$rate = static::ANSWER_LIKE_VALUE;
			} elseif ($record->comment_like_id) {
				$rate = static::COMMENT_LIKE_VALUE;
			} elseif ($record->report_id) {
				$rate = static::REPORT_VALUE;
			}
			if ($record->has_subscription) {
				$rate *= static::SUBSCRIPTION_X;
			}
			$sum += $rate;
		}
		return $sum;
	}

	public static function addAnswerHelped(Answer $answer) {
		if ($answer->user_id != $answer->question->user_id) {
			$record = new self;
			$record->user_id = $answer->user_id;
			$record->answer_helped_id = $answer->answer_id;
			$record->has_subscription = !empty($answer->author->subscription);
			$record->save();
		}
	}

	public static function addLikeAnswer(LikeAnswer $like) {
		if ($like->user_id != $like->answer->user_id) {
			$record = new self;
			$record->user_id = $like->answer->user_id;
			$record->answer_like_id = $like->like_id;
			$record->has_subscription = !empty($like->answer->author->subscription);
			$record->save();
		}
	}

	public static function addLikeComment(LikeComment $like) {
		if ($like->user_id != $like->comment->user_id) {
			$record = new self;
			$record->user_id = $like->comment->user_id;
			$record->comment_like_id = $like->like_id;
			$record->has_subscription = !empty($like->comment->author->subscription);
			$record->save();
		}
	}

	public static function addDuplicate(DuplicateQuestionRequest $request) {
		if ($request->user_id != $request->question->user_id) {
			$record = new self;
			$record->user_id = $request->user_id;
			$record->duplicate_request_id = $request->request_id;
			$record->has_subscription = !empty($request->author->subscription);
			$record->save();
		}
	}

	public static function addReport(Report $report) {
		if ($report->user_id != $report->record->user_id) {
			$record = new self;
			$record->user_id = $report->user_id;
			$record->report_id = $report->report_id;
			$record->has_subscription = !empty($report->author->subscription);
			$record->save();
		}
	}

	public static function removeAnswerHelped(Answer $answer) {
		$record = self::find()
			->where(['user_id' => $answer->user_id])
			->andWhere(['answer_helped_id' => $answer->answer_id])
			->one();
		if ($record) {
			$record->delete();
		}
	}

	public static function removeLikeAnswer(LikeAnswer $like) {
		$record = self::find()
			->where(['user_id' => $like->answer->user_id])
			->andWhere(['answer_like_id' => $like->like_id])
			->one();
		if ($record) {
			$record->delete();
		}
	}

	public static function removeLikeComment(LikeComment $like) {
		$record = self::find()
			->where(['user_id' => $like->comment->user_id])
			->andWhere(['comment_like_id' => $like->like_id])
			->one();
		if ($record) {
			$record->delete();
		}
	}

	public static function removeDuplicate(DuplicateQuestionRequest $request) {
		$record = self::find()
			->where(['user_id' => $request->user_id])
			->andWhere(['duplicate_request_id' => $request->request_id])
			->one();
		if ($record) {
			$record->delete();
		}
	}

	public static function removeReport(Report $report) {
		$record = self::find()
			->where(['user_id' => $report->user_id])
			->andWhere(['report_id' => $report->report_id])
			->one();
		if ($record) {
			$record->delete();
		}
	}

}
