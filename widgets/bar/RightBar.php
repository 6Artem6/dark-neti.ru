<?php
namespace app\widgets\bar;

use Yii;
use kartik\select2\Select2;
use yii\bootstrap5\{Html, Widget};
use yii\helpers\ArrayHelper;

use app\models\data\RightbarList;


class RightBar extends Widget
{

	protected RightbarList $model;
	public ?int $question_id = null;
	public ?string $discipline_name = null;

	public bool $show_active_users = false;
	public bool $show_popular_questions = false;
	public bool $show_popular_disciplines = false;
	public bool $show_similar_disciplines = false;
	public bool $show_discipline_best = false;
	public bool $show_search = false;

	public function beforeRun()
	{
		if (!parent::beforeRun()) {
			return false;
		}
		$this->model = new RightbarList;
		return true;
	}

	public function run()
	{
		$output = '';
		$lists = '';

		if ($this->show_similar_disciplines or $this->show_discipline_best) {
			if ($this->question_id or $this->discipline_name) {
				if ($this->question_id) {
					$this->model->checkDisciplineByQuestion($this->question_id);
				} elseif ($this->discipline_name) {
					$this->model->checkDisciplineByName($this->discipline_name);
				}
			}
		}

		if ($this->show_search) {
			$lists .= Html::tag('div', null, ['id' => 'extra-div-max', 'class' => ['col-lg-12', 'my-0', 'px-2', 'px-lg-0']]);
		}
		if ($this->show_popular_questions) {
			$list = $this->renderPopularQuestionsList();
			if ($list) {
				$lists .= Html::tag('div', $list, ['class' => ['col-lg-12', 'my-0', 'px-2', 'px-lg-0']]);
			}
		}
		if ($this->show_discipline_best) {
			if ($this->question_id or $this->discipline_name) {
				$list = $this->renderDisciplineBestUsersList();
				if ($list) {
					$lists .= Html::tag('div', $list, ['class' => ['col-lg-12', 'my-0', 'px-2', 'px-lg-0']]);
				}
			}
		}
		if ($this->show_active_users) {
			$list = $this->renderActiveUsersList();
			if ($list) {
				$lists .= Html::tag('div', $list, ['class' => ['col-lg-12', 'my-0', 'px-2', 'px-lg-0']]);
			}
		}
		if ($this->show_popular_disciplines) {
			$list = $this->renderPopularDisciplinesList();
			if ($list) {
				$lists .= Html::tag('div', $list, ['class' => ['col-lg-12', 'my-0', 'px-2', 'px-lg-0']]);
			}
		}
		if ($this->show_similar_disciplines) {
			if ($this->question_id or $this->discipline_name) {
				$list = $this->renderSimilarDisciplinesList();
				if ($list) {
					$lists .= Html::tag('div', $list, ['class' => ['col-lg-12', 'my-0', 'px-2', 'px-lg-0']]);
				}
			}
		}

		// if ($lists) {
			$output .= Html::beginTag('div', ['class' => ['col-sm-12', 'col-md-4', 'px-3']]);
			$output .= Html::beginTag('div', ['class' => ['row', 'grid', 'gap-2', 'px-1', 'px-lg-0']]);
			$output .= $lists;
			$output .= Html::endTag('div');
			$output .= Html::endTag('div');
		// }

		return $output;
	}

