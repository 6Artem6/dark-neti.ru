<?php
namespace app\assets\theme;

use Yii;
use yii\web\AssetBundle;

class ImageViewerAsset extends AssetBundle
{

	public $publishOptions = [
		'only' => [
			'viewer.css',
			'viewer.min.css',
			'viewer.js',
			'viewer.min.js'
		],
		// 'forceCopy' => true,
	];

	public $sourcePath = '@npm/imageviewer/dist';

	public $css = [
		YII_DEBUG ? 'viewer.css' : 'viewer.min.css',
	];

	public $js = [
		YII_DEBUG ? 'viewer.js' : 'viewer.min.js'
	];

	public $depends = [
	];
}
