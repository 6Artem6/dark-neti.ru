<?php
namespace app\widgets\form;

use Yii;
use yii\bootstrap5\{Html, Widget};
use yii\helpers\Url;
use kartik\form\ActiveForm;
use kartik\file\FileInput;

use app\components\tuieditor\TuiEditor;
use app\models\question\Answer;


class CreateAnswerForm extends Widget
{

	public int $question_id;
	public int $answer_count;

	public function run()
	{
		$answer_new = new Answer(['question_id' => $this->question_id, 'scenario' => Answer::SCENARIO_CREATE]);

		$placeholder = Yii::t('app', 'Введите текст ответа на вопрос.');
		if ($this->answer_count) {
			$placeholder .= "\n" . Yii::t('app', 'Не забудьте проверить все ответы на случай, если его уже написали.');
		}

		$form_answer = new ActiveForm([
			'id' => 'answer-form',
			'action' => Url::to(['api/save/create-answer', 'id' => $this->question_id]),
			'successCssClass' => '',
			'enableAjaxValidation' => true,
			'validationUrl' => Url::to(['api/save/create-answer', 'id' => $this->question_id]),
			'ajaxParam' => 'validate',
			'options' => [
				'class' => ['row', 'g-3', 'save-form'],
				'enctype' => 'multipart/form-data',
			],
		]);
			/*echo $form_answer->field($answer_new, 'answer_text')->textarea([
				'rows' => 5,
				'id' => 'answer-form-answer_text',
				'class' => 'answer-text',
				'placeholder' => $placeholder,
			])->label(Yii::t('app', 'Текст Вашего ответа'));*/

			echo $form_answer->field($answer_new, 'answer_text')->widget(TuiEditor::class, [
				'options' => [
					'id' => 'answer-form-answer_text',
					'class' => 'answer-text',
				],
				'pluginOptions' => [
					// 'placeholder' => $placeholder,
				]
			])->label(Yii::t('app', 'Текст Вашего ответа'));

			echo Html::beginTag('hr', ['class' => 'my-2']);

			echo Html::beginTag('div', ['id' => 'addFiles', 'class' => ['collapse', 'mt-2']]);
				echo $form_answer->field($answer_new, 'upload_files[]')->widget(FileInput::class, [
					'options' => [
						'id' => 'answer-files',
						'class' => 'answer-files',
						'multiple' => true,
					],
					'pluginOptions' => [
						'showUpload' => false,
						'maxFileCount' => 10,
						'maxFileSize' => 10 * 1024,
						'allowedFileExtensions' => $answer_new->getExtensionList(),
						'theme' => 'bs5',
					],
				]);
			echo Html::endTag('div');

			echo Html::beginTag('hr', ['class' => 'my-2']);

			echo Html::beginTag('div', ['class' => ['d-flex', 'justify-content-end', 'flex-wrap']]);
				echo Html::beginTag('div', ['class' => ['p-2']]);
					echo Html::button(Yii::t('app', 'Прикрепить файлы'), [
						'class'=> [
							'btn', 'btn-md', 'btn-info', 'save-btn', 'rounded-pill', 'collapsed',
							'bi', 'bi-paperclip', 'bi_icon'
						],
						'aria-expanded' => 'false',
						'aria-controls' => 'addFiles',
						'data' => [
							"bs-toggle" => "collapse",
							"bs-target" => "#addFiles",
						],
					]);
				echo Html::endTag('div');
				echo Html::beginTag('div', ['class' => ['p-2']]);
					echo Html::submitButton('Отправить ответ', [
						'name' => 'answer-create',
						'class' => ['btn', 'btn-md', 'btn-primary', 'save-btn', 'rounded-pill'],
					]);
				echo Html::endTag('div');
			echo Html::endTag('div');

		return $form_answer->run();
	}

}
