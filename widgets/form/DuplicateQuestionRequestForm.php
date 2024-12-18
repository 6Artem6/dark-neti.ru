<?php
namespace app\widgets\form;

use Yii;
use yii\bootstrap5\{Html, Widget};
use kartik\form\ActiveForm;

use app\models\question\Question;
use app\models\request\DuplicateQuestionRequest;
use app\models\helpers\HtmlHelper;


class DuplicateQuestionRequestForm extends Widget
{

	public Question $model;
	public int $question_id;
	public bool $is_reported;

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
		$request = new DuplicateQuestionRequest(['scenario' => DuplicateQuestionRequest::SCENARIO_REQUEST]);
		$circle = HtmlHelper::circle();

		$output = "";
		$output .= Html::beginTag('a', [
			'href' => $this->model->getRecordLink(),
			'class' => ['list-group-item', 'list-group-item-action']
		]);
		$output .= Html::beginTag('div', ['class' => ['d-flex w-100', 'justify-content-between']]);
			$output .= Html::tag('h5', $this->model->question_title, ['class' => ['mb-1']]);
		$output .= Html::endTag('div');
		$output .= Html::tag('p', $this->model->shortText, ['class' => ['mb-1']]);
		$output .= Html::beginTag('p', ['class' => ['w-100']]);
		$output .= Html::tag('small', Yii::t('app', 'Решенён: {is}', ['is' => $this->model->is_helped ? Yii::t('app', 'Да') : Yii::t('app', 'Нет')]));
		$output .= $circle;
		$output .= Html::tag('small', Yii::t('app', 'Ответов: {count}', ['count' => $this->model->answer_count]));
		$output .= $circle;
		$output .= Html::tag('small', Yii::t('app', 'Подписчиков: {count}', ['count' => $this->model->followers]));
		if ($this->model->is_closed) {
			$output .= $circle;
			$output .= Html::tag('small', Yii::t('app', 'Закрыт'));
		}
		$output .= Html::endTag('p');
		if ($this->is_reported) {
			$output .= Html::tag('p', Yii::t('app', 'Вопрос уже был предложен'), ['class' => ['mb-1', 'h6', 'text-success']]);
		} else {
			$form_request = new ActiveForm([
				'action' => ['api/save/duplicate-question-request', 'id' => $this->question_id],
				'id' => 'duplicate-question-request-form-'.$this->question_id,
				'successCssClass' => '',
				'options' => ['class' => 'duplicate-question-request-form']
			]);
				echo $form_request->field($request, 'duplicate_question_id')
					->hiddenInput(['value' => $this->model->id, 'id' => 'duplicate-question-id-'.$this->question_id])
					->label(false);
				echo Html::submitButton(Yii::t('app', 'Предложить вопрос'), [
					'name' => "duplicate-question-request",
					'class' => ['btn', 'btn-sm', 'btn-outline-primary', 'rounded-pill', 'duplicate-question-request-submitbutton']
				]);
			$output .= $form_request->run();
		}
		$output .= Html::endTag('a');
		return $output;
	}

}
