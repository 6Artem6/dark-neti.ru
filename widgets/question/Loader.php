<?php

namespace app\widgets\question;

use Yii;
use yii\bootstrap5\{Html, Widget};


class Loader extends Widget
{

	public function run()
	{
		$output = "";

		$output .= Html::beginTag('div', ['class' => ['div-loader']]);
		$output .= Html::beginTag('div', ['class' => ['load-icon']]);
		$output .= Html::tag('div', null, ['class' => ['spinner-grow', 'spinner-grow-sm']]);
		$output .= Html::endTag('div');
		$output .= Html::endTag('div');

		return $output;
	}
}