	protected function renderPopularQuestionsList()
	{
		$limit = 5;
		$select_list = [];
		$current_type = null;
		$list = [
			RightbarList::TYPE_ALL_TIME => null,
			RightbarList::TYPE_MONTH => null,
			RightbarList::TYPE_WEEK => null,
			RightbarList::TYPE_DAY => null,
		];
		$is_empty = false;
		foreach ($list as $type => $value) {
			if (!$is_empty) {
				$list[$type] = $this->model->getPopularQuestionsList($type);
			}
			if (!empty($list[$type])) {
				$current_type = $type;
				$select_list[$type] = $this->model->getTimeName($type);
			} else {
				$is_empty = true;
			}
		}

		$output = '';

		$select_list = array_reverse($select_list);
		$list_id = 'popular-questions-list';

		$output .= Html::beginTag('div', ['class' => [$list_id . '-div', 'card', 'py-3', 'rightbar-list']]);

		$output .= Html::beginTag('div', ['class' => ['card-header', 'justify-content-between', 'border-0', 'py-1']]);
		$output .= Html::tag('h5', Yii::t('app', 'Популярные вопросы'), ['class' => ['card-title']]);
		$output .= Html::endTag('div');

		if (is_null($current_type)) {
			$output .= Html::beginTag('div', ['class' => ['w-100']]);
			$output .= Html::beginTag('div', ['class' => ['card-body', 'py-1']]);
			$output .= Yii::t('app', 'Вопросов пока нет');
			$output .= Html::endTag('div');
			$output .= Html::endTag('div');
		} else {
			$output .= Html::beginTag('div', ['class' => ['card-header', 'border-0', 'px-3', 'py-1']]);
			$output .= Html::beginTag('div', ['class' => ['w-100', 'input-group']]);
			$output .= Select2::widget([
				'value' => $current_type,
				'name' => $list_id,
				'data' => $select_list,
				'options' => [
					'id' => $list_id,
					'data-list' => $list_id,
				],
				'pluginOptions' => [
					'minimumResultsForSearch' => -1
				],
				'size' => Select2::SMALL
			]);
			$output .= Html::endTag('div');
			$output .= Html::endTag('div');

			$class = [];
			$output .= Html::beginTag('div', ['class' => ['card-body', 'px-0', 'py-1']]);
			foreach ($select_list as $type => $name) {
				if (!empty($question_list = $list[$type])) {
					$output .= Html::beginTag('div', ['class' => $class, 'data-list' => $list_id, 'data-type' => $type]);
					$target_id = $this->getTargetId($list_id, $type);
					$count = count($question_list);
					foreach ($question_list as $id => $question) {
						if ($id == $limit) {
							$output .= $this->getCollapseHeader($id, $target_id);
						}
						$output .= PopularQuestionsRecord::widget([
							'model' => $question,
						]);
						if (($id >= $limit) and ($id == ($count - 1))) {
							$output .= $this->getCollapseFooter($id, $target_id);
						}
					}
					$output .= Html::endTag('div');
					$class = ['d-none'];
				}
			}
			$output .= Html::endTag('div');
		}

		$output .= Html::endTag('div');
		return $output;
	}

