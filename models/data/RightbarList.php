<?php

namespace app\models\data;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

use app\models\question\{Question, Answer, Comment};
use app\models\user\User;
use app\models\edu\{StudentGroup, GroupToDiscipline, Discipline};


class RightbarList extends Model
{

	protected $discipline_id;

	public const TYPE_DAY = 'day';
	public const TYPE_WEEK = 'week';
	public const TYPE_MONTH = 'month';
	public const TYPE_ALL_TIME = 'all_time';


	public function checkDisciplineByQuestion(int $id)
	{
		$question = Question::findOne($id);
		if (!empty($question->discipline_id)) {
			$this->discipline_id = $question->discipline_id;
		}
	}

	public function checkDisciplineByName(string $discipline_name)
	{
		$discipline = Discipline::findOne(['discipline_name' => $discipline_name]);
		if (!empty($discipline)) {
			$this->discipline_id = $discipline->discipline_id;
		}
	}

	public function getTimeName(string $type)
	{
		return match ($type) {
			static::TYPE_DAY => Yii::t('app', 'За 24 часа'),
			static::TYPE_WEEK => Yii::t('app', 'За неделю'),
			static::TYPE_MONTH => Yii::t('app', 'За месяц'),
			static::TYPE_ALL_TIME => Yii::t('app', 'За всё время'),
		};
	}

	public function getTime(string $type)
	{
		return match ($type) {
			static::TYPE_DAY => date('Y-m-d 00:00:00', strtotime('-24 hours')),
			static::TYPE_WEEK => date('Y-m-d 00:00:00', strtotime('-7 days')),
			static::TYPE_MONTH => date('Y-m-d 00:00:00', strtotime('-30 days')),
			static::TYPE_ALL_TIME => date('2022-01-01 00:00:00'),
		};
	}

