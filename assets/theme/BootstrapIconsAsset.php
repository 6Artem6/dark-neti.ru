<?php
namespace app\assets\theme;

use Yii;
use yii\web\AssetBundle;

class BootstrapIconsAsset extends AssetBundle
{

	public $publishOptions = [
		// 'only' => [
		// 	'font/bootstrap-icons.css'
		// ],
		// 'forceCopy' => true,
		'except' => [
			'*.md',
			'*.yml',
			'*.json'
		]
	];

	public $sourcePath = '@vendor/twbs/bootstrap-icons';

	public $css = [
		'font/bootstrap-icons.css'
	];

	public $js = [
	];

	public $depends = [
	];
}
