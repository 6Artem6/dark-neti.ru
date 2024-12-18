<?php
namespace app\widgets\form;

use Yii;
use yii\bootstrap5\{Html, Modal, Widget};
use kartik\form\ActiveForm;
use kartik\file\FileInput;

use app\models\request\DuplicateQuestionRequest;


class DuplicateQuestionResponseModalForm extends Widget
{

	public int $question_id;

	public function run()
	{
		$id = 'duplicateQuestionResponseForm-'.$this->question_id;
		$response_id = 'duplicate-question-response-list-body-'.$this->question_id;
		$title = Yii::t('app', 'Выберите имеющийся вопрос');
		$widget = new Modal([
			'options' => ['id' => $id],
			'title' => $title,
			'titleOptions' => ['class' => 'h5'],
			'footer' => $this->renderFooter(),
			'centerVertical' => true,
			'scrollable' => true,
		]);
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
