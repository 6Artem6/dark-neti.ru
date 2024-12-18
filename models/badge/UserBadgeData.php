<?php

namespace app\models\badge;

use Yii;
use yii\db\ActiveRecord;

use app\models\question\{Question, Answer, Comment};
use app\models\like\{LikeAnswer, LikeComment};
use app\models\request\Report;
use app\models\user\Subscription;


class UserBadgeData extends ActiveRecord
{

	public static function tableName()
	{
		return 'user_badge_data';
	}

	public function rules()
	{
		return [
			[['user_id'], 'unique'],
			[['user_id', 'question_count', 'answer_count', 'answer_helped_count',
				'comment_count', 'answer_like_count', 'comment_like_count',
				'report_count', 'subscription_count','top_month_count', 'top_year'], 'integer'],
			[['is_registered', 'has_all'], 'boolean'],
		];
	}

 	public static function primaryKey()
	{
		return [
			'user_id'
		];
	}

	public function attributeLabels()
	{
		return [
			'user_id' => Yii::t('app','Пользователь'),
		];
	}

	public function getRecord(int $type, ?int $level = null)
	{
		$record = UserBadge::find()
			->from(['ub' => UserBadge::tableName()])
			->joinWith('badge as b')
			->joinWith('badge.type as bt')
			->where(['bt.type_id' => $type])
			->andWhere(['ub.user_id' => $this->user_id]);
		if (!is_null($level)) {
			$record = $record->andWhere(['b.badge_level' => $level]);
		}
		return $record->one();
	}

	public function createRecord(int $type, ?int $level = null)
	{
		if (is_null($level)) {
			$badge = Badge::getRecordByType($type);
		} else {
			$badge = Badge::getRecordByTypeAndLevel($type, $level);
		}
		$user_badge = new UserBadge;
		$user_badge->badge_id = $badge->badge_id;
		$user_badge->user_id = $this->user_id;
		$user_badge->save();
		Yii::$app->session->setFlash('badge', true);
	}

	public function removeRecord(int $type, ?int $level = null)
	{
		if (is_null($level)) {
			$badge = Badge::getRecordByType($type);
		} else {
			$badge = Badge::getRecordByTypeAndLevel($type, $level);
		}
		$user_badge = UserBadge::find()
			->where(['badge_id' => $badge->badge_id])
			->andWhere(['user_id' => $this->user_id])
			->one();
		if ($user_badge) {
			$user_badge->delete();
			Yii::$app->session->removeFlash('badge');
		}
	}

	public function checkRecord(int $type, ?int $current_level, ?int $level)
	{
		if (!is_null($current_level)) {
			if (!is_null($level)) {
				if ($current_level < $level) {
					for ($l = $current_level; $l < $level; ++$l) {
						UserBadge::removeByUserTypeLevel($this->user_id, $type, $l);
					}
				}
			} else {
				$level_list = Badge::getLevelList($type);
				$level_list = array_keys($level_list);
				foreach ($level_list as $l) {
					UserBadge::removeByUserTypeLevel($this->user_id, $type, $l);
				}
			}
		}
		if (!is_null($level)) {
			$user_badge = $this->getRecord($type, $level);
			if (!$user_badge) {
				$this->createRecord($type, $level);
			}
		}
	}

	public function getUserBadgeCount(int $type)
	{
		return match ($type) {
			BadgeType::TYPE_IS_REGISTERED => (int)$this->is_registered,
			BadgeType::TYPE_QUESTION => $this->question_count,
			BadgeType::TYPE_ANSWER => $this->answer_count,
			BadgeType::TYPE_COMMENT => $this->comment_count,
			BadgeType::TYPE_LIKE => ($this->answer_like_count + $this->comment_like_count),
			BadgeType::TYPE_REPORT => $this->report_count,
			BadgeType::TYPE_SUBSCRIPTION => $this->subscription_count,
			BadgeType::TYPE_TOP_MONTH => $this->top_month_count,
			BadgeType::TYPE_TOP_YEAR => $this->top_year,
			BadgeType::TYPE_HAS_ALL => (int)$this->has_all,
		};
	}

	public function getQuestionLevel()
	{
		$type = BadgeType::TYPE_QUESTION;
		$count = $this->getUserBadgeCount($type);
		return Badge::getLevel($count, $type);
	}

