<?php

namespace app\widgets\user;

use Yii;
use yii\bootstrap5\{Html, Widget};

use app\models\user\User;
use app\models\helpers\HtmlHelper;

use app\assets\widgets\SliderAsset;


class UserSlider extends Widget
{

	public $list;

	public function beforeRun()
	{
		if (!parent::beforeRun()) {
			return false;
		}
		if ($this->list) {
			foreach ($this->list as $record) {
				if (!($record instanceof User)) {
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

		$output .= Html::beginTag('div', ['class' => ['user-slider-wrapper', 'p-2']]);
		$output .= Html::beginTag('div', [
			'id' => 'slider-user',
			'class' => ['slider'],
			'style' => ['display' => 'none'],
		]);
		if ($this->list) {
			foreach ($this->list as $record) {
				$output .= Html::beginTag('div');
				$output .= UserRecord::widget([
					'model' => $record,
					'show_info' => false,
				]);
				$output .= Html::endTag('div');
			}
		}
		$output .= Html::beginTag('div');
		$output .= Html::beginTag('div', [
			'class' => [
				'card', 'border', 'border-2', 'border-dashed', 'shadow-none',
				'd-flex', 'align-items-center', 'justify-content-center', 'text-center',
				'feed-user'
			]
		]);
		$output .= Html::a(
			Html::tag('i', null, ['class' => ['fa-solid', 'fa-plus']]),
			['/user'],
			['class' => ['stretched-link', 'btn', 'btn-light', 'rounded-circle', 'icon-md']]
		);
		$output .= Html::tag('h6', Yii::t('app', 'Все<br>пользователи'), ['class' => ['mt-2', 'mb-0', 'small']]);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		$output .= Html::endTag('div');
		$output .= Html::endTag('div');
		return $output;
	}

}
