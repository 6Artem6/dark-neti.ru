<?php
namespace app\assets\theme;

use Yii;
use yii\web\AssetBundle;

class ScrollbarAsset extends AssetBundle
{

	public $publishOptions = [
		'only' => [
			'smooth-scrollbar.js'
		],
		// 'forceCopy' => true,
	];

	public $sourcePath = '@npm/smooth-scrollbar/dist/';

	public $css = [
	];

	public $js = [
		'smooth-scrollbar.js'
	];

	public $depends = [
	];
}