	public function getAnswerLevel()
	{
		$type = BadgeType::TYPE_ANSWER;
		$count = $this->getUserBadgeCount($type);
		return Badge::getLevel($count, $type);
	}

	public function getCommentLevel()
	{
		$type = BadgeType::TYPE_COMMENT;
		$count = $this->getUserBadgeCount($type);
		return Badge::getLevel($count, $type);
	}

	public function getLikeLevel()
	{
		$type = BadgeType::TYPE_LIKE;
		$count = $this->getUserBadgeCount($type);
		return Badge::getLevel($count, $type);
	}

	public function getReportLevel()
	{
		$type = BadgeType::TYPE_REPORT;
		$count = $this->getUserBadgeCount($type);
		return Badge::getLevel($count, $type);
	}

	public function getSubscriptionLevel()
	{
		$type = BadgeType::TYPE_SUBSCRIPTION;
		$count = $this->getUserBadgeCount($type);
		return Badge::getLevel($count, $type);
	}

	public function getTopMonthLevel()
	{
		$type = BadgeType::TYPE_TOP_MONTH;
		$count = $this->getUserBadgeCount($type);
		return Badge::getLevel($count, $type);
	}

	public function getTopYearLevel()
	{
		$type = BadgeType::TYPE_TOP_YEAR;
		$count = $this->getUserBadgeCount($type);
		return Badge::getLevel($count, $type);
	}

	public function getQuestionUserLevel()
	{
		$type = BadgeType::TYPE_QUESTION;
		return UserBadge::getUserBadgeLevel($this->user_id, $type);
	}

	public function getAnswerUserLevel()
	{
		$type = BadgeType::TYPE_ANSWER;
		return UserBadge::getUserBadgeLevel($this->user_id, $type);
	}

	public function getCommentUserLevel()
	{
		$type = BadgeType::TYPE_COMMENT;
		return UserBadge::getUserBadgeLevel($this->user_id, $type);
	}

	public function getLikeUserLevel()
	{
		$type = BadgeType::TYPE_LIKE;
		return UserBadge::getUserBadgeLevel($this->user_id, $type);
	}

	public function getReportUserLevel()
	{
		$type = BadgeType::TYPE_REPORT;
		return UserBadge::getUserBadgeLevel($this->user_id, $type);
	}

	public function getSubscriptionUserLevel()
	{
		$type = BadgeType::TYPE_SUBSCRIPTION;
		return UserBadge::getUserBadgeLevel($this->user_id, $type);
	}

	public function getTopMonthUserLevel()
	{
		$type = BadgeType::TYPE_TOP_MONTH;
		return UserBadge::getUserBadgeLevel($this->user_id, $type);
	}

	public function getTopYearUserLevel()
	{
		$type = BadgeType::TYPE_TOP_YEAR;
		return UserBadge::getUserBadgeLevel($this->user_id, $type);
	}

	public function checkAll()
	{
		$user_list = UserBadge::getUserBadgeIds($this->user_id);
		$type = BadgeType::TYPE_HAS_ALL;
		// if (!in_array($type, $user_list)) {
			$badge_list = Badge::getListForPlatinum();
			if (!array_diff($badge_list, $user_list)) {
				if (!$this->has_all) {
					$this->createRecord($type);
					$this->has_all = true;
					$this->save();
				}
			} else {
				if ($this->has_all) {
					$this->removeRecord($type);
					$this->has_all = false;
					$this->save();
				}
			}
		// }
	}

	public function checkRegister()
	{
		$type = BadgeType::TYPE_IS_REGISTERED;
		$user_badge = $this->getRecord($type);
		if (!$user_badge) {
			$level = Badge::LEVEL_GOLD;
			$this->createRecord($type, $level);
			$this->is_registered = true;
			$this->save();
		}
	}

	public function checkQuestionLevel()
	{
		$type = BadgeType::TYPE_QUESTION;
		$level = $this->getQuestionLevel();
		$current_level = $this->getQuestionUserLevel();
		$this->checkRecord($type, $current_level, $level);
	}

	public function checkAnswerLevel()
	{
		$type = BadgeType::TYPE_ANSWER;
		$level = $this->getAnswerLevel();
		$current_level = $this->getAnswerUserLevel();
		$this->checkRecord($type, $current_level, $level);
	}

