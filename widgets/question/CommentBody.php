<?php

namespace app\widgets\question;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\question\Comment;
use app\models\helpers\HtmlHelper;

use app\widgets\form\EditCommentForm;
use app\widgets\question\button\CommentButton;


class CommentBody extends Widget
{

	public Comment $model;
	public array $report_types;
	public bool $is_feed = true;
	public bool $is_unseen = false;

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
		
		$output = '';

		$output .= Html::beginTag('li', [
			'id' => 'comment-'.$this->model->id,
			'class' => ['comment-item'],
		]);
		$output .= Html::beginTag('div', ['class' => 'd-flex']);
		$output .= Html::beginTag('div', ['class' => 'avatar avatar-sm']);
		$output .= $this->model->author->getAvatar('sm');
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => 'ms-2 w-100']);

		$output .= CommentText::widget([
			'model' => $this->model,
			'is_feed' => $this->is_feed,
			'is_unseen' => $this->is_unseen,
		]);

		$output .= Html::beginTag('div');
		$output .= Html::beginTag('div', ['class' => ['d-flex']]);
		$output .= Html::beginTag('div', ['class' => ['flex-grow-1', 'comment-buttons']]);

		$item_list = [];
		if (!$this->model->is_hidden) {
			if (!$this->model->isAuthor and !$this->model->isLiked) {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Полезный')) .
					HtmlHelper::getCountText($this->model->like_count, true);
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-hand-thumbs-up', 'bi_icon']]);
				$item_list[] = HtmlHelper::actionButton($title, 'add-like-comment', $this->model->id, [
					'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Отметить полезным')
				]);
			} elseif (!$this->model->isAuthor and $this->model->isLiked) {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Полезный')) .
					HtmlHelper::getCountText($this->model->like_count, true);
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-hand-thumbs-up-fill', 'bi_icon']]);
				$item_list[] = HtmlHelper::actionButton($title, 'remove-like-comment', $this->model->id, [
					'class' => ['btn', 'btn-light', 'text-secondary', 'mb-0'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Убрать отметку')
				]);
			} elseif($this->model->isAuthor) {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Полезный')) .
					HtmlHelper::getCountText($this->model->like_count, true);
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-hand-thumbs-up', 'bi_icon']]);
				$item_list[] = Html::button($title, [
					'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Автор комментария не может голосовать за него')
				]);
			}
		}

		if (!$this->is_feed) {
			if (is_null($this->model->canEdit())) {
				$title = Html::tag('span', HtmlHelper::getIconText(Yii::t('app', 'Редактировать')), [
					'class' => ['bi', 'bi-pencil-square', 'bi_icon'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Редактировать комментарий')
				]);
				$item_list[] = Html::button($title, [
					'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0', 'collapsed'],
					'aria-expanded' => 'false',
					'aria-controls' => 'editComment-'.$this->model->id,
					'data' => [
						'bs-toggle' => 'collapse',
						'bs-target' => '#editComment-'.$this->model->id,
						'record-type' => 'comment',
						'button-type' => 'edit',
						'id' => $this->model->id,
					],
				]);
			}
		}

		$output .= Html::ul($item_list, [
			'class' => ['nav', 'nav-pills', 'nav-pills-light', 'nav-fill', 'nav-stack', 'feed-comment-nav', 'py-1'],
			'item' => function($item, $index) {
				return Html::tag('li', $item, ['class' => 'nav-item']);
			},
		]);
		$output .= Html::endTag('div');

		$div_id = 'share-comment-' . $this->model->id;
		$output .= CommentButton::widget([
			'model' => $this->model,
			'report_types' => $this->report_types,
			'div_id' => $div_id,
		]);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		if (!$this->is_feed and is_null($this->model->canEdit())) {
			$output .= Html::beginTag('div',[
				'id' => 'editComment-'.$this->model->id,
				'class' => ['collapse'],
			]);
			$output .= EditCommentForm::widget([
				'model' => $this->model
			]);
			$output .= Html::endTag('div');
		}

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::endTag('li');

		return $output;
	}
}