	protected function renderActiveUsersList()
	{
		$limit = 5;
		$select_list = [];
		$current_type = null;
		$list = [
			RightbarList::TYPE_ALL_TIME => null,
			RightbarList::TYPE_MONTH => null,
			RightbarList::TYPE_WEEK => null,
		];

		$is_empty = false;
		foreach ($list as $type => $value) {
			if (!$is_empty) {
				$list[$type]['users'] = $this->model->getActiveUsersList($type);
			}
			if (!empty($list[$type]['users'])) {
				$current_type = $type;
				$select_list[$type] = $this->model->getTimeName($type);
				$user_ids = ArrayHelper::getColumn($list[$type]['users'], 'user_id');
				$list[$type]['answer_counts'] = $this->model->getLastAnswerCounts($type, $user_ids);
				$list[$type]['comment_counts'] = $this->model->getLastCommentCounts($type, $user_ids);
			} else {
				$is_empty = true;
			}
		}

		$output = '';

		$select_list = array_reverse($select_list);
		$list_id = 'active-users-list';

		$output .= Html::beginTag('div', ['class' => [$list_id . '-div', 'card', 'py-3', 'rightbar-list']]);

		$output .= Html::beginTag('div', ['class' => ['card-header', 'justify-content-between', 'border-0', 'py-1']]);
		$output .= Html::tag('h5', Yii::t('app', 'Активные пользователи'), ['class' => ['card-title']]);
		$output .= Html::endTag('div');

		if (is_null($current_type)) {
			$output .= Html::beginTag('div', ['class' => ['w-100']]);
			$output .= Html::beginTag('div', ['class' => ['card-body', 'py-1']]);
			$output .= Yii::t('app', 'Пользователей пока нет');
			$output .= Html::endTag('div');
			$output .= Html::endTag('div');
		} else {
			$output .= Html::beginTag('div', ['class' => ['card-header', 'border-0', 'px-3', 'py-1']]);
			$output .= Html::beginTag('div', ['class' => ['w-100', 'input-group']]);
			$output .= Select2::widget([
				'value' => $current_type,
				'name' => $list_id,
				'data' => $select_list,
				'options' => [
					'id' => $list_id,
					'data-list' => $list_id,
				],
				'pluginOptions' => [
					'minimumResultsForSearch' => -1
				],
				'size' => Select2::SMALL
			]);
			$output .= Html::endTag('div');
			$output .= Html::endTag('div');

			$class = [];
			$output .= Html::beginTag('div', ['class' => ['card-body', 'px-0', 'py-1']]);
			foreach ($select_list as $type => $name) {
				if ($user_list = $list[$type]['users']) {
					$output .= Html::beginTag('div', ['class' => $class, 'data-list' => $list_id, 'data-type' => $type]);
					$target_id = $this->getTargetId($list_id, $type);
					$count = count($user_list);
					foreach ($user_list as $id => $user) {
						if ($id == $limit) {
							$output .= $this->getCollapseHeader($id, $target_id);
						}
						$output .= ActiveUsersRecord::widget([
							'model' => $user,
							'answer_count' => $list[$type]['answer_counts'][$user->id] ?? 0,
							'comment_count' => $list[$type]['comment_counts'][$user->id] ?? 0,
						]);
						if (($id >= $limit) and ($id == ($count - 1))) {
							$output .= $this->getCollapseFooter($id, $target_id);
						}
					}
					$output .= Html::endTag('div');
					$class = ['d-none'];
				}
			}
			$output .= Html::endTag('div');

		}
		$output .= Html::endTag('div');
		return $output;
	}

	protected function renderPopularDisciplinesList()
	{
		$limit = 5;
		$select_list = [];
		$current_type = null;
		$list = [
			RightbarList::TYPE_ALL_TIME => null,
			RightbarList::TYPE_MONTH => null,
			RightbarList::TYPE_WEEK => null,
			RightbarList::TYPE_DAY => null,
		];

		$is_empty = false;
		foreach ($list as $type => $value) {
			if (!$is_empty) {
				$list[$type]['disciplines'] = $this->model->getPopularDisciplinesList($type);
			}
			if (!empty($list[$type]['disciplines'])) {
				$current_type = $type;
				$select_list[$type] = $this->model->getTimeName($type);
				$discipline_ids = ArrayHelper::getColumn($list[$type]['disciplines'], 'discipline_id');
				$list[$type]['question_counts'] = $this->model->getDisciplineQuestionCounts($type, $discipline_ids);
				$list[$type]['question_helped_counts'] = $this->model->getDisciplineQuestionHelpedCounts($type, $discipline_ids);
			} else {
				$is_empty = true;
			}
		}

		$output = '';
		if ($current_type) {
			$select_list = array_reverse($select_list);
			$list_id = 'popular-disciplines-list';

			$output .= Html::beginTag('div', ['class' => [$list_id . '-div', 'card', 'py-3', 'rightbar-list']]);

			$output .= Html::beginTag('div', ['class' => ['card-header', 'justify-content-between', 'border-0', 'py-1']]);
			$output .= Html::tag('h5', Yii::t('app', 'Популярные предметы'), ['class' => ['card-title']]);
			$output .= Html::endTag('div');

			$output .= Html::beginTag('div', ['class' => ['card-header', 'border-0', 'px-3', 'py-1']]);
			$output .= Html::beginTag('div', ['class' => ['w-100', 'input-group']]);
			$output .= Select2::widget([
				'value' => $current_type,
				'name' => $list_id,
				'data' => $select_list,
				'options' => [
					'id' => $list_id,
					'data-list' => $list_id,
				],
				'pluginOptions' => [
					'minimumResultsForSearch' => -1
				],
				'size' => Select2::SMALL
			]);
			$output .= Html::endTag('div');
			$output .= Html::endTag('div');

			$class = [];
			$output .= Html::beginTag('div', ['class' => ['card-body', 'px-0', 'py-1']]);
			foreach ($select_list as $type => $name) {
				if (!empty($discipline_list = $list[$type]['disciplines'])) {
					$output .= Html::beginTag('div', ['class' => $class, 'data-list' => $list_id, 'data-type' => $type]);
					$target_id = $this->getTargetId($list_id, $type);
					$count = count($discipline_list);
					foreach ($discipline_list as $id => $discipline) {
						if ($id == $limit) {
							$output .= $this->getCollapseHeader($id, $target_id);
						}
						$output .= DisciplinesRecord::widget([
							'model' => $discipline,
							'question_count' => $list[$type]['question_counts'][$discipline->id] ?? 0,
							'question_helped_count' => $list[$type]['question_helped_counts'][$discipline->id] ?? 0,
						]);
						if (($id >= $limit) and ($id == ($count - 1))) {
							$output .= $this->getCollapseFooter($id, $target_id);
						}
					}
					$output .= Html::endTag('div');
					$class = ['d-none'];
				}
			}
			$output .= Html::endTag('div');

			$output .= Html::endTag('div');
		}
		return $output;
	}

