<?php

namespace app\models\data;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

use app\models\question\{Question, Answer, Comment};
use app\models\user\User;
use app\models\edu\Discipline;


class RecordInfo extends Model
{
	public static function getUserInfo(string $username, int $question_id = 0, string $discipline_name = '')
	{
		$user = User::find()
			->from(['user' => User::tableName()])
			->joinWith('register')
			->joinWith('data')
			->joinWith('data.badges')
			->joinWith('data.badgeData')
			->joinWith('data.follow')
			->where(['user.username' => $username])
			->one();
		if (empty($user)) {
			return null;
		}

		$discipline_id = 0;
		if (!empty($question_id)) {
			$question = Question::findOne($question_id);
			if (!empty($question->discipline_id)) {
				$discipline_id = $question->discipline_id;
			}
		}
		if (empty($discipline_id) and !empty($discipline_name)) {
			$discipline = Discipline::findByName($question_id);
			if (!empty($discipline->discipline_id)) {
				$discipline_id = $discipline->discipline_id;
			}
		}

		$user_id = $user->id;
		$data = [];
		$data['id'] = $user_id;
		$data['name'] = $user->name;
		$data['username'] = $user->username;
		$data['link'] = $user->getPageLink();
		$data['avatar'] = $user->getAvatar(show_info: false);
		$data['register_datetime'] = $user->register->register_datetime;
		$data['self'] = $user->data->isSelf;
		$data['followers'] = $user->data->followers;
		$data['follow'] = $user->data->isFollowed;
		$data['is_online'] = $user->data->isOnline;
		$data['online_status'] = $user->data->onlineSatus;
		$data['question_count'] = $user->data->badgeData->question_count;

		$data['answer_count'] = $user->data->badgeData->answer_count;
		$data['answer_helped_count'] = $user->data->badgeData->answer_helped_count;

		$data['discipline_answer_count'] = static::getDisciplineAnswerCount($user_id, $discipline_id);
		$data['discipline_answer_helped_count'] = static::getDisciplineAnswerHelpedCount($user_id, $discipline_id);

		$data['rate_sum'] = $user->data->rate_sum;

		$data['badge_platinum_count'] = 0;
		$data['badge_gold_count'] = 0;
		$data['badge_silver_count'] = 0;
		$data['badge_bronze_count'] = 0;

		foreach ($user->data->badges as $record) {
			if ($record->badge->isBronze()) {
				$data['badge_bronze_count']++;
			} elseif ($record->badge->isSilver()) {
				$data['badge_silver_count']++;
			} elseif ($record->badge->isGold()) {
				$data['badge_gold_count']++;
			} elseif ($record->badge->isPlatinum()) {
				$data['badge_platinum_count']++;
			}
		}

		$data['disciplines'] = RecordList::getDisciplineAuthorBestList($user_id, 3);

		return $data;
	}

	public static function getDisciplineAnswerCount(int $user_id, int $discipline_id = 0)
	{
		return Answer::find()
			->select(['COUNT(answer_id) as cnt'])
			->from(['answer' => Answer::tableName()])
			->joinWith('question as question')
			->where(['question.discipline_id' => $discipline_id])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['answer.is_deleted' => false])
			->andWhere(['answer.user_id' => $user_id])
			->column()[0];
	}

	public static function getDisciplineAnswerHelpedCount(int $user_id, int $discipline_id = 0)
	{
		return Answer::find()
			->select(['COUNT(answer_id) as cnt'])
			->from(['answer' => Answer::tableName()])
			->innerJoinWith('question as question')
			->where(['question.discipline_id' => $discipline_id])
			->andWhere(['answer.is_helped' => true])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['answer.is_deleted' => false])
			->andWhere(['answer.user_id' => $user_id])
			->column()[0];
	}

}
