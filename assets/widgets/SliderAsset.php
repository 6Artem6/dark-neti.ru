<?php

namespace app\assets\widgets;

use Yii;
use app\assets\BaseAsset;


class SliderAsset extends BaseAsset
{

	public $publishOptions = ['forceCopy' => true];

	public $sourcePath = '@app/assets/widgets_assets/slider';

	public $css = [
	];

	public $js = [
		'js/slider.js',
	];

	public $depends = [
		\yii\web\YiiAsset::class,
	];
}
