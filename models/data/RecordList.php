<?php

namespace app\models\data;

use Yii;
use yii\db\ActiveRecord;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

use app\models\question\{
	Answer, Comment, Question, Tag
};
use app\models\edu\{
	Discipline, Faculty, Schedule, Teacher,
	StudentGroup, GroupToDiscipline
};
use app\models\follow\{FollowDiscipline, FollowQuestion};
use app\models\user\{User, UserData};
use app\models\helpers\{UserHelper, TextHelper};


class RecordList extends ActiveRecord
{

	public static function getUserFollowedList(int $follower_id)
	{
		$list = UserData::find()
			->from(['data' => UserData::tableName()])
			->joinWith('follow')
			->joinWith('user.data')
			->where(['follower_id' => $follower_id])
			->indexBy('user_id')
			->all();

		$q_list = Question::find()
			->select(['count(question.question_id) as question_count', 'question.user_id'])
			->from(['question' => Question::tableName()])
			->where(['question.user_id' => $follower_id])
			->andWhere(['IN', 'question.user_id', array_keys($list)])
			->groupBy(['question.user_id'])
			->indexBy('user_id')
			->column();

		$a_list = Answer::find()
			->select(['count(answer.answer_id) as answer_count', 'answer.user_id'])
			->from(['answer' => Answer::tableName()])
			->where(['answer.user_id' => $follower_id])
			->andWhere(['IN', 'answer.user_id', array_keys($list)])
			->groupBy(['answer.user_id'])
			->indexBy('user_id')
			->column();

		foreach ($q_list as $user_id => $question_count) {
		    $list[$user_id]->author_question_count = $question_count;
		}
		foreach ($a_list as $user_id => $answer_count) {
		    $list[$user_id]->author_answer_count = $answer_count;
		}
		usort($list, function ($a, $b) {
			if ($a->author_question_count == $b->author_question_count) {
				return $b->author_answer_count <=> $a->author_answer_count;
			} else {
				return $b->author_question_count <=> $a->author_question_count;
			}
		});
		return $list;
	}

	public static function getQuestionFeedList(string $discipline_name = '')
	{
		$page_size = 10;
		$user = Yii::$app->user->identity;
		$user_id = $user->id;
		$group_id = $user->data->group_id;
		$chair_id = null;
		$discipline = Discipline::find()
			->from(['discipline' => Discipline::tableName()])
			->innerJoinWith('chair')
			->where(['discipline.discipline_name' => $discipline_name])
			->one();
		if (!empty($discipline->chair)) {
			$chair_id = $discipline->chair->chair_id;
		}

		$week_schedule = Schedule::getGroupCurrentWeekSchedule($group_id);
		$in_list = Question::find()
			->distinct()
			->from(['q' => Question::tableName()])
			->innerJoinWith('author')
			->innerJoinWith(['follow as follow_question' => function($query) use($user_id) {
				return $query->onCondition(['follow_question.follower_id' => $user_id]);
			}])
			->innerJoinWith('discipline.chair')
			->joinWith(['discipline.follow as follow_discipline' => function($query) use($user_id) {
				return $query->onCondition(['follow_discipline.follower_id' => $user_id]);
			}])
			->where(['q.is_deleted' => false])
			->andWhere(['OR',
				['q.user_id' => $user_id],
				['follow_question.follower_id' => $user_id],
				['follow_discipline.follower_id' => $user_id],
			]);
		if ($discipline) {
			$in_list = $in_list->andWhere(['=', 'discipline.discipline_name', $discipline_name]);
		}
		$count_query = clone $in_list;
		$in_list = $in_list->all();

		$pages = new Pagination([
			'totalCount' => $count_query->select('q.question_id')->count(),
			'defaultPageSize' => $page_size,
		]);

		$current_day = date('w');
		usort($in_list, function($a, $b) use($week_schedule, $current_day, $user_id) {
			$a_weight = static::getQuestionRecordFeedWeight($a, $week_schedule, $current_day, $user_id);
			$b_weight = static::getQuestionRecordFeedWeight($b, $week_schedule, $current_day, $user_id);
			if ($a_weight !== $b_weight) {
				$cmp = $b_weight <=> $a_weight;
			} else {
				$cmp = $b->question_datetime <=> $a->question_datetime;
			}
			return $cmp;
		});

		$question_ids = array_column($in_list, 'question_id');
		$question_ids = array_slice($question_ids, $pages->offset, $pages->limit);

		$list = Question::find()
			->distinct()
			->from(['q' => Question::tableName()])
			->joinWith('files')
			->joinWith('type')
			->joinWith('follow')
			->joinWith('faculty')
			->joinWith('author')
			->joinWith('reports')
			->joinWith('tagRecords')
			->joinWith('discipline')
			->joinWith(['answers as answers' => function($query) use($question_ids) {
				return $query->onCondition(['IN', 'answers.question_id', $question_ids]);
			}])
			->where(['IN', 'q.question_id', $question_ids])
			->all();
		return [$list, $pages];
	}

	protected static function getQuestionRecordFeedWeight(Question $record, array $week_schedule,
														  int $current_day, int $user_id)
	{
		$weight = 0;
		if ($record->isAuthor) {
			$weight += 5;
		} elseif ($record->isFollowed) {
			$weight += 3;
		}
		if (!empty($record->discipline) and $record->discipline->isFollowed) {
			$weight += 1;
		}
		foreach ($week_schedule as $item) {
			$chair_id = $record->discipline->chair->chair_id ?? 0;
			$item_chair_id = $item['discipline']['toChair']['chair_id'] ?? 0;
			if ($record->discipline_id == $item['discipline_id']) {
				if ($current_day <= $item['day']) {
					$day = $item['day'] - $current_day;
				} else {
					$day = $item['day'] + 7 - $current_day;
				}
				$diff = 7 - $day;
				$weight += $diff * 1.5;
			} elseif (!empty($chair_id) and !empty($item_chair_id) and
				($chair_id == $item_chair_id)) {
				if ($current_day < $item['day']) {
					$day = $item['day'] - $current_day;
				} else {
					$day = $item['day'] + 7 - $current_day;
				}
				$diff = 7 - $day;
				$weight += pow($diff, 2) * 1;
			}
		}
		return $weight;
	}

