<?php
namespace app\widgets\form;

use Yii;
use yii\bootstrap5\{Html, Modal, Widget};
use yii\helpers\Url;
use kartik\form\ActiveForm;

use app\models\request\Report;


class ReportAnswerForm extends Widget
{

	public int $answer_id;
	public array $report_types;

	public function run()
	{
		$report = new Report(['scenario' => Report::SCENARIO_ANSWER]);

		$id = 'answerReport-'.$this->answer_id;
		$title = Yii::t('app', 'Выберите тип нарушения');
		$widget = new Modal([
			'options' => ['id' => $id],
			'title' => $title,
			'titleOptions' => ['class' => 'h5'],
			'centerVertical' => true,
			'scrollable' => true,
		]);

		$form_report = new ActiveForm([
			'action' => Url::to(['api/save/report-answer', 'id' => $this->answer_id]),
			'id' => 'report-answer-form-'.$this->answer_id,
			'successCssClass' => '',
			'enableAjaxValidation' => true,
			'validationUrl' => Url::to(['api/save/report-answer', 'id' => $this->answer_id]),
			'ajaxParam' => 'validate',
			'options' => ['class' => 'report-form'],
			'validationDelay' => 250
		]);
		echo $form_report->field($report, 'report_type')
			->radioList($this->report_types, [
				'id' => 'report_type-answer-'.$this->answer_id
			])
			->label(Yii::t('app', 'Тип нарушения'));

		echo Html::tag('hr');

		echo Html::button(Yii::t('app', 'Закрыть'), [
			'class' => ['btn', 'btn-sm', 'btn-light', 'rounded-pill', 'me-2'],
			'data-bs-dismiss' => 'modal',
		]);
		echo Html::submitButton('Отправить жалобу', [
			'name' => 'answer-report-create',
			'class' => ['btn', 'btn-sm', 'btn-primary', 'rounded-pill'],
		]);
		echo $form_report->run();

		return $widget->run();
	}

}
