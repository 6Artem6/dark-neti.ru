<?php

namespace app\widgets\question;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\question\Question;
use app\models\request\ReportType;
use app\models\question\history\QuestionHistory;
use app\models\helpers\{ModelHelper, HtmlHelper};

use app\widgets\question\button\QuestionButton;
use app\widgets\form\DuplicateQuestionResponseModalForm;


class QuestionRecord extends Widget
{

	public Question|QuestionHistory $model;
	public bool $is_short = false;
	public bool $is_first = true;
	public array $report_types;
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
		if ($this->model instanceof Question) {
			$this->record_type = ModelHelper::TYPE_RECORD;
		} elseif ($this->model instanceof QuestionHistory) {
			$this->record_type = ModelHelper::TYPE_HISTORY;
		}
		return true;
	}

	public function run()
	{
		$circle = HtmlHelper::circle();
		$output = '';

		$output .= Html::beginTag('div', ['class' => ['card', 'question-body']]);
		$output .= Html::beginTag('div', ['class' => ['card-header', 'text-secondary', 'h3']]);
		$output .= $this->model->question_title;
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['card-body']]);
		$output .= $this->getHeader();

		$output .= Html::beginTag('div', ['class' => 'question-text']);
		if ($this->model->is_deleted) {
			$output .= Html::tag('h3', Yii::t('app', 'Вопрос удалён'),
				['class' => ['bg-light', 'rounded', 'text-break', 'text-center', 'py-2', 'my-3', 'hide-text']]
			);
		} elseif ($this->model->is_hidden) {
			$output .= Html::tag('h3', Yii::t('app', 'Вопрос скрыт'),
				['class' => ['bg-light', 'rounded', 'text-break', 'text-center', 'py-2', 'my-3', 'hide-text']]
			);
		}
		if (is_null($this->model->canSee())) {
			$output .= $this->getBody();
		}
		$output .= Html::endTag('div');

		$output .= $this->getFooter();

		if (!$this->is_short) {
			if (is_null($this->model->canEdit())) {
				$output .= $this->getAuthorNav();
			}
			$output .= $this->getQuestionNav();
			if (!empty($this->model->duplicateRequests)) {
				$output .= DuplicateQuestionResponseModalForm::widget([
					'question_id' => $this->model->id
				]);
			}
		}

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		return $output;
	}

	protected function getHeader(): string
	{
		$item_list = [];
		if (!$this->is_short) {
			$item_list[] = HtmlHelper::getIconText(Yii::t('app', 'Автор:'), true) .
			Html::a(Html::tag('span', $this->model->author->name,
					['class' => [ 'badge', 'rounded', 'bg-warning', 'text-dark' ] ]),
				$this->model->author->getPageLink()
			);
		}
		if ($this->model->discipline) {
			$item_list[] = HtmlHelper::getIconText(Yii::t('app', 'Предмет:'), true) .
			Html::a($this->model->discipline->name,
				$this->model->discipline->getRecordLink(),
				[ 'class' => [ 'badge', 'rounded-pill', 'bg-info', 'text-white', 'badge-discipline'] ]
			);
		}
		$item_list[] = HtmlHelper::getIconText(Yii::t('app', 'Факультет:'), true) .
		Html::a($this->model->faculty->faculty_shortname,
			$this->model->faculty->getRecordLink(),
			[ 'class' => [ 'badge', 'rounded-pill', 'bg-info', 'text-white'] ]
		);
		if (!$this->is_short) {
			if ($this->model->is_closed) {
				$title = HtmlHelper::getIconText(Yii::t('app', 'Закрыт'));
				$item_list[] = Html::tag('span', $title, [
					'class' => ['badge', 'rounded-pill', 'bg-success', 'fw-bold', 'bi', 'bi-check-circle-fill', 'bi_icon'],
					'data-toggle' => 'tooltip',
					'title' =>  Yii::t('app', "Вопрос закрыт.\nОтветы больше не принимаются.")
				]);
			} elseif (!$this->model->is_closed and $this->model->end_datetime) {
				$item_list[] = Html::tag('span', $this->model->end_datetime, [
					'class' => ['time', 'pe-1'],
					'style' => ['display' => 'none'],
					'data-time' => date('Y-m-d H:i:s', strtotime($this->model->end_datetime)),
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Время сдачи задания: {datetime}', ['datetime' => $this->model->endTimeFull])
				]);
			}
		}

		$count = count($item_list);
		return Html::ul($item_list, [
			'tag' => 'div',
			'class' => ['nav', 'nav-stack', 'align-items-center', 'gap-1'],
			'item' => function($item, $index) use($count, $item_list) {
				$return = $item;
				if ($index < $count - 1) {
					$class = null;
					if (str_contains($item_list[$index + 1], 'time')) {
						$class = 'time-dot';
					}
					$return .= HtmlHelper::circle($class);
				}
				return Html::tag('li', $return, ['class' => 'nav-item']);
			},
		]);
	}

	protected function getBody(): string
	{
		$output = '';

		if ($this->model->hasDuplicate) {
			$output .= Html::beginTag('div', ['class' => ['bg-light', 'rounded', 'text-break', 'duplicate-text', 'px-2']]);
			$output .= Html::tag('h6', Yii::t('app', 'Вопрос уже имеет решение:'), ['class' => ['my-2']]);
			foreach ($this->model->duplicateAccepted as $duplicate) {
				$question = $duplicate->duplicateQuestion;
				$text = $question->question_title;
				$text .= ' ' . Yii::t('app', '(ответов: {count})', [
					'count' => $question->answer_count
				]);
				$output .= Html::beginTag('h6');
				$output .= Html::a($text,
					$question->getRecordLink(),
					['class' => ['my-3']]
				);
				$output .= Html::endTag('h6');
			}
			$output .= Html::endTag('div');
		}

		$output .= Html::tag('div',
			$this->model->text,
			['class' => ['bg-light', 'rounded', 'text-break', 'text-justify', 'mt-4', 'px-2', 'py-1', 'h5']]
		);

		$item_list = [];
		$item_list[] = Html::tag('span', Yii::t('app', 'Описание:'), ['class' => 'me-1']);
		if ($type = $this->model->type) {
			$item_list[] = Html::a($type->type_name, $type->getRecordLink(),
				['class' => ['badge', 'rounded-pill', 'border', 'border-primary', 'bg-light', 'text-secondary']]
			);
		}
		if ($tags = $this->model->tagRecords) {
			foreach ($tags as $tag) {
				$item_list[] = Html::a($tag->name,
					$tag->getRecordLink(),
					['class' => ['badge', 'rounded-pill', 'border', 'border-info', 'bg-light', 'text-secondary']]
				);
			}
		}

		$count = count($item_list);
		$output .= Html::ul($item_list, [
			'tag' => 'div',
			'class' => ['nav', 'nav-divider', 'mt-2', 'h6'],
			'item' => function($item, $index) use($count) {
				$return = $item;
					if (($index > 0) and ($index < $count - 1)) {
					$return .= HtmlHelper::circle();
				}
				return Html::tag('div', $return);
			},
		]);

		if ($teacher = $this->model->teacher) {
			$item_list = [];
			$item_list[] = Html::tag('span', Yii::t('app', 'Преподаватель:'), ['class' => 'me-1']);
			$item_list[] = Html::a($teacher->teacher_name, $teacher->getRecordLink(),
				['class' => ['badge', 'rounded-pill', 'bg-primary', 'text-white']]
			);

			$count = count($item_list);
			$output .= Html::ul($item_list, [
				'tag' => 'div',
				'class' => ['nav', 'nav-divider', 'mt-2', 'h6'],
				'item' => function($item, $index) use($count) {
					$return = $item;
					if (($index > 0) and ($index < $count - 1)) {
						$return .= HtmlHelper::circle();
					}
					return Html::tag('div', $return);
				},
			]);
		}

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

		return $output;
	}

	protected function getFooter(): string
	{
		$item_list = [];
		if ($this->is_short) {
			if ($this->is_first and $this->isRecord()) {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Задан:')) . $this->model->timeElapsed;
				$item_list[] = Html::tag('span', $text, [
					'class' => ['bi', 'bi-clock-fill', 'bi_icon'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Вопрос задан: {datetime}', ['datetime' => $this->model->timeFull])
				]);
			} elseif ($this->is_first and $this->isHistory()) {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Задан:')) . $this->model->question->timeElapsed;
				$item_list[] = Html::tag('span', $text, [
					'class' => ['bi', 'bi-clock-fill', 'bi_icon'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Вопрос задан: {datetime}', ['datetime' => $this->model->question->timeFull])
				]);
			} elseif (!$this->is_first and $this->isRecord()) {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Время последнего изменения:')) . $this->model->editedTimeElapsed;
				$item_list[] = Html::tag('span', $text, [
					'class' => ['bi', 'bi-clock-fill', 'bi_icon'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Время последнего изменения: {datetime}', ['datetime' => $this->model->editedTimeFull])
				]);
			} elseif (!$this->is_first and $this->isHistory()) {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Время изменения:')) . $this->model->timeElapsed;
				$item_list[] = Html::tag('span', $text, [
					'class' => ['bi', 'bi-clock-fill', 'bi_icon'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Время изменения: {datetime}', ['datetime' => $this->model->timeFull])
				]);
			}
		} else {
			$text = HtmlHelper::getIconText(Yii::t('app', 'Задан:')) . $this->model->timeElapsed;
			$item_list[] = Html::tag('span', $text, [
				'class' => ['bi', 'bi-clock-fill', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Вопрос задан: {datetime}', ['datetime' => $this->model->timeFull])
			]);
			if ($this->model->isEdited()) {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Изменён:')) . $this->model->editedTimeElapsed;
				$item_list[] = Html::tag('span', $text, [
					'class' => ['bi', 'bi-pencil-square', 'bi_icon'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Вопрос изменён: {datetime}', ['datetime' => $this->model->editedTimeFull])
				]);
			}
			$text = HtmlHelper::getIconText(Yii::t('app', 'Просмотров:')) . $this->model->views;
			$item_list[] = Html::tag('span', $text, [
				'class' => ['bi', 'bi-eye-fill', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Вопрос просмотрели раз: {views}', ['views' => $this->model->views])
			]);

			$text = HtmlHelper::getIconText(Yii::t('app', 'Подписчиков:')) .
				($this->model->followers ? $this->model->followers : Yii::t('app', 'нет'));
			$title_text = ($this->model->followers ?
				Yii::t('app', 'На вопрос подписано пользователей: {followers}', ['followers' => $this->model->followers]) :
				Yii::t('app', 'На вопрос пока не было подписок')
			);
			$title = Html::tag('span', $text, [
				'class' => ['bi', 'bi-bell-fill', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' => $title_text
			]);
			$item_list[] = Html::a($title,
				$this->model->getFollowersLink(),
				['class' => ['text-secondary', 'link-primary']]
			);
		}

		$count = count($item_list);
		return Html::ul($item_list, [
			'class' => ['nav', 'nav-stack', 'gap-1', 'align-items-center', 'py-4'],
			'item' => function($item, $index) use($count) {
				$return = $item;
				if ($index < $count - 1) {
					$return .= HtmlHelper::circle();
				}
				return Html::tag('li', $return, ['class' => 'nav-item']);
			},
		]);
	}

	protected function getAuthorNav(): string
	{
		$item_list = [];
		if (!$this->model->is_closed) {
			if ($this->model->isAnswered) {
				$title = Html::tag('span', null, ['class' => ['bi', 'bi-check', 'text-success']]) .
					HtmlHelper::getIconText(Yii::t('app', 'Закрыть вопрос'));
				$item_list[] = HtmlHelper::actionButton($title, 'close', $this->model->id, [
					'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0', 'p-2'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Отметить вопрос решённым'),
				]);
			}

			$title = Html::tag('span', null, ['class' => ['bi', 'bi-pencil-square', 'text-info']]) .
				HtmlHelper::getIconText(Yii::t('app', 'Редактировать вопрос'));
			$item_list[] = Html::a($title, $this->model->getEditLink(), [
				'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0', 'p-2'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Изменить параметры и содержание вопроса'),
			]);

			if (!empty($this->model->duplicateRequests)) {
				$title = Html::tag('span', null, ['class' => ['bi', 'bi-question-circle', 'text-info']]) .
					HtmlHelper::getIconText(Yii::t('app', 'Просмореть предложения'));
				$item_list[] = Html::button($title, [
					'class' => ['btn', 'btn-outline-light', 'text-secondary', 'duplicateQuestionResponseButton', 'mb-0', 'p-2'],
					'data-id' => $this->model->id,
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Просмотреть предложенные похожие вопросы'),
					'data' => [
						'bs-toggle' => 'modal',
						'bs-target' => '#duplicateQuestionResponseForm-' . $this->model->id
					],
				]);
			}
		} else {
			$title = Html::tag('span', null, ['class' => ['bi', 'bi-x', 'text-danger']]) .
				HtmlHelper::getIconText(Yii::t('app', 'Открыть вопрос'));
			$item_list[] = HtmlHelper::actionButton($title, 'open', $this->model->id, [
				'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0', 'p-2'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Отметить вопрос нерешённым'),
			]);
		}

		return Html::ul($item_list, [
			'class' => ['nav', 'nav-pills', 'nav-pills-light', 'nav-fill', 'nav-stack', 'border-top', 'question-nav', 'py-1'],
			'item' => function($item, $index) {
				return Html::tag('li', $item, ['class' => ['nav-item']]);
			},
		]);
	}

	protected function getQuestionNav(): string
	{
		$output = '';

		$item_list = [];
		if ($count = $this->model->commentCount) {
			$text = HtmlHelper::getIconText(Yii::t('app', 'Комментарии')) .
				HtmlHelper::getCountText($count, true);
			$title = Html::tag('span', $text, ['class' => ['bi', 'bi-chat', 'bi_icon']]);
		} else {
			$text = HtmlHelper::getIconText(Yii::t('app', 'Комментировать'));
			$title = Html::tag('span', $text, ['class' => ['bi', 'bi-chat', 'bi_icon', 'no_text']]);
		}
		$item_list[] = Html::tag('button', $title, [
			'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0', 'p-2', 'collapsed'],
			'aria-expanded' => 'false',
			'aria-controls' => 'comments',
			'data' => [
				'bs-toggle' => 'collapse',
				'bs-target' => '#comments',
				'record-type' => 'question',
				'button-type' => 'comments',
				'id' => $this->model->id,
			],
		]);

		$append = HtmlHelper::getCountText($this->model->followers, true);
		if (!$this->model->isAuthor and !$this->model->isFollowed) {
			$text = HtmlHelper::getIconText(Yii::t('app', 'Подписаться')) . $append;
			$title = Html::tag('span', $text, ['class' => ['bi', 'bi-bell', 'bi_icon']]);
			$item_list[] = HtmlHelper::actionButton($title, 'follow-question', $this->model->id, [
				'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0', 'p-2'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Подписаться на вопрос')
			]);
		} elseif (!$this->model->isAuthor and $this->model->isFollowed) {
			$text = HtmlHelper::getIconText(Yii::t('app', 'Вы подписаны')) . $append;
			$title = Html::tag('span', $text, ['class' => ['bi', 'bi-bell-fill', 'bi_icon']]);
			$item_list[] = HtmlHelper::actionButton($title, 'unfollow-question', $this->model->id, [
				'class' => ['btn', 'btn-light', 'text-secondary', 'mb-0', 'p-2'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Отписаться от вопроса')
			]);
		} elseif ($this->model->isAuthor) {
			$text = HtmlHelper::getIconText(Yii::t('app', 'Вы подписаны')) . $append;
			$title = Html::tag('span', $text, ['class' => ['bi', 'bi-bell-fill', 'bi_icon']]);
			$item_list[] = Html::button($title, [
				'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0', 'p-2'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Автор воспроса должен быть подписан на него')
			]);
		}

		$text = HtmlHelper::getIconText(Yii::t('app', 'Поделиться'));
		$title = Html::tag('span', $text, ['class' => ['bi', 'bi-share-fill', 'bi_icon']]);
		$id = 'share-question-'.$this->model->id;
		$item_list[] = Html::button($title, [
			'id' => $id,
			'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0', 'p-2', 'btn-share-link'],
			'data-toggle' => 'tooltip',
			'title' => Yii::t('app', 'Скопировать ссылку на вопрос')
		]);

		$item_list[] = QuestionButton::widget([
			'model' => $this->model,
			'report_types' => $this->report_types[ReportType::TYPE_QUESTION]
		]);

		$count = count($item_list);
		$output .= Html::ul($item_list, [
			'class' => ['nav', 'nav-pills', 'nav-pills-light', 'nav-fill', 'nav-stack', 'border-top', 'question-nav', 'py-1'],
			'item' => function($item, $index) use($count) {
				if ($index < $count - 1) {
					$item = Html::tag('li', $item, ['class' => ['nav-item']]);
				}
				return $item;
			},
		]);

		$link = $this->model->getRecordLink(true);
		$output .= HtmlHelper::getShareDiv($id, $link, Yii::t('app', 'Ссылка на воспрос'));

		return $output;
	}

}
