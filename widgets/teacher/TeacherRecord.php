<?php

namespace app\widgets\teacher;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\edu\Teacher;
use app\models\helpers\HtmlHelper;


class TeacherRecord extends Widget
{

	public Teacher $model;

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
		$output = '';

		$output .= Html::beginTag('div', ['class' => ['card', 'h-100']]);

		$output .= Html::beginTag('div', ['class' => ['card-header', 'py-1', 'px-2']]);

		$output .= Html::beginTag('div', ['class' => ['d-flex', 'align-items-center']]);
		$output .= Html::beginTag('div', ['class' => ['p-1']]);
		$output .= Html::tag('span', null, ['class' => ['bi', 'bi-mortarboard-fill']]);
		$output .= Html::endTag('div');
		$output .= Html::beginTag('div', ['class' => ['p-2']]);
		$output .= Html::a($this->model->teacher_fullname, $this->model->getRecordLink(), ['class' => ['mt-3', 'h6']]);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::endTag('div');

		$output .= Html::beginTag('div', ['class' => ['card-body', 'text-center', 'py-2']]);

		$output .= Html::beginTag('div', ['class' => ['discipline-data', 'mb-0', 'small']]);
		$output .= Html::beginTag('span', ['class' => ['my-0', 'px-1', 'text-warning', 'fw-bold']]);
		$title = HtmlHelper::getIconText(Yii::t('app', 'Вопросов:')) .
			HtmlHelper::getCountText($this->model->filter_question_count);
		$output .= Html::tag('span', $title, ['class' => ['bi', 'bi-question-circle', 'bi_icon', 'text-secondary']]);
		$output .= Html::endTag('span');

		$output .= Html::beginTag('span', ['class' => ['my-0', 'px-1', 'text-info', 'fw-bold']]);
		$title = HtmlHelper::getIconText(Yii::t('app', 'Решённых:')) .
			HtmlHelper::getCountText($this->model->filter_question_helped_count);
		$output .= Html::tag('span', $title, ['class' => ['bi', 'bi-check-circle-fill', 'bi_icon', 'text-secondary']]);
		$output .= Html::endTag('span');
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::endTag('div');

		return $output;
	}

}
