<?php
namespace app\assets\theme;

use Yii;
use yii\web\AssetBundle;

class SpotlightAsset extends AssetBundle
{

	public $publishOptions = [
		'only' => [
			'css/shepherd.css',
			'js/shepherd.js',
			'js/shepherd.js.map'
		],
		'forceCopy' => true,
	];

	public $sourcePath = '@npm/shepherd.js/dist';

	public $css = [
		'css/shepherd.css',
	];

	public $js = [
		'js/shepherd.js'
	];

	public $depends = [
	];
}
