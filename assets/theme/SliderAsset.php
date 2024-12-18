<?php
namespace app\assets\theme;

use Yii;
use yii\web\AssetBundle;

class SliderAsset extends AssetBundle
{

	public $publishOptions = [
		'only' => [
			'slick.css',
			'slick-theme.css',
			'slick.min.js',
			'*.gif',
			'fonts/*',
		],
		'forceCopy' => true,
	];

	public $sourcePath = '@npm/slick-slider/slick';

	public $css = [
		'slick.css',
		'slick-theme.css',
	];

	public $js = [
		'slick.min.js'
	];

	public $depends = [
	];
}