	public static function getAuthorLikeList(int $user_id)
	{
		$answers = Answer::find()
			->from(['answer' => Answer::tableName()])
			->joinWith('files')
			->joinWith('question.author')
			->joinWith('question.faculty')
			->joinWith('question.discipline')
			->joinWith('likes as likes')
			->where(['likes.user_id' => $user_id])
			->andWhere(['answer.is_deleted' => false])
			->all();
		$comments = Comment::find()
			->from(['comment' => Comment::tableName()])
			->joinWith('question.author')
			->joinWith('likes as likes')
			->where(['likes.user_id' => $user_id])
			->andWhere(['comment.is_deleted' => false])
			->all();
		return array_merge($answers, $comments);
	}

	public static function getQuestionAuthorList(int $user_id, int $limit = 0)
	{
		$list = Question::find()
			->where(['user_id' => $user_id])
			->orderBy(['question_datetime' => SORT_DESC])
			->indexBy('question_id');
		if ($limit > 0) {
			$list = $list->limit($limit);
		}
		return $list->all();
	}

	public static function getQuestionAuthorBestListAbout(int $user_id, int $limit = 0)
	{
		if ($limit <= 0) {
			$limit = 5;
		}
		return Question::find()
			->from(['q' => Question::tableName()])
			->joinWith('files')
			->joinWith('type')
			->joinWith('follow')
			->joinWith('faculty')
			->joinWith('author')
			->joinWith('reports')
			->joinWith('tagRecords')
			->joinWith('discipline')
			->where(['q.user_id' => $user_id])
			->andWhere(['q.is_deleted' => false])
			->orderBy(['followers' => SORT_DESC, 'is_helped' => SORT_DESC])
			->indexBy('question_id')
			->limit($limit)
			->all();
	}

	public static function getQuestionAuthorBestList(int $user_id, int $limit = 0)
	{
		$in_list = Question::find()
			->distinct()
			->select('q.question_id')
			->from(['q' => Question::tableName()])
			->where(['q.user_id' => $user_id])
			->andWhere(['q.is_deleted' => false])
			->orderBy(['followers' => SORT_DESC, 'is_helped' => SORT_DESC]);
		$count_query = clone $in_list;
		$pages = new Pagination([
			'totalCount' => $count_query->count(),
			'defaultPageSize' => 10,
		]);
		$in_list = $in_list->column();
		$in_list = array_slice($in_list, $pages->offset, $pages->limit);
		$list = Question::find()
			->from(['q' => Question::tableName()])
			->joinWith('files')
			->joinWith('type')
			->joinWith('follow')
			->joinWith('faculty')
			->joinWith('author')
			->joinWith('reports')
			->joinWith('tagRecords')
			->joinWith('discipline')
			->joinWith(['answers as answers' => function($query) use($in_list) {
				return $query->onCondition(['IN', 'answers.question_id', $in_list]);
			}])
			->where(['IN', 'q.question_id', $in_list])
			->indexBy('question_id')
			->all();
		return [$list, $pages];
	}

	public static function getQuestionDisciplineBestListAbout(int $discipline_id, int $limit = 0)
	{
		if ($limit <= 0) {
			$limit = 5;
		}
		return Question::find()
			->from(['q' => Question::tableName()])
			->joinWith('files')
			->joinWith('type')
			->joinWith('follow')
			->joinWith('faculty')
			->joinWith('author')
			->joinWith('reports')
			->joinWith('tagRecords')
			->joinWith('discipline')
			->where(['q.discipline_id' => $discipline_id])
			->andWhere(['q.is_deleted' => false])
			->orderBy(['followers' => SORT_DESC, 'is_helped' => SORT_DESC])
			->indexBy('question_id')
			->limit($limit)
			->all();
	}

	public static function getQuestionDisciplineBestList(int $discipline_id)
	{
		$in_list = Question::find()
			->distinct()
			->select('q.question_id')
			->from(['q' => Question::tableName()])
			->where(['q.discipline_id' => $discipline_id])
			->andWhere(['q.is_deleted' => false])
			->orderBy(['followers' => SORT_DESC, 'is_helped' => SORT_DESC]);
		$count_query = clone $in_list;
		$pages = new Pagination([
			'totalCount' => $count_query->count(),
			'defaultPageSize' => 10,
		]);
		$in_list = $in_list->column();
		$in_list = array_slice($in_list, $pages->offset, $pages->limit);
		$list = Question::find()
			->from(['q' => Question::tableName()])
			->joinWith('files')
			->joinWith('type')
			->joinWith('follow')
			->joinWith('faculty')
			->joinWith('author')
			->joinWith('reports')
			->joinWith('tagRecords')
			->joinWith('discipline')
			->joinWith(['answers as answers' => function($query) use($in_list) {
				return $query->onCondition(['IN', 'answers.question_id', $in_list]);
			}])
			->where(['IN', 'q.question_id', $in_list])
			->orderBy(['followers' => SORT_DESC, 'is_helped' => SORT_DESC])
			->indexBy('question_id')
			->all();
		return [$list, $pages];
	}

	public static function getQuestionAuthorFollowList(int $user_id)
	{
		$in_list = Question::find()
			->distinct()
			->select('q.question_id')
			->from(['q' => Question::tableName()])
			->innerJoinWith('follow as follow')
			->where(['follow.follower_id' => $user_id])
			->andWhere(['q.is_deleted' => false])
			->orderBy(['followers' => SORT_DESC, 'is_helped' => SORT_DESC]);
		$count_query = clone $in_list;
		$pages = new Pagination([
			'totalCount' => $count_query->count(),
			'defaultPageSize' => 10,
		]);
		$in_list = $in_list->column();
		$in_list = array_slice($in_list, $pages->offset, $pages->limit);
		$list = Question::find()
			->from(['q' => Question::tableName()])
			->joinWith('files')
			->joinWith('type')
			->joinWith('follow')
			->joinWith('faculty')
			->joinWith('author')
			->joinWith('reports')
			->joinWith('tagRecords')
			->joinWith('discipline')
			->joinWith(['answers as answers' => function($query) use($in_list) {
				return $query->onCondition(['IN', 'answers.question_id', $in_list]);
			}])
			->where(['IN', 'q.question_id', $in_list])
			->orderBy(['followers' => SORT_DESC, 'is_helped' => SORT_DESC])
			->indexBy('question_id')
			->all();
		return [$list, $pages];
	}

