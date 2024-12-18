<?php

namespace app\widgets\question;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\assets\actions\QuestionAnswerAsset;

use app\models\question\{Question, Answer, Comment};
use app\models\request\ReportType;
use app\models\helpers\HtmlHelper;

use app\widgets\form\{
	CreateCommentQuestionForm,
	CreateCommentAnswerForm,
	CreateAnswerForm
};


class AnswersAndCommentsRecord extends Widget
{

	public Question $model;
	public array $unseen_list;
	public array $report_types;

	public function beforeRun()
	{
		if (!parent::beforeRun()) {
			return false;
		}
		if ($this->model->isNewRecord) {
			return false;
		}
		QuestionAnswerAsset::register($this->view);
		return true;
	}

	public function run()
	{
		$output = '';

		$output .=  Html::beginTag('div', [
			'id' => 'comments',
			'class' => ['collapse'],
		]);
		$output .= Html::beginTag('div', ['class' => ['card', 'card-body', 'question-body']]);
		$output .= Html::beginTag('ul', ['class' => 'comment-wrap list-unstyled mb-4']);
		$output .= Html::beginTag('div', ['class' => ['question-comment-list']]);
		/*if ($comments = $this->model->questionComments) {
			$output .= Html::beginTag('div', ['class' => ['row']]);
			$output .= Html::tag('div',
				Yii::t('app', 'Комментарии ({count}):', ['count' => HtmlHelper::getCountText(count($comments))]),
				['class' => ['mt-0', 'ms-3', 'h5']]
			);
			$output .= Html::endTag('div');

			foreach ($comments as $comment) {
				$is_unseen = in_array($comment->id, $this->unseen_list['comments']);
				$output .= CommentBody::widget([
					'model' => $comment,
					'report_types' => $this->report_types[ReportType::TYPE_COMMENT],
					'is_feed' => false,
					'is_unseen' => $is_unseen,
				]);
			}
		} else {
			$output .= Html::beginTag('div', ['class' => 'row mt-0']);
			$output .= Html::tag('div',
				Yii::t('app', 'Комментариев пока нет.'),
				['class' => 'h5 text-center']
			);
			$output .= Html::endTag('div');
		}*/
		$output .= Html::endTag('div');
		$output .= Html::beginTag('div', ['class' => ['question-comment-create']]);
		$output .= Loader::widget();
		if ($message = (new Comment)->canCreate()) {
			$output .= $message;
		} elseif ($this->model->is_hidden) {
			$output .= Html::beginTag('div', ['class' => 'p-3 bg-light rounded']);
			$output .= Html::tag('h5', Yii::t('app', 'Вопрос скрыт.'), ['class' => ['mt-0', 'text-center']]);
			$output .= Html::tag('h5', Yii::t('app', 'Комментарии временно нельзя отправлять.'), ['class' => ['mt-0', 'text-center']]);
			$output .= Html::endTag('div');
		} else {
			$output .= CreateCommentQuestionForm::widget([
				'question_id' => $this->model->id
			]);
		}
		$output .= Html::endTag('ul');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['card', 'question-body']]);
		$output .= Html::beginTag('div', [
			'id' => 'allAnswers',
			'class' => 'card-body px-0'
		]);
		$output .= Html::beginTag('div', ['class' => 'container-fluid']);
		if ($this->model->answer_count) {
			if ($answers = $this->model->answerHelpedList) {
				$output .= Html::beginTag('div', [
					'id' => 'solves',
					'class' => ['row']
				]);
				$text = Yii::t('app', 'Решения ({count}):', ['count' => HtmlHelper::getCountText(count($answers))]);
				$output .= Html::tag('div', $text, ['class' => ['mt-0', 'ms-3', 'h5']]);

				$output .= Html::beginTag('ul', ['class' => ['answer-list', 'comment-wrap', 'list-unstyled']]);
				foreach ($answers as $k => $answer) {
					if ($k == 25) break;
					$is_unseen = in_array($answer->id, $this->unseen_list['answers']);
					$is_edited = in_array($answer->id, $this->unseen_list['edited']);
					$output .= AnswerBody::widget([
						'model' => $answer,
						'report_types' => $this->report_types[ReportType::TYPE_ANSWER],
						'unseen_comment_list' => $this->unseen_list['comments'] ?? [],
						'is_feed' => false,
						'is_unseen' => $is_unseen,
						'is_edited' => $is_edited,
					]);
				}
				$output .= Html::endTag('ul');
				$output .= Loader::widget();
				$output .= Html::endTag('div');
			}
			if ($answers = $this->model->answerNotHelpedList) {
				$output .= Html::beginTag('div', [
					'id' => 'answers',
					'class' => ['row']
				]);
				$text = Yii::t('app', 'Ответы ({count}):', ['count' => HtmlHelper::getCountText(count($answers))]);
				$output .= Html::tag('div', $text, ['class' => ['mt-0', 'ms-3', 'h5']]);
				$output .= Html::beginTag('ul', ['class' => ['answer-list', 'comment-wrap', 'list-unstyled']]);
				foreach ($answers as $k => $answer) {
					if ($k == 25) break;
					$is_unseen = in_array($answer->id, $this->unseen_list['answers']);
					$is_edited = in_array($answer->id, $this->unseen_list['edited']);
					$output .= AnswerBody::widget([
						'model' => $answer,
						'report_types' => $this->report_types[ReportType::TYPE_ANSWER],
						'unseen_comment_list' => $this->unseen_list['comments'] ?? [],
						'is_feed' => false,
						'is_unseen' => $is_unseen,
						'is_edited' => $is_edited,
					]);
				}
				$output .= Html::endTag('ul');
				$output .= Loader::widget();
				$output .= Html::endTag('div');
			}
		} else {
			$text = Yii::t('app', 'Ответов пока нет.');
			if (!$this->model->isAuthor) {
				$text .= ' ' . Yii::t('app', 'Будьте первыми!');
			}
			$output .= Html::beginTag('div', ['class' => 'row mt-0']);
			$output .= Html::tag('div', $text, ['class' => 'h5 text-center']);
			$output .= Html::endTag('div');
		}

		$output .= Html::beginTag('div', ['class' => 'row my-2']);
		$output .= Html::beginTag('div', ['class' => 'col-12']);

		$output .= Html::beginTag('hr', ['class' => 'my-4']);
		if (!$this->model->is_closed) {
			$answer_new = new Answer(['question_id' => $this->model->id, 'scenario' => Answer::SCENARIO_CREATE]);
			if ($message = $answer_new->canCreate()) {
				$output .= Html::beginTag('div', ['class' => ['bg-light', 'rounded', 'text-center', 'p-3']]);
					$output .= Html::tag('h5', $message, ['class' => ['mt-0']]);
				$output .= Html::endTag('div');
			} elseif ($this->model->is_hidden) {
				$output .= Html::beginTag('div', ['class' => ['bg-light', 'rounded', 'text-center', 'p-3']]);
					$output .= Html::tag('h5', Yii::t('app', 'Вопрос скрыт.'), ['class' => ['mt-0', 'text-center']]);
					$output .= Html::tag('h5', Yii::t('app', 'Ответы временно нельзя отправлять.'), ['class' => ['mt-0', 'text-center']]);
				$output .= Html::endTag('div');
			} else {
				$output .= CreateAnswerForm::widget([
					'question_id' => $this->model->id,
					'answer_count' => $this->model->answer_count
				]);
			}
		} else {
			$output .= Html::beginTag('div', ['class' => ['bg-light', 'rounded', 'text-center', 'p-3', 'mb-3']]);
			$output .= Html::tag('h5', Yii::t('app', 'Вопрос закрыт. Ответы больше не принимаются.'), ['class' => 'mt-0']);
			$output .= Html::tag('h6', Yii::t('app', 'Но вы можете оставить комментарий к вопросу.'), ['class' => 'mt-0 text-secondary']);
			$output .= Html::endTag('div');
		}

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		return $output;
	}
}
