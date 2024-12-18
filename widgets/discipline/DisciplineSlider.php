<?php

namespace app\widgets\discipline;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\edu\Discipline;
use app\models\helpers\HtmlHelper;

use app\assets\widgets\SliderAsset;


class DisciplineSlider extends Widget
{

	public $list;

	public function beforeRun()
	{
		if (!parent::beforeRun()) {
			return false;
		}
		if ($this->list) {
			foreach ($this->list as $record) {
				if (!($record instanceof Discipline)) {
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

		$output .= Html::beginTag('div', ['class' => ['discipline-slider-search-wrapper']]);
		$output .= Html::beginTag('div', [
			'id' => 'slider-user',
			'class' => ['slider'],
			'style' => ['display' => 'none'],
		]);
		if ($this->list) {
			foreach ($this->list as $record) {
				$output .= Html::beginTag('div');
				$output .= Html::beginTag('div', ['class' => ['feed-discipline-search']]);
				$output .= DisciplineRecord::widget([
					'model' => $record,
					'is_search' => true,
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
