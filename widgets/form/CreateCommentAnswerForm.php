<?php
namespace app\widgets\form;

use Yii;
use yii\bootstrap5\{Html, Widget};
use yii\helpers\Url;
use kartik\form\ActiveForm;

use app\models\question\Comment;


class CreateCommentAnswerForm extends Widget
{

	public int $question_id;
	public int $answer_id;

	public function run()
	{
		$comment_answer_new = new Comment(['question_id' => $this->question_id, 'scenario' => Comment::SCENARIO_CREATE_ANSWER]);

		$output = "";

		$output .= Html::beginTag("li", ["class" => 'comment-item py-1']);
		$output .= Html::beginTag("div", ["class" => 'd-flex']);
		$output .= Html::beginTag("div", ["class" => 'ms-2 w-100']);
		$output .= Html::beginTag("div", ["class" => 'bg-light p-3 rounded']);

		$form_comment = new ActiveForm([
			'id' => 'comment-answer-form-'.$this->answer_id,
			'action' => Url::to(['api/save/create-comment-answer', 'id' => $this->answer_id]),
			'successCssClass' => '',
			'enableAjaxValidation' => true,
			'validationUrl' => Url::to(['api/save/create-comment-answer', 'id' => $this->answer_id]),
			'ajaxParam' => 'validate',
			'options' => [
				'class' => ['row', 'g-3', 'save-form'],
				'enctype' => 'multipart/form-data',
			],
		]);
			echo $form_comment->field($comment_answer_new, 'comment_text')->textarea([
				'rows' => 5,
				'id' => 'comment-answer-field-'.$this->answer_id,
				'placeholder' => Yii::t('app', "Введите текст комментария к ответу."),
			])->label(Yii::t('app', 'Текст Вашего комментария'));

			echo Html::beginTag('div', ['class' => ['d-flex', 'justify-content-end', 'flex-wrap', 'mt-0']]);
				echo Html::beginTag('div', ['class' => ['p-2']]);
					echo Html::submitButton('Добавить комментарий', [
						'name' => 'comment-answer-create',
						'class' => ['btn', 'btn-md', 'btn-primary', 'save-btn', 'rounded-pill'],
					]);
				echo Html::endTag('div');
			echo Html::endTag('div');
		$output .= $form_comment->run();

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		$output .= Html::endTag('li');

		return $output;
	}

}
