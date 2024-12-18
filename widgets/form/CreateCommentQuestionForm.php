<?php
namespace app\widgets\form;

use Yii;
use yii\bootstrap5\{Html, Widget};
use yii\helpers\Url;
use kartik\form\ActiveForm;

use app\models\question\Comment;


class CreateCommentQuestionForm extends Widget
{

	public int $question_id;

	public function run()
	{
		$comment_question_new = new Comment(['question_id' => $this->question_id, 'scenario' => Comment::SCENARIO_CREATE_QUESTION]);

		$output = "";

		$output .= Html::beginTag("li", ["class" => 'comment-item py-1']);
		$output .= Html::beginTag("div", ["class" => 'd-flex']);
		$output .= Html::beginTag("div", ["class" => 'ms-2 w-100']);
		$output .= Html::beginTag("div", ["class" => 'bg-light p-3 rounded']);

		$form_comment = new ActiveForm([
			'id' => 'comment-question-form',
			'action' => Url::to(['api/save/create-comment-question', 'id' => $this->question_id]),
			'successCssClass' => '',
			'enableAjaxValidation' => true,
			'validationUrl' => Url::to(['api/save/create-comment-question', 'id' => $this->question_id]),
			'ajaxParam' => 'validate',
			'options' => [
				'class' => ['row', 'g-3', 'save-form'],
				'enctype' => 'multipart/form-data',
			],
		]);
			echo $form_comment->field($comment_question_new, 'comment_text')->textarea([
				'rows' => 5,
				'id' => 'comment-question-field',
				'placeholder' => Yii::t('app', "Введите текст комментария к вопросу.") . "\n" .
								Yii::t('app', "Помните, что комментарий НЕ является ответом."),
			])->label(Yii::t('app', 'Текст Вашего комментария'));

			echo Html::beginTag('div', ['class' => ['d-flex', 'justify-content-end', 'flex-wrap', 'ps-0', 'mt-0']]);
				echo Html::beginTag('div', ['class' => ['p-2']]);
					echo Html::submitButton(Yii::t('app', 'Добавить комментарий'), [
						'name' => 'comment-question-create',
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