	public static function getDuplicateQuestionList(int $question_id = 0, ?string $search_text = null)
	{

		$limit = 10;
		$search_text = strip_tags($search_text);
		$search_text = TextHelper::remove_emoji($search_text);
		$search_text = TextHelper::remove_multiple_whitespaces($search_text);
		$question = Question::findOne($question_id);

		$duplicate_ids = [];
		if ($question) {
			$duplicate_ids = Question::find()
				->select('dr_q.duplicate_question_id')
				->distinct()
				->from(['q' => Question::tableName()])
				->joinWith('duplicateRequests as dr_q')
				->where(['q.question_id' => $question->id])
				->andWhere(['q.is_deleted' => false])
				->column();
		}

		$list = Question::find()
			->distinct()
			->from(['q' => Question::tableName()])
			->where(['q.is_deleted' => false]);
		if ($question) {
			$list = $list->andWhere(['!=', 'q.question_id', $question->id]);
		}
		if ($search_text) {
			$id = TextHelper::getIdFromUrl($search_text, ['question', 'answer']);
			if ($id) {
				$list = $list->andWhere(['q.question_id' => $id]);
			} else {
				$list = $list->andWhere(['OR',
					['LIKE', 'q.question_title', $search_text],
					['LIKE', 'q.question_text', $search_text]
				]);
			}
		} elseif($question) {
			$words = explode(' ', $question->question_title);
			$where = ['OR'];
			foreach ($words as $word) {
				$where[] = ['LIKE', 'q.question_title', $word];
			}
			$list = $list->andWhere($where);
		}
		$list = $list->all();

		usort($list, function($a, $b) use($duplicate_ids) {
			if ($duplicate_ids) {
				if (in_array($b->id, $duplicate_ids) or in_array($a->id, $duplicate_ids)) {
					return in_array($b->id, $duplicate_ids) <=> in_array($a->id, $duplicate_ids);
				}
			}
			if ($b->is_helped != $a->is_helped) {
				return $b->is_helped <=> $a->is_helped;
			}
			return $b->answer_count <=> $a->answer_count;
		});

		if (count($list) > $limit) {
			$list = array_slice($list, 0, $limit);
		}

		$result = [];
		foreach ($list as $record) {
		    $result[] = [
		    	'model' => $record,
		    	'is_reported' => in_array($record->id, $duplicate_ids),
		    ];
		}
		return $result;
	}

	public static function getDuplicateQuestionRequestList(int $question_id = 0)
	{
		$result = [];
		$question = Question::find()
			->distinct()
			->from(['q' => Question::tableName()])
			->joinWith('duplicateRequests')
			->where(['q.question_id' => $question_id])
			->andWhere(['q.is_deleted' => false])
			->one();
		if (empty($question)) {
			return $result;
		}
		if (!$question->isAuthor) {
			return $result;
		}
		if ($list = $question->duplicateRequests) {
			foreach ($list as $record) {
			    $result[] = $record;
			}
		}
		return $result;
	}