	public function getPopularQuestionsList(string $type)
	{
		$user = Yii::$app->user->identity;
		$group = $user->data->group;
		$user_id = $user->id;
		$group_id = $group->group_id;
		$course = $group->course;
		$faculty_id = $group->faculty_id;

		$chair_id = null;
		$discipline = Discipline::find()
			->from(['discipline' => Discipline::tableName()])
			->innerJoinWith('chair')
			->where(['discipline.discipline_id' => $this->discipline_id])
			->one();
		if (!empty($discipline->chair)) {
			$chair_id = $discipline->chair->chair_id;
		}

		$time = $this->getTime($type);
		$limit = 15;
		$id_list = Question::find()
			->select('question.question_id')
			->distinct()
			->from(['question' => Question::tableName()])
			->joinWith(['discipline as d' => function($query) {
				return $query->onCondition(['d.discipline_id' => $this->discipline_id]);
			}], false)
			->joinWith('discipline.followUser as df', false)
			->joinWith(['discipline.chair as c' => function($query) use($chair_id) {
				return $query->onCondition(['c.chair_id' => $chair_id]);
			}], false)
			->leftJoin(['gtd' => GroupToDiscipline::tableName()],
				"(d.discipline_id = gtd.discipline_id) AND (d.discipline_id = :d_id) AND (gtd.group_id = :gtd_g_id)",
				['d_id' => $this->discipline_id, 'gtd_g_id' => $group_id], false
			)
			->leftJoin(['g' => StudentGroup::tableName()],
				"(gtd.group_id = g.group_id) AND (g.group_id = :g_id)",
				['g_id' => $group_id], false
			)
			->where(['>', 'question.question_datetime', $time])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['question.is_hidden' => false])
			->orderBy([
				'd.discipline_id' => SORT_DESC,
				'c.chair_id' => SORT_DESC,
				'df.follower_id' => SORT_DESC,
				'g.group_id' => SORT_DESC,
				'question.answer_count' => SORT_DESC,
				'question.followers' => SORT_DESC,
				'views' => SORT_DESC,
			])
			->limit($limit)
			->column();
		return Question::find()
			->from(['question' => Question::tableName()])
			->where(['IN', 'question.question_id', $id_list])
			->all();
	}

	public function getPopularDisciplinesList(string $type)
	{
		$time = $this->getTime($type);
		$limit = 10;
		$list = Discipline::getDb()->cache(function($db) use($time) {
			return Discipline::find()
				->select([
					'discipline.discipline_id', 'discipline_name', 'discipline.followers',
					'discipline.question_count', 'discipline.question_helped_count',
					'COUNT(question.question_id) as filter_question_count',
					'discipline.icon_id', 'icon.icon_name'
				])
				->from(['discipline' => Discipline::tableName()])
				->joinWith('follow', false)
				->joinWith(['questions as question' => function($query) use($time) {
					return $query->onCondition(['>', 'question.question_datetime', $time]);
				}], false)
				->joinWith('icon as icon')
				->andWhere(['question.is_deleted' => false])
				->andWhere(['question.is_hidden' => false])
				->andWhere(['is_checked' => true])
				->groupBy([
					'discipline.discipline_id', 'discipline_name', 'discipline.followers',
					'discipline.question_count', 'discipline.question_helped_count',
					'discipline.icon_id', 'icon.icon_name'
				])
				->all();
		}, 60 * 60);
		ArrayHelper::multisort($list,
			['filter_question_count', 'question_count', 'question_helped_count', 'followers'],
			SORT_DESC
		);
		return array_slice($list, 0, $limit);
	}

	public function getSimilarDisciplinesList()
	{
		$discipline = Discipline::find()
			->from(['discipline' => Discipline::tableName()])
			->innerJoinWith('chair')
			->where(['discipline.discipline_id' => $this->discipline_id])
			->one();
		if (empty($discipline)) {
			return null;
		}

		$limit = 5;
		$list = Discipline::find()
			->select([
				'discipline.discipline_id', 'discipline_name', 'discipline.followers',
				'discipline.question_count', 'discipline.question_helped_count',
				'COUNT(question.question_id) as filter_question_count'
			])
			->from(['discipline' => Discipline::tableName()])
			->joinWith(['questions as question' => function($query) {
				return $query->onCondition(['question.is_deleted' => false])
					->andOnCondition(['question.is_hidden' => false]);
			}], false)
			->joinWith('chair', false)
			->joinWith('icon as icon')
			->where(['chair.chair_id' => $discipline->chair->chair_id])
			->andWhere(['!=', 'discipline.discipline_id', $this->discipline_id])
			// ->andWhere(['is_checked' => true])
			->groupBy([
				'discipline.discipline_id', 'discipline_name', 'discipline.followers',
				'discipline.question_count', 'discipline.question_helped_count'
			])
			->all();
		ArrayHelper::multisort($list,
			['filter_question_count', 'question_count', 'question_helped_count', 'followers'],
			SORT_DESC
		);

		return array_slice($list, 0, $limit);
	}

	public function getActiveUsersList(string $type)
	{
		$time = $this->getTime($type);
		$limit = 15;
		$answers = Answer::getDb()->cache(function($db) use($time, $limit) {
			return Answer::find()
				->select(['user_id', 'COUNT(answer_id) as ids'])
				->where(['>', 'answer_datetime', $time])
				->andWhere(['is_deleted' => false])
				->groupBy('user_id')
				->orderBy(['ids' => SORT_DESC])
				->limit($limit)
				->column();
		}, 60 * 60);
		$list = $answers;
		$remaining = $limit - count($answers);
		if ($remaining) {
			$comments = Comment::getDb()->cache(function($db) use($time, $list, $remaining) {
				return Comment::find()
					->select(['user_id', 'COUNT(comment_id) as ids'])
					->where(['>', 'comment_datetime', $time])
					->andWhere(['NOT IN', 'user_id', $list])
					->andWhere(['is_deleted' => false])
					->groupBy('user_id')
					->orderBy(['ids' => SORT_DESC])
					->limit($remaining)
					->column();
			}, 60 * 60);
			$list = array_merge($list, $comments);
		}
		return User::getDb()->cache(function($db) use($list) {
			return User::find()
				->from(['user' => User::tableName()])
				->joinWith('data.follow')
				->where(['IN', 'user.user_id', $list])
				->all();
		}, 60 * 60);
	}

	public function getDisciplineBestList(string $type)
	{
		$time = $this->getTime($type);
		$limit = 15;
		$helped = Answer::find()
			->select(['answer.user_id', 'COUNT(answer_id) as ids'])
			->from(['answer' => Answer::tableName()])
			->innerJoinWith(['question as question' => function($query) {
				return $query->onCondition(['question.discipline_id' => $this->discipline_id])
					->andOnCondition(['question.is_deleted' => false])
					->andOnCondition(['question.is_hidden' => false]);
			}], false)
			->andWhere(['>', 'answer_datetime', $time])
			->andWhere(['answer.is_deleted' => false])
			->andWhere(['answer.is_helped' => true])
			->groupBy('user_id')
			->orderBy(['ids' => SORT_DESC])
			->limit($limit)
			->column();
		$list = $helped;
		$remaining = $limit - count($helped);
		if ($remaining) {
			$likes = Answer::find()
				->select(['answer.user_id', 'COUNT(answer_id) as ids'])
				->from(['answer' => Answer::tableName()])
				->innerJoinWith(['question as question' => function($query) {
					return $query->onCondition(['question.discipline_id' => $this->discipline_id])
						->andOnCondition(['question.is_deleted' => false])
						->andOnCondition(['question.is_hidden' => false]);
				}], false)
				->andWhere(['>', 'answer_datetime', $time])
				->andWhere(['NOT IN', 'answer.user_id', $list])
				->andWhere(['answer.is_deleted' => false])
				->groupBy('user_id')
				->orderBy(['ids' => SORT_DESC])
				->limit($remaining)
				->column();
			$list = array_merge($list, $likes);
		}
		return User::find()
			->from(['user' => User::tableName()])
			->joinWith('data')
			->where(['IN', 'user.user_id', $list])
			->all();
	}

	public function getLastAnswerCounts(string $type, array $user_ids)
	{
		$time = $this->getTime($type);
		return Answer::getDb()->cache(function($db) use($time, $user_ids) {
			return Answer::find()
				->select(['COUNT(answer_id) as cnt', 'user_id'])
				->where(['>', 'answer_datetime', $time])
				->andWhere(['is_deleted' => false])
				->andWhere(['IN', 'user_id', $user_ids])
				->groupBy('user_id')
				->indexBy('user_id')
				->column();
		}, 60 * 60);
	}

	public function getLastCommentCounts(string $type, array $user_ids)
	{
		$time = $this->getTime($type);
		return Comment::getDb()->cache(function($db) use($time, $user_ids) {
			return Comment::find()
				->select(['COUNT(comment_id) as cnt', 'user_id'])
				->where(['>', 'comment_datetime', $time])
				->andWhere(['is_deleted' => false])
				->andWhere(['IN', 'user_id', $user_ids])
				->groupBy('user_id')
				->indexBy('user_id')
				->column();
		}, 60 * 60);
	}

	public function getDisciplineBestUsersAnswerCounts(string $type, array $user_ids)
	{
		$time = $this->getTime($type);
		return Answer::find()
			->select(['COUNT(answer_id) as cnt', 'answer.user_id'])
			->from(['answer' => Answer::tableName()])
			->innerJoinWith(['question as question' => function($query) {
				return $query->onCondition(['question.discipline_id' => $this->discipline_id])
					->andOnCondition(['question.is_deleted' => false])
					->andOnCondition(['question.is_hidden' => false]);
			}], false)
			->andWhere(['answer.is_deleted' => false])
			->andWhere(['>', 'answer_datetime', $time])
			->andWhere(['IN', 'answer.user_id', $user_ids])
			->groupBy('answer.user_id')
			->indexBy('answer.user_id')
			->column();
	}

	public function getDisciplineBestUsersAnswerHelpedCounts(string $type, array $user_ids)
	{
		$time = $this->getTime($type);
		return Answer::find()
			->select(['COUNT(answer_id) as cnt', 'answer.user_id'])
			->from(['answer' => Answer::tableName()])
			->innerJoinWith(['question as question' => function($query) {
				return $query->onCondition(['question.discipline_id' => $this->discipline_id])
					->andOnCondition(['question.is_deleted' => false])
					->andOnCondition(['question.is_hidden' => false]);
			}], false)
			->andWhere(['answer.is_deleted' => false])
			->andWhere(['answer.is_helped' => true])
			->andWhere(['>', 'answer_datetime', $time])
			->andWhere(['IN', 'answer.user_id', $user_ids])
			->groupBy('answer.user_id')
			->indexBy('answer.user_id')
			->column();
	}

	public function getDisciplineQuestionCounts(string $type, array $discipline_ids)
	{
		$time = $this->getTime($type);
		return Question::find()
			->select(['COUNT(question.question_id) as cnt', 'question.discipline_id'])
			->from(['question' => Question::tableName()])
			->where(['IN', 'question.discipline_id', $discipline_ids])
			->andWhere(['>', 'question.question_datetime', $time])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['question.is_hidden' => false])
			->groupBy(['question.discipline_id'])
			->indexBy('discipline_id')
			->column();
	}

	public function getDisciplineQuestionHelpedCounts(string $type, array $discipline_ids)
	{
		$time = $this->getTime($type);
		return Answer::find()
			->select(['COUNT(answer.answer_id) as cnt', 'question.discipline_id'])
			->from(['answer' => Answer::tableName()])
			->innerJoinWith(['question as question' => function($query) use($discipline_ids, $time) {
				return $query->onCondition(['IN', 'question.discipline_id', $discipline_ids])
					->andOnCondition(['question.is_deleted' => false])
					->andOnCondition(['question.is_hidden' => false])
					->andOnCondition(['>', 'question.question_datetime', $time]);
			}], false)
			->andWhere(['answer.is_deleted' => false])
			->andWhere(['answer.is_helped' => true])
			->groupBy(['question.discipline_id'])
			->indexBy('discipline_id')
			->column();
	}

}
