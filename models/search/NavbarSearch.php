<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\helpers\{ArrayHelper, Url};

use app\models\user\User;
use app\models\edu\{Discipline, Faculty, Teacher};
use app\models\question\{Question, Tag};
use app\models\helpers\TextHelper;


class NavbarSearch extends Model
{

	public function getList(string $search_text = '')
	{
		$user = Yii::$app->user->identity;
		$group = $user->data->group;
		$user_id = $user->id;
		$group_id = $group->group_id;
		$course = $group->course;
		$faculty_id = $group->faculty_id;

		$search_text = strip_tags($search_text);
		$search_text = TextHelper::remove_emoji($search_text);
		$search_text = TextHelper::remove_multiple_whitespaces($search_text);

		$extra_params = $this->getExtraParams($search_text);

		$record_count = 0;
		$max_records = 12;

		$user_list = $this->getUserList($search_text, $max_records, $group_id);
		$teacher_list = $this->getTeacherList($search_text, $max_records, $group_id, $course, $faculty_id);
		$discipline_list = $this->getDisciplineList($search_text, $max_records);
		$tag_list = $this->getTagList($search_text, $max_records);
		$faculty_list = $this->getFacultyList($search_text);
		$question_list = $this->getQuestionList($search_text, $extra_params, $max_records);

		$faculty_count = count($faculty_list);
		$tag_count = count($tag_list);
		$user_count = count($user_list);
		$teacher_count = count($teacher_list);
		$discipline_count = count($discipline_list);
		$question_count = count($question_list);

		$faculty_limit = 0;
		$tag_limit = 0;
		$teacher_limit = 0;
		$discipline_limit = 0;
		$user_limit = 0;
		$question_limit = 0;

		$this->setCounts($faculty_count, $faculty_limit, $record_count, 1);
		$this->setCounts($tag_count, $tag_limit, $record_count, 2);
		$this->setCounts($user_count, $user_limit, $record_count, 2);
		$this->setCounts($teacher_count, $teacher_limit, $record_count, 2);
		$this->setCounts($discipline_count, $discipline_limit, $record_count, 2);
		$this->setCounts($question_count, $question_limit, $record_count, 3);

		$this->recalculateCounts($question_count, $question_limit, $record_count, $max_records);
		$this->recalculateCounts($discipline_count, $discipline_limit, $record_count, $max_records);
		$this->recalculateCounts($user_count, $user_limit, $record_count, $max_records);
		$this->recalculateCounts($teacher_count, $teacher_limit, $record_count, $max_records);
		$this->recalculateCounts($tag_count, $tag_limit, $record_count, $max_records);
		$this->recalculateCounts($faculty_count, $faculty_limit, $record_count, $max_records);

		$this->checkList($faculty_limit, $faculty_list);
		$this->checkList($tag_limit, $tag_list);
		$this->checkList($teacher_limit, $teacher_list);
		$this->checkList($discipline_limit, $discipline_list);
		$this->checkList($user_limit, $user_list);
		$this->checkList($question_limit, $question_list);

		$result_list = [];
		if (!empty($question_list)) {
			foreach ($question_list as $record) {
				$result_list[] = [
					'title' => $record->question_title,
					'link' => $record->getRecordLink(),
					'icon' => 'bi bi-question-circle-fill'
				];
			}
			$formName = (new QuestionSearch)->formName();
			$result_list[] = [
				'title' => Yii::t('app', 'Искать среди вопросов'),
				'link' => Url::to(['/question', $formName => ['text' => $search_text]]),
				'icon' => 'bi bi-arrow-right'
			];
		}
		if (!empty($user_list)) {
			foreach ($user_list as $record) {
				$result_list[] = [
					'title' => $record->getAtUsername(),
					'link' => $record->getPageLink(),
					'img' => $record->data->getAvatarLink(),
				];
			}
			$formName = (new UserSearch)->formName();
			$result_list[] = [
				'title' => Yii::t('app', 'Искать среди пользоватей'),
				'link' => Url::to(['/user', $formName => ['text' => $search_text]]),
				'icon' => 'bi bi-arrow-right'
			];
		}
		if (!empty($discipline_list)) {
			foreach ($discipline_list as $record) {
				$result_list[] = [
					'title' => $record->discipline_name,
					'link' => $record->getPageLink(),
					'img' => $record->getImgLink(),
				];
			}
			$formName = (new DisciplineSearch)->formName();
			$result_list[] = [
				'title' => Yii::t('app', 'Искать среди предметов'),
				'link' => Url::to(['/discipline', $formName => ['text' => $search_text]]),
				'icon' => 'bi bi-arrow-right'
			];
		}
		if (!empty($teacher_list)) {
			foreach ($teacher_list as $record) {
				$result_list[] = [
					'title' => $record->teacher_fullname,
					'link' => $record->getRecordLink(),
					'icon' => 'bi bi-mortarboard-fill'
				];
			}
		}
		if (!empty($tag_list)) {
			foreach ($tag_list as $record) {
				$result_list[] = [
					'title' => $record->name,
					'link' => $record->getRecordLink(),
					'icon' => 'bi bi-tags'
				];
			}
		}
		if (!empty($faculty_list)) {
			foreach ($faculty_list as $record) {
				$result_list[] = [
					'title' => $record->faculty_fullname,
					'link' => $record->getRecordLink(),
					'icon' => null,
				];
			}
		}
		return $result_list;
	}

