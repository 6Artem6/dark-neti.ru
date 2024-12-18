<?php

namespace app\widgets\question;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\question\{Question, Answer, Comment};
use app\models\request\ReportType;
use app\models\helpers\{ModelHelper, HtmlHelper};

use app\widgets\question\button\{
	QuestionButton, AnswerButton, CommentButton
};


class FeedRecord extends Widget
{

	public Question|Answer|Comment $model;
	public array $unseen_list;
	public array $report_types = [];
	protected string $record_type;
	protected Question $question;
	protected int $message_count = 0;

	protected function isQuestion() {
		return ($this->record_type == ModelHelper::TYPE_QUESTION);
	}

	protected function isAnswer() {
		return ($this->record_type == ModelHelper::TYPE_ANSWER);
	}

	protected function isComment() {
		return ($this->record_type == ModelHelper::TYPE_COMMENT);
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
			$this->record_type = ModelHelper::TYPE_QUESTION;
			$this->question = $this->model;
		} elseif ($this->model instanceof Answer) {
			$this->record_type = ModelHelper::TYPE_ANSWER;
			$this->question = $this->model->question;
		} elseif ($this->model instanceof Comment) {
			$this->record_type = ModelHelper::TYPE_COMMENT;
			$this->question = $this->model->question;
		}
		return true;
	}

	public function run()
	{
		$output = '';
		$output .= Html::beginTag('div', ['class' => ['card', 'px-1']]);
		$output .= $this->getHeader();
		$output .= $this->getBody();
		$output .= $this->getFooter();
		$output .= Html::endTag('div');
		return $output;
	}

	protected function getHeader(): string
	{
		$output = '';

		$output .= Html::beginTag('div', ['class' => ['card-header', 'border-0', 'pb-0', 'pt-3', 'px-3']]);
		$output .= Html::beginTag('div', ['class' => ['d-flex', 'align-items-center', 'justify-content-between']]);

		$output .= Html::beginTag('div', ['class' => ['d-flex', 'align-items-center']]);
		$output .= Html::beginTag('div', ['class' => ['avatar', 'me-2']]);
		$output .= $this->model->author->getAvatar('md');
		$output .= Html::endTag('div');

		$item_list = [];

		if ($this->isQuestion()) {
			$title = Html::tag('span', $this->model->author->name, [
				'class' => [ 'badge', 'rounded', 'bg-warning', 'text-dark' ]
			]);
			$item_list[] = Html::a($title, $this->model->author->getPageLink());
		} else {
			if ($this->model->isAuthorQuestionAuthor) {
				$title = Html::tag('span', $this->model->author->name, [
					'class' => [ 'badge', 'rounded', 'bg-warning', 'text-dark' ]
				]);
				$item_list[] = Html::a($title, $this->model->author->getPageLink());
			} else {
				$title = Html::tag('span', $this->model->author->name);
				$item_list[] = Html::a($title, $this->model->author->getPageLink(), ['class' => 'h6']);
			}
		}

		if ($this->question->discipline) {
			$item_list[] = Html::a($this->question->discipline->shortname,
				$this->question->discipline->getRecordLink(), [
					'class' => [ 'badge', 'rounded-pill', 'bg-info', 'badge-discipline' ],
					'data-toggle' => 'tooltip',
					'title' => $this->question->discipline->name
				]
			);
		}
		$item_list[] = Html::a($this->question->faculty->faculty_shortname,
			$this->question->faculty->getRecordLink(),
			[ 'class' => [ 'badge', 'rounded-pill', 'bg-info', 'text-white'] ]
		);
		if ($this->model->edited_datetime) {
			$item_list[] = Html::tag('span',
				$this->model->editedTimeElapsed, [
				'class' => ['bi', 'bi-pencil-square', 'bi_icon', 'small'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Вопрос изменён: {datetime}',
					['datetime' => $this->model->editedTimeFull]
				)
			]);
		}
		$item_list[] = Html::tag('span',
			$this->model->timeElapsed, [
			'class' => ['bi', 'bi-clock-fill', 'bi_icon', 'small'],
			'data-toggle' => 'tooltip',
			'title' => Yii::t('app', 'Вопрос задан: {datetime}', [
				'datetime' => $this->model->timeFull
			])
		]);
		if (!$this->question->is_closed and $this->question->end_datetime) {
			$item_list[] = Html::tag('span',
				$this->question->end_datetime, [
				'class' => ['time', 'small'],
				'style' => ['display' => 'none'],
				'data-time' => date('Y-m-d H:i:s', strtotime($this->question->end_datetime)),
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Время сдачи задания: {datetime}', [
					'datetime' => $this->question->endTimeFull
				])
			]);
		} elseif ($this->question->is_closed){
			$title = HtmlHelper::getIconText(Yii::t('app', 'Закрыт'));
			$link = ModelHelper::getSearchParamLink('is_closed', true);
			$item_list[] = Html::a($title, $link, [
				'class' => ['badge', 'rounded-pill', 'bg-success', 'fw-bold',
					'bi', 'bi-check-circle-fill', 'bi_icon', 'no_text'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', "Вопрос закрыт. Ответы больше не принимаются.")
			]);
		}

		if ($this->isQuestion()) {
			$text = HtmlHelper::getCountText($this->model->views);
			$item_list[] = Html::tag('span', $text, [
				'class' => ['bi', 'bi-eye-fill', 'bi_icon'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Количество раз вопрос был просмотрен')
			]);
		}

		$count = count($item_list);
		$output .= Html::beginTag('div');
		$output .= Html::ul($item_list, [
			'tag' => 'div',
			'class' => ['nav', 'nav-divider'],
			'item' => function($item, $index) use($count, $item_list) {
				$return = $item;
				if ($index < $count - 1) {
					$class = null;
					if (str_contains($item_list[$index + 1], 'time')) {
						$class = 'time-dot';
					}
					$return .= HtmlHelper::circle($class);
				}
				return Html::tag('div', $return);
			},
		]);

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		return $output;
	}

	protected function getBody(): string
	{
		$output = '';

		$output .= Html::beginTag('div', ['class' => ['card-body', 'py-3', 'px-3']]);
		$output .= $this->getRecordBody();

		if ($this->isQuestion()) {
			if ($answer = $this->model->answerBest) {
				$this->message_count = $this->model->answer_count;
				$is_unseen = false;
				$is_edited = false;
				if (!empty($this->unseen_list[$this->question->id]['answers'])) {
					$is_unseen = in_array($answer->id, $this->unseen_list[$this->question->id]['answers']);
				}
				if (!empty($this->unseen_list[$this->question->id]['edited'])) {
					$is_edited = in_array($answer->id, $this->unseen_list[$this->question->id]['edited']);
				}
				$output .= Html::beginTag('ul', ['class' => ['comment-wrap', 'list-unstyled', 'pt-3', 'mb-0']]);
				$output .= AnswerBody::widget([
					'model' => $answer,
					'report_types' => $this->report_types[ReportType::TYPE_ANSWER],
					'unseen_comment_list' => $this->unseen_list[$this->model->question_id]['comments'] ?? [],
					'is_feed' => true,
					'is_unseen' => $is_unseen,
					'is_edited' => $is_edited,
				]);
				$output .= Html::endTag('ul');
			}
		} elseif ($this->isAnswer()) {
			if ($comments = $this->model->commentFeedList) {
				$this->message_count = $this->model->comment_count;
				$output .= Html::beginTag('ul', ['class' => ['comment-wrap', 'list-unstyled', 'pt-3', 'mb-0']]);
				foreach ($comments as $comment) {
					$is_unseen = false;
					if (!empty($this->unseen_list[$this->question->id]['comments'])) {
						$is_unseen = in_array($comment->id, $this->unseen_list[$this->question->id]['comments']);
					}
					$output .= CommentBody::widget([
						'model' => $comment,
						'report_types' => $this->report_types[ReportType::TYPE_COMMENT],
						'is_feed' => true,
						'is_unseen' => $is_unseen,
					]);
				}
				$output .= Html::endTag('ul');
			}
		} elseif ($this->isComment()) {
			$this->message_count = 0;
		}

		$output .= Html::endTag('div');

		return $output;
	}

	protected function getFooter(): string
	{
		$output = '';
		if ($this->isQuestion() and ($this->message_count > 1)) {
			$output .= Html::beginTag('div', ['class' => ['card-footer', 'border-0', 'pt-0']]);
			$output .= Html::beginTag('a', [
				'href' => $this->model->getAnswersLink(),
				'class' => ['btn', 'btn-link', 'btn-sm', 'text-secondary', 'd-flex', 'align-items-center'],
			]);
			$output .= Html::beginTag('div', ['class' => 'spinner-dots me-2']);
			$output .= Html::tag('span', null, ['class' => 'spinner-dot']);
			$output .= Html::tag('span', null, ['class' => 'spinner-dot']);
			$output .= Html::tag('span', null, ['class' => 'spinner-dot']);
			$output .= Html::tag('span', Yii::t('app', 'Показать все ответы'), ['class' => 'ms-1']);
			$output .= Html::endTag('div');
			$output .= Html::endTag('a');
			$output .= Html::endTag('div');
		} elseif($this->isAnswer() and ($this->message_count > 1)) {
			$output .= Html::beginTag('div', ['class' => ['card-footer', 'border-0', 'pt-0']]);
			$output .= Html::beginTag('a', [
				'href' => $this->model->getCommentsLink(),
				'class' => ['btn', 'btn-link', 'btn-sm', 'text-secondary', 'd-flex', 'align-items-center'],
			]);
			$output .= Html::beginTag('div', ['class' => 'spinner-dots me-2']);
			$output .= Html::tag('span', null, ['class' => 'spinner-dot']);
			$output .= Html::tag('span', null, ['class' => 'spinner-dot']);
			$output .= Html::tag('span', null, ['class' => 'spinner-dot']);
			$output .= Html::tag('span', Yii::t('app', 'Показать все комментарии'), ['class' => 'ms-1']);
			$output .= Html::endTag('div');
			$output .= Html::endTag('a');
			$output .= Html::endTag('div');
		}
		return $output;
	}

	protected function getRecordBody(): string
	{
		$output = '';

		$output .= Html::tag('h4',
			Html::a($this->question->question_title, $this->question->getRecordLink()),
			['class' => ['mt-0', 'fw-bold']]
		);
		if ($this->isComment()) {
			if (!empty($this->model->answer)) {
				if ($this->model->answer->is_hidden) {
					$output .= Html::tag('h5', Yii::t('app', 'Ответ скрыт'),
						['class' => ['text-break', 'text-center', 'my-0', 'hide-text']]
					);
				}
				if (is_null($this->model->answer->canSee())) {
					$output .= Html::tag('h5',
						Html::a($this->model->answer->shortText, $this->model->getRecordLink()),
						['class' => ['my-0', 'fst-italic']]
					);
				}
			}
		}
		if ($this->model->is_hidden) {
			$output .= Html::tag('h5', Yii::t('app', 'Запись скрыта'),
				['class' => ['text-break', 'text-center', 'my-0', 'hide-text']]
			);
		}
		if (is_null($this->model->canSee())) {
			$output .= Html::tag('p',
				Html::a($this->model->shortText, $this->model->getRecordLink(), ['class' => 'text-secondary']),
				['class' => ['my-0', 'text-justify']]
			);
		}

		$output .= $this->getTags();

		$item_list = [];
		if ($this->isAnswer() or $this->isComment()) {
			$text = HtmlHelper::getIconText(Yii::t('app', 'Полезный')) .
				HtmlHelper::getCountText($this->model->like_count, true);
			if($this->model->isAuthor) {
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-hand-thumbs-up', 'bi_icon']]);
				$item_list[] = Html::button($title, [
					'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0', 'p-2'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Автор записи не может голосовать за неё'),
				]);
			} elseif ($this->model->isLiked) {
				$action = 'remove-like-'.$this->record_type;
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-hand-thumbs-up-fill', 'bi_icon']]);
				$item_list[] = HtmlHelper::actionButton($title, $action, $this->model->id, [
					'class' => ['btn', 'btn-light', 'text-secondary', 'mb-0', 'p-2'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Убрать отметку')
				]);
			} else {
				$action = 'add-like-'.$this->record_type;
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-hand-thumbs-up', 'bi_icon']]);
				$item_list[] = HtmlHelper::actionButton($title, $action, $this->model->id, [
					'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0', 'p-2'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Отметить полезным')
				]);
			}
		}

		if ($this->isQuestion()) {
			$count = $this->model->answer_count;
			$classes = ['btn', 'text-secondary', 'mb-0', 'p-2'];
			if ($this->model->isAnswered) {
				$classes[] = 'btn-success';
			} elseif ($count) {
				$classes[] = 'btn-outline-success';
			} else {
				$classes[] = 'btn-outline-light';
			}

			if ($count) {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Ответы')) .
					HtmlHelper::getCountText($count, true);
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-chat-left-text', 'bi_icon']]);
				$tooltip_title = Yii::t('app', 'Перейти ко всем ответам вопроса');
			} else {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Ответить'));
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-chat-left-text', 'bi_icon', 'no_text']]);
				$tooltip_title = Yii::t('app', 'Дать ответ на вопрос');
			}
			$item_list[] = Html::a($title,
				$this->model->getAnswersLink(), [
				'class' => $classes,
				'data-toggle' => 'tooltip',
				'title' => $tooltip_title
			]);

			if ($count = $this->model->comment_count) {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Комментарии')) .
					HtmlHelper::getCountText($count, true);
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-chat', 'bi_icon']]);
				$tooltip_title = Yii::t('app', 'Перейти ко всем комментариям воспроса');
			} else {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Комментировать'));
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-chat', 'bi_icon', 'no_text']]);
				$tooltip_title = Yii::t('app', 'Оставить комментарий к вопросу');
			}
			$item_list[] = Html::a($title,
				$this->model->getCommentsLink(), [
				'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0', 'p-2'],
				'data-toggle' => 'tooltip',
				'title' => $tooltip_title
			]);
		} elseif ($this->isAnswer()) {
			if ($count = $this->model->comment_count) {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Комментарии')) .
					HtmlHelper::getCountText($count, true);
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-chat', 'bi_icon']]);
			} else {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Комментировать'));
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-chat', 'bi_icon', 'no_text']]);
			}
			$item_list[] = Html::a($title,
				$this->model->getCommentsLink(), [
				'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0', 'p-2'],
				'data-toggle' => 'tooltip',
				'title' => Yii::t('app', 'Перейти ко всем комментариям ответа')
			]);
		}

		if ($this->isQuestion()) {
			$append = HtmlHelper::getCountText($this->model->followers, true);
			if ($this->model->isAuthor) {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Вы подписаны')) . $append;
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-bell-fill', 'bi_icon']]);
				$item_list[] = Html::button($title, [
					'class' => ['btn', 'btn-light', 'text-secondary', 'mb-0', 'p-2'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Автор воспроса подписан на него')
				]);
			} elseif ($this->model->isFollowed) {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Вы подписаны')) . $append;
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-bell-fill', 'bi_icon']]);
				$item_list[] = HtmlHelper::actionButton($title, 'unfollow-question', $this->model->id, [
					'class' => ['btn', 'btn-light', 'text-secondary', 'mb-0', 'p-2'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Отписаться от вопроса')
				]);
			} else {
				$text = HtmlHelper::getIconText(Yii::t('app', 'Подписаться')) . $append;
				$title = Html::tag('span', $text, ['class' => ['bi', 'bi-bell', 'bi_icon']]);
				$item_list[] = HtmlHelper::actionButton($title, 'follow-question', $this->model->id, [
					'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0', 'p-2'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Подписаться на вопрос')
				]);
			}
		}

		$text = HtmlHelper::getIconText(Yii::t('app', 'Поделиться'));
		$title = Html::tag('span', $text, ['class' => ['bi', 'bi-share-fill', 'bi_icon']]);
		$id = 'share-' . $this->record_type .'-' . $this->model->id;
		$item_list[] = Html::button($title, [
			'id' => $id,
			'class' => ['btn', 'btn-outline-light', 'text-secondary', 'mb-0', 'p-2', 'btn-share-link'],
			'data-toggle' => 'tooltip',
			'title' => Yii::t('app', 'Скопировать ссылку на запись')
		]);

		if ($this->isQuestion()) {
			$item_list[] = QuestionButton::widget([
				'model' => $this->model,
				'report_types' => $this->report_types[ReportType::TYPE_QUESTION]
			]);
		} elseif ($this->isAnswer()) {
			$item_list[] = AnswerButton::widget([
				'model' => $this->model,
				'report_types' => $this->report_types[ReportType::TYPE_ANSWER],
				'div_id' => $id,
				'is_feed' => true,
			]);
		} elseif ($this->isComment()) {
			$item_list[] = CommentButton::widget([
				'model' => $this->model,
				'report_types' => $this->report_types[ReportType::TYPE_COMMENT],
				'div_id' => $id,
				'is_feed' => true,
			]);
		}

		$count = count($item_list);
		$output .= Html::ul($item_list, [
			'class' => ['nav', 'nav-pills', 'nav-pills-light', 'nav-fill', 'nav-stack', 'feed-nav', 'py-1'],
			'item' => function($item, $index) use($count) {
				if ($index < $count - 1) {
					$item = Html::tag('li', $item, ['class' => 'nav-item']);
				}
				return $item;
			},
		]);

		$link = $this->model->getRecordLink(true);
		$output .= HtmlHelper::getShareDiv($id, $link, Yii::t('app', 'Ссылка на запись'));

		return $output;
	}

	protected function getTags(): string
	{
		$item_list = [];
		$item_list[] = Html::Tag('div', Yii::t('app', 'Описание: '), ['class' => ['me-1']]);
		if ($this->isQuestion()) {
			if ($type = $this->model->type) {
				$item_list[] = Html::a($type->type_name, $type->getRecordLink(),
					['class' => ['badge', 'rounded-pill', 'border', 'border-primary', 'bg-light', 'text-secondary']]
				);
			}
			if ($tags = $this->question->tagRecords){
				foreach ($tags as $tag) {
					$item_list[] = Html::a($tag->name,
						$tag->getRecordLink(),
						['class' => ['badge', 'rounded-pill', 'border', 'border-info', 'bg-light', 'text-secondary']]
					);
				}
			}
			if ($this->question->fileCount){
				$item_list[] = Html::tag('span',
					$this->question->fileCount, [
					'class' => ['badge', 'rounded-pill', 'bg-light', 'text-secondary',
						'bi', 'bi-paperclip', 'paperclip_bagde', 'bi_icon', 'bi_icon', 'py-0'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'К вопросу прикреплены файлы: {count}',
						['count' => $this->question->fileCount])
				]);
			}
			if (!empty($this->unseen_list[$this->question->id]['answers'])) {
				$record_count = count($this->unseen_list[$this->question->id]['answers']);
				$item_list[] = Html::tag('span',
					'+' . $record_count, [
					'class' => ['badge', 'rounded-pill', 'bg-warning', 'text-dark'],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'Количество новых ответов на вопрос: {count}',
						['count' => $record_count])
				]);
			}
		} elseif ($this->isAnswer()) {
			if ($this->model->is_helped) {
				$item_list[] = Html::tag('span',
					HtmlHelper::getIconText(Yii::t('app', 'Ответ')), [
						'class' => ['badge', 'rounded-pill', 'bg-success', 'fw-bold',
							'bi', 'bi-chat-left-text-fill', 'bi_icon', 'no_text'],
						'data-toggle' => 'tooltip',
						'title' => Yii::t('app', 'Ответ решил вопрос')
					]
				);
			} else {
				$item_list[] = Html::tag('span',
					HtmlHelper::getIconText(Yii::t('app', 'Ответ')), [
						'class' => ['badge', 'rounded-pill', 'bg-light', 'text-secondary',
							'bi', 'bi-chat-left-text', 'bi_icon', 'no_text'],
					]
				);
			}
			if ($this->model->fileCount){
				$item_list[] = Html::tag('span',
					$this->model->fileCount, [
					'class' => ['badge', 'rounded-pill', 'bg-light', 'text-secondary',
						'bi', 'bi-paperclip', 'paperclip_bagde', 'bi_icon', 'no_text', 'py-0', ],
					'data-toggle' => 'tooltip',
					'title' => Yii::t('app', 'К ответу прикреплены файлы: {count}',
						['count' => $this->model->fileCount]) ]
				);
			}
		} elseif ($this->isComment()) {
			$item_list[] = Html::tag('span',
				HtmlHelper::getIconText(Yii::t('app', 'Комментарий')), [
					'class' => ['badge', 'rounded-pill', 'bg-light', 'text-secondary',
						'bi', 'bi-chat', 'bi_icon', 'no_text'],
				]
			);
		}

		$count = count($item_list);
		return Html::ul($item_list, [
			'tag' => 'div',
			'class' => ['nav', 'nav-divider', 'small', 'my-3'],
			'item' => function($item, $index) use($count) {
				$return = $item;
				if (($index > 0) and ($index < $count - 1)) {
					$return .= HtmlHelper::circle();
				}
				return Html::tag('div', $return);
			},
		]);
	}

}
