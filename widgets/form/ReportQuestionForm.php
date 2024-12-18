<?php
namespace app\widgets\form;

use Yii;
use yii\bootstrap5\{Html, Modal, Widget};
use yii\helpers\Url;
use kartik\form\ActiveForm;

use app\models\request\Report;


class ReportQuestionForm extends Widget
{

	public int $question_id;
	public array $report_types;

	public function run()
	{
		$report = new Report(['scenario' => Report::SCENARIO_QUESTION]);

		$id = 'questionReport-'.$this->question_id;
		$title = Yii::t('app', 'Выберите тип нарушения');
		$widget = new Modal([
			'options' => ['id' => $id],
			'title' => $title,
			'titleOptions' => ['class' => 'h5'],
			'centerVertical' => true,
			'scrollable' => true,
		]);

		$form_report = new ActiveForm([
			'action' => ['api/save/report-question', 'id' => $this->question_id],
			'id' => 'report-question-form-'.$this->question_id,
			'successCssClass' => '',
			'enableAjaxValidation' => true,
			'validationUrl' => ['api/save/report-question', 'id' => $this->question_id],
			'ajaxParam' => 'validate',
			'options' => ['class' => 'report-form'],
			'validationDelay' => 250
		]);
		echo $form_report->field($report, 'report_type')
			->radioList($this->report_types, [
				'id' => 'report_type-question-'.$this->question_id
			])
			->label(Yii::t('app', 'Тип нарушения'));

		echo Html::tag('hr');

		echo Html::button(Yii::t('app', 'Закрыть'), [
			'class' => ['btn', 'btn-sm', 'btn-light', 'rounded-pill', 'me-2'],
			'data-bs-dismiss' => 'modal',
		]);
		echo Html::submitButton('Отправить жалобу', [
			'name' => 'question-report-create',
			'class' => ['btn', 'btn-sm', 'btn-primary', 'rounded-pill'],
		]);
		echo $form_report->run();

		return $widget->run();
	}

}
