<?php
namespace app\assets\theme;

use Yii;
use yii\web\AssetBundle;

class BootstrapAsset extends AssetBundle
{

	public $publishOptions = [
		'only' => [
			'dist/css/bootstrap.css',
			'dist/js/bootstrap.bundle.js',
		],
		// 'forceCopy' => true,
	];

	public $sourcePath = '@bower/bootstrap';

	public $css = [
		'dist/css/bootstrap.css',
	];

	public $js = [
		'dist/js/bootstrap.bundle.js'
	];

	public $depends = [
		\yii\bootstrap5\BootstrapAsset::class
	];
}
