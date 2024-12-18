<?php

namespace app\widgets\teacher;

use Yii;
use yii\bootstrap5\{Html, Widget};
use yii\helpers\Url;

use app\models\edu\Teacher;
use app\models\helpers\HtmlHelper;

use app\assets\widgets\SliderAsset;


class TeacherSlider extends Widget
{

	public $list;

	public function beforeRun()
	{
		if (!parent::beforeRun()) {
			return false;
		}
		if ($this->list) {
			foreach ($this->list as $record) {
				if (!($record instanceof Teacher)) {
					return false;
				}
				if ($record->isNewRecord) {
					return false;
				}
			}
		}
		SliderAsset::register($this->view);
		return true;
	}

	public function run()
	{
		$output = '';

		$output .= Html::beginTag('div', ['class' => ['teacher-slider-wrapper']]);
		$output .= Html::beginTag('div', [
			'id' => 'slider',
			'class' => ['slider'],
			'style' => ['display' => 'none'],
		]);
		if ($this->list) {
			foreach ($this->list as $record) {
				$output .= Html::beginTag('div');
				$output .= Html::beginTag('div', ['class' => ['feed-teacher']]);
				$output .= TeacherRecord::widget([
					'model' => $record,
				]);
				$output .= Html::endTag('div');
				$output .= Html::endTag('div');
			}
		}
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		return $output;
	}

}
