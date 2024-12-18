<?php
namespace app\assets\theme;

use Yii;
use yii\web\AssetBundle;

class FontAwesomeAsset extends AssetBundle
{

	public $publishOptions = [
		// 'only' => [
		// 	'css/all.css',
		// ],
		// 'forceCopy' => true,
		'except' => [
			'scss/*',
			'less/*',
			'metadata/*',
			'sprites/*',
			'*.txt',
			'*.json'
		]
	];

	public $sourcePath = '@bower/font-awesome/js-packages/@fortawesome/fontawesome-free';

	public $css = [
		'css/all.css'
	];

	public $js = [
	];

	public $depends = [
	];
}