	protected function renderSimilarDisciplinesList()
	{
		$list = [];
		$list['disciplines'] = $this->model->getSimilarDisciplinesList();

		$output = '';
		if (!empty($list['disciplines'])) {
			$list_id = 'similar-disciplines-list';

			$output .= Html::beginTag('div', ['class' => [$list_id . '-div', 'card', 'py-3', 'rightbar-list']]);

			$output .= Html::beginTag('div', ['class' => ['card-header', 'justify-content-between', 'border-0', 'py-1']]);
			$output .= Html::beginTag('div', ['class' => ['w-100']]);
			$output .= Html::tag('h5', Yii::t('app', 'Похожие предметы'), ['class' => ['card-title']]);
			$output .= Html::endTag('div');
			$output .= Html::endTag('div');

			$output .= Html::beginTag('div', ['class' => ['card-body', 'px-0', 'py-1']]);
			$output .= Html::beginTag('div');
			foreach ($list['disciplines'] as $id => $discipline) {
				$output .= DisciplinesRecord::widget([
					'model' => $discipline,
					'question_count' => $list['question_counts'][$discipline->id] ?? 0,
					'question_helped_count' => $list['question_helped_counts'][$discipline->id] ?? 0,
					'show_period_count' => false
				]);
			}
			$output .= Html::endTag('div');
			$output .= Html::endTag('div');

			$output .= Html::endTag('div');
		}
		return $output;
	}