	public function checkCommentLevel()
	{
		$type = BadgeType::TYPE_COMMENT;
		$level = $this->getCommentLevel();
		$current_level = $this->getCommentUserLevel();
		$this->checkRecord($type, $current_level, $level);
	}

	public function checkLikeLevel()
	{
		$type = BadgeType::TYPE_LIKE;
		$level = $this->getLikeLevel();
		$current_level = $this->getLikeUserLevel();
		$this->checkRecord($type, $current_level, $level);
	}

	public function checkReportLevel()
	{
		$type = BadgeType::TYPE_REPORT;
		$level = $this->getReportLevel();
		$current_level = $this->getReportUserLevel();
		$this->checkRecord($type, $current_level, $level);
	}

	public function checkSubscriptionLevel()
	{
		$type = BadgeType::TYPE_SUBSCRIPTION;
		$level = $this->getSubscriptionLevel();
		$current_level = $this->getSubscriptionUserLevel();
		$this->checkRecord($type, $current_level, $level);
	}

	public function checkTopMonthLevel()
	{
		$type = BadgeType::TYPE_TOP_MONTH;
		$level = $this->getTopMonthLevel();
		$current_level = $this->getTopMonthUserLevel();
		$this->checkRecord($type, $current_level, $level);
	}

	public function checkTopYear()
	{
		$type = BadgeType::TYPE_TOP_YEAR;
		$user_badge = $this->getRecord($type);
		if (!$user_badge) {
			$level = Badge::LEVEL_GOLD;
			$this->createRecord($type, $level);
		}
	}

	public function updateData()
	{
		$this->checkRegister();
		$this->updateQuestionCount();
		$this->updateAnswerCount();
		$this->updateCommentCount();
		$this->updateAnswerLikeCount();
		$this->updateCommentLikeCount();
		$this->updateTopMonthCount();
		$this->updateReportCount();
		$this->updateSubscriptionCount();
	}

	public function updateQuestionCount()
	{
		$this->question_count = Question::find()
			->where(['user_id' => $this->user_id])
			->andWhere(['is_deleted' => false])
			->andWhere(['is_hidden' => false])
			->count();
		$this->save();
		$this->checkQuestionLevel();
		$this->checkAll();
	}

	public function updateAnswerCount()
	{
		$this->answer_count = Answer::find()
			->where(['user_id' => $this->user_id])
			->andWhere(['is_deleted' => false])
			->andWhere(['is_hidden' => false])
			->count();
		$this->save();
		$this->checkAnswerLevel();
		$this->checkAll();
	}

	public function updateAnswerHelpedCount()
	{
		$this->answer_helped_count = Answer::find()
			->where(['user_id' => $this->user_id])
			->andWhere(['is_helped' => true])
			->andWhere(['is_deleted' => false])
			->andWhere(['is_hidden' => false])
			->count();
		$this->save();
		$this->checkAnswerLevel();
		$this->checkAll();
	}

	public function updateCommentCount()
	{
		$this->comment_count = Comment::find()
			->where(['user_id' => $this->user_id])
			->andWhere(['is_deleted' => false])
			->andWhere(['is_hidden' => false])
			->count();
		$this->save();
		$this->checkCommentLevel();
		$this->checkAll();
	}

	public function updateAnswerLikeCount()
	{
		$this->answer_like_count = count(LikeAnswer::getUserList($this->user_id));
		$this->save();
		$this->checkLikeLevel();
		$this->checkAll();
	}

	public function updateCommentLikeCount()
	{
		$this->comment_like_count = count(LikeComment::getUserList($this->user_id));
		$this->save();
		$this->checkLikeLevel();
		$this->checkAll();
	}

	public function updateReportCount()
	{
		$this->report_count = Report::find()
			->where(['author_id' => $this->user_id])
			->andWhere(['report_status' => Report::STATUS_CLOSED])
			->count();
		$this->save();
		$this->checkReportLevel();
		$this->checkAll();
	}

	public function updateSubscriptionCount()
	{
		$this->subscription_count = count(Subscription::getUserList($this->user_id));
		$this->save();
		$this->checkSubscriptionLevel();
		$this->checkAll();
	}

	public function updateTopMonthCount()
	{
		$this->top_month_count = 0;
		$this->save();
		$this->checkTopMonthLevel();
		$this->checkAll();
	}

	public function updateTopYearCount()
	{
		$this->top_year = 0;
		$this->save();
		$this->checkTopYear();
		$this->checkAll();
	}

}
