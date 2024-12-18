<?php
namespace app\widgets\form;

use Yii;
use yii\bootstrap5\{Html, Modal, Widget};
use kartik\form\ActiveForm;

use app\models\request\DuplicateQuestionRequest;


class DuplicateQuestionRequestModalForm extends Widget
{

	public int $question_id;

	public function run()
	{
		$request = new DuplicateQuestionRequest(['scenario' => DuplicateQuestionRequest::SCENARIO_REQUEST]);

		$output = "";
		$id = 'duplicateQuestionRequestForm-'.$this->question_id;
		$response_id = 'duplicate-question-request-list-body-'.$this->question_id;
		$title = Yii::t('app', 'Выберите имеющийся решённый вопрос');
		$widget = new Modal([
			'options' => ['id' => $id],
			'title' => $title,
			'titleOptions' => ['class' => 'h5'],
			'footer' => $this->renderFooter(),
			'centerVertical' => true,
			'scrollable' => true,
		]);
		$form_search = new ActiveForm([
			'action' => ['api/list/duplicate-question-request'],
			'id' => 'duplicate-question-request-form-'.$this->question_id,
			'successCssClass' => '',
			'options' => ['class' => 'duplicate-question-request'],
			'validationDelay' => 250
		]);
		echo $form_search->field($request, 'question_text')
			->textInput([
				'id' => 'duplicate-question-request-q-'.$this->question_id,
				'name' => 'duplicate-question-request-q-'.$this->question_id,
				'class' => 'duplicate-question-request-q',
			])
			->label(Yii::t('app', 'Текст вопроса или ссылка на вопрос'));
		echo $form_search->field($request, 'question_id')
			->hiddenInput([
				'value' => $this->question_id,
				'id' => 'duplicate-question-request-id-'.$this->question_id,
				'name' => 'duplicate-question-request-id-'.$this->question_id,
				'class' => 'duplicate-question-request-id',
			])
			->label(false);
		echo $form_search->run();

		echo Html::tag('div', null, [
			'id' => $response_id,
			'class' => ['list-group', 'scroll-body'],
			'style' => ['overflow-y' => 'hidden']
		]);

		return $widget->run();
	}

	protected function renderFooter()
	{
		return Html::button(Yii::t('app', 'Закрыть'), [
			'class' => ['btn', 'btn-light', 'rounded-pill'],
			'data-bs-dismiss' => 'modal',
		]);
	}

}