	private function getExtraParams(string $search_text)
	{
		$extra_params = [];
		$extra_params['exact_strings'] = [];
		$extra_params['username'] = null;
		$extra_params['user'] = null;
		if ($search_text) {
			$strings = $search_text;
			do {
				$exact_string = TextHelper::get_string_between($strings, '"', '"');
				if ($exact_string) {
					$exact_string = '"' . $exact_string . '"';
					$extra_params['exact_strings'][] = $exact_string;
					$strings = str_replace($exact_string, '', $strings);
				}
			} while ($exact_string);
			$strings = explode(' ', $strings);
			foreach ($strings as $key => $string) {
				if ($search_user = User::findByAtUsername($string)) {
					$extra_params['username'] = $search_user->username;
					$extra_params['user'] = $search_user->id;
					unset($strings[$key]);
				}
			}
			$search_text = implode(' ', $strings);
		}
		return $extra_params;
	}

	private function getQuestionList(string $search_text, array $extra_params, int $max_records)
	{
		$query = Question::find()
			->distinct()
			->from(['question' => Question::tableName()])
			->joinWith('files as file')
			// ->joinWith('answersSearch')
			// ->joinWith('answersSearch.files as answer_file')
			// ->joinWith('commentsSearch')
			->where(['question.is_deleted' => false]);

		if (!Yii::$app->user->identity->isModerator()) {
			$query->andWhere(['question.is_hidden' => false]);
		}

		if ($extra_params['exact_strings']) {
			foreach ($extra_params['exact_strings'] as $string) {
				$string = trim($string, '"');
				$query->andWhere(['OR',
					['LIKE', 'question.question_title', $string],
					['LIKE', 'question.question_text', $string],
					// ['LIKE', 'answer.answer_text', $string],
					// ['LIKE', 'comment.comment_text', $string],
					['LIKE', 'file.file_text', $string],
					['LIKE', 'file.file_user_name', $string],
					// ['LIKE', 'answer_file.file_text', $string],
					// ['LIKE', 'answer_file.file_user_name', $string],
				]);
			}
		}
		if ($extra_params['user']) {
			$query->andWhere(['question.user_id' => $extra_params['user']]);
		}
		if ($search_text) {
			$strings = explode(' ', $search_text);
			foreach ($strings as $string) {
				$string = TextHelper::remove_word_end($string);
				$query->andWhere(['OR',
					['LIKE', 'question.question_title', $string],
					['LIKE', 'question.question_text', $string],
					// ['LIKE', 'answer.answer_text', $string],
					// ['LIKE', 'comment.comment_text', $string],
					['LIKE', 'file.file_text', $string],
					['LIKE', 'file.file_user_name', $string],
					// ['LIKE', 'answer_file.file_text', $string],
					// ['LIKE', 'answer_file.file_user_name', $string],
				]);
			}
		}

		$in_list = $query->limit(100)->all();
		$in_ids = array_column($in_list, 'question_id');

		$list = [];
		if ($in_ids) {
			$list = Question::find()
				->from(['question' => Question::tableName()])
				->joinWith('follow')
				->where(['IN', 'question.question_id', $in_ids])
				->limit(100)
				->all();
		}

		ArrayHelper::multisort($list, [
			'isAuthor', 'isFollowed', 'answer_count', 'followers',
			'question_datetime', 'edited_datetime', 'views'
		], SORT_DESC);

		$list = array_slice($list, 0, $max_records);
		return $list;
	}