	public static function getAnswerAuthorBestListAbout(int $user_id, int $limit = 0)
	{
		if ($limit <= 0) {
			$limit = 5;
		}
		return Answer::find()
			->innerJoinWith('question')
			->joinWith('question.faculty')
			->joinWith('question.discipline')
			->joinWith('likes')
			->joinWith('author')
			->joinWith('files')
			->where(['answer.user_id' => $user_id])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['question.is_hidden' => false])
			->andWhere(['answer.is_deleted' => false])
			->andWhere(['answer.is_hidden' => false])
			->orderBy(['like_count' => SORT_DESC])
			->indexBy('answer_id')
			->limit($limit)
			->all();
	}

	public static function getAnswerAuthorBestList(int $user_id)
	{
		$in_list = Answer::find()
			->distinct()
			->select('answer.answer_id')
			->from(['answer' => Answer::tableName()])
			->innerJoinWith('question as question')
			->where(['answer.user_id' => $user_id])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['question.is_hidden' => false])
			->andWhere(['answer.is_deleted' => false])
			->andWhere(['answer.is_hidden' => false])
			->orderBy(['like_count' => SORT_DESC]);
		$count_query = clone $in_list;
		$pages = new Pagination([
			'totalCount' => $count_query->count(),
			'defaultPageSize' => 10,
		]);
		$in_list = $in_list->column();
		$in_list = array_slice($in_list, $pages->offset, $pages->limit);
		$list = Answer::find()
			->from(['answer' => Answer::tableName()])
			->innerJoinWith('question')
			->joinWith('question.faculty')
			->joinWith('question.discipline')
			->joinWith('comments')
			->joinWith('comments.reports')
			->joinWith('comments.likes')
			->joinWith('likes')
			->joinWith('author')
			->joinWith('files')
			->where(['IN', 'answer.answer_id', $in_list])
			->orderBy(['answer.like_count' => SORT_DESC])
			->indexBy('question_id')
			->all();
		return [$list, $pages];
	}

	public static function getAnswerDisciplineBestListAbout(int $discipline_id, int $limit = 0)
	{
		if ($limit <= 0) {
			$limit = 5;
		}
		return Answer::find()
			->innerJoinWith('question')
			->joinWith('question.faculty')
			->joinWith('question.discipline')
			->joinWith('likes')
			->joinWith('author')
			->joinWith('files')
			->where(['question.discipline_id' => $discipline_id])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['question.is_hidden' => false])
			->andWhere(['answer.is_deleted' => false])
			->andWhere(['answer.is_hidden' => false])
			->orderBy(['like_count' => SORT_DESC])
			->indexBy('answer_id')
			->limit($limit)
			->all();
	}

	public static function getAnswerDisciplineBestList(int $discipline_id)
	{
		$in_list = Answer::find()
			->distinct()
			->select('answer.answer_id')
			->from(['answer' => Answer::tableName()])
			->innerJoinWith('question as question')
			->where(['question.discipline_id' => $discipline_id])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['question.is_hidden' => false])
			->andWhere(['answer.is_deleted' => false])
			->andWhere(['answer.is_hidden' => false])
			->orderBy(['like_count' => SORT_DESC]);
		$count_query = clone $in_list;
		$pages = new Pagination([
			'totalCount' => $count_query->count(),
			'defaultPageSize' => 10,
		]);
		$in_list = $in_list->column();
		$in_list = array_slice($in_list, $pages->offset, $pages->limit);
		$list = Answer::find()
			->from(['answer' => Answer::tableName()])
			->innerJoinWith('question')
			->joinWith('question.faculty')
			->joinWith('question.discipline')
			->joinWith('comments')
			->joinWith('comments.reports')
			->joinWith('comments.likes')
			->joinWith('likes')
			->joinWith('author')
			->joinWith('files')
			->where(['IN', 'answer.answer_id', $in_list])
			->orderBy(['answer.like_count' => SORT_DESC])
			->indexBy('question_id')
			->all();
		return [$list, $pages];
	}

	public static function getCommentAuthorBestList(int $user_id)
	{
		$in_list = Comment::find()
			->distinct()
			->from(['comment' => Comment::tableName()])
			->innerJoinWith('question', false)
			->joinWith('answer', false)
			->where(['comment.user_id' => $user_id])
			->andWhere(['IS', 'comment.answer_id', NULL])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['question.is_hidden' => false])
			->andWhere(['comment.is_deleted' => false])
			->andWhere(['comment.is_hidden' => false])
			->orderBy(['comment.like_count' => SORT_DESC]);
		$count_query = clone $in_list;
		$pages = new Pagination([
			'totalCount' => $count_query->count(),
			'defaultPageSize' => 10,
		]);
		$in_list = $in_list->select(['comment.comment_id'])->column();
		$in_list = array_slice($in_list, $pages->offset, $pages->limit);

		$list = Comment::find()
			->distinct()
			->from(['comment' => Comment::tableName()])
			->innerJoinWith('question', false)
			->innerJoinWith('question.faculty', false)
			->innerJoinWith('question.discipline', false)
			->joinWith('answer', false)
			->joinWith('reports', false)
			->joinWith('likes', false)
			->where(['IN', 'comment.comment_id', $in_list])
			->orderBy(['like_count' => SORT_DESC])
			->indexBy('comment_id')
			->all();
		return [$list, $pages];
	}

	public static function getCommentDisciplineBestList(int $discipline_id)
	{
		$in_list = Comment::find()
			->distinct()
			->from(['comment' => Comment::tableName()])
			->innerJoinWith('question', false)
			->joinWith('answer', false)
			->where(['question.discipline_id' => $discipline_id])
			->andWhere(['IS', 'comment.answer_id', NULL])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['question.is_hidden' => false])
			->andWhere(['comment.is_deleted' => false])
			->andWhere(['comment.is_hidden' => false])
			->orderBy(['comment.like_count' => SORT_DESC]);
		$count_query = clone $in_list;
		$pages = new Pagination([
			'totalCount' => $count_query->count(),
			'defaultPageSize' => 10,
		]);
		$in_list = $in_list->select(['comment.comment_id'])->column();
		$in_list = array_slice($in_list, $pages->offset, $pages->limit);

		$list = Comment::find()
			->distinct()
			->from(['comment' => Comment::tableName()])
			->innerJoinWith('question', false)
			->innerJoinWith('question.faculty', false)
			->innerJoinWith('question.discipline', false)
			->joinWith('answer', false)
			->joinWith('reports', false)
			->joinWith('likes', false)
			->where(['IN', 'comment.comment_id', $in_list])
			->orderBy(['like_count' => SORT_DESC])
			->indexBy('comment_id')
			->all();
		return [$list, $pages];
	}

	public static function getDisciplineAuthorBestList(int $user_id, int $limit = 0)
	{
		$q_list = Question::find()
			->select(['count(question.question_id) as question_count', 'question.discipline_id'])
			->from(['question' => Question::tableName()])
			->joinWith('discipline as discipline')
			->where(['question.user_id' => $user_id])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['question.is_hidden' => false])
			->groupBy(['question.discipline_id'])
			->indexBy('question.discipline_id')
			->column();
		$a_list = Answer::find()
			->select(['count(answer.answer_id) as answer_count', 'q_question.discipline_id'])
			->from(['answer' => Answer::tableName()])
			->joinWith('question as q_question')
			->joinWith('question.discipline as discipline')
			->where(['answer.user_id' => $user_id])
			->andWhere(['answer.is_deleted' => false])
			->andWhere(['answer.is_hidden' => false])
			->andWhere(['q_question.is_deleted' => false])
			->andWhere(['q_question.is_hidden' => false])
			->groupBy(['discipline_id'])
			->indexBy('q_question.discipline_id')
			->column();
		$discipline_ids = array_merge(
			array_keys($q_list),
			array_keys($a_list)
		);

		$list = [];
		foreach ($discipline_ids as $discipline_id) {
		    $list[$discipline_id] = [
		    	'discipline_id' => $discipline_id,
		    	'question_count' => 0,
				'answer_count' => 0,
		    ];
		}

		foreach ($q_list as $discipline_id => $question_count) {
		    $list[$discipline_id]['question_count'] = $question_count;
		}
		foreach ($a_list as $discipline_id => $answer_count) {
		    $list[$discipline_id]['answer_count'] = $answer_count;
		}

		usort($list, function ($a, $b) {
			if ($a['answer_count'] == $b['answer_count']) {
				return $b['question_count'] <=> $a['question_count'];
			} else {
				return $b['answer_count'] <=> $a['answer_count'];
			}
		});

		if ($limit > 0) {
			$list = array_slice($list, 0, $limit);
		}

		$result = Discipline::find()
			->from(['discipline' => Discipline::tableName()])
			->where(['IN', 'discipline_id', $discipline_ids])
			->indexBy('discipline_id')
			->all();

		foreach ($list as $record) {
			if (isset($result[ $record['discipline_id'] ])) {
				$result[ $record['discipline_id'] ]->filter_question_count = $record['question_count'];
				$result[ $record['discipline_id'] ]->filter_answer_count = $record['answer_count'];
			}
		}

		usort($result, function ($a, $b) {
			if ($a->filter_answer_count == $b->filter_answer_count) {
				return $b->filter_question_count <=> $a->filter_question_count;
			} else {
				return $b->filter_answer_count <=> $a->filter_answer_count;
			}
		});

		if ($limit > 0) {
			$result = array_slice($result, 0, $limit);
		}

		return $result;
	}

	public static function getDisciplineUserBestList(int $discipline_id, int $limit = 0)
	{
		$helped = Answer::find()
			->select(['answer.user_id', 'COUNT(answer_id) as ids'])
			->from(['answer' => Answer::tableName()])
			->joinWith('question as q_question')
			->joinWith('question.discipline as discipline')
			->where(['discipline.discipline_id' => $discipline_id])
			->andWhere(['question.is_deleted' => false])
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
				->joinWith('question as q_question')
				->joinWith('question.discipline as discipline')
				->where(['discipline.discipline_id' => $discipline_id])
				->andWhere(['NOT IN', 'answer.user_id', $list])
				->andWhere(['question.is_deleted' => false])
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

	public static function getUserDisciplineQuestionCounts(array $user_ids, int $discipline_id = 0)
	{
		return Question::find()
			->select(['COUNT(question_id) as cnt', 'question.user_id'])
			->from(['question' => Question::tableName()])
			->joinWith('discipline as discipline')
			->where(['discipline.discipline_id' => $discipline_id])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['IN', 'question.user_id', $user_ids])
			->indexBy('question.user_id')
			->column();
	}

	public static function getUserDisciplineAnswerCounts(array $user_ids, int $discipline_id = 0)
	{
		return Answer::find()
			->select(['COUNT(answer_id) as cnt', 'answer.user_id'])
			->from(['answer' => Answer::tableName()])
			->joinWith('question as q_question')
			->joinWith('question.discipline as discipline')
			->where(['discipline.discipline_id' => $discipline_id])
			->andWhere(['q_question.is_deleted' => false])
			->andWhere(['answer.is_deleted' => false])
			->andWhere(['IN', 'answer.user_id', $user_ids])
			->indexBy('answer.user_id')
			->column();
	}

	public static function getUserDisciplineAnswerHelpedCounts(array $user_ids, int $discipline_id = 0)
	{
		return Answer::find()
			->select(['COUNT(answer_id) as cnt', 'answer.user_id'])
			->from(['answer' => Answer::tableName()])
			->joinWith('question as q_question')
			->joinWith('question.discipline as discipline')
			->where(['discipline.discipline_id' => $discipline_id])
			->andWhere(['q_question.is_deleted' => false])
			->andWhere(['answer.is_deleted' => false])
			->andWhere(['answer.is_helped' => true])
			->andWhere(['IN', 'answer.user_id', $user_ids])
			->indexBy('answer.user_id')
			->column();
	}

	public static function getDisciplinesPreferredList()
	{
		$year = UserHelper::getYear();
		$semestr = UserHelper::getSemestr();
		$user_id = Yii::$app->user->identity->id;
		return Discipline::find()
			->distinct()
			->from(['discipline' => Discipline::tableName()])
			->joinWith('follow as follow')
			->joinWith('toGroup as tg')
			->joinWith('groups.students')
			// ->where(['follow.follower_id' => $user_id])
			->orWhere(['AND',
				['user_id' => $user_id],
				['tg.year' => $year],
				['tg.semestr' => $semestr],
			])
			->all();
	}

	public static function getDisciplinesUserBestList(array $discipline_ids, int $limit = 0)
	{
		$user = Yii::$app->user->identity;
		$user_id = $user->id;
		$group_id = $user->data->group_id;

		$helped = Answer::find()
			->select(['answer.user_id', 'COUNT(answer_id) as ids'])
			->from(['answer' => Answer::tableName()])
			->joinWith('question as q_question')
			->joinWith('question.discipline as discipline')
			->where(['IN', 'discipline.discipline_id', $discipline_ids])
			->andWhere(['!=', 'answer.user_id', $user_id])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['answer.is_deleted' => false])
			->andWhere(['answer.is_helped' => true])
			->groupBy('user_id')
			->orderBy(['ids' => SORT_DESC])
			->limit($limit)
			->column();
		$list = $helped;
		$remaining = $limit - count($list);
		if ($remaining) {
			$likes = Answer::find()
				->select(['answer.user_id', 'COUNT(answer_id) as ids'])
				->from(['answer' => Answer::tableName()])
				->joinWith('question as q_question')
				->joinWith('question.discipline as discipline')
				->where(['IN', 'discipline.discipline_id', $discipline_ids])
				->andWhere(['!=', 'answer.user_id', $user_id])
				->andWhere(['NOT IN', 'answer.user_id', $list])
				->andWhere(['question.is_deleted' => false])
				->andWhere(['answer.is_deleted' => false])
				->groupBy('user_id')
				->orderBy(['ids' => SORT_DESC])
				->limit($remaining)
				->column();
			$list = array_merge($list, $likes);
			$remaining = $remaining - count($list);
		}
		if ($remaining) {
			$users = User::find()
				->select(['user.user_id'])
				->from(['user' => User::tableName()])
				->joinWith('data.badgeData as bd')
				->where(['group_id' => $group_id])
				->andWhere(['NOT IN', 'user.user_id', $list])
				->andWhere(['!=', 'user.user_id', $user_id])
				->orderBy([
					'answer_helped_count' => SORT_DESC,
					'answer_count' => SORT_DESC,
					'question_count' => SORT_DESC,
				])
				->limit($remaining)
				->column();
			$list = array_merge($list, $users);
		}
		return User::find()
			->from(['user' => User::tableName()])
			->joinWith('data')
			->where(['IN', 'user.user_id', $list])
			->all();
	}

	public static function getDisciplineQuestionList($discipline_name = "", bool $is_search = false)
	{

		$discipline_name = (string)$discipline_name;
		$discipline_name = trim($discipline_name);

		$user = Yii::$app->user->identity;
		$user_id = $user->id;
		$group_id = $user->data->group_id;

		$weight_d = Discipline::find()
			->select(['count(discipline.discipline_id) as cnt', 'discipline_name'])
			->from(['discipline' => Discipline::tableName()])
			->joinWith('groups as groups')
			->where(['groups.group_id' => $group_id])
			->groupBy('discipline_name')
			->orderBy(['cnt' => SORT_DESC])
			->indexBy('discipline_name')
			->column();
		$weight_q = Question::find()
			->select(['count(question_id) as cnt', 'discipline.discipline_name'])
			->from(['question' => Question::tableName()])
			->joinWith('discipline as discipline')
			->where(['user_id' => $user_id])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['question.is_hidden' => false])
			->groupBy('discipline.discipline_name')
			->orderBy(['cnt' => SORT_DESC])
			->indexBy('discipline.discipline_name')
			->column();
		$weight_f = FollowDiscipline::find()
			->select(['count(discipline.discipline_id) as cnt', 'discipline.discipline_name'])
			->joinWith('discipline as discipline')
			->where(['follower_id' => $user_id])
			->groupBy('discipline.discipline_name')
			->orderBy(['cnt' => SORT_DESC])
			->indexBy('discipline.discipline_name')
			->column();

		$result = [];
		if ($is_search) {
			$list = Question::find()
				->select(['discipline.discipline_name'])
				->distinct()
				->innerJoinWith(['discipline as discipline'])
				// ->where(['is_checked' => true])
				->orderBy('discipline_name')
				->indexBy('discipline_name')
				->asArray()
				->all();
		} else {
			$list = Discipline::find()
				->select('discipline_name')
				->distinct()
				->orderBy('discipline_name')
				->indexBy('discipline_name')
				->asArray()
				->all();
		}

		foreach ($list as $key => $value) {
			if (!empty($list[$key])) {
				$weight = 0;
				if (!empty($weight_d[$key])) {
					$weight += $weight_d[$key] * 10;
				}
				if (!empty($weight_q[$key])) {
					$weight += $weight_q[$key] * 5;
				}
				if (!empty($weight_f[$key])) {
					$weight += $weight_f[$key] * 3;
				}
				$list[$key]['weight'] = $weight;
			}
		}

		usort($list, function($a, $b) {
			if ($b['weight'] == $a['weight']) {
				return $a['discipline_name'] <=> $b['discipline_name'];
			} else {
				return $b['weight'] <=> $a['weight'];
			}
		});
		$list = ArrayHelper::map($list, 'discipline_name', 'discipline_name');

		if ($is_search) {
			$result[null] = Yii::t('app', 'Все предметы');
		}
		if ($discipline_name) {
			$result[$discipline_name] = $discipline_name;
		}
		foreach ($list as $key => $value) {
			$result[$key] = $value;
		}

		return $result;
	}

	public static function getDisciplineTagList(bool $add_all = false)
	{
		$weight_t = Tag::find()
			->select(['count(discipline.discipline_id) as cnt', 'discipline.discipline_id'])
			->joinWith('discipline as discipline')
			->groupBy('discipline.discipline_id')
			->orderBy(['cnt' => SORT_DESC])
			->indexBy('discipline.discipline_id')
			->column();

		$result = [];
		$list = Discipline::getDb()->cache(function ($db) {
			return Discipline::find()
				->select(['discipline_name', 'discipline_id'])
				->where(['is_checked' => true])
				->indexBy('discipline_id')
				->asArray()
				->all();
		});

		foreach ($list as $key => $value) {
			$weight = 0;
			if (!empty($weight_t[$key])) {
				$weight += $weight_t[$key];
			}
			$list[$key]['weight'] = $weight;
		}

		usort($list, function($a, $b) {
			if ($b['weight'] == $a['weight']) {
				return $a['discipline_name'] <=> $b['discipline_name'];
			} else {
				return $b['weight'] <=> $a['weight'];
			}
		});
		$list = ArrayHelper::map($list, 'discipline_id', 'discipline_name');

		if ($add_all) {
			$result[-1] = Yii::t('app', 'Все предметы');
		}
		foreach ($list as $key => $value) {
			$result[$key] = $value;
		}

		return $result;
	}

	public static function getDisciplineFollowedList(int $follower_id)
	{
		$list = Discipline::find()
			->from(['d' => Discipline::tableName()])
			->joinWith('follow')
			->where(['follower_id' => $follower_id])
			->indexBy('discipline_id')
			->all();
		$q_list = Question::find()
			->select(['count(question_id) as question_count', 'question.discipline_id'])
			->from(['question' => Question::tableName()])
			->joinWith('discipline as discipline')
			->where(['question.user_id' => $follower_id])
			->andWhere(['IN', 'discipline.discipline_id', array_column($list, 'discipline_id')])
			->andWhere(['question.is_deleted' => false])
			->andWhere(['question.is_hidden' => false])
			->groupBy(['question.discipline_id'])
			->indexBy('question.discipline_id')
			->column();
		$a_list = Answer::find()
			->select(['count(answer.answer_id) as answer_count', 'q_question.discipline_id'])
			->from(['answer' => Answer::tableName()])
			->joinWith('question as q_question')
			->joinWith('question.discipline as discipline')
			->where(['answer.user_id' => $follower_id])
			->andWhere(['IN', 'discipline.discipline_id', array_column($list, 'discipline_id')])
			->andWhere(['answer.is_deleted' => false])
			->andWhere(['answer.is_hidden' => false])
			->andWhere(['q_question.is_deleted' => false])
			->andWhere(['q_question.is_hidden' => false])
			->groupBy(['q_question.discipline_id'])
			->indexBy('q_question.discipline_id')
			->column();

		foreach ($q_list as $discipline_id => $question_count) {
		    $list[$discipline_id]->filter_question_count = $question_count;
		}
		foreach ($a_list as $discipline_id => $answer_count) {
		    $list[$discipline_id]->filter_answer_count = $answer_count;
		}
		usort($list, function ($a, $b) {
			if ($a->filter_question_count == $b->filter_question_count) {
				return $b->filter_answer_count <=> $a->filter_answer_count;
			} else {
				return $b->filter_question_count <=> $a->filter_question_count;
			}
		});
		return $list;
	}

	public static function getDisciplineFollowedUserList()
	{
		return Discipline::find()
			->distinct()
			->joinWith('followUser as followUser')
			->joinWith('follow as follow', false)
			// ->joinWith('questions as question', false)
			->orderBy([
				'follow.follower_id' => SORT_DESC,
				// 'question.question_datetime' => SORT_DESC,
				'question_count' => SORT_DESC,
				'question_helped_count' => SORT_DESC
			])
			->limit(5)
			->all();
	}

	public static function getDisciplineChairList(int $discipline_id, int $limit = 0)
	{
		if ($limit < 0) {
			$limit = 0;
		}
		$list = [];
		$discipline = Discipline::findOne($discipline_id);
		if (!empty($discipline->chair->chair_id)) {
			$chair_id = $discipline->chair->chair_id;
			$list = Discipline::find()
				->distinct()
				->from(['discipline' => Discipline::tableName()])
				->joinWith('followUser as followUser')
				->joinWith('follow as follow')
				->joinWith('questions as question')
				->joinWith('chair as chair')
				->where(['chair.chair_id' => $chair_id])
				->andWhere(['!=', 'discipline.discipline_id', $discipline_id])
				->orderBy([
					'follow.follower_id' => SORT_DESC,
					'followers' => SORT_DESC,
					'question.question_datetime' => SORT_DESC,
					'question_count' => SORT_DESC,
					'question_helped_count' => SORT_DESC,
					'discipline.discipline_name' => SORT_ASC,
				]);
			if ($limit) {
				$list = $list->limit($limit);
			}
			$list = $list->all();
		}
		return $list;
	}

	public static function getDisciplineQuestionCounts(array $discipline_ids, int $limit = 0)
	{
		if ($limit <= 0) {
			$limit = 5;
		}
		$list = Discipline::find()
			->distinct()
			->from(['discipline' => Discipline::tableName()])
			->where(['IN', 'discipline.discipline_id', $discipline_ids])
			->orderBy(['question_helped_count' => SORT_DESC, 'question_count' => SORT_DESC])
			->indexBy('discipline_id');
		if ($limit) {
			$list = $list->limit($limit);
		}
		$list = $list->all();
		return $list;
	}

	public static function getTeacherListByDiscipline(string $discipline_name = '', int $faculty_id = 0, string $q = '')
	{
		if ($discipline_name) {
			$list = static::getTeacherListWithDiscipline($discipline_name, $faculty_id, $q);
		} else {
			$list = static::getTeacherListWithoutDiscipline($faculty_id, $q);
		}
		$list = array_map(function($record) {
			return [
				'id' => $record['teacher_id'],
				'text' => $record['teacher_fullname']
			];
		}, $list);
		$list = array_unique($list, SORT_REGULAR);
		return $list;
	}

	public static function getTeacherListWithDiscipline(string $discipline_name = '', int $faculty_id = 0, string $q = '')
	{
		$user = Yii::$app->user->identity;
		$user_id = $user->id;
		$group = $user->data->group;
		$group_id = $group->group_id;
		$course = $group->course;
		$faculty_ids = array_keys(Faculty::getList());
		if (!in_array($faculty_id, $faculty_ids)) {
			$faculty_id = $group->faculty_id;
		}
		$list = Teacher::find()
			->select([
				't.teacher_id',
				't.teacher_fullname',
				'g.group_name',
				'f.faculty_fullname',
				'ISNULL(gc.group_name) as group_course',
				'd.discipline_name',
				'ts.teacher_fullname'
			])
			->distinct()
			->from(['t' => Teacher::tableName()])
			->joinWith(['groups as g' => function($query) use($group_id) {
				return $query->onCondition(['g.group_id' => $group_id]);
			}])
			->joinWith(['groups as gc' => function($query) use($course, $faculty_id) {
				return $query->onCondition(['gc.course' => $course])
					->andOnCondition(['gc.faculty_id' => $faculty_id]);
			}])
			->joinWith(['groups.faculty as f' => function($query) use($faculty_id) {
				return $query->onCondition(['f.faculty_id' => $faculty_id]);
			}])
			->joinWith(['disciplines as d' => function($query) use($discipline_name) {
				return $query->onCondition(['LIKE', 'discipline_name', $discipline_name]);
			}])
			->joinWith('disciplines.teachers as ts')
			->andWhere(['LIKE', 't.teacher_fullname', $q])
			->orderBy([
				'd.discipline_name' => SORT_DESC,
				'g.group_name' => SORT_DESC,
				'group_course' => SORT_ASC,
				'f.faculty_fullname' => SORT_DESC,
				'ts.teacher_fullname' => SORT_ASC,
				't.teacher_fullname' => SORT_ASC,
			])
			->groupBy([
				't.teacher_id',
				't.teacher_fullname',
				'g.group_name',
				'f.faculty_fullname',
				'ISNULL(gc.group_name)',
				'd.discipline_name',
				'ts.teacher_fullname'
			])
			->asArray()
			->all();
		return $list;
	}

	public static function getTeacherListWithoutDiscipline(int $faculty_id = 0, string $q = '')
	{
		$user = Yii::$app->user->identity;
		$user_id = $user->id;
		$group = $user->data->group;
		$group_id = $group->group_id;
		$course = $group->course;
		$faculty_ids = array_keys(Faculty::getList());
		if (!in_array($faculty_id, $faculty_ids)) {
			$faculty_id = $group->faculty_id;
		}
		$list = Teacher::find()
			->select([
				't.teacher_id',
				't.teacher_fullname',
				'g.group_name',
				'f.faculty_fullname',
				'ISNULL(gc.group_name) as group_course',
			])
			->distinct()
			->from(['t' => Teacher::tableName()])
			->joinWith(['groups as g' => function($query) use($group_id) {
				return $query->onCondition(['g.group_id' => $group_id]);
			}])
			->joinWith(['groups as gc' => function($query) use($course, $faculty_id) {
				return $query->onCondition(['gc.course' => $course])
					->andOnCondition(['gc.faculty_id' => $faculty_id]);
			}])
			->joinWith(['groups.faculty as f' => function($query) use($faculty_id) {
				return $query->onCondition(['f.faculty_id' => $faculty_id]);
			}])
			->andWhere(['LIKE', 't.teacher_fullname', $q])
			->orderBy([
				'g.group_name' => SORT_DESC,
				'group_course' => SORT_ASC,
				'f.faculty_fullname' => SORT_DESC,
				't.teacher_fullname' => SORT_ASC,
			])
			->groupBy([
				't.teacher_id',
				't.teacher_fullname',
				'g.group_name',
				'f.faculty_fullname',
				'ISNULL(gc.group_name)',
			])
			->asArray()
			->all();
		return $list;
	}

	public static function getTeacherQuestionList(bool $add_all = false)
	{
		$user_id = Yii::$app->user->identity->id;
		$weight_q = Teacher::find()
			->select(['count(question.question_id) as cnt', 'teacher.teacher_id'])
			->from(['teacher' => Teacher::tableName()])
			->joinWith('questions as question')
			->where(['question.user_id' => $user_id])
			->groupBy('teacher.teacher_id')
			->orderBy(['cnt' => SORT_DESC])
			->indexBy('teacher.teacher_id')
			->asArray()
			->column();

		$result = [];

		$list = Teacher::find()
			->select(['teacher_fullname', 'teacher_id'])
			// ->where(['is_checked' => true])
			->orderBy('teacher_fullname')
			->indexBy('teacher_id')
			->asArray()
			->all();

		foreach ($list as $key => $value) {
			$weight = 0;
			if (!empty($weight_q[$key])) {
				$weight += $weight_q[$key];
			}
			$list[$key]['weight'] = $weight;
		}

		usort($list, function($a, $b) {
			if ($b['weight'] == $a['weight']) {
				return $a['teacher_fullname'] <=> $b['teacher_fullname'];
			} else {
				return $b['weight'] <=> $a['weight'];
			}
		});
		$list = ArrayHelper::map($list, 'teacher_id', 'teacher_fullname');

		if ($add_all) {
			$result[null] = Yii::t('app', 'Все преподаватели');
		}
		foreach ($list as $key => $value) {
			$result[$key] = $value;
		}

		return $result;
	}

	public static function getTeacherQuestionCounts(array $teacher_ids, int $limit = 0)
	{
		if ($limit <= 0) {
			$limit = 5;
		}
		$list = Teacher::find()
			->from(['teacher' => Teacher::tableName()])
			->where(['IN', 'teacher_id', $teacher_ids])
			->indexBy('teacher_id');
		if ($limit) {
			$list = $list->limit($limit);
		}
		$list = $list->all();
		$question_list = Question::find()
			->select(['count(question_id) as cnt', 'teacher_id'])
			->from(['question' => Question::tableName()])
			->where(['IN', 'teacher_id', $teacher_ids])
			->andWhere(['is_helped' => false])
			->groupBy(['teacher_id'])
			->indexBy('teacher_id')
			->column();
		$question_helped_list = Question::find()
			->select(['count(question_id) as cnt', 'teacher_id'])
			->from(['question' => Question::tableName()])
			->where(['IN', 'teacher_id', $teacher_ids])
			->andWhere(['is_helped' => true])
			->groupBy(['teacher_id'])
			->indexBy('teacher_id')
			->column();
		foreach ($list as $teacher_id => $teacher) {
		    $teacher->filter_question_count = $question_list[$teacher_id] ?? 0;
		    $teacher->filter_question_helped_count = $question_helped_list[$teacher_id] ?? 0;
		}
		usort($list, function ($a, $b) {
			if ($a->filter_question_helped_count == $b->filter_question_helped_count) {
				return $b->filter_question_count <=> $a->filter_question_count;
			} else {
				return $b->filter_question_helped_count <=> $a->filter_question_helped_count;
			}
		});
		return $list;
	}

	public static function getTagQuestionListByDiscipline(?string $discipline_name = '', $tags = [],
														  string $q = '', bool $init = false)
	{
		$discipline_name = (string)$discipline_name;
		$tags = (array)$tags;
		if ($tags) {
			foreach ($tags as $key => $tag) {
				$tags[$key] = (string)$tags[$key];
				if (!$tag) {
					unset($tags[$key]);
				}
			}
		}
		$where = [];
		if ($discipline_name) {
			$where = ['OR',
				['LIKE', 'd.discipline_name', $discipline_name],
				['t.discipline_id' => 0],
			];
		} else {
			$where = ['t.discipline_id' => 0];
		}
		$tag_list = Tag::find()
			->select(['tag_name as text'])
			->from(['t' => Tag::tableName()])
			->joinWith(['discipline as d'])
			->where(['t.is_checked' => true])
			->andWhere($where);
			if ($q) {
				$tag_list = $tag_list->andWhere(['LIKE', 'tag_name', $q]);
			}
		$tag_list = $tag_list->column();
		foreach ($tags as $tag) {
			$tag_list[] = $tag;
		}
		$tag_list = array_unique($tag_list);
		$list = [];
		if ($init) {
			foreach ($tag_list as $tag) {
				$list[] = [$tag => $tag];
			}
		} else {
			foreach ($tag_list as $tag) {
				$list[] = ['id' => $tag, 'text' => $tag];
			}
		}
		return $list;
	}

	public static function getTagListByDiscipline(int $discipline_id = -1, string $q = '', ?int $id = null)
	{
		if ($discipline_id == -1) {
			$discipline_id = 0;
		}
		$list = Tag::find()
			->select(['tag_id as id', 'tag_name as text'])
			->from(['t' => Tag::tableName()])
			->joinWith(['discipline as d'])
			->where(['LIKE', 'tag_name', $q])
			->andWhere(['OR',
				['d.discipline_id' => $discipline_id],
				['t.discipline_id' => 0],
			]);
		if ($id) {
			$list = $list->andWhere(['!=', 'tag_id', $id]);
		}
		return $list->asArray()->all();
	}

	public static function getQuestionTagCounts(array $tag_ids)
	{
		return Tag::find()
			->select(['COUNT(question.question_id) as cnt', 't.tag_id'])
			->from(['t' => Tag::tableName()])
			->joinWith('questions as question')
			->where(['IN', 't.tag_id', $tag_ids])
			->andWhere(['!=', 't.discipline_id', 0])
			->andWhere(['question.is_deleted' => false])
			->orderBy(['cnt' => SORT_DESC])
			->indexBy('t.tag_id')
			->column();
	}

	public static function getDisciplineQuestionTagCounts(int $discipline_id, int $limit = 0)
	{
		if ($limit < 0) {
			$limit = 5;
		}
		$list = [];
		$discipline = Discipline::find()
			->from(['discipline' => Discipline::tableName()])
			->joinWith('tags')
			->where(['discipline.discipline_id' => $discipline_id])
			->one();
		if (!empty($discipline->tags)) {
			$list = $discipline->tags;
			$question_counts = Tag::find()
				->select(['COUNT(question.question_id) as cnt', 't.tag_id'])
				->from(['t' => Tag::tableName()])
				->joinWith('questions as question')
				->where(['t.discipline_id' => $discipline_id])
				->andWhere(['question.is_deleted' => false])
				->indexBy('t.tag_id')
				->column();
			foreach ($list as $tag) {
				$tag->filter_question_count = $question_counts[$tag->id] ?? 0;
			}
			usort($list, function($a, $b) {
				return $b->filter_question_count <=> $a->filter_question_count;
			});
			if ($limit) {
				$list = array_slice($list, 0, $limit);
			}
		}
		return $list;
	}

	public static function getTagQuestionCounts(array $tag_ids, int $limit = 0)
	{
		$question_counts = Tag::find()
			->select(['COUNT(question.question_id) as cnt', 't.tag_id'])
			->from(['t' => Tag::tableName()])
			->joinWith('questions as question')
			->where(['IN', 't.tag_id', $tag_ids])
			->andWhere(['question.is_deleted' => false])
			->indexBy('t.tag_id')
			->column();
		$list = Tag::find()
			->distinct()
			->from(['t' => Tag::tableName()])
			->joinWith('questions as question')
			->where(['IN', 't.tag_id', $tag_ids])
			->andWhere(['question.is_deleted' => false])
			->indexBy('t.tag_id')
			->all();
		foreach ($list as $tag) {
			$tag->filter_question_count = $question_counts[$tag->id] ?? 0;
		}
		usort($list, function($a, $b) {
			return $b->filter_question_count <=> $a->filter_question_count;
		});
		$list = array_slice($list, 0, $limit);
		return $list;
	}

}
