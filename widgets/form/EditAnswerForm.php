<?php
namespace app\widgets\form;

use Yii;
use yii\bootstrap5\{Html, Widget};
use yii\helpers\Url;
use kartik\form\ActiveForm;
use kartik\file\FileInput;

use app\components\tuieditor\TuiEditor;
use app\models\question\Answer;


class EditAnswerForm extends Widget
{

	public Answer $model;

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
		$this->model->setEditing();

		$form_answer = new ActiveForm([
			'id' => 'edit-answer-form-'.$this->model->id,
			'action' => Url::to(['api/save/edit-answer', 'id' => $this->model->id]),
			'successCssClass' => '',
			'enableAjaxValidation' => true,
			'validationUrl' => Url::to(['api/save/edit-answer', 'id' => $this->model->id]),
			'ajaxParam' => 'validate',
			'options' => [
				'class' => ['row', 'g-3', 'save-form'],
				'enctype' => 'multipart/form-data',
			],
		]);
			/*echo $form_answer->field($this->model, 'answer_text')->textarea([
				'rows' => 5,
				'id' => 'edit-answer-form-answer_text-'.$this->model->id,
				'class' => 'answer-text',
				'placeholder' => Yii::t('app', 'Введите отредактированный текст ответа на вопрос.'),
			]);*/

			echo $form_answer->field($this->model, 'answer_text')->widget(TuiEditor::class, [
				'triggerSelector' => "[aria-controls=\"editAnswer-{$this->model->id}\"]",
				'options' => [
					'id' => 'edit-answer-form-answer_text-'.$this->model->id,
					'class' => 'answer-text',
				],
				'pluginOptions' => [
					// 'placeholder' => Yii::t('app', 'Введите отредактированный текст ответа на вопрос.'),
				]
			])->label(Yii::t('app', 'Текст Вашего ответа'));

			echo Html::beginTag('hr', ['class' => 'my-2']);

			echo Html::beginTag('div', ['id' => 'addFiles-'.$this->model->id, 'class' => ['collapse', 'mt-2']]);
				echo $form_answer->field($this->model, 'upload_files[]')->widget(FileInput::class, [
					'options' => [
						'id' => 'answer-files-'.$this->model->id,
						'class' => 'answer-files',
						'multiple' => true,
					],
					'pluginOptions' => [
						'showUpload' => false,
						'maxFileCount' => 10,
						'maxFileSize' => 10 * 1024,
						'allowedFileExtensions' => $this->model->getExtensionList(),
						'theme' => 'bs5',
					],
				]);
			echo Html::endTag('div');

			echo Html::beginTag('hr', ['class' => 'my-2']);

			if (!empty($this->model->files)) {
				echo Html::beginTag('div', ['class' => ['col-12', 'my-1']]);
				foreach ($this->model->files as $file) {
					echo Html::beginTag('div', ['class' => ['row', 'border', 'rounded', 'mx-0'], 'id' => 'file-div-'.$file->file_id]);
						echo Html::beginTag('div', ['class' => ['d-flex', 'px-0']]);
							echo Html::beginTag('div', ['class' => ['p-2', 'flex-grow-1']]);
								echo $file->getDocHtml();
								echo $form_answer->field($this->model, 'old_files[]')
									->hiddenInput(['value' => $file->file_id])->label(false);
							echo Html::endTag('div');
							echo Html::beginTag('div', ['class' => ['p-2', 'file-name']]);
								echo Html::tag('span', $file->file_user_name, ['class' => ['text-break']]);
							echo Html::endTag('div');
							echo Html::beginTag('div', ['class' => 'p-2']);
								echo Html::button(Yii::t('app', 'Удалить'), [
									'data-div-id' => 'file-div-'.$file->file_id,
									'class' => ['btn', 'btn-sm', 'btn-danger', 'save-btn', 'rounded-pill', 'btn-delete', 'px-2'],
								]);
							echo Html::endTag('div');
						echo Html::endTag('div');
					echo Html::endTag('div');
				}
				echo Html::endTag('div');
			}

			echo Html::beginTag('div', ['class' => ['d-flex', 'justify-content-end', 'flex-wrap']]);
				echo Html::beginTag('div', ['class' => ['p-2']]);
					echo Html::button(Yii::t('app', 'Отменить'), [
						'class' => ['btn', 'btn-md', 'btn-light', 'text-secondary', 'save-btn', 'rounded-pill'],
						'aria-expanded' => 'false',
						'aria-controls' => 'editAnswer-'.$this->model->id,
						'data' => [
							"bs-toggle" => "collapse",
							"bs-target" => '#editAnswer-'.$this->model->id
						],
					]);
				echo Html::endTag('div');
				echo Html::beginTag('div', ['class' => ['p-2']]);
					echo Html::button(Yii::t('app', 'Прикрепить файлы'), [
						'class'=> [
							'btn', 'btn-md', 'btn-info', 'save-btn', 'rounded-pill', 'collapsed',
							'bi', 'bi-paperclip', 'bi_icon'
						],
						'aria-expanded' => 'false',
						'aria-controls' => 'addFiles-'.$this->model->id,
						'data' => [
							"bs-toggle" => "collapse",
							"bs-target" => '#addFiles-'.$this->model->id
						],
					]);
				echo Html::endTag('div');
				echo Html::beginTag('div', ['class' => ['p-2']]);
					echo Html::submitButton(Yii::t('app', 'Изменить ответ'), [
						'name' => 'answer-edit',
						'class' => ['btn', 'btn-md', 'btn-primary', 'save-btn', 'rounded-pill'],
					]);
				echo Html::endTag('div');
			echo Html::endTag('div');
		return $form_answer->run();
	}

}
