<?php

namespace app\widgets\question\button;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\question\Question;
use app\models\helpers\HtmlHelper;

use app\widgets\form\{
	DuplicateQuestionRequestModalForm,
	ReportQuestionForm
};


class QuestionButton extends Widget
{

	public Question $model;
	public array $report_types;

	public function beforeRun()
	{
		if (!parent::beforeRun()) {
			return false;
		}
		if ($this->model->isNewRecord) {
			return false;
		}
		return true;
	}

	public function run()
	{
		$item_list = [];
		$title = Html::tag('span', Yii::t('app', 'Подписчики'), [
			'class' => ['bi', 'bi-bookmark', 'bi_icon', 'fa-fw', 'pe-2'],
		]);
		$item_list[] = Html::a($title,
			$this->model->getFollowersLink(), [
				'class' => 'dropdown-item',
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Просмотреть список подписчиков на вопрос')
			]
		);
		if (!$this->model->is_closed) {
			$title = Html::tag('span', Yii::t('app', 'Есть решённый вопрос'), [
				'class' => ['bi', 'bi-question-circle', 'bi_icon', 'fa-fw', 'pe-2'],
			]);
			$item_list[] = Html::button($title, [
				'class' => ['dropdown-item', 'duplicateQuestionRequestButton'],
				'data-id' => $this->model->id,
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Предложить похожий вопрос с решением'),
				'data' => [
					'bs-toggle' => 'modal',
					'bs-target' => '#duplicateQuestionRequestForm-' . $this->model->id,
				],
			]);
		}
		if ($this->model->isEdited() and is_null($this->model->canSee())) {
			$title = Html::tag('span', Yii::t('app', 'Изменения вопроса'), [
				'class' => ['bi', 'bi-clock-history', 'bi_icon', 'fa-fw', 'pe-2'],
			]);
			$item_list[] = Html::a($title,
				$this->model->getHistoryLink(), [
				'class' => 'dropdown-item',
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Просмотреть историю изменений вопроса')
			]);
		}
		if (!$this->model->isAuthor) {
			$text = ($this->model->reportCount ?
				Yii::t('app', 'Пожаловаться ({count})', ['count' => $this->model->reportCount]) :
				Yii::t('app', 'Пожаловаться')
			);
			$title = Html::tag('span', $text, ['class' => ['bi', 'bi-flag', 'bi_icon', 'fa-fw', 'pe-2']]);
			$item_list[] = Html::button($title, [
				'class' => 'dropdown-item',
				'data-toggle' => 'tooltip',
				'title' => ($this->model->isReported ?
					Yii::t('app', 'Вы уже пожаловались на вопрос') :
					Yii::t('app', 'Пожаловаться на воспрос')
				),
				'data' => [
					'bs-toggle' => 'modal',
					'bs-target' => '#questionReport-' . $this->model->id,
				],
			]);
		}
		if (Yii::$app->user->identity->isModerator()) {
			if ($this->model->is_hidden) {
				$title = Html::tag('span', Yii::t('app', 'Показать'), [
					'class' => ['bi', 'bi-eye-fill', 'bi_icon', 'fa-fw', 'pe-2'],
				]);
				$item_list[] = HtmlHelper::actionModeratorButton($title, 'show-question', $this->model->id, [
					'class' => 'dropdown-item',
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Показать вопрос')
				]);
			} else {
				$title = Html::tag('span', Yii::t('app', 'Скрыть'), [
					'class' => ['bi', 'bi-eye-slash-fill', 'bi_icon', 'fa-fw', 'pe-2'],
				]);
				$item_list[] = HtmlHelper::actionModeratorButton($title, 'hide-question', $this->model->id, [
					'class' => 'dropdown-item',
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Скрыть вопрос')
				]);
			}
		}
		if (is_null($this->model->canDelete())) {
			if (!$this->model->is_deleted) {
				$title = Html::tag('span', Yii::t('app', 'Удалить'), [
					'class' => ['bi', 'bi-trash', 'bi_icon', 'fa-fw', 'pe-2'],
				]);
				$item_list[] = HtmlHelper::actionButton($title, 'question-delete', $this->model->id, [
					'class' => 'dropdown-item',
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Удалить вопрос')
				]);
			} else {
				$title = Html::tag('span', Yii::t('app', 'Восстановить'), [
					'class' => ['bi', 'bi-trash', 'bi_icon', 'fa-fw', 'pe-2'],
				]);
				$item_list[] = HtmlHelper::actionButton($title, 'question-restore', $this->model->id, [
					'class' => 'dropdown-item',
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Восстановить вопрос')
				]);
			}
		}

		$count = count($item_list);

		$output = '';

		$output .= Html::beginTag('div', ['class' => ['dropdown']]);
		$title = Html::tag('i', null, ['class' => ['bi', 'bi-three-dots']]);
		$output .= Html::a($title, null, [
			'class' => ['btn', 'btn-secondary-soft-hover', 'text-secondary', 'py-1', 'px-2'],
			'id' => 'questionFeedAction-'.$this->model->id,
			'aria-expanded' => 'false',
			'data' => [
				'bs-toggle' => 'dropdown',
				'bs-offset' => [10, 20],
				'bs-auto-close' => 'true',
			],
		]);
		$output .= Html::ul($item_list, [
			'class' => 'dropdown-menu dropdown-menu-end',
			'aria-labelledby' => 'questionFeedAction-'.$this->model->id,
			'item' => function($item, $index) use($count) {
				$return = Html::tag('li', $item);
				if ($index < $count - 1) {
					$return .= HtmlHelper::divider();
				}
				return $return;
			},
		]);
		$output .= Html::endTag('div');

		if (!$this->model->is_closed) {
			$output .= DuplicateQuestionRequestModalForm::widget([
				'question_id' => $this->model->id
			]);
		}
		if (!$this->model->isAuthor and !$this->model->isReported) {
			$output .= ReportQuestionForm::widget([
				'question_id' => $this->model->id,
				'report_types' => $this->report_types,
			]);
		}

		return $output;
	}
}
