<?php
namespace app\widgets\form;

use Yii;
use yii\bootstrap5\{Html, Widget};
use yii\helpers\Url;
use kartik\form\ActiveForm;
use kartik\file\FileInput;

use app\models\question\Comment;


class EditCommentForm extends Widget
{

	public Comment $model;

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

		$form_comment = new ActiveForm([
			'id' => 'edit-comment-form-'.$this->model->id,
			'action' => Url::to(['api/save/edit-comment', 'id' => $this->model->id]),
			'successCssClass' => '',
			'enableAjaxValidation' => true,
			'validationUrl' => Url::to(['api/save/edit-comment', 'id' => $this->model->id]),
			'ajaxParam' => 'validate',
			'options' => [
				'class' => ['row', 'g-3', 'save-form'],
				'enctype' => 'multipart/form-data',
			],
		]);
			echo $form_comment->field($this->model, 'comment_text')->textarea([
				'rows' => 5,
				'id' => 'edit-comment-field-comment_text-'.$this->model->id,
				'placeholder' => Yii::t('app', "Введите отредактированный текст комментария."),
			]);

			echo Html::beginTag('div', ['class' => ['d-flex', 'justify-content-end', 'flex-wrap', 'mt-0']]);
				echo Html::beginTag('div', ['class' => ['p-2']]);
					echo Html::button(Yii::t('app', 'Отменить'), [
						'class' => ['btn', 'btn-md', 'btn-light', 'text-secondary', 'save-btn', 'rounded-pill'],
						"aria-expanded" => "false",
						"aria-controls" => "editComment-".$this->model->id,
						'data' => [
							"bs-toggle" => "collapse",
							"bs-target" => "#editComment-".$this->model->id,
						]
					]);
				echo Html::endTag('div');
				echo Html::beginTag('div', ['class' => ['p-2']]);
					echo Html::submitButton(Yii::t('app', 'Изменить комментарий'), [
						'name' => 'comment-edit',
						'class' => ['btn', 'btn-md', 'btn-primary', 'save-btn', 'rounded-pill'],
					]);
				echo Html::endTag('div');
			echo Html::endTag('div');
		return $form_comment->run();
	}

}
