<?php

namespace app\widgets\question;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\question\Answer;
use app\models\question\history\AnswerHistory;
use app\models\helpers\{ModelHelper, HtmlHelper};


class AnswerText extends Widget
{

	public Answer|AnswerHistory $model;
	public bool $is_feed = true;
	public bool $is_short = false;
	public bool $is_first = false;
	public bool $is_unseen = false;
	public bool $is_edited = false;
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
		if ($this->model instanceof Answer) {
			$this->record_type = ModelHelper::TYPE_RECORD;
		} elseif ($this->model instanceof AnswerHistory) {
			$this->record_type = ModelHelper::TYPE_HISTORY;
		}
		return true;
	}

	public function run()
	{
		$output = '';

		$output .= Html::beginTag('div', ['class' => ['bg-light', 'rounded-start-top-0', 'px-3', 'py-2', 'rounded', 'position-relative']]);
		$output .= Html::beginTag('div', ['class' => ['d-flex', 'border-bottom', 'mb-2']]);
		if (!$this->is_short) {
			$output .= $this->getFullHeader();
		} else {
			$output .= $this->getShortHeader();
		}
		$output .= Html::endTag('div');
		$output .= Html::beginTag('div', ['class' => 'answer-text']);
		if ($this->model->is_deleted) {
			$output .= Html::tag('h5', Yii::t('app', 'Ответ удалён'),
				['class' => ['text-break', 'text-center', 'my-0', 'hide-text']]
			);
		} elseif ($this->model->is_hidden) {
			$output .= Html::tag('h5', Yii::t('app', 'Ответ скрыт'),
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
				if ($this->model->files) {
					$output .= Html::beginTag('div', ['class' => ['nav', 'nav-divider', 'my-3', 'h6']]);
					$output .= Html::tag('span', Yii::t('app', 'Прикреплённые файлы:'));
					$output .= Html::endTag('div');
					foreach ($this->model->getFilesDocs() as $file) {
						$output .= Html::tag('div', $file->docHtml, ['class' => ['h5', 'mt-0']]);
					}
					if ($files = $this->model->getFilesImages()) {
						$output .= Html::beginTag('div', ['class' => 'images']);
						foreach ($files as $file) {
							$output .= Html::tag('div', $file->docHtml, ['class' => ['h5', 'mt-0']]);
						}
						$output .= Html::endTag('div');
					}
				}
			}
		}
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		return $output;
	}

	protected function getFullHeader(): string
	{
		$output = '';

		$author = $this->model->author;
		$author_data = $author->data;
		$circle = HtmlHelper::circle();

		if ($this->is_unseen or $this->is_edited) {
			$message = [];
			$classes = [
				'position-absolute', 'translate-middle', 'rounded-circle',
				'top-0', 'start-0', 'p-1',
			];
			if ($this->is_unseen) {
				$message[] = Yii::t('app', 'Новый ответ.');
				$classes[] = 'bg-warning';
			}
			if ($this->is_edited) {
				$message[] = Yii::t('app', 'Ответ отредактирован.');
				$classes[] = 'bg-info';
			}
			$output .= Html::tag('span', null, [
				'class' => $classes,
				'data-toggle' => 'tooltip',
				'title' => implode(' ', $message)
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
			$output .= Html::tag('small',
				Html::tag('span',
					$this->model->editedTimeElapsed, [
					'class' => ['bi', 'bi-pencil-square', 'bi_icon'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Ответ изменён: {datetime}', ['datetime' => $this->model->editedTimeFull])
				])
			);
			$output .= $circle;
		}
		$output .= Html::tag('small',
			Html::tag('span',
			$this->model->timeElapsed, [
				'class' => ['bi', 'bi-clock-fill', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Ответ дан: {datetime}', ['datetime' => $this->model->timeFull])
			])
		);
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
				'title' => Yii::t('app', 'Ответ дан: {datetime}', ['datetime' => $this->model->timeFull])
			]);
		} elseif ($this->is_first and $this->isHistory()) {
			$output .= Html::tag('span',
				$this->model->answer->timeFull, [
				'class' => ['bi', 'bi-clock-fill', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Ответ дан: {datetime}', ['datetime' => $this->model->answer->timeFull])
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