	private function getUserList(string $search_text, int $max_records, int $group_id)
	{
		$list = [];
		if ($search_text) {
			$list = User::find()
				->distinct()
				->from(['user' => User::tableName()])
				->joinWith('data as data')
				->joinwith('data.follow as follow')
				->joinWith('data.badgeData as bd')
				->joinWith(['data.group as dg' => function($query) use($group_id) {
					return $query->onCondition(['dg.group_id' => $group_id]);
				}])
				->where(['OR',
					['LIKE', 'username', $search_text],
					['LIKE', "concat(`first_name`, ' ', `last_name`)", $search_text],
					['LIKE', "CONCAT(SUBSTRING(`first_name`, 1, 1), `last_name`)", $search_text],
				])
				->orderBy([
					'follow.follower_id' => SORT_DESC,
					'dg.group_id' => SORT_DESC,
					'bd.question_count' => SORT_DESC,
					'bd.answer_helped_count' => SORT_DESC,
					'username' => SORT_ASC,
				])
				->limit($max_records)
				->all();
		}
		return $list;
	}

	private function getTeacherList(string $search_text, int $max_records, int $group_id, int $course, int $faculty_id)
	{
		$list = [];
		if (mb_strlen($search_text) >= 2) {
			$list = Teacher::find()
				->select([
					't.*',
					'ISNULL(gc.group_name) as group_course',
					'g.group_name',
					'f.faculty_fullname',
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
				->where(['LIKE', 'teacher_fullname', $search_text])
				->orderBy([
					'g.group_name' => SORT_DESC,
					'group_course' => SORT_ASC,
					'f.faculty_fullname' => SORT_DESC,
					't.teacher_fullname' => SORT_ASC,
				])
				->limit($max_records)
				->all();
		}
		return $list;
	}

	private function getDisciplineList(string $search_text, int $max_records)
	{
		$list = [];
		if ($search_text) {
			$list = Discipline::find()
				->distinct()
				->joinWith('followUser as follow')
				->joinWith('icon as icon');
			$strings = explode(' ', $search_text);
			foreach ($strings as $string) {
				$string = TextHelper::remove_word_end($string);
				$list->andWhere(['LIKE', 'discipline_name', $string]);
			}
			$list = $list
				->orderBy([
					'follower_id' => SORT_DESC,
					'question_count' => SORT_DESC,
					'question_helped_count' => SORT_DESC,
					'followers' => SORT_DESC,
					'discipline_name' => SORT_ASC,
				])
				->limit($max_records)
				->all();
		}
		return $list;
	}

	private function getTagList(string $search_text, int $max_records)
	{
		$list = [];
		if (mb_strlen($search_text) >= 2) {
			$list = Tag::find()
				->from(['tag' => Tag::tableName()])
				->where(['LIKE', 'tag_name', $search_text])
				->limit($max_records)
				->all();
		}
		return $list;
	}

	private function getFacultyList(string $search_text)
	{
		$list = [];
		if (mb_strlen($search_text) >= 3) {
			$list = Faculty::find()
				->where(['OR',
					['LIKE', 'faculty_fullname', $search_text],
					['faculty_shortname' => $search_text],
				])
				->all();
		}
		return $list;
	}

	private function setCounts(int &$count, int &$limit, int &$record_count, int $max)
	{
		if ($count) {
			$limit = ($count < $max ? $count : $max);
			$record_count += $limit;
		}
	}

	private function recalculateCounts(int &$count, int &$limit, int &$record_count, int $max_records)
	{
		if ($count) {
			$remains = $max_records - $record_count;
			if (($remains > 0) and ($count > $limit)) {
				$record_count -= $limit;
				if ($count < $remains) {
					$limit = $count;
				} else {
					$limit += $remains;
				}
				$record_count += $limit;
			}
		}
	}

	private function checkList(int $limit, array &$list)
	{
		if ($limit) {
			$list = array_slice($list, 0, $limit);
		}
	}
}
