<?php

namespace app\widgets\question\button;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\question\Comment;
use app\models\helpers\HtmlHelper;

use app\widgets\form\{
	ReportCommentForm
};


class CommentButton extends Widget
{

	public Comment $model;
	public array $report_types;
	public string $div_id;
	public bool $is_feed = false;

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
		if ($this->model->isEdited() and is_null($this->model->canSee())) {
			$title = Html::tag('span', Yii::t('app', 'Просмотреть изменения'), [
				'class' => ['bi', 'bi-clock-history', 'bi_icon', 'fa-fw', 'pe-2'],
			]);
			$item_list[] = Html::a($title,
				$this->model->getHistoryLink(), [
				'class' => 'dropdown-item',
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Просмотреть историю изменений комментария')
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
					Yii::t('app', 'Пожаловаться на ответ')
				),
				'data' => [
					'bs-toggle' => 'modal',
					'bs-target' => '#commentReport-' . $this->model->id
				],
			]);
		}
		if (Yii::$app->user->identity->isModerator()) {
			if ($this->model->is_hidden) {
				$title = Html::tag('span', Yii::t('app', 'Показать'), [
					'class' => ['bi', 'bi-eye-fill', 'bi_icon', 'fa-fw', 'pe-2'],
				]);
				$item_list[] = HtmlHelper::actionModeratorButton($title, 'show-comment', $this->model->id, [
					'class' => 'dropdown-item',
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Показать комментарий')
				]);
			} else {
				$title = Html::tag('span', Yii::t('app', 'Скрыть'), [
					'class' => ['bi', 'bi-eye-slash-fill', 'bi_icon', 'fa-fw', 'pe-2'],
				]);
				$item_list[] = HtmlHelper::actionModeratorButton($title, 'hide-comment', $this->model->id, [
					'class' => 'dropdown-item',
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Скрыть комментарий')
				]);
			}
		}
		if (is_null($this->model->canDelete())) {
			if (!$this->model->is_deleted) {
				$title = Html::tag('span', Yii::t('app', 'Удалить'), [
					'class' => ['bi', 'bi-trash', 'bi_icon', 'fa-fw', 'pe-2'],
				]);
				$item_list[] = HtmlHelper::actionButton($title, 'comment-delete', $this->model->id, [
					'class' => 'dropdown-item',
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Удалить комментарий')
				]);
			} else {
				$title = Html::tag('span', Yii::t('app', 'Восстановить'), [
					'class' => ['bi', 'bi-trash', 'bi_icon', 'fa-fw', 'pe-2'],
				]);
				$item_list[] = HtmlHelper::actionButton($title, 'comment-restore', $this->model->id, [
					'class' => 'dropdown-item',
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Восстановить комментарий')
				]);
			}
		}

		$output = '';
		if ($item_list) {
			$count = count($item_list);
			$output .= Html::beginTag('div', ['class' => ['dropdown']]);
			$title = Html::tag('i', null, ['class' => ['bi', 'bi-three-dots']]);
			$output .= Html::a($title, null, [
				'class' => ['btn', 'btn-secondary-soft-hover', 'text-secondary', 'py-1', 'px-2'],
				'id' => 'commentFeedAction-'.$this->model->id,
				'data' => [
					'bs-toggle' => 'dropdown',
					'bs-offset' => [10, 20],
					'bs-auto-close' => 'true',
				],
			]);
			$output .= Html::ul($item_list, [
				'class' => 'dropdown-menu dropdown-menu-end',
				'aria-labelledby' => 'commentFeedAction-'.$this->model->id,
				'item' => function($item, $index) use($count) {
					$return = Html::tag('li', $item);
					if ($index < $count - 1) {
						$return .= HtmlHelper::divider();
					}
					return $return;
				},
			]);
			$output .= Html::endTag('div');

			if (!$this->model->isAuthor and !$this->model->isReported) {
				$output .= ReportCommentForm::widget([
					'comment_id' => $this->model->id,
					'report_types' => $this->report_types,
				]);
			}
		}

		return $output;
	}
}
