<?php

namespace app\widgets\question;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\question\Comment;
use app\models\question\history\CommentHistory;
use app\models\helpers\{ModelHelper, HtmlHelper};


class CommentText extends Widget
{

	public Comment|CommentHistory $model;
	public bool $is_feed = true;
	public bool $is_short = false;
	public bool $is_first = false;
	public bool $is_unseen = false;
	protected string $record_type;

	protected function isRecord() {
		return $this->record_type == ModelHelper::TYPE_RECORD;
	}

	protected function isHistory() {
		return $this->record_type == ModelHelper::TYPE_HISTORY;
	}

	public function beforeRun()
	{
		if (!parent::beforeRun()) {
			return false;
		}
		if ($this->model->isNewRecord) {
			return false;
		}
		if ($this->model instanceof Comment) {
			$this->record_type = ModelHelper::TYPE_RECORD;
		} elseif ($this->model instanceof CommentHistory) {
			$this->record_type = ModelHelper::TYPE_HISTORY;
		}
		return true;
	}

	public function run()
	{
		$output = '';

		$output .= Html::beginTag('div', ['class' => ['bg-light', 'px-3', 'py-2', 'rounded', 'position-relative']]);
		$output .= Html::beginTag('div', ['class' => ['d-flex', 'border-bottom', 'mb-2']]);
		if (!$this->is_short) {
			$output .= $this->getFullHeader();
		} else {
			$output .= $this->getShortHeader();
		}
		$output .= Html::endTag('div');
		$output .= Html::beginTag('div', ['class' => 'answer-text']);
		if ($this->model->is_deleted) {
			$output .= Html::tag('h5', Yii::t('app', 'Комментарий удалён'),
				['class' => ['text-break', 'text-center', 'my-0', 'hide-text']]
			);
		} elseif ($this->model->is_hidden) {
			$output .= Html::tag('h5', Yii::t('app', 'Комментарий скрыт'),
				['class' => ['text-break', 'text-center', 'my-0', 'hide-text']]
			);
		}
		if (is_null($this->model->canSee())) {
			if ($this->is_feed) {
				$output .= Html::beginTag('div', ['class' => 'mb-0']);
				$output .= Html::a($this->model->shortText,
					$this->model->getRecordLink(),
					['class' => 'text-secondary']
				);
				$output .= Html::endTag('div');
			} else {
				$output .= Html::tag('div', $this->model->text, ['class' => ['mb-0', 'text-justify']]);
			}
		}
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		return $output;
	}

	protected function getFullHeader(): string
	{
		$author = $this->model->author;
		$author_data = $author->data;
		$circle = HtmlHelper::circle();

		$output = '';
		if ($this->is_unseen) {
			$output .= Html::tag('span', null, [
				'class' => [
					'position-absolute',  'translate-middle', 'rounded-circle',
					'bg-info', 'top-0', 'start-0', 'p-1',
				],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Новый комментарий.')
			]);
		}

		$output .= Html::beginTag('div', ['class' => 'flex-grow-1']);
		$output .= Html::beginTag('h6', ['class' => 'mb-1']);
		if ($this->model->isAuthorQuestionAuthor) {
			$title = Html::tag('span', $author->name, [
				'class' => ['badge', 'rounded', 'bg-warning', 'text-dark'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Автор вопроса')
			]);
			$output .= Html::a($title, $author->getPageLink(), ['class' => ['pe-1']]);
		} else {
			$title = Html::tag('span', $author->name);
			$output .= Html::a($title, $author->getPageLink(), ['class' => ['pe-1']]);
		}
		$output .= Html::endTag('h6');
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div');
		if ($this->model->isEdited()) {
			$title = Html::tag('span',
				$this->model->editedTimeElapsed, [
				'class' => ['bi', 'bi-pencil-square', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Комментарий изменён: {datetime}', ['datetime' => $this->model->editedTimeFull])
			]);
			$output .= Html::tag('small', $title);
			$output .= $circle;
		}
		$title = Html::tag('span',
			$this->model->timeElapsed, [
			'class' => ['bi', 'bi-clock-fill', 'bi_icon'],
			'data-toggle' => 'tooltip',
			'title' => Yii::t('app', 'Комментарий оставлен: {datetime}', ['datetime' => $this->model->timeFull])
		]);
		$output .= Html::tag('small', $title);
		$output .= Html::endTag('div');
		return $output;
	}

	protected function getShortHeader(): string
	{
		$output = '';
		$output .= Html::beginTag('div');
		$output .= Html::beginTag('small');
		if ($this->is_first and $this->isRecord()) {
			$output .= Html::tag('span',
				$this->model->timeFull, [
				'class' => ['bi', 'bi-clock-fill', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Комментарий дан: {datetime}', ['datetime' => $this->model->timeFull])
			]);
		} elseif ($this->is_first and $this->isHistory()) {
			$output .= Html::tag('span',
				$this->model->comment->timeFull, [
				'class' => ['bi', 'bi-clock-fill', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Комментарий дан: {datetime}', ['datetime' => $this->model->comment->timeFull])
			]);
		} elseif (!$this->is_first and $this->isRecord()) {
			$output .= Html::tag('span',
				$this->model->editedTimeFull, [
				'class' => ['bi', 'bi-clock-fill', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Время последнего изменения: {datetime}', ['datetime' => $this->model->editedTimeFull])
			]);
		} elseif (!$this->is_first and $this->isHistory()) {
			$output .= Html::tag('span',
				$this->model->timeFull, [
				'class' => ['bi', 'bi-clock-fill', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Время изменения: {datetime}', ['datetime' => $this->model->timeFull])
			]);
		}
		$output .= Html::endTag('small');
		$output .= Html::endTag('div');
		return $output;
	}

}
