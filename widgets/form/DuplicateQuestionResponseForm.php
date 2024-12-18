<?php
namespace app\widgets\form;

use Yii;
use yii\bootstrap5\{Html, Widget};
use kartik\form\ActiveForm;

use app\models\request\DuplicateQuestionRequest;
use app\models\helpers\HtmlHelper;


class DuplicateQuestionResponseForm extends Widget
{

	public DuplicateQuestionRequest $model;

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
		$response = new DuplicateQuestionRequest(['scenario' => DuplicateQuestionRequest::SCENARIO_RESPONSE]);
		$circle = HtmlHelper::circle();
		$question = $this->model->duplicateQuestion;

		$output = "";
		$output .= Html::beginTag('a', [
			'href' => $question->getRecordLink(),
			'class' => ['list-group-item', 'list-group-item-action']
		]);
		$output .= Html::beginTag('div', ['class' => ['d-flex w-100', 'justify-content-between']]);
			$output .= Html::tag('h5', $question->question_title, ['class' => ['mb-1']]);
		$output .= Html::endTag('div');
		$output .= Html::tag('p', $question->shortText, ['class' => ['mb-1']]);
		$output .= Html::beginTag('p', ['class' => ['w-100']]);
		$output .= Html::tag('small', Yii::t('app', 'Решений: {count}', ['count' => $question->answerHelpedCount]));
		$output .= $circle;
		$output .= Html::tag('small', Yii::t('app', 'Ответов: {count}', ['count' => $question->answerCount]));
		$output .= $circle;
		$output .= Html::tag('small', Yii::t('app', 'Подписчиков: {count}', ['count' => $question->followers]));
		if ($question->is_closed) {
			$output .= $circle;
			$output .= Html::tag('small', Yii::t('app', 'Закрыт'));
		}
		$output .= Html::endTag('p');
		$form_response = new ActiveForm([
			'action' => ['api/save/duplicate-question-response', 'id' => $this->model->request_id],
			'id' => 'duplicate-question-response-form-'.$this->model->question_id,
			'successCssClass' => '',
			'options' => ['class' => 'duplicate-question-response-form']
		]);
		echo $form_response->field($response, 'request_id')
			->hiddenInput(['value' => $this->model->request_id, 'id' => 'duplicate-question-response-id-'.$this->model->question_id])
			->label(false);

		$text = '';
		$class = '';
		if ($this->model->isAccepted()) {
			$class = 'd-none';
			$text = Yii::t('app', 'Вопрос принят');
		}
		echo Html::submitButton(Yii::t('app', 'Принять и закрыть вопрос'), [
			'name' => "question-response-accept",
			'class' => ['btn', 'btn-sm', 'btn-outline-success', 'rounded-pill', 'me-2', 'duplicate-question-response-submitbutton', $class],
			'title' => Yii::t('app', 'Принять и закрыть вопрос')
		]);

		$class = '';
		if ($this->model->isRejected()) {
			$class = 'd-none';
			$text = Yii::t('app', 'Вопрос отклонён');
		}
		$reject_title = Yii::t('app', 'Отклонить');
		if ($this->model->isAccepted() and $question->is_closed) {
			$reject_title = Yii::t('app', 'Отклонить и открыть вопрос');
		}
		echo Html::submitButton($reject_title, [
			'name' => "question-response-reject",
			'class' => ['btn', 'btn-sm', 'btn-outline-danger', 'rounded-pill', 'me-2', 'duplicate-question-response-submitbutton', $class],
			'title' => Yii::t('app', 'Отклонить и оставить вопрос открытым')
		]);

		$class = '';
		if ($this->model->isSent()) {
			$class = 'd-none';
		}
		echo Html::tag('span', $text, [
			'class' => ['btn', 'btn-sm', 'btn-outline-light', 'rounded-pill', 'text-secondary', 'request-result', $class]
		]);

		$output .= $form_response->run();
		$output .= Html::endTag('a');
		return $output;
	}

}
