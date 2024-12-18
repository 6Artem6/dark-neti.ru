<?php

namespace app\widgets\question;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\question\{Answer, Comment};
use app\models\helpers\HtmlHelper;

use app\widgets\form\{
	CreateCommentAnswerForm,
	EditAnswerForm
};
use app\widgets\question\button\AnswerButton;


class AnswerBody extends Widget
{

	public Answer $model;
	public array $report_types;
	public array $unseen_comment_list;
	public bool $is_feed = true;
	public bool $is_unseen = false;
	public bool $is_edited = false;

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
			'id' => 'answer-'.$this->model->id,
			'class' => ['comment-item'],
		]);
		$output .= Html::beginTag('div', ['class' => ['d-flex', 'position-relative']]);
		$output .= Html::beginTag('div', ['class' => ['avatar', 'avatar-sm']]);
		$output .= $this->model->author->getAvatar('sm');
		if ($this->model->is_helped) {
			$output .= Html::tag('span', null, [
				'class' => ['bi', 'bi-check-circle-fill', 'text-success', 'helped'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Ответ решил вопрос')
			]);
		}
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => 'ms-2 w-100']);
		$output .= AnswerText::widget([
			'model' => $this->model,
			'is_feed' => $this->is_feed,
			'is_unseen' => $this->is_unseen,
			'is_edited' => $this->is_edited,
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
				$item_list[] = HtmlHelper::actionButton($title, 'add-like-answer', $this->model->id, [
					'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Отметить полезным')
				]);
			} elseif (!$this->model->isAuthor and $this->model->isLiked) {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Полезный')) .
					HtmlHelper::getCountText($this->model->like_count, true);
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-hand-thumbs-up-fill', 'bi_icon']]);
				$item_list[] = HtmlHelper::actionButton($title, 'remove-like-answer', $this->model->id, [
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
					'title' => Yii::t('app', 'Автор ответа не может голосовать за него'),
				]);
			}
		}

		$count = $this->model->comment_count;
		if ($this->is_feed) {
			if (!$count) {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Комментировать'));
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-chat', 'bi_icon', 'no_text']]);
			} else {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Комментарии')) .
					HtmlHelper::getCountText($count, true);
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-chat', 'bi_icon']]);
			}
			$item_list[] = Html::a($title,
				$this->model->getCommentsLink(), [
					'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0']
				]
			);
		} else {
			if (!$count) {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Комментировать'));
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-chat', 'bi_icon', 'no_text']]);
			} else {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Комментарии')) .
					HtmlHelper::getCountText($count, true);
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-chat', 'bi_icon']]);
			}
			$item_list[] = Html::button($title, [
				'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0', 'collapsed'],
				'aria-expanded' => 'false',
				'aria-controls' => 'answerComments-'.$this->model->id,
				'data' => [
					'bs-toggle' => 'collapse',
					'bs-target' => '#answerComments-'.$this->model->id,
					'record-type' => 'answer',
					'button-type' => 'comments',
					'id' => $this->model->id,
				],
			]);
		}

		if (!$this->is_feed) {
			if (is_null($this->model->canEdit())) {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Редактировать'));
				$title = Html::tag('span', $text, [
					'class' => ['bi', 'bi-pencil-square', 'bi_icon', 'no_text'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Редактировать ответ')
				]);
				$item_list[] = Html::button($title, [
					'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0', 'collapsed'],
					'aria-expanded' => 'false',
					'aria-controls' => 'editAnswer-'.$this->model->id,
					'data' => [
						'bs-toggle' => 'collapse',
						'bs-target' => '#editAnswer-'.$this->model->id,
						'record-type' => 'answer',
						'button-type' => 'edit',
						'id' => $this->model->id,
					],
				]);
			}
			if (!$this->model->is_hidden and $this->model->isUserQuestionAuthor) {
				if ($this->model->is_helped) {
					$text = HtmlHelper::getIconText(Yii::t('app', 'Решил вопрос'));
					$title = Html::tag('span', $text, ['class' => ['bi', 'bi-check-circle-fill', 'bi_icon', 'no_text']]);
					$item_list[] = HtmlHelper::actionButton($title, 'answer-not-helped', $this->model->id, [
						'class' => ['btn', 'btn-light', 'text-secondary', 'mb-0'],
						'data-toggle' => 'tooltip',
						'title' => Yii::t('app', 'Убрать отметку')
					]);
				} else {
					$text = HtmlHelper::getIconText(Yii::t('app', 'Решил вопрос'));
					$title = Html::tag('span', $text, ['class' => ['bi', 'bi-check-circle', 'bi_icon', 'no_text']]);
					$item_list[] = HtmlHelper::actionButton($title, 'answer-helped', $this->model->id, [
						'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0'],
						'data-toggle' => 'tooltip',
						'title' => Yii::t('app', 'Поставить отметку')
					]);
				}
			}
		}

		$output .= Html::ul($item_list, [
			'class' => ['nav', 'nav-pills', 'nav-pills-light', 'nav-fill', 'nav-stack', 'feed-answer-nav', 'py-1'],
			'item' => function($item, $index) {
				return Html::tag('li', $item, ['class' => 'nav-item']);
			},
		]);
		$output .= Html::endTag('div');

		$div_id = 'share-answer-' . $this->model->id;
		$output .= AnswerButton::widget([
			'model' => $this->model,
			'report_types' => $this->report_types,
			'div_id' => $div_id,
			'is_feed' => false,
		]);
		$output .= Html::endTag('div');

		$link = $this->model->getRecordLink(true);
		$output .= HtmlHelper::getShareDiv($div_id, $link, Yii::t('app', 'Ссылка на ответ'));
		$output .= Html::endTag('div');

		if (!$this->is_feed and is_null($this->model->canEdit())) {
			$output .= Html::beginTag('div',[
				'id' => 'editAnswer-'.$this->model->id,
				'class' => ['collapse'],
			]);
			$output .= EditAnswerForm::widget([
				'model' => $this->model
			]);
			$output .= Html::endTag('div');
		}

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		if ($this->is_feed) {
			$output .= $this->getCommentsFeed();
		} else {
			$output .=  Html::beginTag('div', [
				'id' => 'answerComments-'.$this->model->id,
				'class' => ['container-fluid', 'collapse', 'mt-4', 'pe-0',],
			]);
			$output .= $this->getCommentsAll($this->model);
			$output .= Html::endTag('div');
		}

		$output .= Html::endTag('li');

		return $output;
	}

	protected function getCommentsFeed(): string
	{
		$output = '';
		if ($comments = $this->model->commentFeedList) {
			$output .= Html::beginTag('ul', ['class' => ['comment-item-nested', 'list-unstyled']]);
			foreach ($comments as $comment) {
				$is_unseen = false;
				if (!empty($this->unseen_comment_list)) {
					$is_unseen = in_array($comment->id, $this->unseen_comment_list);
				}
				$output .= CommentBody::widget([
					'model' => $comment,
					'report_types' => $this->report_types,
					'is_feed' => true,
					'is_unseen' => $is_unseen,
				]);
			}
			$output .= Html::endTag('ul');
		}
		return $output;
	}

	protected function getCommentsAll(): string
	{
		$output = '';
		$output .= Html::tag('ul', null, [
			'class' => ['answer-comment-list', 'comment-item-nested', 'list-unstyled', 'py-1'],
			'data' => [
				'answer' => $this->model->id
			],
		]);
		$output .= Loader::widget();
		$output .= Html::beginTag('ul', ['class' => ['comment-item-nested', 'list-unstyled', 'py-1']]);
		if ($message = (new Comment)->canCreate()) {
			$output .= $message;
		} elseif ($this->model->is_hidden) {
			$output .= Html::beginTag('div', ['class' => 'p-3 bg-light rounded']);
				$output .= Html::tag('h5', Yii::t('app', 'Вопрос скрыт.'), ['class' => ['mt-0', 'text-center']]);
				$output .= Html::tag('h5', Yii::t('app', 'Комментарии временно нельзя отправлять.'), ['class' => ['mt-0', 'text-center']]);
			$output .= Html::endTag('div');
		} elseif ($this->model->is_hidden) {
			$output .= Html::beginTag('div', ['class' => 'p-3 bg-light rounded']);
				$output .= Html::tag('h5', Yii::t('app', 'Ответ скрыт.'), ['class' => ['mt-0', 'text-center']]);
				$output .= Html::tag('h5', Yii::t('app', 'Комментарии временно нельзя отправлять.'), ['class' => ['mt-0', 'text-center']]);
			$output .= Html::endTag('div');
		} else {
			$output .= CreateCommentAnswerForm::widget([
				'question_id' => $this->model->id,
				'answer_id' => $this->model->id,
			]);
		}
		$output .= Html::endTag('ul');
		return $output;
	}
}