	protected function renderDisciplineBestUsersList()
	{
		$limit = 5;
		$select_list = [];
		$current_type = null;
		$list = [
			RightbarList::TYPE_ALL_TIME => null,
			RightbarList::TYPE_MONTH => null,
			RightbarList::TYPE_WEEK => null,
		];

		$is_empty = false;
		foreach ($list as $type => $value) {
			if (!$is_empty) {
				$list[$type]['users'] = $this->model->getDisciplineBestList($type);
			}
			if (!empty($list[$type]['users'])) {
				$current_type = $type;
				$select_list[$type] = $this->model->getTimeName($type);
				$user_ids = ArrayHelper::getColumn($list[$type]['users'], 'user_id');
				$list[$type]['answer_counts'] = $this->model->getDisciplineBestUsersAnswerCounts($type, $user_ids);
				$list[$type]['helped_counts'] = $this->model->getDisciplineBestUsersAnswerHelpedCounts($type, $user_ids);
			} else {
				$is_empty = true;
			}
		}

		$output = '';
		if ($current_type) {
			$select_list = array_reverse($select_list);
			$list_id = 'discipline-best-list';

			$output .= Html::beginTag('div', ['class' => [$list_id . '-div', 'card', 'py-3', 'rightbar-list']]);

			$output .= Html::beginTag('div', ['class' => ['card-header', 'justify-content-between', 'border-0', 'py-1']]);
			$output .= Html::tag('h5', Yii::t('app', 'Лучшие пользователи по предмету'), ['class' => ['card-title']]);
			$output .= Html::endTag('div');

			$output .= Html::beginTag('div', ['class' => ['card-header', 'border-0', 'px-3', 'py-1']]);
			$output .= Html::beginTag('div', ['class' => ['w-100', 'input-group']]);
			$output .= Select2::widget([
				'value' => $current_type,
				'name' => $list_id,
				'data' => $select_list,
				'options' => [
					'id' => $list_id,
					'data-list' => $list_id,
				],
				'pluginOptions' => [
					'minimumResultsForSearch' => -1
				],
				'size' => Select2::SMALL
			]);
			$output .= Html::endTag('div');
			$output .= Html::endTag('div');

			$class = [];
			$output .= Html::beginTag('div', ['class' => ['card-body', 'px-0', 'py-1']]);
			foreach ($select_list as $type => $name) {
				if ($user_list = $list[$type]['users']) {
					$output .= Html::beginTag('div', ['class' => $class, 'data-list' => $list_id, 'data-type' => $type]);
					$target_id = $this->getTargetId($list_id, $type);
					$count = count($user_list);
					foreach ($user_list as $id => $user) {
						if ($id == $limit) {
							$output .= $this->getCollapseHeader($id, $target_id);
						}
						$output .= DisciplineBestUsersRecord::widget([
							'model' => $user,
							'answer_count' => $list[$type]['answer_counts'][$user->id] ?? 0,
							'helped_count' => $list[$type]['helped_counts'][$user->id] ?? 0,
						]);
						if (($id >= $limit) and ($id == ($count - 1))) {
							$output .= $this->getCollapseFooter($id, $target_id);
						}
					}
					$output .= Html::endTag('div');
					$class = ['d-none'];
				}
			}
			$output .= Html::endTag('div');

			$output .= Html::endTag('div');
		}
		return $output;
	}

	protected function getTargetId(string $list_id, string $type)
	{
		return $list_id . '-' . $type . '-collapse';
	}

	protected function getCollapseHeader(int $id, string $target_id)
	{
		return Html::beginTag('div', [
			'id' => $target_id,
			'class' => ['collapse', $target_id]
		]);
	}

	protected function getCollapseFooter(int $id, string $target_id)
	{
		$output = '';
		$output .= Html::endTag('div');
		$more_id = $target_id . '-more';
		$less_id = $target_id . '-less';
		$controls = $target_id . ' ' . $more_id . ' ' . $less_id;

		$output .= Html::beginTag('div', ['class' => ['card-footer', 'border-0', 'text-center', 'py-0']]);
		$output .= Html::button(Yii::t('app', 'Больше'), [
			'id' => $more_id,
			'class' => [
				'btn', 'btn-outline-info', 'btn-sm', 'rounded-pill',
				'px-3', 'mt-3', 'w-100', 'collapse', 'show', $target_id
			],
			'aria-expanded' => 'false',
			'aria-controls' => $controls,
			'data' => [
				'bs-toggle' => 'collapse',
				'bs-target' => '.' . $target_id
			],
		]);

		$output .= Html::button(Yii::t('app', 'Меньше'), [
			'id' => $less_id,
			'class' => [
				'btn', 'btn-outline-info', 'btn-sm', 'rounded-pill',
				'px-3', 'mt-3', 'w-100', 'collapse', $target_id
			],
			'aria-expanded' => 'false',
			'aria-controls' => $controls,
			'data' => [
				'bs-toggle' => 'collapse',
				'bs-target' => '.' . $target_id
			],
		]);
		$output .= Html::endTag('div');
		return $output;
	}

}
